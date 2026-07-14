<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Models\ReleaseItem;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function liquidation(Request $request)
    {
        $query = Release::query()
            ->with('items')
            ->where('status', 'Released')
            ->orWhere('status', 'Released through pass')
            ->latest('date_released');

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $facility = $request->input('facility');

        if ($startDate) {
            $query->whereDate('date_released', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date_released', '<=', $endDate);
        }

        if ($facility) {
            $query->where('facility_name', 'like', '%' . $facility . '%');
        }

        $releases = $query->paginate(15);

        $totalQuantity = 0;
        $totalCost = 0;

        foreach ($releases as $release) {
            foreach ($release->items as $item) {
                $totalQuantity += (int) $item->quantity_released;
                $totalCost += ((float) $item->unit_cost ?? 0) * (int) $item->quantity_released;
            }
        }

        return view('reports.liquidation', compact('releases', 'totalQuantity', 'totalCost'));
    }
}
