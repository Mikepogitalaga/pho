@extends('layouts.app')

@section('title', 'Receiving Details')
@section('pageHeading', 'Receiving Details')
@section('pageSubheading', 'Review receiving slip information and received items.')

@section('content')
    <div class="section-card">
        <div class="section-header">
            <div>
                <h1 class="page-heading">{{ $receiving->receiving_number }}</h1>
                <p class="page-description">PO: {{ $receiving->po_number ?? '—' }} · Supplier: {{ $receiving->supplier->company_name ?? '—' }}</p>
            </div>
            <a href="{{ route('receivings.edit', $receiving) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('receivings.index') }}" class="btn btn-secondary">Back to Receivings</a>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Supplier</label>
                <p>{{ $receiving->supplier->company_name ?? '—' }}</p>
            </div>

            <div class="form-group">
                <label>PO Number</label>
                <p>{{ $receiving->po_number ?? '—' }}</p>
            </div>

            <div class="form-group">
                <label>Date Received</label>
                <p>{{ $receiving->date_received->format('M d, Y') }}</p>
            </div>

            <div class="form-group">
                <label>Received By</label>
                <p>{{ $receiving->received_by ?? '—' }}</p>
            </div>

            <div class="form-group">
                <label>Location</label>
                <p>{{ $receiving->location ?? '—' }}</p>
            </div>

            <div class="form-group">
                <label>ICS/PTR/RIS</label>
                <p>{{ $receiving->ics_ptr_ris ?? '—' }}</p>
            </div>

            <div class="form-group">
                <label>Document Date</label>
                <p>{{ $receiving->document_date ? $receiving->document_date->format('M d, Y') : '—' }}</p>
            </div>

            <div class="form-group">
                <label>Stock Keeping Unit</label>
                <p>{{ $receiving->stock_keeping_unit ?? '—' }}</p>
            </div>

            <div class="form-group">
                <label>Program Coordinator</label>
                <p>{{ $receiving->program_coordinator ?? '—' }}</p>
            </div>
        </div>

        @if(!empty($receiving->notes))
            <div class="form-group">
                <label>Notes</label>
                <p>{{ $receiving->notes }}</p>
            </div>
        @endif

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
            <h2 class="section-card-title" style="margin-bottom: 1.25rem;">Received Items</h2>
            <div class="table-container">
                <table>
                    <thead>
                     <tr>
                         <th style="text-align: left;">Item Description</th>
                         <th style="text-align: left;" class="col-hide-md">Category</th>
                         <th style="text-align: center;">Quantity</th>
                         <th style="text-align: center;">UOM</th>
                         <th style="text-align: center;" class="col-hide-md">Lot Number</th>
                         <th style="text-align: center;" class="col-hide-md">Expiry Date</th>
                         <th style="text-align: right;">Unit Cost</th>
                         <th style="text-align: right;">Total</th>
                         <th>Action</th>
                     </tr>
                    </thead>
                     <tbody>
                         @forelse($receiving->items as $receivingItem)
                              <tr>
                                  <td class="mobile-card-header">
                                      <span style="font-weight:600;color:var(--text);">{{ $receivingItem->item_description ?? $receivingItem->item?->name ?? '—' }}</span>
                                  </td>
                                  <td data-label="Category" class="col-hide-md">{{ $receivingItem->category ?? $receivingItem->item?->category ?? '—' }}</td>
                                  <td data-label="Quantity" style="text-align:center;">{{ $receivingItem->quantity_received }}</td>
                                  <td data-label="UOM" style="text-align:center;">{{ $receivingItem->uom ?? $receivingItem->item?->unit ?? '—' }}</td>
                                  <td data-label="Lot Number" class="col-hide-md" style="text-align:center;">{{ $receivingItem->lot_number ?? '—' }}</td>
                                  <td data-label="Expiry Date" class="col-hide-md" style="text-align:center;">{{ $receivingItem->expiry_date ? $receivingItem->expiry_date->format('M d, Y') : '—' }}</td>
                                  <td data-label="Unit Cost" style="text-align:right;">₱ {{ isset($receivingItem->unit_cost) ? number_format($receivingItem->unit_cost, 2) : '—' }}</td>
                                  <td data-label="Total" style="text-align:right;font-weight:600;color:var(--danger);">₱ {{ isset($receivingItem->unit_cost) && isset($receivingItem->quantity_received) ? number_format($receivingItem->unit_cost * $receivingItem->quantity_received, 2) : '—' }}</td>
                                 <td class="mobile-card-actions">
                                     @if($receivingItem->item)
                                         <a href="{{ route('items.show', $receivingItem->item) }}" class="btn btn-secondary" style="min-height:2rem;padding:0.3rem 0.7rem;font-size:0.8rem;">View Item</a>
                                     @else
                                         <span style="color:var(--text-muted);">—</span>
                                     @endif
                                 </td>
                             </tr>
                         @empty
                             <tr>
                                <td colspan="9" style="padding: 2rem; text-align: center;">
                                    <div class="empty-state">
                                        <strong style="font-size: 1rem;">No items found</strong>
                                        <div style="margin-top: 0.5rem; color: var(--text-muted);">This receiving slip does not contain any received items.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
