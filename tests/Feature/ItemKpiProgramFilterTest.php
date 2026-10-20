<?php

use App\Http\Controllers\ItemController;
use App\Models\Item;
use App\Models\Program;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('filters DOH and GSO KPI stats by the current user program on items index', function () {
    $programA = Program::create(['name' => 'Program A', 'description' => 'A', 'status' => 'Active']);
    $programB = Program::create(['name' => 'Program B', 'description' => 'B', 'status' => 'Active']);

    $dohSupplier = Supplier::create(['company_name' => 'DOH Supplier', 'supplier_type' => 'DOH']);
    $gsoSupplier = Supplier::create(['company_name' => 'GSO Supplier', 'supplier_type' => 'GSO']);

    $itemProgramA = Item::create([
        'name' => 'Item For Program A',
        'category' => 'General',
        'unit' => 'pcs',
        'quantity_on_hand' => 10,
        'reorder_level' => 0,
        'stock_keeping_unit' => 'Program A',
    ]);

    $itemProgramB = Item::create([
        'name' => 'Item For Program B',
        'category' => 'General',
        'unit' => 'pcs',
        'quantity_on_hand' => 10,
        'reorder_level' => 0,
        'stock_keeping_unit' => 'Program B',
    ]);

    // DOH supplier receives both program items
    $receivingDohA = Receiving::create([
        'receiving_number' => 'RCV-DOH-A',
        'supplier_id' => $dohSupplier->id,
        'date_received' => now()->toDateString(),
    ]);
    ReceivingItem::create([
        'receiving_id' => $receivingDohA->id,
        'item_id' => $itemProgramA->id,
        'item_code' => 'ITEM-A',
        'quantity_received' => 10,
    ]);

    $receivingDohB = Receiving::create([
        'receiving_number' => 'RCV-DOH-B',
        'supplier_id' => $dohSupplier->id,
        'date_received' => now()->toDateString(),
    ]);
    ReceivingItem::create([
        'receiving_id' => $receivingDohB->id,
        'item_id' => $itemProgramB->id,
        'item_code' => 'ITEM-B',
        'quantity_received' => 10,
    ]);

    // GSO supplier receives both program items
    $receivingGsoA = Receiving::create([
        'receiving_number' => 'RCV-GSO-A',
        'supplier_id' => $gsoSupplier->id,
        'date_received' => now()->toDateString(),
    ]);
    ReceivingItem::create([
        'receiving_id' => $receivingGsoA->id,
        'item_id' => $itemProgramA->id,
        'item_code' => 'ITEM-A',
        'quantity_received' => 5,
    ]);

    $receivingGsoB = Receiving::create([
        'receiving_number' => 'RCV-GSO-B',
        'supplier_id' => $gsoSupplier->id,
        'date_received' => now()->toDateString(),
    ]);
    ReceivingItem::create([
        'receiving_id' => $receivingGsoB->id,
        'item_id' => $itemProgramB->id,
        'item_code' => 'ITEM-B',
        'quantity_received' => 5,
    ]);

    // Program user assigned to Program A only
    $programUser = User::factory()->create([
        'role' => User::ROLE_STAFF,
        'is_active' => true,
        'program_id' => $programA->id,
        'password' => Hash::make('password'),
    ]);
    $programUser->programs()->attach($programA);

    Auth::login($programUser);

    $request = Request::create('/items', 'GET');

    $controller = app(ItemController::class);
    $data = $controller->index($request)->getData();

    $doh = $data['supplierStats']->get('DOH');
    $gso = $data['supplierStats']->get('GSO');

    expect($doh->item_count)->toBe(1)->and($doh->units_received)->toBe(10);
    expect($gso->item_count)->toBe(1)->and($gso->units_received)->toBe(5);
});
