<?php

use App\Http\Controllers\DashboardController;
use App\Models\Item;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('calculates inventory value from all items using quantity on hand multiplied by unit cost', function () {
    Item::create([
        'name' => 'Item Without Receiving',
        'category' => 'General',
        'unit' => 'pcs',
        'quantity_on_hand' => 10,
        'reorder_level' => 0,
        'unit_cost' => 2.50,
        'stock_keeping_unit' => 'SKU-001',
        'program_coordinator' => 'Program A',
    ]);

    $itemWithReceiving = Item::create([
        'name' => 'Item With Receiving',
        'category' => 'General',
        'unit' => 'pcs',
        'quantity_on_hand' => 3,
        'reorder_level' => 0,
        'unit_cost' => 1.00,
        'stock_keeping_unit' => 'SKU-002',
        'program_coordinator' => 'Program B',
    ]);

    $supplier = Supplier::create([
        'company_name' => 'Test Supplier',
    ]);

    $receiving = Receiving::create([
        'receiving_number' => 'RCV-0001',
        'supplier_id' => $supplier->id,
        'date_received' => now()->toDateString(),
    ]);

    ReceivingItem::create([
        'receiving_id' => $receiving->id,
        'item_id' => $itemWithReceiving->id,
        'quantity_received' => 3,
        'unit_cost' => 1.00,
        'category' => 'General',
        'item_description' => 'Item With Receiving',
    ]);

    $view = app(DashboardController::class)->index();

    expect($view->getData()['inventoryValue'])->toBe(28.0);
});
