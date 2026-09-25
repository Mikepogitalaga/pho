<?php

/*
|--------------------------------------------------------------------------
| "Last 4 numbers reset every year?" probe (READ ONLY)
|--------------------------------------------------------------------------
| Focus of this file: does the trailing 4-digit sequence of
|   PAS-YYYY-MM-NNNN        (PasController)
|   PTR/ITR/RIS-YYYY-MM-NNNN (ReleaseController)
|   PREFIX-YY-MM-NNNN       (ReceivingController seed + form JS)
| restart at 0001 when the YEAR changes?
|
| Nothing in app/ was modified - the tests only call the existing
| generators (App\Traits\GeneratesCodes through the controllers) and
| inspect the values they return / write into the in-memory test DB.
*/

use App\Http\Controllers\PasController;
use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\ReleaseController;
use App\Models\Item;
use App\Models\Pas;
use App\Models\Program;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Release;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function yr4SeedPas(string $pasNumber): void
{
    Pas::create([
        'pas_number'           => $pasNumber,
        'date_of_pass'         => Carbon::now()->toDateString(),
        'facility_name'        => 'Year Reset Facility',
        'facility_coordinator' => 'Year Reset Coordinator',
        'transfer_type'        => 'PTR',
        'status'               => 'Pending',
    ]);
}

function yr4SeedRelease(string $releaseNumber, string $ptrItrRisNo): void
{
    Release::create([
        'release_number' => $releaseNumber,
        'ptr_itr_ris_no' => $ptrItrRisNo,
        'date_released'  => Carbon::now()->toDateString(),
        'status'         => 'Pending',
    ]);
}

function yr4SeedItemCode(string $itemCode): void
{
    $supplier = Supplier::firstOrCreate(['company_name' => 'Year Reset Supplier']);

    $receiving = Receiving::create([
        'supplier_id'   => $supplier->id,
        'date_received' => Carbon::now()->toDateString(),
    ]);

    $item = Item::create(['name' => 'Year Reset Item ' . $itemCode]);

    ReceivingItem::create([
        'receiving_id'      => $receiving->id,
        'item_id'           => $item->id,
        'item_code'         => $itemCode,
        'item_description'  => 'Year reset probe ' . $itemCode,
        'quantity_received' => 1,
    ]);
}

/** Next PAS number the "New PAS" form would offer. */
function yr4PasNext(): string
{
    return app(PasController::class)->create()->getData()['pasNumber'];
}

/** Next PTR/ITR/RIS number the release form (or its type switcher) returns. */
function yr4PtrNext(string $type = 'PTR'): string
{
    if ($type === 'PTR') {
        return app(ReleaseController::class)->create()->getData()['ptrNumber'];
    }

    $response = app(ReleaseController::class)->nextPtrNumber($type);

    return json_decode($response->getContent(), true)['number'];
}

/** Next 4-digit sequence the receiving form seeds for a program prefix. */
function yr4ItemCodeSeq(string $prefix): int
{
    $data = app(ReceivingController::class)->create()->getData();

    return (int) ($data['programSequences'][$prefix] ?? 0);
}

/** The complete code the receiving form assembles: PREFIX-YY-MM-NNNN. */
function yr4ItemCodeNext(string $prefix): string
{
    return sprintf(
        '%s-%s-%s-%04d',
        $prefix,
        now()->format('y'),
        now()->format('m'),
        yr4ItemCodeSeq($prefix)
    );
}


test('PAS: the last 4 digits restart at 0001 when the year changes', function () {
    // One code for every month of 2026, the December one reaching 0099 ...
    foreach (range(1, 12) as $month) {
        yr4SeedPas(sprintf('PAS-2026-%02d-%04d', $month, $month === 12 ? 99 : 10));
    }

    // ... plus leftovers from previous years that must never be inherited.
    yr4SeedPas('PAS-2025-12-9999');
    yr4SeedPas('PAS-2024-12-9999');

    $this->travelTo(Carbon::parse('2026-12-31 23:30:00'));
    expect(yr4PasNext())->toBe('PAS-2026-12-0100'); // still counting inside December 2026

    $this->travelTo(Carbon::parse('2027-01-01 00:10:00'));
    expect(yr4PasNext())->toBe('PAS-2027-01-0001'); // last 4 digits restarted, 2026 max ignored

    // The new counter is fed only by the new year's own rows.
    yr4SeedPas('PAS-2027-01-0001');
    expect(yr4PasNext())->toBe('PAS-2027-01-0002');

    expect(Pas::where('pas_number', 'like', 'PAS-2027-%')->pluck('pas_number')->all())
        ->toBe(['PAS-2027-01-0001']);
});

test('PTR / ITR / RIS: the last 4 digits restart at 0001 when the year changes', function () {
    yr4SeedRelease('REL-YR-01', 'PTR-2026-01-0050');
    yr4SeedRelease('REL-YR-02', 'ITR-2026-06-0075');
    yr4SeedRelease('REL-YR-03', 'RIS-2026-12-0099'); // December max of 2026
    yr4SeedRelease('REL-YR-04', 'PTR-2025-12-0999'); // old year leftovers
    yr4SeedRelease('REL-YR-05', 'ITR-2024-12-0999');

    $this->travelTo(Carbon::parse('2026-12-31 23:30:00'));
    expect(yr4PtrNext())->toBe('PTR-2026-12-0100'); // same shared counter, still 2026

    $this->travelTo(Carbon::parse('2027-01-01 00:05:00'));
    expect(yr4PtrNext())->toBe('PTR-2027-01-0001');
    expect(yr4PtrNext('ITR'))->toBe('ITR-2027-01-0001');
    expect(yr4PtrNext('RIS'))->toBe('RIS-2027-01-0001');

    yr4SeedRelease('REL-YR-06', 'PTR-2027-01-0001');
    expect(yr4PtrNext())->toBe('PTR-2027-01-0002');
});

test('receiving item code: the last 4 digits restart at 0001 when the year changes', function () {
    Program::create(['name' => 'DOH Program', 'description' => 'DOH']);

    foreach (range(1, 12) as $month) {
        yr4SeedItemCode(sprintf('DOH-26-%02d-%04d', $month, $month === 12 ? 99 : 10));
    }
    yr4SeedItemCode('DOH-25-12-9999'); // old year leftovers

    $this->travelTo(Carbon::parse('2026-12-31 23:30:00'));
    expect(yr4ItemCodeNext('DOH'))->toBe('DOH-26-12-0100'); // still counting inside 26-12

    $this->travelTo(Carbon::parse('2027-01-01 00:05:00'));
    expect(yr4ItemCodeNext('DOH'))->toBe('DOH-27-01-0001'); // 2-digit year changed -> 0001 again

    yr4SeedItemCode('DOH-27-01-0001');
    expect(yr4ItemCodeNext('DOH'))->toBe('DOH-27-01-0002');
});

/*
 * Nuance: because the lookup pattern contains the month as well, the last 4
 * digits restart on the 1st of EVERY MONTH - not only when the year changes.
 */
test('the last 4 digits also restart at the start of every month inside the same year', function () {
    yr4SeedPas('PAS-2026-11-0099');
    yr4SeedRelease('REL-YR-07', 'PTR-2026-11-0099');
    Program::create(['name' => 'DOH Program', 'description' => 'DOH']);
    yr4SeedItemCode('DOH-26-11-0099');

    $this->travelTo(Carbon::parse('2026-12-01 00:05:00'));

    expect(yr4PasNext())->toBe('PAS-2026-12-0001');
    expect(yr4PtrNext())->toBe('PTR-2026-12-0001');
    expect(yr4ItemCodeNext('DOH'))->toBe('DOH-26-12-0001');
});

test('the same last 4 digits may exist in different years - nothing collides', function () {
    yr4SeedPas('PAS-2025-01-0001');
    yr4SeedPas('PAS-2026-01-0001');

    expect(Pas::where('pas_number', 'PAS-2025-01-0001')->exists())->toBeTrue();
    expect(Pas::where('pas_number', 'PAS-2026-01-0001')->exists())->toBeTrue();

    $this->travelTo(Carbon::parse('2027-01-05 09:00:00'));
    expect(yr4PasNext())->toBe('PAS-2027-01-0001'); // year 2027 reuses 0001 freely
    expect(Pas::where('pas_number', 'PAS-2027-01-0001')->exists())->toBeFalse();
});

test('the 4-digit tail only stays 4 digits while it is below 9999', function () {
    yr4SeedPas('PAS-2026-09-9999');

    $this->travelTo(Carbon::parse('2026-09-20 10:00:00'));
    expect(yr4PasNext())->toBe('PAS-2026-09-10000'); // grows to 5 digits, it does not wrap to 0001

    $this->travelTo(Carbon::parse('2027-01-05 10:00:00'));
    expect(yr4PasNext())->toBe('PAS-2027-01-0001'); // a new year still starts at 0001
});

