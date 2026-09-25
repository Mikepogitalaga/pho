<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Pas;
use App\Models\Program;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Release;
use App\Models\ReleaseItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get program names assigned to the current user.
     *
     * @return array<int, string>
     */
    private function userProgramNames(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $programIds = $user->all_programs;

        if ($programIds->isEmpty()) {
            return [];
        }

        return Program::whereIn('id', $programIds)
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Determine whether the dashboard should be scoped to the current user's programs.
     */
    private function isProgramScoped(): bool
    {
        $user = auth()->user();

        if (! $user || $user->isAdmin()) {
            return false;
        }

        return $user->all_programs->isNotEmpty();
    }

    /**
     * Scope an item query to the current user's programs.
     */
    private function scopeItemQuery($query)
    {
        $programNames = $this->userProgramNames();

        if (! empty($programNames)) {
            $query->whereIn('stock_keeping_unit', $programNames);
        }

        return $query;
    }

    /**
     * Scope a receiving query to the current user's programs.
     */
    private function scopeReceivingQuery($query)
    {
        $programNames = $this->userProgramNames();

        if (! empty($programNames)) {
            $query->whereIn('stock_keeping_unit', $programNames);
        }

        return $query;
    }

    /**
     * Scope a pas query to the current user's programs.
     */
    private function scopePasQuery($query)
    {
        $programNames = $this->userProgramNames();

        if (! empty($programNames)) {
            $query->whereIn('program', $programNames);
        }

        return $query;
    }
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
        $programScoped = $this->isProgramScoped();

        // ── KPI Data ──────────────────────────────────────────────────
        $totalItemsQuery = Item::query()->whereHas('receivingItems');
        $totalItems = $programScoped ? $this->scopeItemQuery($totalItemsQuery)->count() : $totalItemsQuery->count();

        $totalSuppliers = Supplier::count();

        $currentStockQuery = Item::query()->whereHas('receivingItems');
        $currentStock = $programScoped ? $this->scopeItemQuery($currentStockQuery)->sum('quantity_on_hand') : $currentStockQuery->sum('quantity_on_hand');

        $totalReceivedQuery = ReceivingItem::query();
        $totalReceived = $programScoped
            ? $totalReceivedQuery->whereHas('item', fn($q) => $this->scopeItemQuery($q))->sum('quantity_received')
            : $totalReceivedQuery->sum('quantity_received');

        $totalReleasedQuery = ReleaseItem::query()->whereHas('release', function ($q) {
            $q->whereIn('status', ['Released', 'Released through pass']);
        });
        $totalReleased = $programScoped
            ? $totalReleasedQuery->whereHas('item', fn($q) => $this->scopeItemQuery($q))->sum('quantity_released')
            : $totalReleasedQuery->sum('quantity_released');

        // Low Stock items (qty <= reorder_level or <= 20 if no reorder)
        $lowStockItemsQuery = Item::query()
            ->whereHas('receivingItems')
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
            ->limit(10);
        $lowStockItems = $programScoped ? $this->scopeItemQuery($lowStockItemsQuery)->get() : $lowStockItemsQuery->get();
        $lowStockCount = $lowStockItems->count();

        // Expiring items within 30 days
        $expiringItemsCountQuery = ReceivingItem::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(90))
            ->whereDate('expiry_date', '>=', now());
        $expiringItemsCount = $programScoped
            ? $expiringItemsCountQuery->whereHas('item', fn($q) => $this->scopeItemQuery($q))->count()
            : $expiringItemsCountQuery->count();

        $upcomingExpiriesQuery = ReceivingItem::with('item')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(90))
            ->whereDate('expiry_date', '>=', now())
            ->orderBy('expiry_date')
            ->limit(10);
        $upcomingExpiries = $programScoped
            ? $upcomingExpiriesQuery->whereHas('item', fn($q) => $this->scopeItemQuery($q))->get()
            : $upcomingExpiriesQuery->get();

        // Inventory Value
        $inventoryValueQuery = Item::query()
            ->selectRaw('COALESCE(SUM(quantity_on_hand * COALESCE(unit_cost, 0)), 0) as total_value');
        $inventoryValue = $programScoped
            ? $this->scopeItemQuery($inventoryValueQuery)->value('total_value') ?? 0
            : $inventoryValueQuery->value('total_value') ?? 0;

        // ── Chart Data ────────────────────────────────────────────────

        // 1. Supply Movement Trend (last 12 months)
        $supplyMovement = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $receivedQuery = ReceivingItem::whereHas('receiving', function ($q) use ($start, $end) {
                $q->whereBetween('date_received', [$start, $end]);
            });
            $received = $programScoped
                ? $receivedQuery->whereHas('item', fn($q) => $this->scopeItemQuery($q))->sum('quantity_received')
                : $receivedQuery->sum('quantity_received');

            $releasedQuery = ReleaseItem::whereHas('release', function ($q) use ($start, $end) {
                $q->whereIn('status', ['Released', 'Released through pass'])
                  ->whereBetween('date_released', [$start, $end]);
            });
            $released = $programScoped
                ? $releasedQuery->whereHas('item', fn($q) => $this->scopeItemQuery($q))->sum('quantity_released')
                : $releasedQuery->sum('quantity_released');

            $supplyMovement->push([
                'month' => $month->format('M Y'),
                'received' => $received,
                'released' => $released,
            ]);
        }

        // 2. Inventory by Category
        $inventoryByCategoryQuery = Item::whereHas('receivingItems')
            ->select('category', DB::raw('COUNT(*) as count'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('count');
        $inventoryByCategory = $programScoped
            ? $this->scopeItemQuery($inventoryByCategoryQuery)->get()
            : $inventoryByCategoryQuery->get();
        $inventoryByCategory = $inventoryByCategory->map(function ($item) {
            return [
                'category' => $item->category ?: 'Uncategorized',
                'count' => $item->count,
            ];
        });

        // 3. Top 10 Most Released Items
        $topReleasedItemsQuery = ReleaseItem::select(
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
            ->with('item');
        $topReleasedItems = $programScoped
            ? $topReleasedItemsQuery->whereHas('item', fn($q) => $this->scopeItemQuery($q))->get()
            : $topReleasedItemsQuery->get();
        $topReleasedItems = $topReleasedItems->map(function ($ri) {
            return [
                'name' => $ri->item?->name ?? $ri->item_description ?? "Item #{$ri->item_id}",
                'total' => $ri->total_released,
            ];
        });

        // 4. Monthly Receiving by Supplier (last 6 months, top suppliers)
        $monthlyReceivingBySupplierQuery = ReceivingItem::select(
                'suppliers.company_name',
                DB::raw('SUM(receiving_items.quantity_received) as total_received')
            )
            ->join('receivings', 'receiving_items.receiving_id', '=', 'receivings.id')
            ->join('suppliers', 'receivings.supplier_id', '=', 'suppliers.id')
            ->where('receivings.date_received', '>=', now()->subMonths(6))
            ->groupBy('suppliers.id', 'suppliers.company_name')
            ->orderByDesc('total_received')
            ->limit(6);
        if ($programScoped) {
            $monthlyReceivingBySupplierQuery->whereIn('receivings.stock_keeping_unit', $this->userProgramNames());
        }
        $monthlyReceivingBySupplier = $monthlyReceivingBySupplierQuery->get()->map(function ($row) {
            return [
                'supplier' => $row->company_name,
                'total' => $row->total_received,
            ];
        });

        // 5. Releases by Facility
        $releasesByFacilityQuery = ReleaseItem::select(
                'releases.facility_name',
                DB::raw('SUM(release_items.quantity_released) as total_released')
            )
            ->join('releases', 'release_items.release_id', '=', 'releases.id')
            ->whereIn('releases.status', ['Released', 'Released through pass'])
            ->whereNotNull('releases.facility_name')
            ->groupBy('releases.facility_name')
            ->orderByDesc('total_released')
            ->limit(10);
        if ($programScoped) {
            $releasesByFacilityQuery->whereHas('item', fn($q) => $this->scopeItemQuery($q));
        }
        $releasesByFacility = $releasesByFacilityQuery->get()->map(function ($row) {
            return [
                'facility' => $row->facility_name,
                'total' => $row->total_released,
            ];
        });

        // 6. Stock Status Distribution
        $totalItemCountQuery = Item::whereHas('receivingItems');
        $totalItemCount = $programScoped ? $this->scopeItemQuery($totalItemCountQuery)->count() : $totalItemCountQuery->count();

        $outOfStockCountQuery = Item::whereHas('receivingItems')->where('quantity_on_hand', '<=', 0);
        $outOfStockCount = $programScoped ? $this->scopeItemQuery($outOfStockCountQuery)->count() : $outOfStockCountQuery->count();

        $availableCountQuery = Item::whereHas('receivingItems')->where('quantity_on_hand', '>', 0);
        $availableCount = $programScoped ? $this->scopeItemQuery($availableCountQuery)->count() : $availableCountQuery->count();

        // ── Recent Records ────────────────────────────────────────────
        $recentReceivedQuery = Receiving::query();
        $recentReceived = $programScoped
            ? $this->scopeReceivingQuery($recentReceivedQuery)->latest('date_received')->limit(5)->get()
            : $recentReceivedQuery->latest('date_received')->limit(5)->get();

        $recentReleasedQuery = Release::query();
        $recentReleased = $programScoped
            ? $recentReleasedQuery->whereHas('items.item', fn($q) => $this->scopeItemQuery($q))->latest('date_released')->limit(5)->get()
            : $recentReleasedQuery->latest('date_released')->limit(5)->get();

        // ── Notifications ─────────────────────────────────────────────
        $notifications = collect();

        foreach ($lowStockItems as $item) {
            $productCode = $item->receivingItems()->whereNotNull('item_code')->value('item_code');
            $notifications->push([
                'type' => 'warning',
                'label' => 'LOW STOCK',
                'code' => $productCode ?: '—',
                'name' => $item->name,
                'detail' => "{$item->quantity_on_hand} on hand",
                'href' => route('items.show', $item),
            ]);
        }

        foreach ($upcomingExpiries as $expiry) {
            $notifications->push([
                'type' => 'danger',
                'label' => 'EXPIRING SOON',
                'code' => $expiry->item_code ?: '—',
                'name' => $expiry->item->name,
                'detail' => 'Expires ' . $expiry->expiry_date->format('M d, Y'),
                'href' => route('items.show', $expiry->item),
            ]);
        }

        $pendingApprovals = collect();
        if (! $programScoped) {
            $pendingApprovals = Pas::where('request_status', 'pending_approval')
                ->with(['items', 'requester'])
                ->latest('date_of_pass')
                ->limit(5)
                ->get();
        }

        foreach ($pendingApprovals as $pas) {
            $notifications->push([
                'type' => 'info',
                'label' => 'PENDING APPROVAL',
                'code' => $pas->pas_number ?? '—',
                'name' => $pas->facility_name ?? '—',
                'requester' => $pas->requester?->name ?? '—',
                'detail' => 'Submitted ' . ($pas->date_of_pass?->format('M d, Y') ?? '—'),
                'href' => route('pas.view', $pas),
            ]);
        }

        $notificationCount = $notifications->count();

        $programScoped = $this->isProgramScoped();
        $myProgramItemsCount = 0;
        $availableStock = 0;
        $lowStockAlerts = collect();
        $expiringSoon = collect();
        $pendingRequests = 0;
        $approvedRequests = 0;
        $totalPasSubmitted = 0;
        $stockStatusDistribution = [];
        $pasRequestStatus = [];
        $monthlySupplyMovement = collect();
        $expiringItemsTimeline = collect();
        $topItemsByStock = collect();

        if ($programScoped) {
            $userProgramNames = $this->userProgramNames();

            $myProgramItemsQuery = $this->scopeItemQuery(Item::query()->whereHas('receivingItems'));
            $myProgramItemsCount = $myProgramItemsQuery->count();
            $availableStock = $myProgramItemsQuery->sum('quantity_on_hand');

            $lowStockAlerts = $this->scopeItemQuery(Item::query()
                ->whereHas('receivingItems')
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
                ->get());

            $expiringSoonQuery = ReceivingItem::with('item')
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', now()->addDays(90))
                ->whereDate('expiry_date', '>=', now())
                ->orderBy('expiry_date')
                ->limit(10);
            $expiringSoon = $expiringSoonQuery->whereHas('item', fn($q) => $this->scopeItemQuery($q))->get();

            $pasQuery = Pas::query()->whereIn('program', $userProgramNames);
            $pendingRequests = (clone $pasQuery)->where('request_status', 'pending_approval')->count();
            $approvedRequests = (clone $pasQuery)->where('request_status', 'approved')->count();
            $totalPasSubmitted = (clone $pasQuery)->count();

            $pendingApprovalRecords = (clone $pasQuery)
                ->where('request_status', 'pending_approval')
                ->with(['items', 'requester'])
                ->latest('date_of_pass')
                ->limit(5)
                ->get();

            $availableCount = $this->scopeItemQuery(Item::whereHas('receivingItems')->where('quantity_on_hand', '>', 0))->count();
            $lowStockCount = $lowStockAlerts->count();
            $outOfStockCount = $this->scopeItemQuery(Item::whereHas('receivingItems')->where('quantity_on_hand', '<=', 0))->count();
            $stockStatusDistribution = [
                ['status' => 'Available', 'count' => $availableCount - $lowStockCount],
                ['status' => 'Low Stock', 'count' => $lowStockCount],
                ['status' => 'Out of Stock', 'count' => $outOfStockCount],
            ];

            $pasRequestStatus = [
                ['status' => 'Pending', 'count' => $pendingRequests],
                ['status' => 'Approved', 'count' => $approvedRequests],
                ['status' => 'Rejected', 'count' => (clone $pasQuery)->where('request_status', 'rejected')->count()],
            ];

            $monthlySupplyMovement = collect();
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();

                $received = ReceivingItem::whereHas('receiving', function ($q) use ($start, $end) {
                    $q->whereBetween('date_received', [$start, $end]);
                })->whereHas('item', fn($q) => $this->scopeItemQuery($q))->sum('quantity_received');

                $released = ReleaseItem::whereHas('release', function ($q) use ($start, $end) {
                    $q->whereIn('status', ['Released', 'Released through pass'])
                      ->whereBetween('date_released', [$start, $end]);
                })->whereHas('item', fn($q) => $this->scopeItemQuery($q))->sum('quantity_released');

                $monthlySupplyMovement->push([
                    'month' => $month->format('M Y'),
                    'received' => $received,
                    'released' => $released,
                ]);
            }

            $expiringItemsTimeline = $expiringSoon;

            $topItemsByStock = $this->scopeItemQuery(Item::whereHas('receivingItems')->select('name', 'quantity_on_hand', 'unit_cost'))
                ->orderByDesc('quantity_on_hand')
                ->limit(10)
                ->get()
                ->map(fn($item) => [
                    'name' => $item->name,
                    'total' => $item->quantity_on_hand,
                ]);
        }

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
            'notificationCount',
            'programScoped',
            'myProgramItemsCount',
            'availableStock',
            'lowStockAlerts',
            'expiringSoon',
            'pendingRequests',
            'approvedRequests',
            'totalPasSubmitted',
            'stockStatusDistribution',
            'pasRequestStatus',
            'monthlySupplyMovement',
            'expiringItemsTimeline',
            'topItemsByStock',
            'pendingApprovals'
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
        $pendingApprovalRecords = collect();

        $typeItemIds = $this->getItemIdsBySupplierType($type);
        if (!empty($typeItemIds)) {
            $lowStockItems = Item::whereIn('id', $typeItemIds)
                ->whereHas('receivingItems')
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
                $productCode = $item->receivingItems()->whereNotNull('item_code')->value('item_code');
                $notifications->push([
                    'type' => 'warning',
                    'label' => 'LOW STOCK',
                    'code' => $productCode ?: '—',
                    'name' => $item->name,
                    'detail' => "{$item->quantity_on_hand} on hand",
                    'href' => route('items.show', $item),
                ]);
            }

            $upcomingExpiries = ReceivingItem::with('item')
                ->whereIn('item_id', $typeItemIds)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', now()->addDays(90))
                ->whereDate('expiry_date', '>=', now())
                ->orderBy('expiry_date')
                ->limit(10)
                ->get();

            foreach ($upcomingExpiries as $expiry) {
                $notifications->push([
                    'type' => 'danger',
                    'label' => 'EXPIRING SOON',
                    'code' => $expiry->item_code ?: '—',
                    'name' => $expiry->item->name,
                    'detail' => 'Expires ' . $expiry->expiry_date->format('M d, Y'),
                    'href' => route('items.show', $expiry->item),
                ]);
            }

            foreach ($pendingApprovalRecords as $pas) {
                $notifications->push([
                    'type' => 'info',
                    'label' => 'PENDING APPROVAL',
                    'code' => $pas->pas_number ?? '—',
                    'name' => $pas->facility_name ?? '—',
                    'requester' => $pas->requester?->name ?? '—',
                    'detail' => 'Submitted ' . ($pas->date_of_pass?->format('M d, Y') ?? '—'),
                    'href' => route('pas.view', $pas),
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
