<?php

namespace App\Http\Controllers;

use App\Models\Coordinator;
use App\Models\Item;
use App\Models\Program;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ReceivingController extends Controller
{
    public function index(Request $request)
    {
        $query = Receiving::with('supplier')->latest('date_received');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where('receiving_number', 'like', '%' . $search . '%');
        }

        $supplier = trim((string) $request->input('supplier', ''));
        if ($supplier !== '') {
            $query->whereHas('supplier', function ($q) use ($supplier) {
                $q->where('company_name', 'like', '%' . $supplier . '%');
            });
        }

        $poNumber = trim((string) $request->input('po_number', ''));
        if ($poNumber !== '') {
            $query->where('po_number', 'like', '%' . $poNumber . '%');
        }

        $startDate = $request->input('start_date');
        if ($startDate) {
            $query->whereDate('date_received', '>=', $startDate);
        }

        $endDate = $request->input('end_date');
        if ($endDate) {
            $query->whereDate('date_received', '<=', $endDate);
        }

        $receivings = $query->paginate(15);

        return view('receivings.index', compact('receivings'));
    }

    public function view(Receiving $receiving)
    {
        $receiving->load('items');

        return view('receivings.view', compact('receiving'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('company_name')->get();
        $items = Item::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $coordinators = Coordinator::with('programs')->orderBy('full_name')->get();

        // Compute next item code sequence
        $lastItem = Item::where('item_code', 'like', 'ITEM-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(item_code, "-", -1) AS UNSIGNED) DESC')
            ->value('item_code');

        if ($lastItem) {
            $lastSeq = (int) substr(strrchr($lastItem, '-'), 1);
            $nextCodeSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextCodeSeq = '0001';
        }

        $nextItemCode = 'ITEM-' . $nextCodeSeq;

        return view('receivings.create', compact('suppliers', 'items', 'nextItemCode', 'programs', 'coordinators'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'po_number' => 'nullable|string|max:255',
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
            $documentNumber = $request->input('po_number') ?? $request->input('source_document_number');

            $receiving = Receiving::create([
                'receiving_number' => 'REC-' . strtoupper(Str::random(8)),
                'po_number' => $documentNumber,
                'source_document_number' => $documentNumber,
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
