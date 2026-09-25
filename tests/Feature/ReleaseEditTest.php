<?php

use App\Models\Item;
use App\Models\Release;
use App\Models\ReleaseItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\ViewErrorBag;

function makeReleaseEditFixture(): array
{
    $item = new Item([
        'name'             => 'Test Item',
        'category'         => 'MDL',
        'unit'             => 'pcs',
        'unit_cost'        => 50.00,
        'quantity_on_hand' => 100,
    ]);
    $item->forceFill(['id' => 1]);
    $item->exists = true;
    $item->setRelation('receivingItems', new Collection([
        (object) [
            'item_code'        => 'ITM-001',
            'uom'              => 'pcs',
            'unit_cost'        => 50.00,
            'quantity_received' => 25,
            'category'         => 'MDL',
            'lot_number'       => 'LOT-001',
            'expiry_date'      => Carbon::parse('2026-12-31'),
        ],
    ]));

    $release = new Release([
        'id'                        => 1,
        'release_number'            => 'REL-TEST123',
        'pas_number'                => 'PAS-001',
        'health_program_coordinator' => 'Test Program',
        'ptr_itr_ris_no'            => 'PTR-2026-09-0001',
        'source_docs_ptr_po_no'     => 'PO-001',
        'facility_name'             => 'Test Facility',
        'facility_category'         => 'Hospitals',
        'received_by'               => '',
        'date_released'             => null,
        'status'                    => 'Unreleased',
        'status_reason'             => null,
        'notes'                     => '',
    ]);
    $release->forceFill(['id' => 1]);
    $release->exists = true;
    $release->setRelation('items', new Collection([
        tap(new ReleaseItem([
            'release_id'        => 1,
            'item_id'           => 1,
            'item_description'  => 'Test Item',
            'category'          => 'MDL',
            'quantity_released' => 10,
            'uom'               => 'pcs',
            'lot_number'        => 'LOT-001',
            'unit_cost'         => 50.00,
            'expiry_date'       => Carbon::parse('2026-12-31'),
        ]), function (ReleaseItem $ri) {
            $ri->forceFill(['id' => 1]);
            $ri->exists = true;
        }),
    ]));

    $itemModel = new Item(['name' => 'Test Item', 'category' => 'MDL', 'unit' => 'pcs', 'unit_cost' => 50.00]);
    $itemModel->forceFill(['id' => 1]);
    $itemModel->exists = true;
    $itemModel->setRelation('receivingItems', new Collection([
        (object) [
            'item_code'        => 'ITM-001',
            'uom'              => 'pcs',
            'unit_cost'        => 50.00,
            'quantity_received' => 25,
            'category'         => 'MDL',
            'lot_number'       => 'LOT-001',
            'expiry_date'      => Carbon::parse('2026-12-31'),
        ],
    ]));

    return [
        'release'       => $release,
        'items'         => collect([$itemModel]),
        'facilities'    => collect([(object) ['name' => 'Test Facility', 'category' => 'Hospitals']]),
        'programs'      => collect([(object) ['name' => 'Test Program']]),
        'coordinators'  => collect([(object) ['full_name' => 'Test Coordinator', 'assigned_programs' => 'Test Program']]),
        'itemLotNumbers' => collect([1 => 'LOT-001']),
    ];
}

it('renders the release edit page with pre-filled data and item rows', function () {
    app('view')->share('errors', new ViewErrorBag());

    $html = view('releases.edit', makeReleaseEditFixture())->render();

    expect($html)->toContain('Edit Release Slip');
    expect($html)->toContain('REL-TEST123');
    expect($html)->toContain('PAS-001');
    expect($html)->toContain('PTR-2026-09-0001');
    expect($html)->toContain('Test Item');
    expect($html)->toContain('10');
    expect($html)->toContain('name="items[0][_release_item_id]" value="1"');
});
