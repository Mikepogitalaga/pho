<?php

use App\Models\Item;
use App\Models\Pas;
use App\Models\PasItem;
use App\Models\Supplier;
use Illuminate\Support\Collection;

function makePasPrintFixture(): Pas
{
    $supplier = new Supplier([
        'company_name' => 'Health Source Supply',
        'contact_person' => 'Jane Doe',
        'email' => 'supplier@example.com',
        'phone_number' => '09171234567',
        'address' => 'Test Address',
    ]);
    $supplier->forceFill(['id' => 1]);
    $supplier->exists = true;

    $item = new Item([
        'item_code' => 'ITM-001',
        'name' => 'Test Item',
        'unit' => 'kits',
        'description' => 'Test item description',
    ]);
    $item->forceFill(['id' => 1]);
    $item->exists = true;

    $pas = new Pas([
        'pas_number' => 'PAS-2026-08-0001',
        'date_of_pass' => '2026-08-07',
        'date_released' => '2026-08-08',
        'supplier_id' => 1,
        'purpose_activity' => 'For the participants of World Hepatitis Day Celebration',
        'facility_name' => 'Apayao State College',
        'facility_coordinator' => 'Kimberly Miranda',
        'transfer_type' => 'PTR',
        'program' => 'STI/HIV AIDS PREVENTION AND CONTROL PROGRAM (NASPCP)',
        'status' => 'Pending',
        'notes' => 'Accomplished in duplicate copies',
    ]);
    $pas->forceFill(['id' => 1]);
    $pas->exists = true;
    $pas->setRelation('supplier', $supplier);
    $pas->setRelation('release', null);
    $pas->setRelation('items', new Collection([
        tap(new PasItem([
            'item_id' => 1,
            'item_description' => 'PK Health kits - D26070417',
            'product_code' => 'D26070417',
            'lot_number' => 'LOT-001',
            'expiration_date' => '2026-12-31',
            'quantity' => 88,
            'unit' => 'kits',
            'unit_cost' => 100.50,
            'total_cost' => 8844.00,
        ]), function (PasItem $pasItem) use ($item) {
            $pasItem->forceFill(['id' => 1, 'pas_id' => 1]);
            $pasItem->exists = true;
            $pasItem->setRelation('item', $item);
        }),
    ]));

    return $pas;
}

test('pas print view renders the pas html template fields with synced record data', function () {
    $pas = makePasPrintFixture();

    $html = view('pas.print', compact('pas'))->render();

    expect($html)->toContain('PROPERTY ALLOCATION SLIP');
    expect($html)->toContain('Provincial Health Office');
    expect($html)->toContain('STI/HIV AIDS PREVENTION AND CONTROL PROGRAM (NASPCP)');
    expect($html)->toContain('PTR #:');
    expect($html)->toContain('PAS #:');
    expect($html)->toContain('2026-08-0001');
    expect($html)->toContain('For the participants of World Hepatitis Day Celebration');
    expect($html)->toContain('PK Health kits - D26070417');
    expect($html)->toContain('GRAND TOTAL');
    expect($html)->toContain('Accomplished in duplicate copies');
    expect($html)->toContain('Original copy for PHO Supply Section');
    expect($html)->toContain('Second copy for Employee file');
    expect($html)->toContain('KIMBERLY MIRANDA');
    expect($html)->toContain('MARK JOLEEN M. CALBAN, MD, MPM-HSD');
});
