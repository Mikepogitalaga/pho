@extends('layouts.app')

@section('title', 'Item Details')
@section('pageHeading', 'Item Details')
@section('pageSubheading', 'View inventory item information. Stock is updated through Receivings and Releases only.')

@section('content')
    <div class="section-card">
        <div class="section-header">
            <div>
                <h1 class="page-heading">{{ $item->item_code }} - {{ $item->name }}</h1>
                <p class="page-description">Current stock and item details.</p>
            </div>
            <a href="{{ route('items.index') }}" class="btn btn-secondary">Back to Items</a>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Product Code</label>
                <p>{{ $item->item_code }}</p>
            </div>
            <div class="form-group">
                <label>Item Description</label>
                <p>{{ $item->name }}</p>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Category</label>
                <p>{{ $item->category }}</p>
            </div>
            <div class="form-group">
                <label>Unit of Measure (UOM)</label>
                <p>{{ $item->display_unit }}</p>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Current Stock</label>
                <p>{{ $item->quantity_on_hand }}</p>
            </div>
            <div class="form-group">
                <label>Unit Cost</label>
                <p>{{ $item->unit_cost ? number_format($item->unit_cost, 2) : '0.00' }}</p>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Location</label>
                <p>{{ $item->location }}</p>
            </div>
            <div class="form-group">
                <label>Stock Keeping Unit (Program)</label>
                <p>{{ $item->stock_keeping_unit }}</p>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Program Coordinator</label>
                <p>{{ $item->program_coordinator }}</p>
            </div>
            <div class="form-group">
                <label>Status</label>
                <p><span class="status-pill {{ $item->status_class }}">{{ $item->status }}</span></p>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Expiry</label>
                <p><span class="status-pill {{ $item->expiry_badge_class }}">{{ $item->expiry_label }}</span></p>
            </div>
            <div></div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <p>{{ $item->description }}</p>
        </div>
    </div>

    <div class="section-card" style="margin-top: 2rem; padding: 1.25rem;">
        <div class="section-header compact" style="padding: 0 0 1rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <div>
                <h2 class="section-card-title" style="margin: 0;">Stock Deduction Tracking</h2>
                <p class="page-description" style="margin-top: 0.25rem;">View all deductions from releases and receivings.</p>
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
                <p class="kpi-card-value">{{ $item->quantity_on_hand }}</p>
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
                            <th>Reference</th>
                            <th style="text-align: center;">Quantity</th>
                            <th>Facility / Receiver</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deductionHistory as $record)
                            <tr>
                                <td>{{ $record['date']->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge" style="background: {{ $record['type'] === 'Release' ? 'rgba(220, 38, 38, 0.1)' : 'rgba(37, 99, 235, 0.1)' }}; color: {{ $record['type'] === 'Release' ? 'var(--danger)' : 'var(--primary)' }};">{{ $record['type'] }}</span>
                                </td>
                                <td>{{ $record['reference'] }}</td>
                                <td style="text-align: center; font-weight: 600;">{{ $record['quantity'] }}</td>
                                <td>{{ $record['facility'] }}</td>
                                <td>
                                    <span class="badge badge-success">{{ $record['status'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 1.25rem; text-align: center;">
                                    <div class="empty-state">
                                        <strong>No deduction history found.</strong>
                                        <div style="margin-top: 0.35rem;">This item has not been released yet.</div>
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
