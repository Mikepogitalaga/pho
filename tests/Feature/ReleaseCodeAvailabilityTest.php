<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Release;
use App\Models\ReleaseItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

function makeTwoCodeProduct(): array
{
    $item = Item::create([
        'name' => 'Shared Item',
        'unit' => 'pcs',
        'quantity_on_hand' => 1000,
    ]);

    $supplier = Supplier::create([
        'company_name' => 'Test Supplier',
    ]);

    $receiving = Receiving::create([
        'po_number' => 'PO-TEST-001',
        'source_document_number' => 'SD-TEST-001',
        'supplier_id' => $supplier->id,
        'date_received' => now(),
        'received_by' => 'Test Receiver',
    ]);

    $firstExpiry = now()->addMonths(2);
    $secondExpiry = now()->addMonths(6);

    ReceivingItem::create([
        'receiving_id' => $receiving->id,
        'item_id' => $item->id,
        'item_code' => 'PHO-001',
        'quantity_received' => 800,
        'unit_cost' => 10,
        'uom' => 'pcs',
        'lot_number' => 'LOT-A',
        'expiry_date' => $firstExpiry,
    ]);

    ReceivingItem::create([
        'receiving_id' => $receiving->id,
        'item_id' => $item->id,
        'item_code' => 'PHO-002',
        'quantity_received' => 200,
        'unit_cost' => 10,
        'uom' => 'pcs',
        'lot_number' => 'LOT-B',
        'expiry_date' => $secondExpiry,
    ]);

    $item->refresh();

    return [$item, $firstExpiry, $secondExpiry];
}

class ReleaseCodeAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_release_page_shows_per_code_availability(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        [$item] = makeTwoCodeProduct();

        $this->actingAs($user)
            ->get(route('releases.create'))
            ->assertOk()
            ->assertSee('PHO-001')
            ->assertSee('PHO-002');
    }

    public function test_pas_create_page_shows_per_code_availability(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        [$item] = makeTwoCodeProduct();

        $this->actingAs($user)
            ->get(route('pas.create'))
            ->assertOk()
            ->assertSee('PHO-001')
            ->assertSee('PHO-002');
    }

    public function test_release_code_dropdown_shows_only_matching_codes_for_exact_description(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        [$item] = makeTwoCodeProduct();

        app('view')->share('errors', new ViewErrorBag());

        $html = view('releases.create', [
            'items' => $item->load('receivingItems')->each(fn ($i) => $i->attachCodeAvailability()),
            'ptrNumber' => 'PTR-2026-09-0001',
            'year' => now()->format('Y'),
            'month' => now()->format('m'),
            'itemLotNumbers' => collect(),
            'programs' => collect(),
            'coordinators' => collect(),
            'facilities' => collect(),
        ])->render();

        preg_match('/const allItemsData = (\[.*?\]);/s', $html, $matches);

        $rows = collect(json_decode($matches[1] ?? '[]', true))->keyBy('code');

        expect($rows->keys()->all())->toBe(['PHO-001', 'PHO-002']);
    }

    public function test_pas_create_dropdown_shows_only_matching_codes_for_exact_description(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        [$item] = makeTwoCodeProduct();

        app('view')->share('errors', new ViewErrorBag());

        $html = view('pas.create', [
            'items' => $item->load('receivingItems')->each(fn ($i) => $i->attachCodeAvailability()),
            'coordinators' => collect(),
            'programs' => collect(),
            'pasNumber' => 'PAS-2026-09-0001',
            'itemLotNumbers' => collect(),
            'facilities' => collect(),
        ])->render();

        preg_match('/const pasAllItems = (\[.*?\]);/s', $html, $matches);

        $rows = collect(json_decode($matches[1] ?? '[]', true))->keyBy('code');

        expect($rows->keys()->all())->toBe(['PHO-001', 'PHO-002']);
    }

    public function test_release_charges_the_specific_code(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        [$item, $firstExpiry, $secondExpiry] = makeTwoCodeProduct();

        $request = Request::create('/releases', 'POST', [
            'pas_number' => 'PAS-2026-09-0001',
            'health_program_coordinator' => 'Program A',
            'ptr_itr_ris_no' => 'PTR-2026-09-0001',
            'source_docs_ptr_po_no' => 'PO-1001',
            'facility_name' => 'Test Facility',
            'received_by' => 'Juan Dela Cruz',
            'date_released' => '2026-09-23',
            'status' => 'Released',
            'items' => [
                [
                    'item_id' => $item->id,
                    'item_code' => 'PHO-002',
                    'item_description' => 'Shared Item',
                    'quantity_released' => 200,
                    'uom' => 'pcs',
                    'unit_cost' => 10.00,
                ],
            ],
        ]);
        $request->setUserResolver(fn () => $user);

        $response = app(\App\Http\Controllers\ReleaseController::class)->store($request);

        expect($response->getStatusCode())->toBe(302);

        $releaseItem = ReleaseItem::latest('id')->first();

        expect($releaseItem->item_code)->toBe('PHO-002');
        expect($releaseItem->quantity_released)->toBe(200);
        expect($item->fresh()->quantity_on_hand)->toBe(1000);

        $availability = $item->fresh(['receivingItems', 'releaseItems.release'])->codeAvailability();

        expect($availability[(string) $item->fresh()->receivingItems->firstWhere('item_code', 'PHO-002')->id])->toBe(0);
        expect(array_sum($availability))->toBe(1000);
    }

    public function test_release_without_code_charges_the_code_without_lot(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $item = Item::create([
            'name' => 'Legacy Item',
            'unit' => 'pcs',
            'quantity_on_hand' => 500,
        ]);

        $supplier = Supplier::create([
            'company_name' => 'Test Supplier',
        ]);

        $receiving = Receiving::create([
            'po_number' => 'PO-TEST-002',
            'source_document_number' => 'SD-TEST-002',
            'supplier_id' => $supplier->id,
            'date_received' => now(),
            'received_by' => 'Test Receiver',
        ]);

        ReceivingItem::create([
            'receiving_id' => $receiving->id,
            'item_id' => $item->id,
            'item_code' => 'LEGACY-001',
            'quantity_received' => 500,
            'unit_cost' => 10,
            'uom' => 'pcs',
            'lot_number' => '',
            'expiry_date' => null,
        ]);

        $item->refresh();

        $request = Request::create('/releases', 'POST', [
            'pas_number' => 'PAS-2026-09-0001',
            'health_program_coordinator' => 'Program A',
            'ptr_itr_ris_no' => 'PTR-2026-09-0001',
            'source_docs_ptr_po_no' => 'PO-1001',
            'facility_name' => 'Test Facility',
            'received_by' => 'Juan Dela Cruz',
            'date_released' => '2026-09-23',
            'status' => 'Released',
            'items' => [
                [
                    'item_id' => $item->id,
                    'item_code' => null,
                    'lot_number' => null,
                    'item_description' => 'Legacy Item',
                    'quantity_released' => 200,
                    'uom' => 'pcs',
                    'unit_cost' => 10.00,
                ],
            ],
        ]);
        $request->setUserResolver(fn () => $user);

        $response = app(\App\Http\Controllers\ReleaseController::class)->store($request);

        expect($response->getStatusCode())->toBe(302);

        $availability = $item->fresh(['receivingItems', 'releaseItems.release'])->codeAvailability();

        expect(array_sum($availability))->toBe(300);
    }

    public function test_pas_create_shows_correct_per_code_availability(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        [$item] = makeTwoCodeProduct();

        $items = $item->load('receivingItems')->each(fn ($i) => $i->attachCodeAvailability());

        app('view')->share('errors', new ViewErrorBag());

        $html = view('pas.create', [
            'items' => $items,
            'coordinators' => collect(),
            'programs' => collect(),
            'pasNumber' => 'PAS-2026-09-0001',
            'itemLotNumbers' => collect(),
            'facilities' => collect(),
        ])->render();

        preg_match('/const pasAllItems = (\[.*?\]);/s', $html, $matches);

        $rows = collect(json_decode($matches[1] ?? '[]', true))->keyBy('code');

        expect($rows['PHO-001']['qty'])->toBe(800);
        expect($rows['PHO-002']['qty'])->toBe(200);
    }

    public function test_canceled_release_restores_code_availability(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        [$item] = makeTwoCodeProduct();

        $request = Request::create('/releases', 'POST', [
            'pas_number' => 'PAS-2026-09-0001',
            'health_program_coordinator' => 'Program A',
            'ptr_itr_ris_no' => 'PTR-2026-09-0001',
            'source_docs_ptr_po_no' => 'PO-1001',
            'facility_name' => 'Test Facility',
            'received_by' => 'Juan Dela Cruz',
            'date_released' => '2026-09-23',
            'status' => 'Released',
            'items' => [
                [
                    'item_id' => $item->id,
                    'item_code' => 'PHO-002',
                    'item_description' => 'Shared Item',
                    'quantity_released' => 200,
                    'uom' => 'pcs',
                    'unit_cost' => 10.00,
                ],
            ],
        ]);
        $request->setUserResolver(fn () => $user);

        $response = app(\App\Http\Controllers\ReleaseController::class)->store($request);

        expect($response->getStatusCode())->toBe(302);

        $release = Release::latest('id')->first();
        $release->update(['status' => 'Canceled']);

        $availability = $item->fresh(['receivingItems', 'releaseItems.release'])->codeAvailability();

        expect($availability[(string) $item->receivingItems->firstWhere('item_code', 'PHO-002')->id])->toBe(200);
        expect(array_sum($availability))->toBe(1000);
    }

    public function test_spreads_unknown_release_across_codes_first_expiry_first_out(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        [$item] = makeTwoCodeProduct();

        $request = Request::create('/releases', 'POST', [
            'pas_number' => 'PAS-2026-09-0001',
            'health_program_coordinator' => 'Program A',
            'ptr_itr_ris_no' => 'PTR-2026-09-0001',
            'source_docs_ptr_po_no' => 'PO-1001',
            'facility_name' => 'Test Facility',
            'received_by' => 'Juan Dela Cruz',
            'date_released' => '2026-09-23',
            'status' => 'Released',
            'items' => [
                [
                    'item_id' => $item->id,
                    'item_code' => 'UNKNOWN',
                    'lot_number' => null,
                    'item_description' => 'Shared Item',
                    'quantity_released' => 500,
                    'uom' => 'pcs',
                    'unit_cost' => 10.00,
                ],
            ],
        ]);
        $request->setUserResolver(fn () => $user);

        $response = app(\App\Http\Controllers\ReleaseController::class)->store($request);

        expect($response->getStatusCode())->toBe(302);

        $availability = $item->fresh(['receivingItems', 'releaseItems.release'])->codeAvailability();

        expect($availability[(string) $item->receivingItems->firstWhere('item_code', 'PHO-001')->id])->toBe(300);
        expect($availability[(string) $item->receivingItems->firstWhere('item_code', 'PHO-002')->id])->toBe(200);
    }
}
