<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Release;
use App\Models\ReleaseItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get item IDs that were received from suppliers of a given type.
     */
    private function getItemIdsBySupplierType(string $type): array
    {
        return ReceivingItem::whereHas('receiving.supplier', function ($q) use ($type) {
            $q->where('supplier_type', $type);
        })->pluck('item_id')->unique()->toArray();
    }

    /**
     * Get release metrics scoped to items from a specific supplier type.
     */
    private function getReleaseMetricsBySupplierType(string $type)
    {
        $itemIds = $this->getItemIdsBySupplierType($type);

        if (empty($itemIds)) {
            return [
                'totalReleases' => 0,
                'totalReleasedItems' => 0,
                'monthlyReleases' => 0,
                'monthlyReleasedQty' => 0,
                'recentReleases' => collect(),
                'monthlyReleaseLabels' => [],
                'monthlyReleaseCounts' => [],
            ];
        }

        // Releases that contain items from this supplier type
        $releaseIds = ReleaseItem::whereIn('item_id', $itemIds)
            ->whereHas('release', function ($q) {
                $q->whereIn('status', ['Released', 'Released through pass']);
            })->pluck('release_id')->unique()->toArray();

        $totalReleases = count($releaseIds);
        $totalReleasedItems = ReleaseItem::whereIn('item_id', $itemIds)
            ->whereHas('release', function ($q) {
                $q->whereIn('status', ['Released', 'Released through pass']);
            })->sum('quantity_released');

        $monthlyReleaseIds = ReleaseItem::whereIn('item_id', $itemIds)
            ->whereHas('release', function ($q) {
                $q->whereIn('status', ['Released', 'Released through pass'])
                  ->whereBetween('date_released', [now()->startOfMonth(), now()->endOfMonth()]);
            })->pluck('release_id')->unique()->toArray();
        $monthlyReleases = count($monthlyReleaseIds);

        $monthlyReleasedQty = ReleaseItem::whereIn('item_id', $itemIds)
            ->whereHas('release', function ($q) {
                $q->whereIn('status', ['Released', 'Released through pass'])
                  ->whereBetween('date_released', [now()->startOfMonth(), now()->endOfMonth()]);
            })->sum('quantity_released');

        $recentReleases = Release::whereIn('id', $releaseIds)
            ->whereIn('status', ['Released', 'Released through pass'])
            ->latest('date_released')
            ->limit(5)
            ->get();

        // Chart: Monthly releases for last 6 months
        $monthlyReleaseLabels = [];
        $monthlyReleaseCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $label = $month->format('M Y');
            $monthlyReleaseLabels[] = $label;

            $count = ReleaseItem::whereIn('item_id', $itemIds)
                ->whereHas('release', function ($q) use ($month) {
                    $q->whereIn('status', ['Released', 'Released through pass'])
                      ->whereYear('date_released', $month->year)
                      ->whereMonth('date_released', $month->month);
                })->sum('quantity_released');
            $monthlyReleaseCounts[] = $count;
        }

        return compact(
            'totalReleases',
            'totalReleasedItems',
            'monthlyReleases',
            'monthlyReleasedQty',
            'recentReleases',
            'monthlyReleaseLabels',
            'monthlyReleaseCounts'
        );
    }

    public function index()
    {
        // ── KPI Data ──────────────────────────────────────────────────
        $totalItems = Item::count();
        $totalSuppliers = Supplier::count();
        $currentStock = Item::sum('quantity_on_hand');
        $totalReceived = ReceivingItem::sum('quantity_received');
        $totalReleased = ReleaseItem::whereHas('release', function ($q) {
            $q->whereIn('status', ['Released', 'Released through pass']);
        })->sum('quantity_released');

        // Low Stock items (qty <= reorder_level or <= 20 if no reorder)
        $lowStockItems = Item::query()
            ->where('quantity_on_hand', '>', 0)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('reorder_level')
                        ->where('reorder_level', '>', 0)
                        ->whereColumn('quantity_on_hand', '<=', 'reorder_level');
                })->orWhere(function ($q2) {
                    $q2->where(function ($q3) {
                        $q3->whereNull('reorder_level')
                            ->orWhere('reorder_level', 0);
                    })->where('quantity_on_hand', '<=', 20);
                });
            })
            ->orderBy('quantity_on_hand')
            ->limit(10)
            ->get();

        // Expiring items within 30 days
        $expiringItemsCount = ReceivingItem::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->whereDate('expiry_date', '>=', now())
            ->count();

        $upcomingExpiries = ReceivingItem::with('item')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->whereDate('expiry_date', '>=', now())
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        // Inventory Value
        $inventoryValue = Item::selectRaw('SUM(quantity_on_hand * COALESCE(unit_cost, 0)) as total_value')
            ->value('total_value') ?? 0;

        // ── Chart Data ────────────────────────────────────────────────

        // 1. Supply Movement Trend (last 12 months)
        $supplyMovement = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $received = ReceivingItem::whereHas('receiving', function ($q) use ($start, $end) {
                $q->whereBetween('date_received', [$start, $end]);
            })->sum('quantity_received');

            $released = ReleaseItem::whereHas('release', function ($q) use ($start, $end) {
                $q->whereIn('status', ['Released', 'Released through pass'])
                  ->whereBetween('date_released', [$start, $end]);
            })->sum('quantity_released');

            $supplyMovement->push([
                'month' => $month->format('M Y'),
                'received' => $received,
                'released' => $released,
            ]);
        }

        // 2. Inventory by Category
        $inventoryByCategory = Item::select('category', DB::raw('COUNT(*) as count'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category ?: 'Uncategorized',
                    'count' => $item->count,
                ];
            });

        // 3. Top 10 Most Released Items
        $topReleasedItems = ReleaseItem::select(
                'item_id',
                DB::raw('SUM(quantity_released) as total_released'),
                DB::raw('MAX(item_description) as item_description')
            )
            ->whereHas('release', function ($q) {
                $q->whereIn('status', ['Released', 'Released through pass']);
            })
            ->groupBy('item_id')
            ->orderByDesc('total_released')
            ->limit(10)
            ->with('item')
            ->get()
            ->map(function ($ri) {
                return [
                    'name' => $ri->item?->name ?? $ri->item_description ?? "Item #{$ri->item_id}",
                    'total' => $ri->total_released,
                ];
            });

        // 4. Monthly Receiving by Supplier (last 6 months, top suppliers)
        $monthlyReceivingBySupplier = ReceivingItem::select(
                'suppliers.company_name',
                DB::raw('SUM(receiving_items.quantity_received) as total_received')
            )
            ->join('receivings', 'receiving_items.receiving_id', '=', 'receivings.id')
            ->join('suppliers', 'receivings.supplier_id', '=', 'suppliers.id')
            ->where('receivings.date_received', '>=', now()->subMonths(6))
            ->groupBy('suppliers.id', 'suppliers.company_name')
            ->orderByDesc('total_received')
            ->limit(6)
            ->get()
            ->map(function ($row) {
                return [
                    'supplier' => $row->company_name,
                    'total' => $row->total_received,
                ];
            });

        // 5. Releases by Facility
        $releasesByFacility = ReleaseItem::select(
                'releases.facility_name',
                DB::raw('SUM(release_items.quantity_released) as total_released')
            )
            ->join('releases', 'release_items.release_id', '=', 'releases.id')
            ->whereIn('releases.status', ['Released', 'Released through pass'])
            ->whereNotNull('releases.facility_name')
            ->groupBy('releases.facility_name')
            ->orderByDesc('total_released')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return [
                    'facility' => $row->facility_name,
                    'total' => $row->total_released,
                ];
            });

        // 6. Stock Status Distribution
        $totalItemCount = Item::count();
        $outOfStockCount = Item::where('quantity_on_hand', '<=', 0)->count();
        $availableCount = Item::where('quantity_on_hand', '>', 0)->count();
        $lowStockCount = $lowStockItems->count();

        // ── Recent Records ────────────────────────────────────────────
        $recentReceived = Receiving::latest('date_received')->limit(5)->get();
        $recentReleased = Release::latest('date_released')->limit(5)->get();

        // ── Notifications ─────────────────────────────────────────────
        $notifications = collect();

        foreach ($lowStockItems as $item) {
            $notifications->push([
                'type' => 'warning',
                'message' => "Low stock: {$item->item_code} · {$item->name} ({$item->quantity_on_hand} on hand)",
                'href' => route('items.show', $item),
            ]);
        }

        foreach ($upcomingExpiries as $expiry) {
            $notifications->push([
                'type' => 'danger',
                'message' => "Expiring soon: {$expiry->item->item_code} · {$expiry->item->name} on {$expiry->expiry_date->format('M d, Y')}",
                'href' => route('items.show', $expiry->item),
            ]);
        }

        $notificationCount = $notifications->count();

        return view('dashboard', compact(
            'totalItems',
            'totalSuppliers',
            'currentStock',
            'totalReceived',
            'totalReleased',
            'lowStockItems',
            'expiringItemsCount',
            'upcomingExpiries',
            'inventoryValue',
            'supplyMovement',
            'inventoryByCategory',
            'topReleasedItems',
            'monthlyReceivingBySupplier',
            'releasesByFacility',
            'availableCount',
            'lowStockCount',
            'outOfStockCount',
            'totalItemCount',
            'recentReceived',
            'recentReleased',
            'notifications',
            'notificationCount'
        ));
    }

    /**
     * Aggregated dashboard for ALL DOH suppliers.
     */
    public function dohIndex()
    {
        return $this->supplierTypeDashboard('DOH', 'DOH');
    }

    /**
     * Aggregated dashboard for ALL GSO suppliers.
     */
    public function gsoIndex()
    {
        return $this->supplierTypeDashboard('GSO', 'GSO');
    }

    /**
     * Shared logic for supplier-type-wide dashboards.
     */
    private function supplierTypeDashboard(string $type, string $viewSlug)
    {
        // ── KPI Data ──────────────────────────────────────────────────
        $totalSuppliers = Supplier::where('supplier_type', $type)->count();

        $totalReceivingsAll = Receiving::whereHas('supplier', function ($q) use ($type) {
            $q->where('supplier_type', $type);
        })->count();

        $totalItemsReceived = ReceivingItem::whereHas('receiving.supplier', function ($q) use ($type) {
            $q->where('supplier_type', $type);
        })->sum('quantity_received');

        $monthlyReceivings = Receiving::whereHas('supplier', function ($q) use ($type) {
            $q->where('supplier_type', $type);
        })->whereBetween('date_received', [now()->startOfMonth(), now()->endOfMonth()])->count();

        $monthlyReceivedQty = ReceivingItem::whereHas('receiving.supplier', function ($q) use ($type) {
            $q->where('supplier_type', $type);
        })->whereHas('receiving', function ($q) {
            $q->whereBetween('date_received', [now()->startOfMonth(), now()->endOfMonth()]);
        })->sum('quantity_received');

        // ── Chart: Monthly receivings (last 6 months) ──────────────────
        $monthlyLabels = [];
        $monthlyCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $label = $month->format('M Y');
            $monthlyLabels[] = $label;
            $monthlyCounts[] = Receiving::whereHas('supplier', function ($q) use ($type) {
                $q->where('supplier_type', $type);
            })
                ->whereYear('date_received', $month->year)
                ->whereMonth('date_received', $month->month)
                ->count();
        }

        // ── Chart: Supply Movement (last 6 months) ─────────────────────
        $supplyMovement = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $received = ReceivingItem::whereHas('receiving.supplier', function ($q) use ($type) {
                $q->where('supplier_type', $type);
            })->whereHas('receiving', function ($q) use ($start, $end) {
                $q->whereBetween('date_received', [$start, $end]);
            })->sum('quantity_received');

            $released = ReleaseItem::whereHas('release', function ($q) use ($start, $end, $type) {
                $q->whereIn('status', ['Released', 'Released through pass'])
                  ->whereBetween('date_released', [$start, $end]);
            })->whereHas('release.items.item.receivingItems.receiving.supplier', function ($q) use ($type) {
                $q->where('supplier_type', $type);
            })->sum('quantity_released');

            $supplyMovement->push([
                'month' => $month->format('M Y'),
                'received' => (int) $received,
                'released' => (int) $released,
            ]);
        }

        // ── Recent Receivings ─────────────────────────────────────────
        $recentReceived = Receiving::whereHas('supplier', function ($q) use ($type) {
            $q->where('supplier_type', $type);
        })->latest('date_received')->limit(5)->get();

        // ── Top Suppliers by receiving volume (last 6 months) ─────────
        $topSuppliers = Supplier::where('supplier_type', $type)
            ->withCount(['receivings' => function ($q) {
                $q->where('date_received', '>=', now()->subMonths(6));
            }])
            ->having('receivings_count', '>', 0)
            ->orderByDesc('receivings_count')
            ->limit(5)
            ->get();

        $shareLabels = $topSuppliers->pluck('company_name')->toArray();
        $shareCounts = $topSuppliers->pluck('receivings_count')->toArray();

        // ── Release metrics scoped to items from this supplier type ──
        $releaseMetrics = $this->getReleaseMetricsBySupplierType($type);

        // ── Type-specific heading & subheading ────────────────────────
        $dashboardTitle = $type === 'DOH' ? 'DOH Dashboard' : 'GSO Dashboard';
        $dashboardSubheading = $type === 'DOH'
            ? 'Aggregated DOH supply chain overview — receiving to release pipeline.'
            : 'Aggregated GSO supply chain overview — receiving to release pipeline.';

        // ── Notifications (low stock / expiring items from this type) ──
        $notifications = collect();
        $notificationCount = 0;

        $typeItemIds = $this->getItemIdsBySupplierType($type);
        if (!empty($typeItemIds)) {
            $lowStockItems = Item::whereIn('id', $typeItemIds)
                ->where('quantity_on_hand', '>', 0)
                ->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->whereNotNull('reorder_level')
                            ->where('reorder_level', '>', 0)
                            ->whereColumn('quantity_on_hand', '<=', 'reorder_level');
                    })->orWhere(function ($q2) {
                        $q2->where(function ($q3) {
                            $q3->whereNull('reorder_level')->orWhere('reorder_level', 0);
                        })->where('quantity_on_hand', '<=', 20);
                    });
                })
                ->orderBy('quantity_on_hand')
                ->limit(10)
                ->get();

            foreach ($lowStockItems as $item) {
                $notifications->push([
                    'type' => 'warning',
                    'message' => "Low stock: {$item->item_code} · {$item->name} ({$item->quantity_on_hand} on hand)",
                    'href' => route('items.show', $item),
                ]);
            }

            $upcomingExpiries = ReceivingItem::with('item')
                ->whereIn('item_id', $typeItemIds)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', now()->addDays(30))
                ->whereDate('expiry_date', '>=', now())
                ->orderBy('expiry_date')
                ->limit(10)
                ->get();

            foreach ($upcomingExpiries as $expiry) {
                $notifications->push([
                    'type' => 'danger',
                    'message' => "Expiring soon: {$expiry->item->item_code} · {$expiry->item->name} on {$expiry->expiry_date->format('M d, Y')}",
                    'href' => route('items.show', $expiry->item),
                ]);
            }
        }

        $notificationCount = $notifications->count();

        return view("{$viewSlug}.overview", compact(
            'type',
            'totalSuppliers',
            'totalReceivingsAll',
            'totalItemsReceived',
            'monthlyReceivings',
            'monthlyReceivedQty',
            'monthlyLabels',
            'monthlyCounts',
            'supplyMovement',
            'recentReceived',
            'topSuppliers',
            'shareLabels',
            'shareCounts',
            'dashboardTitle',
            'dashboardSubheading',
            'notifications',
            'notificationCount'
        ) + $releaseMetrics);
    }
}
