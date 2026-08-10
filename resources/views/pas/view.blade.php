@extends('layouts.app')

@section('title', 'PAS ' . $pas->pas_number)
@section('pageHeading', 'PAS ' . $pas->pas_number)

@section('content')
<section class="card">
    <div class="section-header">
        <div>
            <h1 class="page-heading">{{ $pas->pas_number }}</h1>
            @php
                $linkedRelease = $pas->release;
                $displayStatus = $linkedRelease ? 'PTR created' : $pas->status;
                $badgeClass = match($displayStatus) {
                    'Released' => 'badge-success',
                    'Canceled' => 'badge-danger',
                    'PTR created' => 'badge-success',
                    default    => 'badge-warning',
                };
            @endphp
            <p class="page-description">
                <span class="badge {{ $badgeClass }}">{{ $displayStatus }}</span>
                &nbsp; Property Allocation Slip — items are NOT deducted from inventory.
            </p>
        </div>
            <div style="display:flex;gap:0.5rem;">
                @php
                    $releaseParams = [
                        'pas_number'                 => $pas->pas_number,
                        'health_program_coordinator' => $pas->program,
                        'release_coordinator'        => $pas->facility_coordinator,
                        'facility_name'              => $pas->facility_name ?: $pas->facility_coordinator,
                        'transfer_type'              => $pas->transfer_type ?? 'PTR',
                    ];
                    if (!empty($pas->purpose_activity)) {
                        $releaseParams['purpose_activity'] = $pas->purpose_activity;
                    }
                    foreach ($pas->items as $index => $item) {
                        $releaseParams["items[{$index}][item_description]"] = $item->item_description;
                        $releaseParams["items[{$index}][quantity_released]"] = $item->quantity;
                        $releaseParams["items[{$index}][uom]"] = $item->unit;
                        $releaseParams["items[{$index}][unit_cost]"] = $item->unit_cost;
                        $releaseParams["items[{$index}][item_id]"] = $item->item_id;
                        $releaseParams["items[{$index}][lot_number]"] = $item->lot_number;
                    }
                @endphp
                @if($linkedRelease)
                    <a href="{{ route('releases.view', $linkedRelease) }}" class="btn btn-primary">View PTR</a>
                @else
                    <a href="{{ route('releases.create', $releaseParams) }}" class="btn btn-primary">+ Create PTR</a>
                @endif
                <a href="{{ route('pas.edit', $pas) }}" class="btn btn-secondary">Edit</a>
                <a href="{{ route('pas.print', $pas) }}" target="_blank" class="btn btn-secondary">🖨 Print PAS</a>
                <a href="{{ route('pas.index') }}" class="btn btn-secondary">Back to PAS</a>
            </div>
    </div>

    {{-- Header Details --}}
    <div class="form-grid-3" style="margin-bottom:1.5rem;">
        <div>
            <p class="detail-label">PAS Number</p>
            <p class="detail-value">{{ $pas->pas_number }}</p>
        </div>
        <div>
            <p class="detail-label">Date of PASS</p>
            <p class="detail-value">{{ $pas->date_of_pass?->format('M d, Y') }}</p>
        </div>
        <div>
            <p class="detail-label">Date Released</p>
            <p class="detail-value">{{ $pas->date_released?->format('M d, Y') ?? '—' }}</p>
        </div>
        <div>
            <p class="detail-label">Supplier</p>
            <p class="detail-value">{{ $pas->supplier?->company_name ?? '—' }}</p>
        </div>
        <div>
            <p class="detail-label">Facility / End-user</p>
            <p class="detail-value">{{ $pas->facility_name ?? '—' }}</p>
        </div>
        <div>
            <p class="detail-label">Facility Coordinator</p>
            <p class="detail-value">{{ $pas->facility_coordinator ?? '—' }}</p>
        </div>
        <div>
            <p class="detail-label">Program</p>
            <p class="detail-value">{{ $pas->program ?? '—' }}</p>
        </div>
        <div>
            <p class="detail-label">Purpose / Activity</p>
            <p class="detail-value">{{ $pas->purpose_activity ?? '—' }}</p>
        </div>
        <div>
            <p class="detail-label">Status</p>
            <p class="detail-value">{{ $pas->status }}</p>
        </div>
        @if($pas->notes)
        <div>
            <p class="detail-label">Notes</p>
            <p class="detail-value">{{ $pas->notes }}</p>
        </div>
        @endif
    </div>

    {{-- Items Table --}}
    <h2 class="section-title" style="margin-bottom:0.75rem;">Items</h2>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Description</th>
                    <th>Product Code</th>
                    <th>Lot Number</th>
                    <th>Expiration</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Unit Cost</th>
                    <th>Total Cost</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @forelse($pas->items as $i => $item)
                    @php $grandTotal += (float) $item->total_cost; @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->item_description }}</td>
                        <td>{{ $item->product_code ?? $item->item?->item_code ?? '—' }}</td>
                        <td>{{ $item->lot_number ?? '—' }}</td>
                        <td>{{ $item->expiration_date?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ number_format($item->quantity) }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ number_format($item->unit_cost, 2) }}</td>
                        <td>{{ number_format($item->total_cost, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:2rem;">No items recorded.</td></tr>
                @endforelse
            </tbody>
            @if($pas->items->count())
            <tfoot>
                <tr>
                    <td colspan="8" style="text-align:right;font-weight:600;">Grand Total</td>
                    <td style="font-weight:600;">{{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Status Actions --}}
    @if($pas->status === 'Pending')
    <div style="margin-top:1.5rem;display:flex;gap:0.75rem;flex-wrap:wrap;">
        <form method="POST" action="{{ route('pas.status', [$pas, 'Released']) }}">
            @csrf
            <input type="hidden" name="date_released" value="{{ now()->toDateString() }}">
            <button type="submit" class="btn btn-primary"
                onclick="return confirm('Mark this PAS as Released? This will NOT affect inventory.')">
                Mark as Released
            </button>
        </form>
        <form method="POST" action="{{ route('pas.status', [$pas, 'Canceled']) }}">
            @csrf
            <button type="submit" class="btn btn-danger"
                onclick="return confirm('Cancel this PAS?')">
                Cancel PAS
            </button>
        </form>
    </div>
    @endif
</section>
@endsection
