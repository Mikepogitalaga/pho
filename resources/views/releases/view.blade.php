@extends('layouts.app')

@section('title', 'Release Details')
@section('pageHeading', 'Release Details')
@section('pageSubheading', 'Review release slip information and released items.')

@section('content')
    <section class="card">
        <div class="section-header">
            <div>
                <h1 class="page-heading">{{$release->ptr_itr_ris_no }}</h1>
                <p class="page-description">PAS: {{ $release->pas_number ?? '—' }}</p>
            </div>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <a href="{{ route('releases.edit', $release) }}" class="btn btn-primary">✏️ Edit</a>
                <a href="{{ route('reports.liquidation.export', ['release' => $release->id]) }}" class="btn btn-secondary">Download Excel</a>
                <a href="{{ route('releases.print', $release) }}" target="_blank" class="btn btn-secondary">🖨 Print PTR</a>
                <a href="{{ route('releases.index') }}" class="btn btn-secondary">Back to Releases</a>
            </div>
        </div>

        <form class="stack" onsubmit="return false" style="margin-top: 1.5rem;">
            @csrf
            @method('PUT')

            <div class="form-grid-3">
                <div class="form-group">
                    <label>Facility / End-user</label>
                    <input type="text" value="{{ old('facility_name', $release->facility_name ?? '') }}" readonly />
                </div>

                <div class="form-group">
                    <label>Status</label>
                    @php
                        $status = $release->status ?? 'Unreleased';
                        $statusClass = match($status) {
                            'Released' => 'badge-success',
                            'Released through pass' => 'badge-primary',
                            'Canceled' => 'badge-danger',
                            'Returned' => 'badge-warning',
                            default => 'badge-secondary',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}" style="display:inline-flex;align-items:center;justify-content:center;min-height:2.4rem;padding:0 0.9rem;">{{ $status }}</span>
                </div>

                <div class="form-group">
                    <label>PTR / ITR / RIS No.</label>
                    <input type="text" value="{{ old('ptr_itr_ris_no', $release->ptr_itr_ris_no ?? '') }}" readonly />
                </div>

                <div class="form-group">
                    <label>Source Docs. PTR / PO No.</label>
                    <input type="text" value="{{ old('source_docs_ptr_po_no', $release->source_docs_ptr_po_no ?? '') }}" readonly />
                </div>

                <div class="form-group">
                    <label>PAS No.</label>
                    <input type="text" value="{{ old('pas_number', $release->pas_number ?? '') }}" readonly />
                </div>

                <div class="form-group">
                    <label>Health Program</label>
                    <input type="text" value="{{ old('health_program_coordinator', $release->health_program_coordinator ?? '') }}" readonly />
                </div>

                <div class="form-group">
                    <label>Date Released</label>
                    <input type="text" value="{{ old('date_released', isset($release->date_released) ? $release->date_released->format('F d, Y') : '') }}" readonly />
                </div>

                <div class="form-group">
                    <label>Received By</label>
                    <input type="text" value="{{ old('received_by', $release->received_by ?? '') }}" readonly />
                </div>
            </div>

            <div class="form-grid-2" style="margin-top: 1.25rem;">
                <div class="form-group">
                    <label>Reason for Transfer</label>
                    <input type="text" value="{{ old('reason_for_transfer', $release->reason_for_transfer ?? '') }}" readonly />
                </div>

                <div class="form-group">
                    <label>Purpose / Activity</label>
                    <textarea rows="3" readonly>{{ old('notes', $release->notes ?? '') }}</textarea>
                </div>
            </div>
        </form>

        <div class="view-section" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
            <h2 class="section-title" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); margin-bottom: 0.85rem;">Released Items</h2>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Item Description</th>
                            <th style="text-align: center;">PHO Code</th>
                            <th style="text-align: center;">Batch / Lot No.</th>
                            <th style="text-align: center;">Expiry Date</th>
                            <th style="text-align: center;">Quantity</th>
                            <th style="text-align: center;">UOM</th>
                            <th style="text-align: right;">Unit Cost</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($release->items as $releaseItem)
                            @php
                                $lineTotal = ($releaseItem->unit_cost ?? 0) * $releaseItem->quantity_released;
                                $recentReceiving = $releaseItem->item->receivingItems->sortByDesc('created_at')->first();
                            @endphp
                            <tr>
                                 <td style="text-align: left;">{{ $releaseItem->item?->name ?? $releaseItem->item_description ?? '—' }}</td>
                                <td style="text-align: center;">{{ $recentReceiving?->item_code ?? '—' }}</td>
                                <td style="text-align: center;">{{ $releaseItem->lot_number ?? '—' }}</td>
                                <td style="text-align: center;">
                                    @php
                                        $raw = $releaseItem->expiry_date ?? $recentReceiving?->expiry_date;
                                    @endphp
                                    {{ $raw ? \Carbon\Carbon::parse($raw)->format('m/d/Y') : '—' }}
                                </td>
                                <td style="text-align: center;">{{ number_format($releaseItem->quantity_released) }}</td>
                                <td style="text-align: center;">{{ $releaseItem->uom ?? '—' }}</td>
                                <td style="text-align: right;">₱ {{ isset($releaseItem->unit_cost) ? number_format($releaseItem->unit_cost, 2) : '—' }}</td>
                                <td style="text-align: right; font-weight: 600; color: var(--danger);">₱ {{ isset($releaseItem->unit_cost) ? number_format($lineTotal, 2) : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="padding: 2.5rem; text-align: center;">
                                    <div class="empty-state">
                                        <strong style="font-size: 1rem;">No items found</strong>
                                        <div style="margin-top: 0.5rem; color: var(--text-muted);">This release slip does not contain any released items.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        @if($release->items->isNotEmpty())
                            <tfoot>
                                <tr class="grand-total-row">
                                    <td colspan="7" style="text-align: right; font-weight: 700; padding: 0.75rem 0.5rem;">GRAND TOTAL</td>
                                    <td style="text-align: right; font-weight: 700; padding: 0.75rem 0.5rem;">
                                        ₱ {{ number_format($release->items->sum(fn($i) => ($i->unit_cost ?? 0) * $i->quantity_released), 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
