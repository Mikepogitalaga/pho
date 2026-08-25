<?php

namespace App\Http\Controllers;

use App\Exports\LiquidationExport;
use App\Models\Release;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function liquidation(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);

        if ($perPage <= 0) {
            $perPage = PHP_INT_MAX;
        }

        $releases = $this->liquidationQuery($request)->paginate($perPage)->withQueryString();

        $totalQuantity = 0;
        $totalCost = 0;

        foreach ($releases as $release) {
            foreach ($release->items as $item) {
                $totalQuantity += (int) $item->quantity_released;
                $totalCost += ((float) $item->unit_cost ?? 0) * (int) $item->quantity_released;
            }
        }

        $categoriesWithLiquidations = \App\Models\ReleaseItem::whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('reports.liquidation', compact('releases', 'totalQuantity', 'totalCost', 'categoriesWithLiquidations'));
    }

    public function export(Request $request)
    {
        $query = $this->liquidationQuery($request);

        if ($releaseId = $request->query('release')) {
            $query->where('releases.id', $releaseId);
        }

        $releases = $query->get();

        $ptrFilter = trim((string) $request->input('ptr_number', ''));
        $releaseId = $request->query('release');
        $category = trim((string) $request->input('category', ''));

        if ($ptrFilter !== '') {
            $fileName = 'Liquidation_Report_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $ptrFilter) . '.xlsx';
        } elseif ($releaseId) {
            $release = Release::find($releaseId);
            $ptr = $release ? ($release->ptr_itr_ris_no ?? $release->release_number ?? 'liquidation') : 'liquidation';
            $fileName = 'Liquidation_Report_' . preg_replace('/[^A-Za-z0-9\-]/', '_', (string) $ptr) . '.xlsx';
        } elseif ($category !== '') {
            $fileName = 'Liquidation_Report_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $category) . '.xlsx';
        } else {
            $fileName = 'Liquidation_Report_' . now()->format('Y-m-d-His') . '.xlsx';
        }

        return Excel::download(new LiquidationExport($releases, $category !== '' ? $category : null), $fileName);
    }

    private function liquidationQuery(Request $request)
    {
        $query = Release::query()
            ->with('items.item')
            ->latest('date_released');

        $ptrNumber = trim((string) $request->input('ptr_number', ''));
        $facility = trim((string) $request->input('facility', ''));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemDescription = trim((string) $request->input('item_description', ''));
        $category = trim((string) $request->input('category', ''));

        if ($ptrNumber !== '') {
            $query->where(function ($q) use ($ptrNumber) {
                $q->where('ptr_itr_ris_no', 'like', '%' . $ptrNumber . '%')
                  ->orWhere('release_number', 'like', '%' . $ptrNumber . '%');
            });
        }

        if ($facility !== '') {
            $query->where('facility_name', 'like', '%' . $facility . '%');
        }

        if ($startDate) {
            $query->whereDate('date_released', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date_released', '<=', $endDate);
        }

        if ($itemDescription !== '') {
            $query->whereHas('items', function ($q) use ($itemDescription) {
                $q->where('item_description', 'like', '%' . $itemDescription . '%');
            });
        }

        if ($category !== '') {
            $query->whereHas('items', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }

        return $query;
    }
}
