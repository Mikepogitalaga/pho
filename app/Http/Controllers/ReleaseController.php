<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Program;
use App\Models\Coordinator;
use App\Models\Facility;
use App\Models\ReceivingItem;
use App\Models\Release;
use App\Models\ReleaseItem;
use App\Exports\ReleaseListExport;
use App\Traits\GeneratesCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ReleaseController extends Controller
{
    use GeneratesCodes;

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
        $query = Release::query()->with('items.item.receivingItems.receiving.supplier')->latest('date_released');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('release_number', 'like', '%' . $search . '%')
                    ->orWhere('pas_number', 'like', '%' . $search . '%')
                    ->orWhere('facility_name', 'like', '%' . $search . '%')
                    ->orWhere('ptr_itr_ris_no', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhereHas('items', function ($sq) use ($search) {
                        $sq->where('item_description', 'like', '%' . $search . '%')
                            ->orWhereHas('item.receivingItems', function ($receivingQuery) use ($search) {
                                $receivingQuery->where('item_code', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        $facility = trim((string) $request->input('facility', ''));
        if ($facility !== '') {
            $query->where('facility_name', 'like', '%' . $facility . '%');
        }

        $productCode = trim((string) $request->input('product_code', ''));
        if ($productCode !== '') {
            $query->whereHas('items.item.receivingItems', function ($q) use ($productCode) {
                $q->where('item_code', 'like', '%' . $productCode . '%');
            });
        }

        $period = $request->input('period', '');
        if ($period === 'today') {
            $query->whereDate('date_released', today());
        } elseif ($period === 'week') {
            $query->whereBetween('date_released', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereBetween('date_released', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        $supplierType = strtoupper(trim((string) $request->input('supplier_type', '')));
        if (in_array($supplierType, ['GSO', 'DOH'], true)) {
            $query->whereHas('items.item.receivingItems.receiving.supplier', function ($q) use ($supplierType) {
                $q->where('supplier_type', $supplierType);
            });
        }

        $category = strtoupper(trim((string) $request->input('category', '')));
        if (in_array($category, ['MDL', 'DM', 'OTHER SUPPLIES'], true)) {
            $query->whereHas('items', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }

        $pasNumber = trim((string) $request->input('pas_number', ''));
        if ($pasNumber !== '') {
            $query->where('pas_number', 'like', '%' . $pasNumber . '%');
        }

        $program = trim((string) $request->input('program', ''));
        if ($program !== '') {
            $query->where('health_program_coordinator', 'like', '%' . $program . '%');
        }

        $itemId = trim((string) $request->input('item', ''));
        if ($itemId !== '') {
            $query->whereHas('items', function ($q) use ($itemId) {
                $q->where('items.id', $itemId);
            });
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

        $perPage = (int) $request->query('per_page', 15);

        if ($perPage <= 0) {
            $perPage = PHP_INT_MAX;
        }

        $releases = $query->paginate($perPage)->withQueryString();

        $programs = Program::orderBy('name')->get();

        return view('releases.index', compact('releases', 'programs', 'program', 'period', 'supplierType', 'category'));
    }

    public function exportList(Request $request)
    {
        $releases = $this->releaseListQuery($request)->get();

        return Excel::download(new ReleaseListExport($releases), 'Release_Records_' . now()->format('YmdHis') . '.xlsx');
    }

    public function printList(Request $request)
    {
        $releases = $this->releaseListQuery($request)->get();

        return view('releases.list-print', compact('releases'));
    }

    private function releaseListQuery(Request $request)
    {
        $query = Release::query()->with('items.item.receivingItems.receiving.supplier')->latest('date_released');
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('release_number', 'like', '%' . $search . '%')
                    ->orWhere('pas_number', 'like', '%' . $search . '%')
                    ->orWhere('facility_name', 'like', '%' . $search . '%')
                    ->orWhere('ptr_itr_ris_no', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhereHas('items', function ($sq) use ($search) {
                        $sq->where('item_description', 'like', '%' . $search . '%')
                            ->orWhereHas('item.receivingItems', fn ($rq) => $rq->where('item_code', 'like', '%' . $search . '%'));
                    });
            });
        }
        if (($facility = trim((string) $request->input('facility', ''))) !== '') $query->where('facility_name', 'like', "%{$facility}%");
        if (($productCode = trim((string) $request->input('product_code', ''))) !== '') $query->whereHas('items.item.receivingItems', fn ($q) => $q->where('item_code', 'like', "%{$productCode}%"));
        if (($pasNumber = trim((string) $request->input('pas_number', ''))) !== '') $query->where('pas_number', 'like', "%{$pasNumber}%");
        if (($program = trim((string) $request->input('program', ''))) !== '') $query->where('health_program_coordinator', 'like', "%{$program}%");
        if (($period = $request->input('period', '')) === 'today') $query->whereDate('date_released', today());
        elseif ($period === 'week') $query->whereBetween('date_released', [now()->startOfWeek(), now()->endOfWeek()]);
        elseif ($period === 'month') $query->whereBetween('date_released', [now()->startOfMonth(), now()->endOfMonth()]);
        if (($supplierType = strtoupper(trim((string) $request->input('supplier_type', '')))) && in_array($supplierType, ['GSO', 'DOH'], true)) $query->whereHas('items.item.receivingItems.receiving.supplier', fn ($q) => $q->where('supplier_type', $supplierType));
        if (($category = strtoupper(trim((string) $request->input('category', '')))) && in_array($category, ['MDL', 'DM', 'OTHER SUPPLIES'], true)) $query->whereHas('items', fn ($q) => $q->where('category', $category));
        $status = $request->input('status');
        if ($status) {
            $status = match ($status) { 'released-through-pass' => 'Released through pass', 'released' => 'Released', 'canceled' => 'Canceled', 'returned' => 'Returned', 'unreleased' => 'Unreleased', default => $status };
            $query->where('status', $status);
        }
        return $query;
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

    public function edit(Release $release)
    {
        $release->load('items.item.receivingItems.receiving');
        $items = Item::with('receivingItems')->orderBy('name')->get();
        $items->each(fn($i) => $i->attachCodeAvailability());
        $facilities = Facility::active()->orderBy('category')->orderBy('name')->get(['name', 'category']);
        $programs = Program::orderBy('name')->get();
        $coordinators = Coordinator::with('programs')->orderBy('full_name')->get();

        $itemLotNumbers = ReceivingItem::select('item_id', 'lot_number')
            ->whereNotNull('lot_number')
            ->whereIn('item_id', $items->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('item_id')
            ->map(fn($group) => $group->first()->lot_number);

        return view('releases.edit', compact('release', 'items', 'facilities', 'programs', 'coordinators', 'itemLotNumbers'));
    }

    public function update(Request $request, Release $release)
    {
        $request->validate([
            'pas_number'                 => 'nullable|string|max:255',
            'health_program_coordinator' => 'nullable|string|max:255',
            'ptr_itr_ris_no'             => 'nullable|string|max:255',
            'source_docs_ptr_po_no'      => 'nullable|string|max:255',
            'facility_name'              => 'nullable|string|max:255',
            'received_by'                => 'nullable|string|max:255',
            'date_released'              => 'nullable|date',
            'status'                     => 'required|string|in:Unreleased,Released,Released through pass,Canceled,Returned',
            'status_reason'              => 'nullable|string|max:1000',
            'reason_for_transfer'        => 'nullable|string',
            'reason_for_transfer_others' => 'nullable|string|max:255',
            'notes'                      => 'nullable|string',
            'items'                      => 'nullable|array',
            'items.*.item_id'            => 'nullable|exists:items,id',
            'items.*.item_description'   => 'required_with:items|string|max:1000',
            'items.*.quantity_released'  => 'required_with:items|integer|min:1',
            'items.*.uom'                => 'required_with:items|string|max:255',
            'items.*.unit_cost'          => 'required_with:items|numeric|min:0',
            'items.*.lot_number'         => 'nullable|string|max:255',
            'items.*.expiry_date'        => 'nullable|date',
        ]);

        $previousStatus = $release->getRawOriginal('status') ?? $release->status;
        $newStatus      = $request->input('status');

        DB::transaction(function () use ($request, $release, $previousStatus, $newStatus) {
            $facilityName = $request->input('facility_name', $release->facility_name);
            $facilityCategory = $release->facility_category;
            if ($request->has('facility_name') && $facilityName !== $release->facility_name) {
                $facility = \App\Models\Facility::where('name', $facilityName)->first();
                $facilityCategory = $facility ? $facility->category : $request->input('facility_category', $release->facility_category);
            }
            if ($request->input('facility_category')) {
                $facilityCategory = $request->input('facility_category');
            }

            $release->fill($request->only([
                'pas_number', 'health_program_coordinator', 'ptr_itr_ris_no',
                'source_docs_ptr_po_no', 'facility_name',
                'received_by', 'date_released', 'status', 'status_reason', 'notes',
            ]));

            if ($request->has('reason_for_transfer')) {
                $release->reason_for_transfer = $request->input('reason_for_transfer') === 'Others'
                    ? $request->input('reason_for_transfer_others')
                    : $request->input('reason_for_transfer');
            }
            $release->facility_category = $facilityCategory;

            // Sync release items — delete removed, update changed, add new
            $existingItems = $release->items()->get()->keyBy('id');
            $submittedItems = $request->input('items', []);
            $submittedIds = [];

            foreach ($submittedItems as $itemData) {
                $itemId = $itemData['item_id'] ?? null;
                $description = trim((string) ($itemData['item_description'] ?? ''));

                if (empty($itemId) && $description !== '') {
                    $matchedItem = Item::whereRaw('LOWER(name) = ?', [Str::lower($description)])
                        ->orWhereRaw('LOWER(item_code) = ?', [Str::lower($description)])
                        ->first();
                    if (! $matchedItem) {
                        $matchedItem = Item::whereRaw('LOWER(name) like ?', ["%" . Str::lower($description) . "%"])
                            ->orWhereRaw('LOWER(item_code) like ?', ["%" . Str::lower($description) . "%"])
                            ->first();
                    }
                    $itemId = $matchedItem?->id;
                }

                if (empty($itemId) && empty($description)) {
                    continue;
                }

                $item = $itemId ? Item::find($itemId) : null;
                $qty  = (int) ($itemData['quantity_released'] ?? 0);
                $uom  = $itemData['uom'] ?? ($item?->unit ?? '');
                $cost = $itemData['unit_cost'] ?? ($item?->unit_cost ?? null);
                $lot  = $itemData['lot_number'] ?? null;
                $exp  = !empty($itemData['expiry_date']) ? $itemData['expiry_date'] : null;

                if ($itemId && $item) {
                    $category = $item->category;
                } else {
                    $category = $itemData['category'] ?? null;
                }

                // If item_data contains a hidden _release_item_id, try to update existing
                $releaseItemId = $itemData['_release_item_id'] ?? null;
                if ($releaseItemId && $existingItems->has($releaseItemId)) {
                    $existingItem = $existingItems[$releaseItemId];
                    $submittedIds[] = $releaseItemId;

                    $oldQty = (int) $existingItem->quantity_released;
                    $qtyDiff = $qty - $oldQty;

                    $existingItem->update([
                        'item_id'           => $itemId,
                        'item_description'  => $description ?: ($item?->name ?? $existingItem->item_description),
                        'category'          => $category ?: $existingItem->category,
                        'quantity_released' => $qty,
                        'uom'               => $uom ?: $existingItem->uom,
                        'lot_number'        => $lot,
                        'unit_cost'         => $cost,
                        'expiry_date'       => $exp,
                    ]);

                    // Adjust stock for quantity change (only when already active/unreleased — not during status transition)
                    if ($qtyDiff !== 0) {
                        $itemIdForStock = $existingItem->item_id;
                        $stockItem = Item::find($itemIdForStock);
                        if ($stockItem) {
                            if ($qtyDiff > 0) {
                                $stockItem->decrement('quantity_on_hand', $qtyDiff);
                            } else {
                                $stockItem->increment('quantity_on_hand', abs($qtyDiff));
                            }
                        }
                    }

                    // Propagate description/UOM/unit_cost changes to ReceivingItems
                    if ($item && $lot) {
                        \App\Models\ReceivingItem::where('item_id', $item->id)
                            ->where('lot_number', $lot)
                            ->update([
                                'item_description' => $description ?: $item->name,
                                'uom'         => $uom,
                                'unit_cost'   => $cost,
                            ]);
                    }
                } else {
                    // New release item — check stock
                    if ($item && $item->quantity_on_hand < $qty) {
                        throw new \Exception("Not enough stock for item {$item->name}. Available: {$item->quantity_on_hand}, Requested: {$qty}.");
                    }

                    $newItem = ReleaseItem::create([
                        'release_id'        => $release->id,
                        'item_id'           => $itemId,
                        'item_code'         => $code,
                        'item_description'  => $description ?: ($item?->name ?? ''),
                        'category'          => $category,
                        'quantity_released' => $qty,
                        'uom'               => $uom,
                        'lot_number'        => $lot,
                        'unit_cost'         => $cost,
                        'expiry_date'       => $exp,
                    ]);
                    $submittedIds[] = $newItem->id;

                    // Deduct stock for new item
                    if ($item) {
                        $item->decrement('quantity_on_hand', $qty);

                        if ($lot) {
                            \App\Models\ReceivingItem::where('item_id', $item->id)
                                ->where('lot_number', $lot)
                                ->update([
                                    'item_description' => $description ?: $item->name,
                                    'uom'         => $uom,
                                    'unit_cost'   => $cost,
                                ]);
                        }
                    }
                }
            }

            // Delete removed release items — restore stock
            $removedItems = $existingItems->whereNotIn('id', $submittedIds)->values();
            foreach ($removedItems as $removed) {
                $stockItem = Item::find($removed->item_id);
                if ($stockItem) {
                    $stockItem->increment('quantity_on_hand', (int) $removed->quantity_released);
                }
                $removed->delete();
            }

            $release->load('items');
            $this->applyStatusTransition($release, $newStatus, $previousStatus);
            $release->save();
        });

        return redirect()->route('releases.index')->with('success', 'Release details updated successfully.');
    }

    public function create()
    {
        $items = Item::with('receivingItems')->orderBy('name')->get();
        $items->each(fn($i) => $i->attachCodeAvailability());
        $facilities = Facility::active()->orderBy('category')->orderBy('name')->get(['name', 'category']);
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

        // Auto-generate PTR/ITR/RIS No. in format: {TYPE}-yyyy-mm-XXXX
        $year = now()->format('Y');
        $month = now()->format('m');
        $ptrType = 'PTR'; // default type
        $prefix = "{$ptrType}-{$year}-{$month}-";

        // Get the last sequential number across ALL types (PTR, ITR, RIS) for this year-month
        $nextSeq = $this->nextYearSequence(Release::class, 'ptr_itr_ris_no', "%-{$year}-{$month}-%");

        $ptrNumber = $prefix . $nextSeq;

        return view('releases.create', compact('items', 'ptrNumber', 'year', 'month', 'itemLotNumbers', 'programs', 'coordinators', 'facilities'));
    }

    public function nextPtrNumber(string $type)
    {
        $type = strtoupper($type);
        if (!in_array($type, ['PTR', 'ITR', 'RIS'])) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $year = now()->format('Y');
        $month = now()->format('m');
        $prefix = "{$type}-{$year}-{$month}-";

        // Get the last sequential number across ALL types (PTR, ITR, RIS) for this year-month
        $nextSeq = $this->nextYearSequence(Release::class, 'ptr_itr_ris_no', "%-{$year}-{$month}-%");

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
            'source_docs_ptr_po_no' => 'required|string|max:255',
            'facility_name' => 'required|string|max:255',
            'received_by' => 'required|string|max:255',
            'date_released' => 'nullable|date',
            'status' => 'required|string|max:255',
            'reason_for_transfer' => 'nullable|string',
            'reason_for_transfer_others' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_description' => 'required|string|max:1000',
            'items.*.quantity_released' => 'required|integer|min:1',
            'items.*.uom' => 'required|string|max:255',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $facilityName = $request->input('facility_name');
                $facilityCategory = null;
                if ($facilityName) {
                    $facility = \App\Models\Facility::where('name', $facilityName)->first();
                    $facilityCategory = $facility ? $facility->category : null;
                }

                $release = Release::create([
                    'release_number' => 'REL-' . strtoupper(Str::random(8)),
                    'pas_number' => $request->input('pas_number'),
                    // After saving a new release slip, it starts as "Unreleased".
                    // Status will be updated later via dedicated actions.
                    'status' => 'Unreleased',
                    'health_program_coordinator' => $request->input('health_program_coordinator'),
                    'ptr_itr_ris_no' => $request->input('ptr_itr_ris_no'),
                    'source_docs_ptr_po_no' => $request->input('source_docs_ptr_po_no'),
                    'facility_name' => $facilityName,
                    'facility_category' => $facilityCategory,
                    'reason_for_transfer' => $request->input('reason_for_transfer') === 'Others'
                        ? $request->input('reason_for_transfer_others')
                        : $request->input('reason_for_transfer'),
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
                        'item_code' => $itemData['item_code'] ?? null,
                        'item_description' => $itemData['item_description'] ?? $item->name,
                        'category' => $item->category,
                        'quantity_released' => $itemData['quantity_released'],
                        'uom' => $itemData['uom'] ?? $item->unit,
                        'lot_number' => $itemData['lot_number'] ?? null,
                        'unit_cost' => $itemData['unit_cost'] ?? null,
                        'expiry_date' => !empty($itemData['expiry_date']) ? $itemData['expiry_date'] : null,
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

