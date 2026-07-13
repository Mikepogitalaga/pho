<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Release;
use App\Models\Supplier;

class DashboardController extends Controller
{
    public function index()
    {
        $totalItems = Item::count();
        $totalSuppliers = Supplier::count();
        $totalReceivedThisMonth = Receiving::whereBetween('date_received', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $totalReleasedThisMonth = Release::whereBetween('date_released', [now()->startOfMonth(), now()->endOfMonth()])->count();
        // Low Stock: treat items as low if they are <= reorder_level;
        // if reorder_level is null/0, fall back to fixed threshold 20.
        $lowStockItems = Item::query()
            ->where('quantity_on_hand', '>', 0)
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
            })
            ->orderBy('quantity_on_hand')
            ->limit(5)
            ->get();




        $recentReceived = Receiving::latest('date_received')->limit(5)->get();
        $recentReleased = Release::latest('date_released')->limit(5)->get();
        $upcomingExpiries = ReceivingItem::with('item')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date')
            ->limit(5)
            ->get();

        $notifications = collect();

        foreach ($lowStockItems as $item) {
            $notifications->push([
                'type' => 'warning',
                'message' => "Low stock (<20): {$item->item_code} · {$item->name} ({$item->quantity_on_hand} on hand)",
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
            'totalReceivedThisMonth',
            'totalReleasedThisMonth',
            'lowStockItems',
            'recentReceived',
            'recentReleased',
            'upcomingExpiries',
            'notifications',
            'notificationCount'
        ));
    }
}
