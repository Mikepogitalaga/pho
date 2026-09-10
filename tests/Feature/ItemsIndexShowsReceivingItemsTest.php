<?php

use App\Models\Item;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows every receiving item row on the items index page', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $item = Item::create([
        'item_code' => 'MASTER-001',
        'name' => 'Shared Item Name',
        'category' => 'General',
        'unit' => 'pcs',
        'description' => 'Shared Item Name',
        'quantity_on_hand' => 5,
        'reorder_level' => 0,
        'location' => 'Warehouse A',
        'stock_keeping_unit' => 'SKU-100',
        'program_coordinator' => 'Program Alpha',
        'unit_cost' => 10.00,
    ]);

    $supplier = Supplier::create([
        'company_name' => 'Demo Supplier',
    ]);

    $receiving = Receiving::create([
        'receiving_number' => 'REC-0001',
        'supplier_id' => $supplier->id,
        'date_received' => now()->toDateString(),
        'location' => 'Warehouse A',
        'stock_keeping_unit' => 'SKU-100',
        'program_coordinator' => 'Program Alpha',
    ]);

    ReceivingItem::create([
        'receiving_id' => $receiving->id,
        'item_id' => $item->id,
        'item_code' => 'PHO-001',
        'item_description' => 'Shared Item Name',
        'category' => 'General',
        'purchase_source' => 'Local',
        'quantity_received' => 2,
        'uom' => 'pcs',
        'lot_number' => 'LOT-001',
        'unit_cost' => 10.00,
        'location' => 'Warehouse A',
    ]);

    ReceivingItem::create([
        'receiving_id' => $receiving->id,
        'item_id' => $item->id,
        'item_code' => 'PHO-002',
        'item_description' => 'Shared Item Name',
        'category' => 'General',
        'purchase_source' => 'Local',
        'quantity_received' => 3,
        'uom' => 'pcs',
        'lot_number' => 'LOT-002',
        'unit_cost' => 10.00,
        'location' => 'Warehouse A',
    ]);

    $response = $this->actingAs($user)->withoutMiddleware()->get(route('items.index'));

    $response->assertOk();
    $response->assertSee('PHO-001');
    $response->assertSee('PHO-002');
});
