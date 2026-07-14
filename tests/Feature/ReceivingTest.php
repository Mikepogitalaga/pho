<?php

use App\Http\Controllers\ReceivingController;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

test('receiving stores a single document number for both purchase order and source document fields', function () {
    $user = User::factory()->create();
    Supplier::create([
        'company_name' => 'Test Supplier',
        'contact_person' => 'Jane Doe',
        'email' => 'supplier@example.com',
        'phone' => '1234567890',
        'address' => 'Test Address',
    ]);

    $request = Request::create('/receivings', 'POST', [
        'supplier_id' => 1,
        'po_number' => 'PO-1001',
        'date_received' => '2026-07-14',
        'items' => [
            [
                'item_code' => 'ITM-001',
                'item_description' => 'Test Item',
                'quantity_received' => 2,
            ],
        ],
    ]);

    $request->setUserResolver(fn () => $user);

    $response = app(ReceivingController::class)->store($request);

    expect($response->getStatusCode())->toBe(302);

    $receiving = \App\Models\Receiving::latest()->first();
    expect($receiving->po_number)->toBe('PO-1001');
    expect($receiving->source_document_number)->toBe('PO-1001');
});

test('receiving stores the entered uom on the item record', function () {
    $user = User::factory()->create();
    Supplier::create([
        'company_name' => 'Test Supplier',
        'contact_person' => 'Jane Doe',
        'email' => 'supplier@example.com',
        'phone' => '1234567890',
        'address' => 'Test Address',
    ]);

    $request = Request::create('/receivings', 'POST', [
        'supplier_id' => 1,
        'po_number' => 'PO-1002',
        'date_received' => '2026-07-14',
        'items' => [
            [
                'item_code' => 'ITM-002',
                'item_description' => 'UOM Item',
                'uom' => 'Box',
                'quantity_received' => 3,
            ],
        ],
    ]);

    $request->setUserResolver(fn () => $user);

    app(ReceivingController::class)->store($request);

    $item = \App\Models\Item::where('item_code', 'ITM-002')->first();

    expect($item)->not->toBeNull();
    expect($item->unit)->toBe('Box');
});
