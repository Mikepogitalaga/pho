<?php

namespace App\Http\Controllers;

use App\Models\Coordinator;
use App\Models\Facility;
use App\Models\Item;
use App\Models\Pas;
use App\Models\PasItem;
use App\Models\Program;
use App\Models\ReceivingItem;
use App\Models\Supplier;
use App\Traits\GeneratesCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PasController extends Controller
{
    use GeneratesCodes;

    public function index(Request $request)
    {
        $query = Pas::with(['supplier', 'release', 'requester'])->latest('date_of_pass');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('pas_number', 'like', '%'.$search.'%')
                    ->orWhere('facility_coordinator', 'like', '%'.$search.'%')
                    ->orWhere('program', 'like', '%'.$search.'%')
                    ->orWhere('purpose_activity', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%');
            });
        }

        $status = $request->input('status');
        if (! empty($status)) {
            $query->where('status', $status);
        }

        $user = auth()->user();
        $userProgramScoped = false;

        if ($user) {
            $programIds = $user->all_programs;

            if ($programIds->isNotEmpty()) {
                $programNames = Program::whereIn('id', $programIds)
                    ->pluck('name')
                    ->unique()
                    ->values()
                    ->toArray();

                if (! empty($programNames)) {
                    $userProgramScoped = true;
                    $query->whereIn('program', $programNames);
                }
            }
        }

        $perPage = (int) $request->query('per_page', 15);

        if ($perPage <= 0) {
            $perPage = PHP_INT_MAX;
        }

        $slips = $query->paginate($perPage)->withQueryString();

        return view('pas.index', compact('slips', 'userProgramScoped'));
    }

    public function create()
    {
        $itemsQuery = Item::with(['receivingItems', 'releaseItems.release'])->orderBy('name');
        $user = auth()->user();

        if ($user && ! $user->isAdmin() && $user->program_id) {
            $programIds = $user->all_programs;
            if ($programIds->isNotEmpty()) {
                $programNames = Program::whereIn('id', $programIds)->pluck('name')->unique()->values()->toArray();
                if (! empty($programNames)) {
                    $itemsQuery->whereIn('stock_keeping_unit', $programNames);
                }
            }
        }

        $items = $itemsQuery->get()->each(fn($i) => $i->attachCodeAvailability());
        $coordinators = Coordinator::with('programs')->orderBy('full_name')->get();
        $facilities = Facility::active()->orderBy('category')->orderBy('name')->get(['name', 'category']);

        $user = auth()->user();
        if ($user && $user->program_id) {
            $programs = Program::whereIn('id', $user->all_programs)->orderBy('name')->get();
        } else {
            $programs = Program::orderBy('name')->get();
        }

        $itemLotNumbers = ReceivingItem::select('item_id', 'lot_number', 'expiry_date')
            ->whereNotNull('lot_number')
            ->whereIn('item_id', $items->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('item_id')
            ->map(fn ($g) => [
                'lot_number' => $g->first()->lot_number,
                'expiry_date' => $g->first()->expiry_date?->format('Y-m-d'),
            ]);

        $year = now()->format('Y');
        $month = now()->format('m');
        $nextSeq = $this->nextYearSequence(Pas::class, 'pas_number', "PAS-{$year}-{$month}-%");
        $pasNumber = "PAS-{$year}-{$month}-{$nextSeq}";

        return view('pas.create', compact('items', 'coordinators', 'programs', 'pasNumber', 'itemLotNumbers', 'facilities'));
    }

    public function store(Request $request)
    {
        $rules = [
            'date_of_pass' => 'required|date',
            'date_released' => 'nullable|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purpose_activity' => 'nullable|string|max:500',
            'reason_for_transfer' => 'nullable|string',
            'facility_name' => 'required|string|max:255',
            'facility_coordinator' => 'required|string|max:255',
            'program' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_description' => 'required|string|max:1000',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:100',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ];

        if (! auth()->user()?->program_id) {
            $rules['pas_number'] = ['required', 'string', 'max:255', 'unique:property_allocation_slips,pas_number'];
            $rules['transfer_type'] = ['required', 'string', 'in:PTR,ITR,RIS'];
        }

        $request->validate($rules);

        try {
            DB::transaction(function () use ($request) {
                $year = now()->format('Y');
                $month = now()->format('m');

                if (! auth()->user()?->program_id) {
                    $pasNumber = $request->input('pas_number');
                } else {
                    $pasNumber = null;
                }

                $data = [
                    'pas_number' => $pasNumber,
                    'date_of_pass' => $request->input('date_of_pass'),
                    'date_released' => $request->input('date_released'),
                    'supplier_id' => $request->input('supplier_id'),
                    'purpose_activity' => $request->input('purpose_activity'),
                    'reason_for_transfer' => $request->input('reason_for_transfer'),
                    'facility_name' => $request->input('facility_name'),
                    'facility_coordinator' => $request->input('facility_coordinator'),
                    'transfer_type' => auth()->user()?->program_id ? 'PTR' : $request->input('transfer_type'),
                    'program' => $request->input('program'),
                    'status' => 'Pending',
                    'notes' => $request->input('notes'),
                    'requested_by' => auth()->id(),
                    'request_status' => 'pending_approval',
                ];

                $pas = Pas::create($data);

                $yy = now()->format('y');
                $mm = now()->format('m');
                $productSeq = (int) $this->nextYearSequence(PasItem::class, 'product_code', "PC{$yy}{$mm}%");

                foreach ($request->input('items') as $row) {
                    $qty = (int) $row['quantity'];
                    $unitCost = (float) $row['unit_cost'];

                    PasItem::create([
                        'pas_id' => $pas->id,
                        'item_id' => $row['item_id'] ?? null,
                        'item_description' => $row['item_description'],
                        'product_code' => 'PC'.$yy.$mm.str_pad($productSeq++, 4, '0', STR_PAD_LEFT),
                        'lot_number' => $row['lot_number'] ?? null,
                        'expiration_date' => ! empty($row['expiration_date']) ? $row['expiration_date'] : null,
                        'quantity' => $qty,
                        'unit' => $row['unit'],
                        'unit_cost' => $unitCost,
                        'total_cost' => $qty * $unitCost,
                    ]);
                }
            });

            return redirect()->route('pas.index')->with('success', 'Property Allocation Slip saved successfully.');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function view(Pas $pas)
    {
        $pas->load(['items.item', 'supplier', 'release']);

        return view('pas.view', compact('pas'));
    }

    public function edit(Pas $pas)
    {
        $pas->load(['items.item', 'supplier', 'release']);

        $itemsQuery = Item::with(['receivingItems', 'releaseItems.release'])->orderBy('name');
        $user = auth()->user();

        if ($user && ! $user->isAdmin() && $user->program_id) {
            $programIds = $user->all_programs;
            if ($programIds->isNotEmpty()) {
                $programNames = Program::whereIn('id', $programIds)->pluck('name')->unique()->values()->toArray();
                if (! empty($programNames)) {
                    $itemsQuery->whereIn('stock_keeping_unit', $programNames);
                }
            }
        }

        $items = $itemsQuery->get()->each(fn($i) => $i->attachCodeAvailability());
        $suppliers = Supplier::orderBy('company_name')->get();
        $coordinators = Coordinator::with('programs')->orderBy('full_name')->get();
        $programs = Program::orderBy('name')->get();
        $facilities = Facility::active()->orderBy('category')->orderBy('name')->get(['name', 'category']);

        $itemLotNumbers = ReceivingItem::select('item_id', 'lot_number', 'expiry_date')
            ->whereNotNull('lot_number')
            ->whereIn('item_id', $items->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('item_id')
            ->map(fn ($g) => [
                'lot_number' => $g->first()->lot_number,
                'expiry_date' => $g->first()->expiry_date?->format('Y-m-d'),
            ]);

        $nextSeq = (int) $this->nextYearSequence(PasItem::class, 'product_code', 'PC'.now()->format('ym').'%');

        return view('pas.edit', compact('pas', 'items', 'suppliers', 'coordinators', 'programs', 'facilities', 'itemLotNumbers', 'nextSeq'));
    }

    public function update(Request $request, Pas $pas)
    {
        $rules = [
            'date_of_pass' => 'required|date',
            'date_released' => 'nullable|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purpose_activity' => 'nullable|string|max:500',
            'reason_for_transfer' => 'nullable|string',
            'facility_name' => 'required|string|max:255',
            'facility_coordinator' => 'required|string|max:255',
            'program' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_description' => 'required|string|max:1000',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:100',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ];

        if (! auth()->user()?->program_id) {
            $rules['pas_number'] = ['required', 'string', 'max:255', Rule::unique('property_allocation_slips', 'pas_number')->ignore($pas->id)];
            $rules['transfer_type'] = ['required', 'string', 'in:PTR,ITR,RIS'];
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $pas) {
            $data = [
                'date_of_pass' => $request->input('date_of_pass'),
                'date_released' => $request->input('date_released'),
                'supplier_id' => $request->input('supplier_id'),
                'purpose_activity' => $request->input('purpose_activity'),
                'reason_for_transfer' => $request->input('reason_for_transfer'),
                'facility_name' => $request->input('facility_name'),
                'facility_coordinator' => $request->input('facility_coordinator'),
                'notes' => $request->input('notes'),
            ];

            if (! auth()->user()?->program_id) {
                $data['pas_number'] = $request->input('pas_number');
                $data['transfer_type'] = $request->input('transfer_type');
                $data['program'] = $request->input('program');
            } else {
                $data['transfer_type'] = $pas->transfer_type ?? 'PTR';
            }

            $pas->update($data);

            $existingItems = $pas->items->keyBy('id');
            $keptIds = [];

            $yy = now()->format('y');
            $mm = now()->format('m');
            $productSeq = (int) $this->nextYearSequence(PasItem::class, 'product_code', "PC{$yy}{$mm}%");

            foreach ($request->input('items') as $row) {
                $pasItemId = isset($row['pas_item_id']) && $row['pas_item_id'] !== '' ? (int) $row['pas_item_id'] : null;
                $qty = (int) $row['quantity'];
                $unitCost = (float) $row['unit_cost'];

                $existing = ($pasItemId && $existingItems->has($pasItemId)) ? $existingItems[$pasItemId] : null;
                $productCode = ($existing && $existing->product_code)
                    ? $existing->product_code
                    : 'PC'.$yy.$mm.str_pad($productSeq++, 4, '0', STR_PAD_LEFT);

                $data = [
                    'item_id' => $row['item_id'] ?? null,
                    'item_description' => $row['item_description'],
                    'product_code' => $productCode,
                    'lot_number' => $row['lot_number'] ?? null,
                    'expiration_date' => ! empty($row['expiration_date']) ? $row['expiration_date'] : null,
                    'quantity' => $qty,
                    'unit' => $row['unit'],
                    'unit_cost' => $unitCost,
                    'total_cost' => $qty * $unitCost,
                ];

                if ($pasItemId && $existingItems->has($pasItemId)) {
                    $existingItems[$pasItemId]->update($data);
                    $keptIds[] = $pasItemId;
                } else {
                    $newRow = PasItem::create(array_merge($data, ['pas_id' => $pas->id]));
                    $keptIds[] = $newRow->id;
                }
            }

            foreach ($existingItems as $id => $existingRow) {
                if (! in_array($id, $keptIds)) {
                    $existingRow->delete();
                }
            }
        });

        return redirect()->route('pas.view', $pas)->with('success', 'PAS updated successfully.');
    }

    public function print(Pas $pas)
    {
        $pas->load(['items.item', 'supplier']);

        return view('pas.print', compact('pas'));
    }

    public function updateStatus(Request $request, Pas $pas, string $status)
    {
        $allowed = ['Pending', 'Released', 'Canceled'];
        if (! in_array($status, $allowed, true)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $pas->status = $status;

        if ($status === 'Released') {
            $pas->date_released = $request->input('date_released', now()->toDateString());
        }

        $pas->save();

        return redirect()->route('pas.view', $pas)->with('success', 'PAS status updated.');
    }

    public function myRequests(Request $request)
    {
        $query = Pas::with(['supplier', 'release', 'requester'])
            ->where('requested_by', auth()->id())
            ->latest('date_of_pass');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('pas_number', 'like', '%'.$search.'%')
                    ->orWhere('facility_coordinator', 'like', '%'.$search.'%')
                    ->orWhere('program', 'like', '%'.$search.'%')
                    ->orWhere('purpose_activity', 'like', '%'.$search.'%')
                    ->orWhere('request_status', 'like', '%'.$search.'%');
            });
        }

        $requestStatus = $request->input('request_status');
        if (! empty($requestStatus)) {
            $query->where('request_status', $requestStatus);
        }

        $user = auth()->user();
        $userProgramScoped = false;

        if ($user) {
            $programIds = $user->all_programs;

            if ($programIds->isNotEmpty()) {
                $programNames = Program::whereIn('id', $programIds)
                    ->pluck('name')
                    ->unique()
                    ->values()
                    ->toArray();

                if (! empty($programNames)) {
                    $userProgramScoped = true;
                    $query->whereIn('program', $programNames);
                }
            }
        }

        $perPage = (int) $request->query('per_page', 15);
        if ($perPage <= 0) {
            $perPage = PHP_INT_MAX;
        }

        $requests = $query->paginate($perPage)->withQueryString();

        return view('pas.my-requests', compact('requests', 'userProgramScoped'));
    }

    public function approveRequest(Pas $pas)
    {
        if ($pas->request_status !== 'pending_approval') {
            return redirect()->back()->with('error', 'This request is not pending approval.');
        }

        if (! $pas->pas_number) {
            $year = now()->format('Y');
            $month = now()->format('m');
            $nextSeq = $this->nextYearSequence(Pas::class, 'pas_number', "PAS-{$year}-{$month}-%");
            $pas->pas_number = "PAS-{$year}-{$month}-{$nextSeq}";
        }

        $pas->request_status = 'approved';
        $pas->save();

        return redirect()->route('pas.view', $pas)->with('success', 'Request approved successfully.');
    }

    public function rejectRequest(Request $request, Pas $pas)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        if ($pas->request_status !== 'pending_approval') {
            return redirect()->back()->with('error', 'This request is not pending approval.');
        }

        $pas->request_status = 'rejected';
        $pas->notes = $pas->notes
            ? $pas->notes."\n\nRejection Reason: ".$request->input('rejection_reason')
            : 'Rejection Reason: '.$request->input('rejection_reason');
        $pas->save();

        return redirect()->route('pas.view', $pas)->with('success', 'Request rejected.');
    }
}
