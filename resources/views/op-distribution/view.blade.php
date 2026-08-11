@extends('layouts.app')

@section('title', 'OP Distribution — ' . $opDistribution->reference_number)
@section('pageHeading', $opDistribution->reference_number)
@section('pageSubheading', 'Office of the President distribution record.')

@section('content')
<div class="section-card">
    <div class="section-header">
        <div>
            <h1 class="page-heading">{{ $opDistribution->reference_number }}</h1>
            <p class="page-description">
                {{ $opDistribution->date_distributed->format('F d, Y') }}
                @if($opDistribution->distributed_by) &middot; {{ $opDistribution->distributed_by }} @endif
            </p>
        </div>
        <div style="display:flex;gap:0.5rem;">
            <a href="{{ route('op-distribution.edit', $opDistribution) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('op-distribution.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="form-grid-3" style="margin-bottom:1.5rem;">
        <div>
            <p class="detail-label">Reference No.</p>
            <p class="detail-value">{{ $opDistribution->reference_number }}</p>
        </div>
        <div>
            <p class="detail-label">Date Distributed</p>
            <p class="detail-value">{{ $opDistribution->date_distributed->format('M d, Y') }}</p>
        </div>
        <div>
            <p class="detail-label">Distributed By</p>
            <p class="detail-value">{{ $opDistribution->distributed_by ?? '—' }}</p>
        </div>
        <div>
            <p class="detail-label">Status</p>
            <p class="detail-value">
                @php $sc = match($opDistribution->status) { 'Released'=>'badge-success','Canceled'=>'badge-danger',default=>'badge-warning' }; @endphp
                <span class="status-pill {{ $sc }}">{{ $opDistribution->status }}</span>
            </p>
        </div>
        @if($opDistribution->notes)
        <div style="grid-column:span 2;">
            <p class="detail-label">Notes</p>
            <p class="detail-value">{{ $opDistribution->notes }}</p>
        </div>
        @endif
    </div>

    <h2 class="section-card-title" style="margin-bottom:1rem;">Patient Records ({{ $opDistribution->items->count() }})</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th style="text-align:left;">Patient Name</th>
                    <th style="text-align:center;">Age</th>
                    <th style="text-align:center;">Gender</th>
                    <th style="text-align:left;">Item Description</th>
                    <th style="text-align:center;">Qty</th>
                    <th style="text-align:center;">UOM</th>
                    <th style="text-align:right;">Unit Cost</th>
                    <th style="text-align:right;">Total</th>
                    <th style="text-align:center;">Lot No.</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @forelse($opDistribution->items as $i => $row)
                    @php
                        $lineTotal   = ($row->unit_cost ?? 0) * $row->quantity;
                        $grandTotal += $lineTotal;
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="font-weight:600;">{{ $row->patient_name }}</td>
                        <td style="text-align:center;">{{ $row->patient_age ?? '—' }}</td>
                        <td style="text-align:center;">{{ $row->patient_gender ?? '—' }}</td>
                        <td>{{ $row->item_description ?? $row->item?->name ?? '—' }}</td>
                        <td style="text-align:center;">{{ number_format($row->quantity) }}</td>
                        <td style="text-align:center;">{{ $row->uom ?? '—' }}</td>
                        <td style="text-align:right;">{{ $row->unit_cost !== null ? '₱ ' . number_format($row->unit_cost, 2) : '—' }}</td>
                        <td style="text-align:right;font-weight:600;color:var(--primary);">{{ $row->unit_cost !== null ? '₱ ' . number_format($lineTotal, 2) : '—' }}</td>
                        <td style="text-align:center;">{{ $row->lot_number ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="padding:2rem;text-align:center;color:var(--text-muted);">No patient records found.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($opDistribution->items->count())
            <tfoot>
                <tr>
                    <td colspan="8" style="text-align:right;font-weight:600;">Grand Total</td>
                    <td style="text-align:right;font-weight:700;color:var(--primary);">₱ {{ number_format($grandTotal, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
