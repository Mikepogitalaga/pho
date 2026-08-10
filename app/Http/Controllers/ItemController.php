<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $category = $request->query('category');

        $query = Item::query();

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
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
                // Low stock rule (fallback):
                // - If reorder_level is set (>0), low if quantity_on_hand <= reorder_level
                // - Otherwise (null/0), low if quantity_on_hand <= 20
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

        $categories = Item::select('category')
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->groupBy('category')
            ->orderBy('category')
            ->pluck('category');

        $groupedItems = $query->with('nextExpiryItem', 'receivingItems.receiving.supplier')->orderBy('name')->get()
            ->groupBy('name')
            ->map(function ($items) {
                $item = $items->first();
                $item->quantity_on_hand = $items->sum('quantity_on_hand');
                $item->record_count = $items->count();

                $item->supplier_types = $items->flatMap(fn ($i) => $i->receivingItems)
                    ->map(fn ($ri) => $ri->receiving?->supplier?->supplier_type)
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values()
                    ->implode(', ');

                return $item;
            })
            ->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = new LengthAwarePaginator(
            $groupedItems->forPage($currentPage, 15)->values(),
            $groupedItems->count(),
            15,
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

        return view('items.index', compact('items', 'search', 'status', 'category', 'categories', 'supplierStats'));
    }

    public function show(Item $item)
    {
        $items = Item::where('name', $item->name)
            ->with('nextExpiryItem', 'receivingItems.receiving.supplier', 'releaseItems.release')
            ->orderBy('location')
            ->orderBy('item_code')
            ->get();

        $totalReleased = $items->sum(fn ($groupedItem) => $groupedItem->releaseItems
            ->filter(fn ($ri) => ! in_array($ri->release->status ?? '', ['Canceled', 'Returned'], true))
            ->sum('quantity_released'));
        $totalReceived = $items->sum(fn ($groupedItem) => $groupedItem->receivingItems->sum('quantity_received'));
        $totalStock = $items->sum('quantity_on_hand');
        $deductionPercentage = $totalReceived > 0 ? round(($totalReleased / $totalReceived) * 100) : 0;

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

        foreach ($items as $groupedItem) {
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
        $product = Item::where('item_code', $productCode)
            ->with('nextExpiryItem', 'receivingItems.receiving.supplier', 'releaseItems.release')
            ->firstOrFail();

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
        $search = $request->query('search');
        $status = $request->query('status');
        $category = $request->query('category');

        $query = Item::query();

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
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
                // Export uses same low-stock fallback rule
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

        $items = $query->orderBy('name')->get();
        $filename = 'items-export-' . now()->format('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($items) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Product Code',
                'Item Description',
                'Category',
                'UOM',
                'Current Stock',
                'Unit Cost',
                'Location',
                'Stock Keeping Unit',
                'Program Coordinator',
                'Status',
            ]);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->item_code,
                    $item->name,
                    $item->category,
                    $item->unit,
                    $item->quantity_on_hand,
                    $item->unit_cost,
                    $item->location,
                    $item->stock_keeping_unit,
                    $item->program_coordinator,
                    $item->status,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
