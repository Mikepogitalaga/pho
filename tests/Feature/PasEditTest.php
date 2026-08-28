<?php

use App\Models\Coordinator;
use App\Models\Item;
use App\Models\Pas;
use App\Models\PasItem;
use App\Models\Program;
use App\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Support\ViewErrorBag;

function makePasEditFixture(): Pas
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
        'notes' => 'Test notes',
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

it('renders the PAS edit add-item script so the row button can append another item', function () {
    app('view')->share('errors', new ViewErrorBag());

    $pas = makePasEditFixture();

    $coordinator = new Coordinator([
        'full_name' => 'Kimberly Miranda',
        'assigned_programs' => 'STI/HIV AIDS PREVENTION AND CONTROL PROGRAM (NASPCP)',
    ]);
    $coordinator->forceFill(['id' => 1]);
    $coordinator->exists = true;
    $coordinator->setRelation('programs', collect([new Program(['name' => 'STI/HIV AIDS PREVENTION AND CONTROL PROGRAM (NASPCP)'])]));

    $html = view('pas.edit', [
        'pas' => $pas,
        'items' => collect([$pas->items->first()->item]),
        'suppliers' => collect([$pas->supplier]),
        'coordinators' => collect([$coordinator]),
        'programs' => collect([new Program(['name' => 'STI/HIV AIDS PREVENTION AND CONTROL PROGRAM (NASPCP)'])]),
        'itemLotNumbers' => collect(),
        'facilities' => collect(['Apayao State College']),
    ])->render();

    expect($html)->toContain('id="add-pas-item"');
    expect($html)->toContain('const addBtn    = document.getElementById(\'add-pas-item\');');
    expect($html)->toContain('addBtn.addEventListener(\'click\'', 'edit view is missing the add-item click handler');
});
