<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Pas;
use App\Models\PasItem;
use App\Models\Supplier;
use App\Models\Coordinator;
use App\Models\Program;
use App\Models\ReceivingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PasController extends Controller
{
    public function index(Request $request)
    {
        $query = Pas::with('supplier')->latest('date_of_pass');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('pas_number', 'like', '%' . $search . '%')
                  ->orWhere('facility_coordinator', 'like', '%' . $search . '%')
                  ->orWhere('program', 'like', '%' . $search . '%')
                  ->orWhere('purpose_activity', 'like', '%' . $search . '%')
                  ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        $status = $request->input('status');
        if (!empty($status)) {
            $query->where('status', $status);
        }

        $slips = $query->paginate(15);

        return view('pas.index', compact('slips'));
    }

    public function create()
    {
        $items       = Item::orderBy('name')->get();
        $suppliers   = Supplier::orderBy('company_name')->get();
        $coordinators = Coordinator::with('programs')->orderBy('full_name')->get();
        $programs    = Program::orderBy('name')->get();

        // Latest lot number per item from receiving_items
        $itemLotNumbers = ReceivingItem::select('item_id', 'lot_number', 'expiry_date')
            ->whereNotNull('lot_number')
            ->whereIn('item_id', $items->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('item_id')
            ->map(fn($g) => [
                'lot_number' => $g->first()->lot_number,
                'expiry_date' => $g->first()->expiry_date?->format('Y-m-d'),
            ]);

        // Auto-generate PAS number: PAS-yyyy-mm-XXXX
        $year  = now()->format('Y');
        $month = now()->format('m');
        $last  = Pas::where('pas_number', 'like', "PAS-{$year}-{$month}-%")
            ->orderByRaw('CAST(SUBSTRING_INDEX(pas_number, "-", -1) AS UNSIGNED) DESC')
            ->value('pas_number');

        $nextSeq   = $last ? str_pad((int) substr(strrchr($last, '-'), 1) + 1, 4, '0', STR_PAD_LEFT) : '0001';
        $pasNumber = "PAS-{$year}-{$month}-{$nextSeq}";

        return view('pas.create', compact('items', 'suppliers', 'coordinators', 'programs', 'pasNumber', 'itemLotNumbers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pas_number'          => 'required|string|max:255|unique:property_allocation_slips,pas_number',
            'date_of_pass'        => 'required|date',
            'date_released'       => 'nullable|date',
            'supplier_id'         => 'nullable|exists:suppliers,id',
            'purpose_activity'    => 'nullable|string|max:500',
            'facility_name'       => 'required|string|max:255',
            'facility_coordinator'=> 'required|string|max:255',
            'transfer_type'       => 'required|string|in:PTR,ITR,RIS',
            'program'             => 'nullable|string|max:255',
            'items'               => 'required|array|min:1',
            'items.*.item_description' => 'required|string|max:1000',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit'        => 'required|string|max:100',
            'items.*.unit_cost'   => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $pas = Pas::create([
                    'pas_number'           => $request->input('pas_number'),
                    'date_of_pass'         => $request->input('date_of_pass'),
                    'date_released'        => $request->input('date_released'),
                    'supplier_id'          => $request->input('supplier_id'),
                    'purpose_activity'     => $request->input('purpose_activity'),
                    'facility_name'        => $request->input('facility_name'),
                    'facility_coordinator' => $request->input('facility_coordinator'),
                    'transfer_type'        => $request->input('transfer_type'),
                    'program'              => $request->input('program'),
                    'status'               => 'Pending',
                    'notes'                => $request->input('notes'),
                ]);

                foreach ($request->input('items') as $row) {
                    $qty      = (int) $row['quantity'];
                    $unitCost = (float) $row['unit_cost'];

                    PasItem::create([
                        'pas_id'           => $pas->id,
                        'item_id'          => $row['item_id'] ?? null,
                        'item_description' => $row['item_description'],
                        'product_code'     => $row['product_code'] ?? null,
                        'lot_number'       => $row['lot_number'] ?? null,
                        'expiration_date'  => !empty($row['expiration_date']) ? $row['expiration_date'] : null,
                        'quantity'         => $qty,
                        'unit'             => $row['unit'],
                        'unit_cost'        => $unitCost,
                        'total_cost'       => $qty * $unitCost,
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
        $pas->load(['items.item', 'supplier']);

        return view('pas.view', compact('pas'));
    }

    public function print(Pas $pas)
    {
        $pas->load(['items.item', 'supplier']);

        return view('pas.print', compact('pas'));
    }

    public function updateStatus(Request $request, Pas $pas, string $status)
    {
        $allowed = ['Pending', 'Released', 'Canceled'];
        if (!in_array($status, $allowed, true)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $pas->status = $status;

        if ($status === 'Released') {
            $pas->date_released = $request->input('date_released', now()->toDateString());
        }

        $pas->save();

        return redirect()->route('pas.view', $pas)->with('success', 'PAS status updated.');
    }
}
