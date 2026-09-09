<?php

namespace App\Http\Controllers;

use App\Exports\ItemsExport;
use App\Models\Item;
use App\Models\Program;
use App\Models\ReceivingItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $category = $request->query('category');
        $program = $request->query('program');

        $query = Item::query()->whereHas('receivingItems');

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('receivingItems', function ($receivingQuery) use ($search) {
                        $receivingQuery->where('item_code', 'like', "%{$search}%");
                    })
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('stock_keeping_unit', 'like', "%{$search}%")
                    ->orWhere('program_coordinator', 'like', "%{$search}%");
            });
        }

        if ($status) {
            if ($status === 'available') {
                $query->where('quantity_on_hand', '>', 0)
                      ->whereColumn('quantity_on_hand', '>', 'reorder_level');
            } elseif ($status === 'low') {
                $query->where('quantity_on_hand', '>', 0)
                      ->where('quantity_on_hand', '<=', 20)
                      ->where(function ($q) {
                          $q->where(function ($q2) {
                              $q2->whereNotNull('reorder_level')
                                 ->where('reorder_level', '>', 0)
                                 ->whereColumn('quantity_on_hand', '<=', 'reorder_level');
                          })->orWhere(function ($q2) {
                              $q2->whereNull('reorder_level')
                                 ->orWhere('reorder_level', 0);
                          });
                      });
            } elseif ($status === 'out') {
                $query->where('quantity_on_hand', '<=', 0);
            }
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($program) {
            $query->where('stock_keeping_unit', $program);
        }

        $categories = Item::select('category')
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->groupBy('category')
            ->orderBy('category')
            ->pluck('category');

        $programs = Program::orderBy('name')->get();

        $groupedItems = $query->with('nextExpiryItem', 'receivingItems.receiving.supplier')->orderBy('name')->get()
            ->groupBy('name')
            ->map(function ($items) {
                $item = $items->first();
                $item->quantity_on_hand = $items->sum('quantity_on_hand');
                $item->product_codes = $items->flatMap(fn ($i) => $i->receivingItems)
                    ->pluck('item_code')
                    ->filter()
                    ->unique()
                    ->values();
                $item->record_count = $items->flatMap(fn ($i) => $i->receivingItems)->count();

                $item->supplier_types = $items->flatMap(fn ($i) => $i->receivingItems)
                    ->map(fn ($ri) => $ri->receiving?->supplier?->supplier_type)
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values()
                    ->implode(', ');
                $item->item_code = $item->product_codes->first();

                return $item;
            })
            ->values();

        $perPageParam = $request->query('per_page', 15);

        if ($perPageParam === 'all') {
            $perPage = PHP_INT_MAX;
        } else {
            $perPage = (int) $perPageParam;

            if ($perPage <= 0) {
                $perPage = 15;
            }
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = new LengthAwarePaginator(
            $groupedItems->forPage($currentPage, $perPage)->values(),
            $groupedItems->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
        $items->withQueryString();

        $supplierStats = DB::table('receivings')
            ->join('suppliers', 'receivings.supplier_id', '=', 'suppliers.id')
            ->join('receiving_items', 'receivings.id', '=', 'receiving_items.receiving_id')
            ->join('items', 'receiving_items.item_id', '=', 'items.id')
            ->whereIn('suppliers.supplier_type', ['DOH', 'GSO'])
            ->select(
                'suppliers.supplier_type',
                DB::raw('COUNT(DISTINCT items.name) as item_count'),
                DB::raw('SUM(receiving_items.quantity_received) as units_received')
            )
            ->groupBy('suppliers.supplier_type')
            ->get()
            ->keyBy('supplier_type');

        foreach (['DOH', 'GSO'] as $supplierType) {
            $supplierStats->put($supplierType, $supplierStats->get($supplierType, (object) [
                'item_count' => 0,
                'units_received' => 0,
            ]));
        }

        return view('items.index', compact('items', 'search', 'status', 'category', 'categories', 'supplierStats', 'programs', 'program'));
    }

    public function show(Item $item)
    {
        $itemGroups = Item::where('name', $item->name)
            ->with([
                'nextExpiryItem',
                'receivingItems' => fn ($query) => $query->with('receiving.supplier')->orderBy('item_code'),
                'releaseItems.release',
            ])
            ->orderBy('location')
            ->get();

        $totalReleased = $itemGroups->sum(fn ($groupedItem) => $groupedItem->releaseItems
            ->filter(fn ($ri) => ! in_array($ri->release->status ?? '', ['Canceled', 'Returned'], true))
            ->sum('quantity_released'));
        $totalReceived = $itemGroups->sum(fn ($groupedItem) => $groupedItem->receivingItems->sum('quantity_received'));
        $totalStock = $itemGroups->sum('quantity_on_hand');
        $deductionPercentage = $totalReceived > 0 ? round(($totalReleased / $totalReceived) * 100) : 0;

        $items = $itemGroups->flatMap(function ($groupedItem) {
            $receivingByCode = $groupedItem->receivingItems
                ->filter(fn ($receivingItem) => filled($receivingItem->item_code))
                ->groupBy('item_code');

            if ($receivingByCode->isEmpty()) {
                $receivingByCode = collect(['' => collect()]);
            }

            return $receivingByCode->map(function ($receivingItems, $productCode) use ($groupedItem) {
                $row = clone $groupedItem;
                $source = $receivingItems->sortByDesc('created_at')->first();
                $receiving = $source?->receiving;
                $row->item_code = $productCode ?: null;
                $row->receivingItems = $receivingItems;
                $row->quantity_on_hand = $receivingItems->isNotEmpty()
                    ? $receivingItems->sum('quantity_received')
                    : $groupedItem->quantity_on_hand;
                if ($source) {
                    $row->category = $source->category ?: $row->category;
                    $row->unit = $source->uom ?: $row->unit;
                    $row->unit_cost = $source->unit_cost ?? $row->unit_cost;
                    $row->location = $receiving?->location ?: $row->location;
                    $row->stock_keeping_unit = $receiving?->stock_keeping_unit ?: $row->stock_keeping_unit;
                    $row->program_coordinator = $receiving?->program_coordinator ?: $row->program_coordinator;
                }
                $row->setRelation('nextExpiryItem', $receivingItems->sortBy('expiry_date')->first());

                return $row;
            });
        })->values();

        $statsRows = DB::table('receivings')
            ->join('suppliers', 'receivings.supplier_id', '=', 'suppliers.id')
            ->join('receiving_items', 'receivings.id', '=', 'receiving_items.receiving_id')
            ->join('items', 'receiving_items.item_id', '=', 'items.id')
            ->where('items.name', $item->name)
            ->whereIn('suppliers.supplier_type', ['DOH', 'GSO'])
            ->select(
                'suppliers.supplier_type',
                DB::raw('SUM(receiving_items.quantity_received) as units_received')
            )
            ->groupBy('suppliers.supplier_type')
            ->get()
            ->keyBy('supplier_type');

        $supplierStats = collect(['DOH', 'GSO'])->mapWithKeys(fn ($type) => [
            $type => (object) [
                'item_count' => $statsRows->has($type) ? 1 : 0,
                'units_received' => $statsRows->get($type)?->units_received ?? 0,
            ]
        ]);

        $deductionHistory = [];

        foreach ($itemGroups as $groupedItem) {
            foreach ($groupedItem->releaseItems as $releaseItem) {
                $release    = $releaseItem->release;
                $isInactive = in_array($release->status, ['Canceled', 'Returned'], true);

                $deductionHistory[] = [
                    'date'      => $release->date_released,
                    'type'      => 'Release',
                    'direction' => 'deduct',
                    'item_code' => $groupedItem->item_code,
                    'reference' => $release->ptr_itr_ris_no ?? $release->release_number,
                    'quantity'  => $releaseItem->quantity_released,
                    'facility'  => $release->facility_name,
                    'status'    => $release->status,
                    'reason'    => $release->status_reason ?? null,
                    'release_id' => $release->id,
                ];

                if ($isInactive) {
                    $deductionHistory[] = [
                        'date'      => $release->updated_at,
                        'type'      => $release->status,
                        'direction' => 'restore',
                        'item_code' => $groupedItem->item_code,
                        'reference' => $release->ptr_itr_ris_no ?? $release->release_number,
                        'quantity'  => $releaseItem->quantity_released,
                        'facility'  => $release->facility_name,
                        'status'    => $release->status,
                        'reason'    => $release->status_reason ?? null,
                        'release_id' => $release->id,
                    ];
                }
            }
        }

        usort($deductionHistory, function ($a, $b) {
            $aTimestamp = $a['date']?->timestamp ?? 0;
            $bTimestamp = $b['date']?->timestamp ?? 0;
            return $bTimestamp - $aTimestamp;
        });

        return view('items.show', compact('item', 'items', 'totalStock', 'totalReleased', 'deductionPercentage', 'deductionHistory', 'supplierStats'));
    }

    public function productCodeShow(Item $item, $productCode)
    {
        $receivingItem = ReceivingItem::where('item_id', $item->id)
            ->where('item_code', $productCode)
            ->with('receiving.supplier')
            ->firstOrFail();

        $product = $item->load('nextExpiryItem', 'releaseItems.release');
        $product->item_code = $receivingItem->item_code;

        $totalReleased = $product->releaseItems
            ->filter(fn ($ri) => ! in_array($ri->release->status ?? '', ['Canceled', 'Returned'], true))
            ->sum('quantity_released');
        $totalReceived = $product->receivingItems->sum('quantity_received');
        $totalStock = $product->quantity_on_hand;
        $deductionPercentage = $totalReceived > 0 ? round(($totalReleased / $totalReceived) * 100) : 0;

        $deductionHistory = [];
        foreach ($product->releaseItems as $releaseItem) {
            $release = $releaseItem->release;

            $isInactive = in_array($release->status, ['Canceled', 'Returned'], true);

            // Original release row — always shown
            $deductionHistory[] = [
                'date'      => $release->date_released,
                'type'      => 'Release',
                'direction' => 'deduct',
                'item_code' => $product->item_code,
                'reference' => $release->ptr_itr_ris_no ?? $release->release_number,
                'quantity'  => $releaseItem->quantity_released,
                'facility'  => $release->facility_name,
                'status'    => $release->status,
                'reason'    => $release->status_reason ?? null,
                'release_id' => $release->id,
            ];

            // Stock restore row — only when Canceled or Returned
            if ($isInactive) {
                $deductionHistory[] = [
                    'date'      => $release->updated_at,
                    'type'      => $release->status,
                    'direction' => 'restore',
                    'item_code' => $product->item_code,
                    'reference' => $release->ptr_itr_ris_no ?? $release->release_number,
                    'quantity'  => $releaseItem->quantity_released,
                    'facility'  => $release->facility_name,
                    'status'    => $release->status,
                    'reason'    => $release->status_reason ?? null,
                    'release_id' => $release->id,
                ];
            }
        }

        usort($deductionHistory, function ($a, $b) {
            $aTimestamp = $a['date']?->timestamp ?? 0;
            $bTimestamp = $b['date']?->timestamp ?? 0;
            return $bTimestamp - $aTimestamp;
        });

        return view('items.productcode-show', compact('item', 'product', 'totalStock', 'totalReleased', 'deductionPercentage', 'deductionHistory'));
    }

    public function export(Request $request)
    {
        [$items, $title] = $this->filteredItems($request);
        $filename = 'Items_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $title) . '.xlsx';

        return Excel::download(new ItemsExport($items, $title), $filename);
    }

    public function printView(Request $request)
    {
        [$items, $title] = $this->filteredItems($request);

        return view('items.print', compact('items', 'title'));
    }

    private function filteredItems(Request $request): array
    {
        $search   = trim((string) $request->query('search', ''));
        $status   = $request->query('status', '');
        $category = trim((string) $request->query('category', ''));
        $program  = trim((string) $request->query('program', ''));

        $query = Item::query()->whereHas('receivingItems');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                                    ->orWhereHas('receivingItems', function ($receivingQuery) use ($search) {
                                            $receivingQuery->where('item_code', 'like', "%{$search}%");
                                    })
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('stock_keeping_unit', 'like', "%{$search}%")
                  ->orWhere('program_coordinator', 'like', "%{$search}%");
            });
        }

        if ($status === 'available') {
            $query->where('quantity_on_hand', '>', 0)->whereColumn('quantity_on_hand', '>', 'reorder_level');
        } elseif ($status === 'low') {
            $query->where('quantity_on_hand', '>', 0)->where('quantity_on_hand', '<=', 20)
                  ->where(function ($q) {
                      $q->where(function ($q2) {
                          $q2->whereNotNull('reorder_level')->where('reorder_level', '>', 0)->whereColumn('quantity_on_hand', '<=', 'reorder_level');
                      })->orWhere(function ($q2) {
                          $q2->whereNull('reorder_level')->orWhere('reorder_level', 0);
                      });
                  });
        } elseif ($status === 'out') {
            $query->where('quantity_on_hand', '<=', 0);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($program) {
            $query->where('stock_keeping_unit', $program);
        }

        $items = $query->with('receivingItems')->orderBy('name')->get()->map(function ($item) {
            $item->product_codes = $item->receivingItems->pluck('item_code')->filter()->unique()->values();
            $item->item_code = $item->product_codes->first();

            return $item;
        });

        // Build a human-readable title based on active filters
        $parts = [];
        if ($status === 'low')           $parts[] = 'Low Stock';
        elseif ($status === 'out')       $parts[] = 'Out of Stock';
        elseif ($status === 'available') $parts[] = 'Available';
        if ($category) $parts[] = $category;
        if ($program)  $parts[] = $program;
        if ($search)   $parts[] = '"' . $search . '"';
        $title = $parts ? implode(' · ', $parts) . ' Items' : 'All Items';

        return [$items, $title];
    }
}
