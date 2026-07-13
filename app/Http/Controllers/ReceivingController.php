<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ReceivingController extends Controller
{
    public function index()
    {
        $receivings = Receiving::with('supplier')->latest('date_received')->paginate(15);

        return view('receivings.index', compact('receivings'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('company_name')->get();
        $items = Item::orderBy('name')->get();

        return view('receivings.create', compact('suppliers', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'po_number' => 'nullable|string|max:255',
            'source_document_number' => 'nullable|string|max:255',
            'ics_ptr_ris' => 'nullable|string|max:255',
            'document_date' => 'nullable|date',
            'date_received' => 'required|date',
            'received_by' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'stock_keeping_unit' => 'nullable|string|max:255',
            'program_coordinator' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_code' => 'nullable|string|max:255',
            'items.*.item_description' => 'required|string|max:255',
            'items.*.category' => 'nullable|string|max:255',
            'items.*.uom' => 'nullable|string|max:255',
            'items.*.quantity_received' => 'required|integer|min:1',
            'items.*.lot_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $receiving = Receiving::create([
                'receiving_number' => 'REC-' . strtoupper(Str::random(8)),
                'po_number' => $request->input('po_number'),
                'source_document_number' => $request->input('source_document_number'),
                'ics_ptr_ris' => $request->input('ics_ptr_ris'),
                'document_date' => $request->input('document_date'),
                'supplier_id' => $request->input('supplier_id'),
                'date_received' => $request->input('date_received'),
                'received_by' => $request->input('received_by'),
                'location' => $request->input('location'),
                'stock_keeping_unit' => $request->input('stock_keeping_unit'),
                'program_coordinator' => $request->input('program_coordinator'),
                'notes' => $request->input('notes'),
            ]);

            foreach ($request->input('items') as $itemData) {
                $itemQuery = Item::query();

                if (!empty($itemData['item_code'])) {
                    $itemQuery->where('item_code', $itemData['item_code']);
                } else {
                    $itemQuery->where('name', $itemData['item_description']);
                }

                $item = $itemQuery->first();

                if (!$item) {
                    $item = Item::create([
                        'item_code' => $itemData['item_code'] ?? null,
                        'name' => $itemData['item_description'],
                        'category' => $itemData['category'] ?? null,
                        'unit' => $itemData['uom'] ?? null,
                        'description' => $itemData['item_description'],
                        'location' => $request->input('location'),
                        'stock_keeping_unit' => $request->input('stock_keeping_unit'),
                        'program_coordinator' => $request->input('program_coordinator'),
                        'unit_cost' => $itemData['unit_cost'] ?? null,
                        'quantity_on_hand' => 0,
                    ]);
                } else {
                    $item->fill([
                        'name' => $itemData['item_description'] ?? $item->name,
                        'category' => $itemData['category'] ?? $item->category,
                        'unit' => $itemData['uom'] ?? $item->unit,
                        'description' => $itemData['item_description'] ?? $item->description,
                        'location' => $request->input('location') ?? $item->location,
                        'stock_keeping_unit' => $request->input('stock_keeping_unit') ?? $item->stock_keeping_unit,
                        'program_coordinator' => $request->input('program_coordinator') ?? $item->program_coordinator,
                    ]);

                    if (!empty($itemData['unit_cost'])) {
                        $item->unit_cost = $itemData['unit_cost'];
                    }

                    $item->save();
                }

                ReceivingItem::create([
                    'receiving_id' => $receiving->id,
                    'item_id' => $item->id,
                    'category' => $itemData['category'] ?? null,
                    'item_description' => $itemData['item_description'],
                    'quantity_received' => $itemData['quantity_received'],
                    'uom' => $itemData['uom'] ?? null,
                    'lot_number' => $itemData['lot_number'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'unit_cost' => $itemData['unit_cost'] ?? null,
                ]);

                $item->increment('quantity_on_hand', $itemData['quantity_received']);
            }
        });

        return redirect()->route('receivings.index')->with('success', 'Receiving recorded and inventory updated.');
    }
}
