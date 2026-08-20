<?php

namespace App\Http\Controllers;

use App\Models\Coordinator;
use App\Models\Item;
use App\Models\Program;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Supplier;
use App\Traits\GeneratesCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ReceivingController extends Controller
{
    use GeneratesCodes;

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

    public function export(Request $request)
    {
        $receivings = Receiving::with(['supplier', 'items.item'])
            ->latest('date_received')
            ->get();

        $fileName = 'receivings-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'PURCHASE ORDER NO.',
            'SUPPLIER/DEALER',
            'ICS /PTR /RIS',
            'Date(PTR/RIS/ICS',
            'Product Code',
            'Item Description',
            'Lot/Batch/SR/Model No.',
            'Expiry Date/Est Useful life',
            'Quantity',
            'UOM',
            'cost',
            'date received',
            'location',
            'stock keeping unit(program)',
            'program coordinator',
        ];

        $callback = function () use ($receivings, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($receivings as $receiving) {
                $baseColumns = [
                    $receiving->po_number ?? '—',
                    $receiving->supplier?->company_name ?? '—',
                    $receiving->ics_ptr_ris ?? '—',
                    $receiving->document_date?->format('Y-m-d') ?? '—',
                ];

                if ($receiving->items->isEmpty()) {
                    fputcsv($handle, array_merge($baseColumns, ['—', '—', '—', '—', '—', '—', '—', '—', '—', '—', '—']));
                    continue;
                }

                foreach ($receiving->items as $item) {
                    fputcsv($handle, array_merge($baseColumns, [
                        $item->item?->item_code ?? '—',
                        $item->item_description ?? $item->item?->name ?? '—',
                        $item->lot_number ?? '—',
                        $item->expiry_date?->format('Y-m-d') ?? '—',
                        $item->quantity_received ?? '—',
                        $item->uom ?? $item->item?->unit ?? '—',
                        isset($item->unit_cost) ? number_format((float) $item->unit_cost, 2, '.', '') : '—',
                        $receiving->date_received?->format('Y-m-d') ?? '—',
                        $receiving->location ?? '—',
                        $receiving->stock_keeping_unit ?? '—',
                        $receiving->program_coordinator ?? '—',
                    ]));
                }
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function view(Receiving $receiving)
    {
        $receiving->load('items');

        return view('receivings.view', compact('receiving'));
    }

    public function edit(Receiving $receiving)
    {
        $receiving->load('items.item');
        $suppliers    = Supplier::orderBy('company_name')->get();
        $items        = Item::orderBy('name')->get();
        $programs     = Program::orderBy('name')->get();
        $coordinators = Coordinator::with('programs')->orderBy('full_name')->get();

        return view('receivings.edit', compact('receiving', 'suppliers', 'items', 'programs', 'coordinators'));
    }

    public function update(Request $request, Receiving $receiving)
    {
        $request->validate([
            'supplier_id'        => 'required|exists:suppliers,id',
            'po_number'          => 'nullable|string|max:255',
            'ics_ptr_ris'        => 'nullable|string|max:255',
            'document_date'      => 'nullable|date',
            'date_received'      => 'required|date',
            'received_by'        => 'nullable|string|max:255',
            'location'           => 'nullable|string|max:255',
            'stock_keeping_unit' => 'nullable|string|max:255',
            'program_coordinator'=> 'nullable|string|max:255',
            'items'              => 'required|array|min:1',
            'items.*.item_description'  => 'required|string|max:255',
            'items.*.quantity_received' => 'required|integer|min:1',
            'items.*.uom'        => 'nullable|string|max:255',
            'items.*.lot_number' => 'nullable|string|max:255',
            'items.*.expiry_date'=> 'nullable|date',
            'items.*.unit_cost'  => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $receiving) {
            $receiving->update([
                'po_number'           => $request->input('po_number'),
                'source_document_number' => $request->input('po_number'),
                'ics_ptr_ris'         => $request->input('ics_ptr_ris'),
                'document_date'       => $request->input('document_date'),
                'supplier_id'         => $request->input('supplier_id'),
                'date_received'       => $request->input('date_received'),
                'received_by'         => $request->input('received_by'),
                'location'            => $request->input('location'),
                'stock_keeping_unit'  => $request->input('stock_keeping_unit'),
                'program_coordinator' => $request->input('program_coordinator'),
                'notes'               => $request->input('notes'),
            ]);

            $existingItems  = $receiving->items->keyBy('id');  // keyed by int
            $keptExistingIds = [];

            foreach ($request->input('items') as $itemData) {
                $receivingItemId = isset($itemData['receiving_item_id']) && $itemData['receiving_item_id'] !== ''
                    ? (int) $itemData['receiving_item_id']
                    : null;

                // Resolve or create the Item record
                $item = null;
                if (!empty($itemData['item_id'])) {
                    $item = Item::find((int) $itemData['item_id']);
                }
                if (!$item) {
                    $item = Item::where('name', $itemData['item_description'])->first();
                }
                if (!$item) {
                    $item = Item::create([
                        'item_code'           => $itemData['item_code'] ?? null,
                        'name'                => $itemData['item_description'],
                        'category'            => $itemData['category'] ?? null,
                        'unit'                => $itemData['uom'] ?? null,
                        'description'         => $itemData['item_description'],
                        'location'            => $request->input('location'),
                        'stock_keeping_unit'  => $request->input('stock_keeping_unit'),
                        'program_coordinator' => $request->input('program_coordinator'),
                        'unit_cost'           => $itemData['unit_cost'] ?? null,
                        'quantity_on_hand'    => 0,
                    ]);
                } else {
                    $item->fill([
                        'name'                => $itemData['item_description'],
                        'category'            => $itemData['category'] ?? $item->category,
                        'unit'                => $itemData['uom'] ?? $item->unit,
                        'location'            => $request->input('location') ?? $item->location,
                        'stock_keeping_unit'  => $request->input('stock_keeping_unit') ?? $item->stock_keeping_unit,
                        'program_coordinator' => $request->input('program_coordinator') ?? $item->program_coordinator,
                    ]);
                    if (!empty($itemData['unit_cost'])) {
                        $item->unit_cost = $itemData['unit_cost'];
                    }
                    $item->save();
                }

                $newQty = (int) $itemData['quantity_received'];

                if ($receivingItemId && $existingItems->has($receivingItemId)) {
                    // Existing row — reconcile stock delta
                    $existingRow = $existingItems[$receivingItemId];
                    $oldQty      = (int) $existingRow->quantity_received;
                    $delta       = $newQty - $oldQty;

                    $existingRow->update([
                        'item_id'           => $item->id,
                        'item_description'  => $itemData['item_description'],
                        'category'          => $itemData['category'] ?? null,
                        'uom'               => $itemData['uom'] ?? null,
                        'lot_number'        => $itemData['lot_number'] ?? null,
                        'expiry_date'       => !empty($itemData['expiry_date']) ? $itemData['expiry_date'] : null,
                        'quantity_received' => $newQty,
                        'unit_cost'         => $itemData['unit_cost'] ?? null,
                    ]);

                    if ($delta !== 0) {
                        $item->increment('quantity_on_hand', $delta);
                    }

                    $keptExistingIds[] = $receivingItemId;
                } else {
                    // New row
                    ReceivingItem::create([
                        'receiving_id'      => $receiving->id,
                        'item_id'           => $item->id,
                        'item_description'  => $itemData['item_description'],
                        'category'          => $itemData['category'] ?? null,
                        'uom'               => $itemData['uom'] ?? null,
                        'lot_number'        => $itemData['lot_number'] ?? null,
                        'expiry_date'       => !empty($itemData['expiry_date']) ? $itemData['expiry_date'] : null,
                        'quantity_received' => $newQty,
                        'unit_cost'         => $itemData['unit_cost'] ?? null,
                    ]);
                    $item->increment('quantity_on_hand', $newQty);
                }
            }

            // Remove rows that were deleted in the form and reverse their stock
            foreach ($existingItems as $id => $existingRow) {
                if (!in_array($id, $keptExistingIds, false)) {
                    if ($existingRow->item_id) {
                        Item::where('id', $existingRow->item_id)
                            ->decrement('quantity_on_hand', (int) $existingRow->quantity_received);
                    }
                    $existingRow->delete();
                }
            }
        });

        return redirect()->route('receivings.view', $receiving)->with('success', 'Receiving updated and inventory adjusted.');
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('company_name')->get();
        $items = Item::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $coordinators = Coordinator::with('programs')->orderBy('full_name')->get();

        // Compute next product code sequence in format PC{yy}{mm}{seq} (resets each year)
        $yy = now()->format('y');
        $mm = now()->format('m');
        $nextItemSeq  = (int) $this->nextYearSequence(Item::class, 'item_code', "PC{$yy}{$mm}%");
        $nextItemCode = 'PC' . $yy . $mm . str_pad($nextItemSeq, 4, '0', STR_PAD_LEFT);

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
