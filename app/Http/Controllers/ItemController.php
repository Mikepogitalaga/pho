<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

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

        $items = $query->with('nextExpiryItem')->orderBy('name')->paginate(15)->withQueryString();

        return view('items.index', compact('items', 'search', 'status', 'category', 'categories'));
    }

    public function show(Item $item)
    {
        $item->load('nextExpiryItem', 'releaseItems');

        $totalReleased = $item->releaseItems()->sum('quantity_released');
        $totalReceived = $item->receivingItems()->sum('quantity_received');
        $deductionPercentage = $totalReceived > 0 ? round(($totalReleased / $totalReceived) * 100) : 0;

        $deductionHistory = [];

        foreach ($item->releaseItems as $releaseItem) {
            $release = $releaseItem->release;
            $deductionHistory[] = [
                'date' => $release->date_released,
                'type' => 'Release',
                'reference' => $release->ptr_itr_ris_no ?? $release->release_number,
                'quantity' => $releaseItem->quantity_released,
                'facility' => $release->facility_name,
                'status' => $release->status,
            ];
        }

        usort($deductionHistory, function ($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });

        return view('items.show', compact('item', 'totalReleased', 'deductionPercentage', 'deductionHistory'));
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
