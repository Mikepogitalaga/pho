@extends('layouts.app')

@section('title', 'Product Code - ' . $product->item_code)
@section('pageHeading', 'Product Code Details')
@section('pageSubheading', 'Stock deduction tracking and history for product code ' . $product->item_code)

@section('content')
    <div class="section-card" style="margin-bottom: 1.5rem;">
        <div class="section-header">
            <div>
                <h1 class="page-heading" style="margin:0;">{{ $product->item_code }} — {{ $product->name }}</h1>
                <p class="page-description" style="margin-top:0.25rem;">Stock Deduction Tracking for this product code.</p>
            </div>
            <div style="display:flex; gap:0.5rem;">
                <a href="{{ route('items.show', $item) }}" class="btn btn-secondary">Back to Item</a>
            </div>
        </div>

        {{-- Item Details Card --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:0.75rem; padding:1rem 0; border-top:1px solid var(--border); margin-top:0.5rem;">
            <div>
                <span style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted); display:block;">Location</span>
                <span style="font-size:1rem; font-weight:600;">{{ $product->location ?? '—' }}</span>
            </div>
            <div>
                <span style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted); display:block;">Category</span>
                <span style="font-size:1rem; font-weight:600;">{{ $product->category ?? '—' }}</span>
            </div>
            <div>
                <span style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted); display:block;">UOM</span>
                <span style="font-size:1rem; font-weight:600;">{{ $product->display_unit }}</span>
            </div>
            <div>
                <span style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted); display:block;">Program (SKU)</span>
                <span style="font-size:1rem; font-weight:600;">{{ $product->stock_keeping_unit ?? '—' }}</span>
            </div>
            <div>
                <span style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted); display:block;">Status</span>
                <span class="status-pill {{ $product->status_class }}">{{ $product->status }}</span>
            </div>
            <div>
                <span style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted); display:block;">Expiry</span>
                <span class="status-pill {{ $product->expiry_badge_class }}" style="font-size:0.82rem;">{{ $product->expiry_label }}</span>
            </div>
        </div>
    </div>

    <div class="section-card" style="padding: 1.25rem;">
        <div class="section-header compact" style="padding: 0 0 1rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <div>
                <h2 class="section-card-title" style="margin: 0;">Stock Deduction Tracking</h2>
                <p class="page-description" style="margin-top: 0.25rem;">View all deductions from releases for this product code.</p>
            </div>
        </div>

        <div class="dashboard-content-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
            <div class="kpi-card kpi-card--alert" style="padding: 1rem 1.1rem;">
                <div class="kpi-card-header">
                    <div class="kpi-card-icon" style="background: rgba(220, 38, 38, 0.14); color: var(--danger);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                    </div>
                    <div>
                        <div class="kpi-card-label">Total Released</div>
                    </div>
                </div>
                <p class="kpi-card-value" style="color: var(--danger);">{{ $totalReleased }}</p>
                <p class="kpi-card-foot">Units deducted via releases</p>
            </div>

            <div class="kpi-card kpi-card--blue" style="padding: 1rem 1.1rem;">
                <div class="kpi-card-header">
                    <div class="kpi-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </div>
                    <div>
                        <div class="kpi-card-label">Current Stock</div>
                    </div>
                </div>
                <p class="kpi-card-value">{{ $totalStock }}</p>
                <p class="kpi-card-foot">Available in inventory</p>
            </div>

            <div class="kpi-card kpi-card--green" style="padding: 1rem 1.1rem;">
                <div class="kpi-card-header">
                    <div class="kpi-card-icon" style="background: rgba(245, 158, 11, 0.14); color: var(--warning);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                    </div>
                    <div>
                        <div class="kpi-card-label">Deduction %</div>
                    </div>
                </div>
                <p class="kpi-card-value" style="color: var(--warning);">{{ $deductionPercentage }}%</p>
                <p class="kpi-card-foot">Of total stock deducted</p>
            </div>
        </div>

        <div>
            <h3 class="section-card-title" style="margin-bottom: 0.85rem;">Deduction History</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Product Code</th>
                            <th>Reference</th>
                            <th>Reason</th>
                            <th style="text-align: center;">Quantity</th>
                            <th>Facility / Receiver</th>
                            <th>Status</th>
                            <th style="text-align: center;">Action</th>
                        </tr>
                    </thead>
                     <tbody>
                         @forelse($deductionHistory as $record)
                             <tr>
                                 <td class="mobile-card-header">
                                     <span style="font-weight:600;color:#991B1B;">{{ optional($record['date'])->format('M d, Y') ?? '—' }}</span>
                                     @php
                                         $typeColor = match ($record['type']) {
                                             'Released' => 'var(--primary)',
                                             'Release' => 'var(--danger)',
                                             'Canceled' => 'var(--danger)',
                                             'Returned' => 'var(--success)',
                                             default => 'var(--text)',
                                         };
                                     @endphp
                                     <span class="badge" style="background:rgba(220,38,38,0.1);color:#dc2626;">{{ $record['type'] }}</span>
                                 </td>
                                 <td data-label="Product Code"><span style="font-weight:600;">{{ $record['item_code'] }}</span></td>
                                 <td data-label="Reference">{{ $record['reference'] }}</td>
                                 <td data-label="Reason" style="max-width:220px;">
                                     @if(in_array($record['type'], ['Canceled', 'Returned']))
                                         {{ $record['reason'] ?? '—' }}
                                     @else
                                         —
                                     @endif
                                 </td>
                                 <td data-label="Quantity" style="text-align:center;font-weight:600;color:{{ ($record['direction'] ?? 'deduct') === 'restore' ? 'var(--success)' : 'var(--danger)' }}">
                                     {{ ($record['direction'] ?? 'deduct') === 'restore' ? '+' : '−' }}{{ $record['quantity'] }}
                                 </td>
                                 <td data-label="Facility / Receiver">{{ $record['facility'] ?? '—' }}</td>
                                 <td class="mobile-card-actions" style="text-align:center;">
                                     @if($record['type'] === 'Release' || $record['type'] === 'Released')
                                         <a href="{{ route('releases.view', $record['release_id']) }}" class="btn btn-secondary" style="min-height:2rem;padding:0.3rem 0.7rem;font-size:0.8rem;">View</a>
                                     @endif
                                 </td>
                             </tr>
                                <td>{{ $record['facility'] }}</td>
                                <td>
                                    @php
                                        $statusClass = match ($record['status']) {
                                            'Canceled' => 'badge-danger',
                                            'Returned' => 'badge-success',
                                            'Released' => 'badge-info',
                                            'Released through pass' => 'badge-info',
                                            'Unreleased' => 'badge-warning',
                                            default => 'badge-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $record['status'] }}</span>
                                </td>
                                <td style="text-align: center;">
                                    @if(!empty($record['release_id']))
                                        <a href="{{ route('releases.view', $record['release_id']) }}" class="btn btn-secondary" style="min-height:auto; padding:0.35rem 0.85rem; font-size:0.82rem;">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="padding: 1.25rem; text-align: center;">
                                    <div class="empty-state">
                                        <strong>No deduction history found.</strong>
                                        <div style="margin-top: 0.35rem;">This product code has not been released yet.</div>
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

