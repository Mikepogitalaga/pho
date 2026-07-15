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
            ->with('items.item')
            ->where(function ($q) {
                $q->where('status', 'Released')
                  ->orWhere('status', 'Released through pass');
            })
            ->latest('date_released');

        $ptrNumber = $request->input('ptr_number');
        $facility = $request->input('facility');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemDescription = $request->input('item_description');
        $category = $request->input('category');

        if ($ptrNumber) {
            $query->where(function ($q) use ($ptrNumber) {
                $q->where('ptr_itr_ris_no', 'like', '%' . $ptrNumber . '%')
                  ->orWhere('release_number', 'like', '%' . $ptrNumber . '%');
            });
        }

        if ($facility) {
            $query->where('facility_name', 'like', '%' . $facility . '%');
        }

        if ($startDate) {
            $query->whereDate('date_released', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date_released', '<=', $endDate);
        }

        if ($itemDescription) {
            $query->whereHas('items', function ($q) use ($itemDescription) {
                $q->where('item_description', 'like', '%' . $itemDescription . '%');
            });
        }

        if ($category) {
            $query->whereHas('items.item', function ($q) use ($category) {
                $q->where('category', 'like', '%' . $category . '%');
            });
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
