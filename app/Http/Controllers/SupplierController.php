<?php

namespace App\Http\Controllers;

use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Release;
use App\Models\ReleaseItem;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $suppliers = Supplier::when($search, function ($query, $search) {
            $query->where('company_name', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })->orderBy('company_name')->paginate(15);

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'supplier_type' => 'required|string|in:DOH,GSO',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        Supplier::create($request->all());

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $totalReceivings = $supplier->receivings()->count();

        $totalItemsReceived = ReceivingItem::whereHas('receiving', function ($q) use ($supplier) {
            $q->where('supplier_id', $supplier->id);
        })->sum('quantity_received');

        $totalReceivedCost = ReceivingItem::whereHas('receiving', function ($q) use ($supplier) {
            $q->where('supplier_id', $supplier->id);
        })->selectRaw('SUM(quantity_received * unit_cost) as total')
            ->value('total');

        $receivingsThisMonth = Receiving::where('supplier_id', $supplier->id)
            ->whereBetween('date_received', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $recentReceivings = $supplier->receivings()
            ->with('items')
            ->latest('date_received')
            ->limit(5)
            ->get();

        $latestReceiving = $supplier->receivings()
            ->latest('date_received')
            ->first();

        return view('suppliers.show', compact(
            'supplier',
            'totalReceivings',
            'totalItemsReceived',
            'totalReceivedCost',
            'receivingsThisMonth',
            'recentReceivings',
            'latestReceiving'
        ));
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
        $releaseIds = ReleaseItem::whereIn('item_id', $itemIds)->pluck('release_id')->unique()->toArray();

        $totalReleases = count($releaseIds);
        $totalReleasedItems = ReleaseItem::whereIn('item_id', $itemIds)->sum('quantity_released');

        $monthlyReleaseIds = ReleaseItem::whereIn('item_id', $itemIds)
            ->whereHas('release', function ($q) {
                $q->whereBetween('date_released', [now()->startOfMonth(), now()->endOfMonth()]);
            })->pluck('release_id')->unique()->toArray();
        $monthlyReleases = count($monthlyReleaseIds);

        $monthlyReleasedQty = ReleaseItem::whereIn('item_id', $itemIds)
            ->whereHas('release', function ($q) {
                $q->whereBetween('date_released', [now()->startOfMonth(), now()->endOfMonth()]);
            })->sum('quantity_released');

        $recentReleases = Release::whereIn('id', $releaseIds)
            ->latest('date_released')
            ->limit(5)
            ->get();

        // Chart: Monthly releases for last 6 months (scoped to items from this supplier type)
        $monthlyReleaseLabels = [];
        $monthlyReleaseCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $label = $month->format('M Y');
            $monthlyReleaseLabels[] = $label;

            $count = Release::whereIn('id', $releaseIds)
                ->whereYear('date_released', $month->year)
                ->whereMonth('date_released', $month->month)
                ->count();
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

    public function dohDashboard(Supplier $supplier)
    {
        // Validate supplier is DOH type
        abort_if(!$supplier->isDoh(), 404, 'Supplier is not a DOH supplier.');

        $supplierId = $supplier->id;

        // DOH Receiving metrics scoped to this specific supplier
        $totalReceivingsAll = Receiving::where('supplier_id', $supplierId)->count();

        $monthlyReceivings = Receiving::where('supplier_id', $supplierId)
            ->whereBetween('date_received', [now()->startOfMonth(), now()->endOfMonth()])->count();

        $monthlyReceivedQty = ReceivingItem::whereHas('receiving', function ($q) use ($supplierId) {
            $q->where('supplier_id', $supplierId);
        })->whereHas('receiving', function ($q) {
            $q->whereBetween('date_received', [now()->startOfMonth(), now()->endOfMonth()]);
        })->sum('quantity_received');

        $totalItemsReceived = ReceivingItem::whereHas('receiving', function ($q) use ($supplierId) {
            $q->where('supplier_id', $supplierId);
        })->sum('quantity_received');

        // Chart: Monthly receivings for last 6 months
        $monthlyLabels = [];
        $monthlyCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $label = $month->format('M Y');
            $monthlyLabels[] = $label;
            $monthlyCounts[] = Receiving::where('supplier_id', $supplierId)
                ->whereYear('date_received', $month->year)
                ->whereMonth('date_received', $month->month)
                ->count();
        }

        // Recent receivings
        $recentReceived = Receiving::where('supplier_id', $supplierId)
            ->latest('date_received')->limit(5)->get();

        // Release metrics scoped to items received from this supplier
        $releaseMetrics = $this->getReleaseMetricsBySupplierType($supplier->supplier_type);

        return view('doh.dashboard', array_merge(compact(
            'supplier',
            'totalReceivingsAll',
            'monthlyReceivings',
            'monthlyReceivedQty',
            'totalItemsReceived',
            'monthlyLabels',
            'monthlyCounts',
            'recentReceived'
        ), $releaseMetrics));
    }

    public function gsoDashboard(Supplier $supplier)
    {
        // Validate supplier is GSO type
        abort_if(!$supplier->isGso(), 404, 'Supplier is not a GSO supplier.');

        $supplierId = $supplier->id;

        // GSO Receiving metrics scoped to this specific supplier
        $totalReceivingsAll = Receiving::where('supplier_id', $supplierId)->count();

        $monthlyReceivings = Receiving::where('supplier_id', $supplierId)
            ->whereBetween('date_received', [now()->startOfMonth(), now()->endOfMonth()])->count();

        $monthlyReceivedQty = ReceivingItem::whereHas('receiving', function ($q) use ($supplierId) {
            $q->where('supplier_id', $supplierId);
        })->whereHas('receiving', function ($q) {
            $q->whereBetween('date_received', [now()->startOfMonth(), now()->endOfMonth()]);
        })->sum('quantity_received');

        $totalItemsReceived = ReceivingItem::whereHas('receiving', function ($q) use ($supplierId) {
            $q->where('supplier_id', $supplierId);
        })->sum('quantity_received');

        // Chart: Monthly receivings for last 6 months
        $monthlyLabels = [];
        $monthlyCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $label = $month->format('M Y');
            $monthlyLabels[] = $label;
            $monthlyCounts[] = Receiving::where('supplier_id', $supplierId)
                ->whereYear('date_received', $month->year)
                ->whereMonth('date_received', $month->month)
                ->count();
        }

        // Recent receivings
        $recentReceived = Receiving::where('supplier_id', $supplierId)
            ->latest('date_received')->limit(5)->get();

        // Release metrics scoped to items received from this supplier
        $releaseMetrics = $this->getReleaseMetricsBySupplierType($supplier->supplier_type);

        return view('gso.dashboard', array_merge(compact(
            'supplier',
            'totalReceivingsAll',
            'monthlyReceivings',
            'monthlyReceivedQty',
            'totalItemsReceived',
            'monthlyLabels',
            'monthlyCounts',
            'recentReceived'
        ), $releaseMetrics));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'supplier_type' => 'required|string|in:DOH,GSO',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $supplier->update($request->all());

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
