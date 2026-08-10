<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Program;
use App\Models\Coordinator;
use App\Models\ReceivingItem;
use App\Models\Release;
use App\Models\ReleaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReleaseController extends Controller
{
    private function applyStatusTransition(Release $release, string $newStatus, ?string $previousStatus = null): void
    {
        $previousStatus = $previousStatus ?? $release->getRawOriginal('status') ?? $release->status;

        $wasInactive = in_array($previousStatus, ['Canceled', 'Returned'], true);
        $isInactive  = in_array($newStatus,      ['Canceled', 'Returned'], true);

        if ($isInactive && ! $wasInactive) {
            // Active → Canceled/Returned: restore stock
            foreach ($release->items as $releaseItem) {
                Item::where('id', $releaseItem->item_id)
                    ->increment('quantity_on_hand', (int) $releaseItem->quantity_released);
            }
        } elseif (! $isInactive && $wasInactive) {
            // Canceled/Returned → Active: deduct stock again
            foreach ($release->items as $releaseItem) {
                Item::where('id', $releaseItem->item_id)
                    ->decrement('quantity_on_hand', (int) $releaseItem->quantity_released);
            }
        }
    }

    public function updateStatus(Request $request, Release $release, string $status)
    {
        $allowed = ['released-through-pass', 'released', 'canceled', 'returned', 'unreleased'];
        if (!in_array($status, $allowed, true)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $previousStatus = $release->getRawOriginal('status') ?? $release->status;

        $newStatus = match ($status) {
            'released-through-pass' => 'Released through pass',
            'released'              => 'Released',
            'canceled'              => 'Canceled',
            'returned'              => 'Returned',
            default                 => 'Unreleased',
        };

        $release->status = $newStatus;

        if (in_array($newStatus, ['Released', 'Released through pass'], true)) {
            $release->received_by   = $request->input('received_by', $release->received_by);
            $release->date_released = $request->input('date_released', $release->date_released);
            $release->ptr_itr_ris_no = $request->input('ptr_itr_ris_no', $release->ptr_itr_ris_no);
        }

        if (in_array($newStatus, ['Canceled', 'Returned'], true)) {
            $release->status_reason = $request->input('status_reason', $release->status_reason);
        }

        DB::transaction(function () use ($release, $newStatus, $previousStatus) {
            $release->load('items');
            $this->applyStatusTransition($release, $newStatus, $previousStatus);
            $release->save();
        });

        return redirect()->route('releases.view', $release)->with('success', 'Release status updated.');
    }

    public function index(Request $request)
    {
        $query = Release::query()->latest('date_released');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('release_number', 'like', '%' . $search . '%')
                    ->orWhere('pas_number', 'like', '%' . $search . '%')
                    ->orWhere('pho_code', 'like', '%' . $search . '%')
                    ->orWhere('facility_name', 'like', '%' . $search . '%')
                    ->orWhere('ptr_itr_ris_no', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        $facility = trim((string) $request->input('facility', ''));
        if ($facility !== '') {
            $query->where('facility_name', 'like', '%' . $facility . '%');
        }

        $phoCode = trim((string) $request->input('pho_code', ''));
        if ($phoCode !== '') {
            $query->where('pho_code', 'like', '%' . $phoCode . '%');
        }

        $pasNumber = trim((string) $request->input('pas_number', ''));
        if ($pasNumber !== '') {
            $query->where('pas_number', 'like', '%' . $pasNumber . '%');
        }

        $status = $request->input('status');
        if (!empty($status)) {
            $newStatus = match ($status) {
                'released-through-pass' => 'Released through pass',
                'released' => 'Released',
                'canceled' => 'Canceled',
                'returned' => 'Returned',
                'unreleased' => 'Unreleased',
                default => $status,
            };

            $query->where('status', $newStatus);
        }

        $releases = $query->paginate(15);

        return view('releases.index', compact('releases'));
    }

    public function view(Release $release)
    {
        $release->load(['items.item.receivingItems.receiving']);

        return view('releases.view', compact('release'));
    }

    public function print(Release $release)
    {
        $release->load(['items.item.receivingItems.receiving']);

        return view('releases.print', compact('release'));
    }

    public function update(Request $request, Release $release)
    {
        $request->validate([
            'pas_number'                 => 'nullable|string|max:255',
            'health_program_coordinator' => 'nullable|string|max:255',
            'ptr_itr_ris_no'             => 'nullable|string|max:255',
            'pho_code'                   => 'nullable|string|max:255',
            'source_docs_ptr_po_no'      => 'nullable|string|max:255',
            'facility_name'              => 'nullable|string|max:255',
            'received_by'                => 'required|string|max:255',
            'date_released'              => 'required|date',
            'status'                     => 'required|string|in:Unreleased,Released,Released through pass,Canceled,Returned',
            'status_reason'              => 'nullable|string|max:1000',
            'notes'                      => 'nullable|string',
        ]);

        // Capture BEFORE fill() overwrites it — this is critical for the stock transition
        $previousStatus = $release->getRawOriginal('status') ?? $release->status;
        $newStatus      = $request->input('status');

        DB::transaction(function () use ($request, $release, $previousStatus, $newStatus) {
            $release->fill($request->only([
                'pas_number', 'health_program_coordinator', 'ptr_itr_ris_no',
                'pho_code', 'source_docs_ptr_po_no', 'facility_name',
                'received_by', 'date_released', 'status', 'status_reason', 'notes',
            ]));

            // Reload items fresh so increment/decrement works on correct records
            $release->load('items');
            $this->applyStatusTransition($release, $newStatus, $previousStatus);
            $release->save();
        });

        return redirect()->route('releases.view', $release)->with('success', 'Release details updated successfully.');
    }

    public function create()
    {
        $items = Item::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $coordinators = Coordinator::with('programs')->orderBy('full_name')->get();

        // Fetch the latest lot_number for each item from receiving_items
        $itemLotNumbers = ReceivingItem::select('item_id', 'lot_number')
            ->whereNotNull('lot_number')
            ->whereIn('item_id', $items->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('item_id')
            ->map(fn($group) => $group->first()->lot_number);

        // Auto-generate PTR/ITR/RIS No. in format: 14538-{TYPE}-yyyy-mm-XXXX
        $year = now()->format('Y');
        $month = now()->format('m');
        $ptrType = 'PTR'; // default type
        $prefix = "14538-{$ptrType}-{$year}-{$month}-";

        // Get the last sequential number across ALL types (PTR, ITR, RIS) for this year-month
        $lastPtr = Release::where('ptr_itr_ris_no', 'like', "14538-%-{$year}-{$month}-%")
            ->orderByRaw('CAST(SUBSTRING_INDEX(ptr_itr_ris_no, "-", -1) AS UNSIGNED) DESC')
            ->value('ptr_itr_ris_no');

        if ($lastPtr) {
            $lastSeq = (int) substr(strrchr($lastPtr, '-'), 1);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        $ptrNumber = $prefix . $nextSeq;

        return view('releases.create', compact('items', 'ptrNumber', 'year', 'month', 'itemLotNumbers', 'programs', 'coordinators'));
    }

    public function nextPtrNumber(string $type)
    {
        $type = strtoupper($type);
        if (!in_array($type, ['PTR', 'ITR', 'RIS'])) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $year = now()->format('Y');
        $month = now()->format('m');
        $prefix = "14538-{$type}-{$year}-{$month}-";

        // Get the last sequential number across ALL types (PTR, ITR, RIS) for this year-month
        $lastPtr = Release::where('ptr_itr_ris_no', 'like', "14538-%-{$year}-{$month}-%")
            ->orderByRaw('CAST(SUBSTRING_INDEX(ptr_itr_ris_no, "-", -1) AS UNSIGNED) DESC')
            ->value('ptr_itr_ris_no');

        if ($lastPtr) {
            $lastSeq = (int) substr(strrchr($lastPtr, '-'), 1);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return response()->json(['number' => $prefix . $nextSeq]);
    }

    public function store(Request $request)
    {
        // Map item descriptions to item IDs if the user entered a matching product name.
        $items = $request->input('items', []);
        foreach ($items as $index => $itemData) {
            if (empty($itemData['item_id']) && !empty($itemData['item_description'])) {
                $itemDescription = trim($itemData['item_description']);
                $lowerDescription = Str::lower($itemDescription);

                $matchedItem = Item::whereRaw('LOWER(name) = ?', [$lowerDescription])
                    ->orWhereRaw('LOWER(item_code) = ?', [$lowerDescription])
                    ->first();

                if (! $matchedItem) {
                    $matchedItem = Item::whereRaw('LOWER(name) like ?', ["%{$lowerDescription}%"] )
                        ->orWhereRaw('LOWER(item_code) like ?', ["%{$lowerDescription}%"] )
                        ->first();
                }

                if ($matchedItem) {
                    $items[$index]['item_id'] = $matchedItem->id;
                }
            }
        }
        $request->merge(['items' => $items]);

        // Status is set automatically after saving.
        $request->validate([
            'pas_number' => 'required|string|max:255',
            'health_program_coordinator' => 'required|string|max:255',
            'ptr_itr_ris_no' => 'required|string|max:255',
            'pho_code' => 'required|string|max:255',
            'source_docs_ptr_po_no' => 'required|string|max:255',
            'facility_name' => 'required|string|max:255',
            'received_by' => 'required|string|max:255',
            'date_released' => 'nullable|date',
            'status' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_description' => 'required|string|max:1000',
            'items.*.quantity_released' => 'required|integer|min:1',
            'items.*.uom' => 'required|string|max:255',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $release = Release::create([
                    'release_number' => 'REL-' . strtoupper(Str::random(8)),
                    'pas_number' => $request->input('pas_number'),
                    // After saving a new release slip, it starts as "Unreleased".
                    // Status will be updated later via dedicated actions.
                    'status' => 'Unreleased',
                    'health_program_coordinator' => $request->input('health_program_coordinator'),
                    'ptr_itr_ris_no' => $request->input('ptr_itr_ris_no'),
                    'pho_code' => $request->input('pho_code'),
                    'source_docs_ptr_po_no' => $request->input('source_docs_ptr_po_no'),
                    'facility_name' => $request->input('facility_name'),
                    'received_by' => $request->input('received_by'),
                    'date_released' => $request->input('date_released') ?: null,
                    'notes' => $request->input('notes'),
                ]);

                foreach ($request->input('items') as $itemData) {
                    $item = Item::find($itemData['item_id']);

                    if ($item && $item->quantity_on_hand < (int) $itemData['quantity_released']) {
                        $available = (int) $item->quantity_on_hand;
                        $requested = (int) $itemData['quantity_released'];

                        throw new \Exception(
                            "Not enough stock for item {$item->name}. Available: {$available}, Requested: {$requested}."
                        );
                    }

                    ReleaseItem::create([
                        'release_id' => $release->id,
                        'item_id' => $itemData['item_id'],
                        'item_description' => $itemData['item_description'] ?? $item->name,
                        'quantity_released' => $itemData['quantity_released'],
                        'uom' => $itemData['uom'] ?? $item->unit,
                        'lot_number' => $itemData['lot_number'] ?? null,
                        'unit_cost' => $itemData['unit_cost'] ?? null,
                    ]);

                    $item->decrement('quantity_on_hand', (int) $itemData['quantity_released']);
                }
            });

            return redirect()->route('releases.index')->with('success', 'Release slip saved and inventory updated.');
        } catch (Throwable $e) {
            // Show as flash notification (layouts/app.blade.php reads session('error')).
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}

