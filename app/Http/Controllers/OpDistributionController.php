<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\OpDistribution;
use App\Models\OpDistributionItem;
use App\Models\ReceivingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpDistributionController extends Controller
{
    public function index(Request $request)
    {
        $query = OpDistribution::withCount('items')->latest('date_distributed');

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('distributed_by', 'like', "%{$search}%")
                  ->orWhereHas('items', fn($q2) => $q2->where('patient_name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $perPage = (int) $request->query('per_page', 15);

        if ($perPage <= 0) {
            $perPage = PHP_INT_MAX;
        }

        $distributions = $query->paginate($perPage)->withQueryString();

        return view('op-distribution.index', compact('distributions'));
    }

    public function create()
    {
        $items          = Item::orderBy('name')->get();
        $itemLotNumbers = $this->itemLotNumbers($items);
        $refNumber      = $this->nextRefNumber();

        return view('op-distribution.create', compact('items', 'itemLotNumbers', 'refNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_distributed'                    => 'required|date',
            'distributed_by'                      => 'nullable|string|max:255',
            'items'                               => 'required|array|min:1',
            'items.*.patient_name'                => 'required|string|max:255',
            'items.*.patient_age'                 => 'nullable|integer|min:0|max:150',
            'items.*.patient_gender'              => 'nullable|string|in:Male,Female,Other',
            'items.*.medicines'                   => 'required|array|min:1',
            'items.*.medicines.*.item_description'=> 'required|string|max:1000',
            'items.*.medicines.*.quantity'        => 'required|integer|min:1',
            'items.*.medicines.*.uom'             => 'nullable|string|max:100',
            'items.*.medicines.*.unit_cost'       => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $dist = OpDistribution::create([
                'reference_number' => $this->nextRefNumber(),
                'date_distributed' => $request->input('date_distributed'),
                'distributed_by'   => $request->input('distributed_by'),
                'status'           => 'Draft',
                'notes'            => $request->input('notes'),
            ]);

            foreach ($request->input('items') as $patient) {
                foreach ($patient['medicines'] as $med) {
                    OpDistributionItem::create([
                        'op_distribution_id' => $dist->id,
                        'item_id'            => $med['item_id'] ?: null,
                        'patient_name'       => $patient['patient_name'],
                        'patient_age'        => $patient['patient_age'] ?: null,
                        'patient_gender'     => $patient['patient_gender'] ?: null,
                        'item_description'   => $med['item_description'],
                        'quantity'           => (int) $med['quantity'],
                        'uom'                => $med['uom'] ?? null,
                        'unit_cost'          => $med['unit_cost'] ?: null,
                        'lot_number'         => $med['lot_number'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('op-distribution.index')->with('success', 'OP Distribution record saved.');
    }

    public function view(OpDistribution $opDistribution)
    {
        $opDistribution->load('items.item');

        return view('op-distribution.view', compact('opDistribution'));
    }

    public function edit(OpDistribution $opDistribution)
    {
        $opDistribution->load('items.item');
        $items          = Item::orderBy('name')->get();
        $itemLotNumbers = $this->itemLotNumbers($items);

        return view('op-distribution.edit', compact('opDistribution', 'items', 'itemLotNumbers'));
    }

    public function update(Request $request, OpDistribution $opDistribution)
    {
        $request->validate([
            'date_distributed'                    => 'required|date',
            'distributed_by'                      => 'nullable|string|max:255',
            'status'                              => 'required|string|in:Draft,Released,Canceled',
            'items'                               => 'required|array|min:1',
            'items.*.patient_name'                => 'required|string|max:255',
            'items.*.patient_age'                 => 'nullable|integer|min:0|max:150',
            'items.*.patient_gender'              => 'nullable|string|in:Male,Female,Other',
            'items.*.medicines'                   => 'required|array|min:1',
            'items.*.medicines.*.item_description'=> 'required|string|max:1000',
            'items.*.medicines.*.quantity'        => 'required|integer|min:1',
            'items.*.medicines.*.uom'             => 'nullable|string|max:100',
            'items.*.medicines.*.unit_cost'       => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $opDistribution) {
            $opDistribution->update([
                'date_distributed' => $request->input('date_distributed'),
                'distributed_by'   => $request->input('distributed_by'),
                'status'           => $request->input('status'),
                'notes'            => $request->input('notes'),
            ]);

            // Delete all existing items and re-insert (simpler since patient grouping changes row count)
            $opDistribution->items()->delete();

            foreach ($request->input('items') as $patient) {
                foreach ($patient['medicines'] as $med) {
                    OpDistributionItem::create([
                        'op_distribution_id' => $opDistribution->id,
                        'item_id'            => $med['item_id'] ?: null,
                        'patient_name'       => $patient['patient_name'],
                        'patient_age'        => $patient['patient_age'] ?: null,
                        'patient_gender'     => $patient['patient_gender'] ?: null,
                        'item_description'   => $med['item_description'],
                        'quantity'           => (int) $med['quantity'],
                        'uom'                => $med['uom'] ?? null,
                        'unit_cost'          => $med['unit_cost'] ?: null,
                        'lot_number'         => $med['lot_number'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('op-distribution.view', $opDistribution)->with('success', 'OP Distribution updated.');
    }

    private function nextRefNumber(): string
    {
        $prefix = 'OP-' . now()->format('Y-m') . '-';
        $last   = OpDistribution::where('reference_number', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(reference_number, "-", -1) AS UNSIGNED) DESC')
            ->value('reference_number');

        $seq = $last ? str_pad((int) substr(strrchr($last, '-'), 1) + 1, 4, '0', STR_PAD_LEFT) : '0001';

        return $prefix . $seq;
    }

    private function itemLotNumbers($items): array
    {
        return ReceivingItem::select('item_id', 'lot_number', 'expiry_date')
            ->whereNotNull('lot_number')
            ->whereIn('item_id', $items->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('item_id')
            ->map(fn($g) => [
                'lot_number'  => $g->first()->lot_number,
                'expiry_date' => $g->first()->expiry_date?->format('Y-m-d'),
            ])
            ->toArray();
    }
}
