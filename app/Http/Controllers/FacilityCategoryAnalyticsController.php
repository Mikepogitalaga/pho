<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Release;
use App\Models\ReleaseItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacilityCategoryAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $categories = Facility::categories();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $activeCategory = $request->input('facility_category', 'All');

        $dateFilter = $this->buildDateFilter($startDate, $endDate);

        $chartData = [];
        foreach ($categories as $category) {
            $chartData[$category] = $this->getTopItemsByCategory($category, $dateFilter);
        }

        return view('analytics.facility-categories', compact(
            'categories',
            'chartData',
            'activeCategory',
            'startDate',
            'endDate'
        ));
    }

    public function facilityItems(Request $request)
    {
        $categories = Facility::categories();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $activeCategory = $request->input('facility_category', 'All');

        $dateFilter = $this->buildDateFilter($startDate, $endDate);

        $facilities = \App\Models\Facility::query()
            ->when($activeCategory !== 'All', fn($q) => $q->where('category', $activeCategory))
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $chartData = [];
        foreach ($facilities as $facility) {
            $chartData[$facility->id] = [
                'facility' => $facility,
                'items' => $this->getTopItemsByFacility($facility->name, $dateFilter),
            ];
        }

        return view('analytics.facility-items', compact(
            'categories',
            'facilities',
            'chartData',
            'activeCategory',
            'startDate',
            'endDate'
        ));
    }

    private function getTopItemsByFacility(string $facilityName, array $dateFilter): array
    {
        $releasedStatuses = ['Released', 'Released through pass'];

        $query = ReleaseItem::query()
            ->select(
                'release_items.item_id',
                'release_items.item_description',
                DB::raw('SUM(CASE WHEN LOWER(release_items.category) LIKE "%dm%" THEN release_items.quantity_released ELSE 0 END) as dm_released'),
                DB::raw('SUM(CASE WHEN LOWER(release_items.category) LIKE "%mdl%" THEN release_items.quantity_released ELSE 0 END) as mdl_released'),
                DB::raw('SUM(release_items.quantity_released) as total_released'),
                DB::raw('COUNT(DISTINCT releases.id) as transaction_count')
            )
            ->join('releases', 'release_items.release_id', '=', 'releases.id')
            ->whereIn('releases.status', $releasedStatuses)
            ->where('releases.facility_name', $facilityName)
            ->whereBetween('releases.date_released', [$dateFilter['start'], $dateFilter['end']])
            ->groupBy('release_items.item_id', 'release_items.item_description')
            ->orderByDesc('total_released')
            ->limit(10);

        return $query->get()->toArray();
    }

    private function buildDateFilter(?string $startDate, ?string $endDate): array
    {
        $filter = [];

        if ($startDate && $endDate) {
            $filter['start'] = Carbon::parse($startDate)->startOfDay();
            $filter['end'] = Carbon::parse($endDate)->endOfDay();
        } elseif ($startDate) {
            $filter['start'] = Carbon::parse($startDate)->startOfDay();
            $filter['end'] = Carbon::now()->endOfDay();
        } elseif ($endDate) {
            $filter['start'] = Carbon::create(2020, 1, 1)->startOfDay();
            $filter['end'] = Carbon::parse($endDate)->endOfDay();
        } else {
            $filter['start'] = Carbon::now()->startOfYear();
            $filter['end'] = Carbon::now()->endOfDay();
        }

        return $filter;
    }

    private function getTopItemsByCategoryItems(string $facilityCategory, array $dateFilter): array
    {
        $releasedStatuses = ['Released', 'Released through pass'];

        $query = ReleaseItem::query()
            ->select(
                'release_items.item_id',
                'release_items.item_description',
                DB::raw('SUM(CASE WHEN LOWER(release_items.category) LIKE "%dm%" THEN release_items.quantity_released ELSE 0 END) as dm_released'),
                DB::raw('SUM(CASE WHEN LOWER(release_items.category) LIKE "%mdl%" THEN release_items.quantity_released ELSE 0 END) as mdl_released'),
                DB::raw('SUM(release_items.quantity_released) as total_released'),
                DB::raw('COUNT(DISTINCT releases.facility_name) as facilities_served'),
                DB::raw('COUNT(DISTINCT releases.id) as transaction_count')
            )
            ->join('releases', 'release_items.release_id', '=', 'releases.id')
            ->whereIn('releases.status', $releasedStatuses)
            ->where('releases.facility_category', $facilityCategory)
            ->whereBetween('releases.date_released', [$dateFilter['start'], $dateFilter['end']])
            ->groupBy('release_items.item_id', 'release_items.item_description')
            ->orderByDesc('total_released')
            ->limit(10);

        return $query->get()->toArray();
    }

    private function getTopItemsByCategory(string $facilityCategory, array $dateFilter): array
    {
        $releasedStatuses = ['Released', 'Released through pass'];

        $query = ReleaseItem::query()
            ->select(
                'releases.facility_name',
                DB::raw('SUM(CASE WHEN LOWER(release_items.category) LIKE "%dm%" THEN release_items.quantity_released ELSE 0 END) as dm_released'),
                DB::raw('SUM(CASE WHEN LOWER(release_items.category) LIKE "%mdl%" THEN release_items.quantity_released ELSE 0 END) as mdl_released'),
                DB::raw('SUM(release_items.quantity_released) as total_released'),
                DB::raw('COUNT(DISTINCT release_items.item_id) as items_count'),
                DB::raw('COUNT(DISTINCT releases.id) as transaction_count')
            )
            ->join('releases', 'release_items.release_id', '=', 'releases.id')
            ->whereIn('releases.status', $releasedStatuses)
            ->where('releases.facility_category', $facilityCategory)
            ->whereBetween('releases.date_released', [$dateFilter['start'], $dateFilter['end']])
            ->groupBy('releases.facility_name')
            ->orderByDesc('total_released')
            ->limit(10);

        $results = $query->get()->toArray();

        foreach ($results as &$result) {
            $topItems = ReleaseItem::query()
                ->select(
                    'release_items.item_description',
                    DB::raw('SUM(release_items.quantity_released) as total')
                )
                ->join('releases', 'release_items.release_id', '=', 'releases.id')
                ->whereIn('releases.status', $releasedStatuses)
                ->where('releases.facility_category', $facilityCategory)
                ->where('releases.facility_name', $result['facility_name'])
                ->whereBetween('releases.date_released', [$dateFilter['start'], $dateFilter['end']])
                ->groupBy('release_items.item_description')
                ->orderByDesc('total')
                ->limit(3)
                ->get()
                ->map(fn($i) => $i->item_description . ' (' . number_format($i->total) . ')')
                ->toArray();

            $result['top_items'] = $topItems;
        }

        return $results;
    }

    public function detail(Request $request, string $facilityCategory, string $facilityName)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $dateFilter = $this->buildDateFilter($startDate, $endDate);

        $releasedStatuses = ['Released', 'Released through pass'];

        $items = ReleaseItem::query()
            ->select(
                'release_items.item_id',
                'release_items.item_description',
                'release_items.category as item_category',
                DB::raw('SUM(CASE WHEN LOWER(release_items.category) LIKE "%dm%" THEN release_items.quantity_released ELSE 0 END) as dm_released'),
                DB::raw('SUM(CASE WHEN LOWER(release_items.category) LIKE "%mdl%" THEN release_items.quantity_released ELSE 0 END) as mdl_released'),
                DB::raw('SUM(release_items.quantity_released) as total_released'),
                DB::raw('COUNT(DISTINCT releases.id) as transaction_count')
            )
            ->join('releases', 'release_items.release_id', '=', 'releases.id')
            ->whereIn('releases.status', $releasedStatuses)
            ->where('releases.facility_category', $facilityCategory)
            ->where('releases.facility_name', $facilityName)
            ->whereBetween('releases.date_released', [$dateFilter['start'], $dateFilter['end']])
            ->groupBy('release_items.item_id', 'release_items.item_description', 'release_items.category')
            ->orderByDesc('total_released')
            ->limit(10)
            ->get();

        return view('analytics.facility-category-detail', compact(
            'items',
            'facilityCategory',
            'facilityName',
            'startDate',
            'endDate'
        ));
    }
}
