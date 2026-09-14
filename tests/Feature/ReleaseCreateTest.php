<?php

use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\ViewErrorBag;

function makeReleaseCreateFixture(): array
{
    $item = new Item([
        'name' => 'Test Item',
        'category' => 'MDL',
        'unit' => 'pcs',
        'unit_cost' => 10.5,
    ]);
    $item->forceFill(['id' => 1]);
    $item->exists = true;
    $item->setRelation('receivingItems', new Collection([
        (object) [
            'item_code' => 'ITM-001',
            'uom' => 'pcs',
            'unit_cost' => 10.5,
            'quantity_received' => 25,
            'category' => 'MDL',
            'lot_number' => 'LOT-001',
            'expiry_date' => Carbon::parse('2026-12-31'),
        ],
    ]));

    return [
        'items' => collect([$item]),
        'ptrNumber' => 'PTR-2026-09-0001',
        'programs' => collect([(object) ['name' => 'Test Program']]),
        'coordinators' => collect([(object) ['full_name' => 'Test Coordinator', 'assigned_programs' => 'Test Program']]),
        'facilities' => collect([(object) ['name' => 'Test Facility', 'category' => 'Hospitals']]),
    ];
}

it('renders a release-items helper with the same PHO code autocomplete synchronization as PAS', function () {
    app('view')->share('errors', new ViewErrorBag());

    $html = view('releases.create', makeReleaseCreateFixture())->render();

    expect($html)->toContain('function applyItemToRow(row, item)');
    expect($html)->toContain('function bindPhocodeAutocomplete(row)');
    expect($html)->toContain("phocodeInput.addEventListener('input', function () { showOptions(this.value); });");
    expect($html)->toContain('applyItemToRow(row, item);');
    expect($html)->toContain('if (expiryInput && match.expiry) expiryInput.value = match.expiry;');
    expect($html)->toContain('function calcTotal(row)');
    expect($html)->toContain('item-totalcost-display');
    expect($html)->toContain("quantityInput.placeholder = 'Available: ' + (item.qty || 0);");
    expect($html)->toContain('if (uomInput)      uomInput.value      = item.uom || \'\';');
    expect($html)->toContain('if (unitCostInput) unitCostInput.value = item.cost || \'\';');
    expect($html)->toContain('if (lotInput)      lotInput.value      = item.lot_number || \'\';');
});

