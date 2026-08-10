@extends('layouts.app')

@section('title', 'Liquidation Report')
@section('pageHeading', 'Liquidation Report')
@section('pageSubheading', 'View released items and liquidation details.')

@section('content')
    

    <section class="card" aria-label="Report filters" style="padding: 1rem; margin-bottom: 1.25rem;">
        <div class="section-header compact" style="padding: 0 0 0.9rem; margin-bottom: 0.9rem;">
            <div>
                <h2 class="section-card-title" style="margin: 0;">Filters</h2>
                <p class="page-description" style="margin-top: 0.25rem;">Filter liquidation records by PTR number, facility, dates, item, and category.</p>
            </div>
            <div class="table-actions">
                @if(request()->hasAny(['ptr_number','facility','start_date','end_date','item_description','category']))
                    <a href="{{ route('reports.liquidation') }}" class="btn btn-secondary">Clear All</a>
                @endif
            </div>
        </div>

        <form id="liquidationFilterForm" method="GET" class="search-panel" style="margin-top: 0;">
            <div>
                <label for="ptrNumber" class="sr-only">PTR Number</label>
                <input id="ptrNumber" type="text" name="ptr_number" value="{{ request('ptr_number') }}" placeholder="Filter by PTR Number" class="search-input" />
            </div>

            <div>
                <label for="facilityFilter" class="sr-only">Facility / End-user</label>
                <input id="facilityFilter" type="text" name="facility" value="{{ request('facility') }}" placeholder="Filter by facility" class="search-input" />
            </div>

            <div>
                <label for="startDate" class="sr-only">Start Date</label>
                <input id="startDate" type="date" name="start_date" value="{{ request('start_date') }}" placeholder="Start Date" class="search-input" />
            </div>

            <div>
                <label for="endDate" class="sr-only">End Date</label>
                <input id="endDate" type="date" name="end_date" value="{{ request('end_date') }}" placeholder="End Date" class="search-input" />
            </div>

            <div>
                <label for="itemDescription" class="sr-only">Item Description</label>
                <input id="itemDescription" type="text" name="item_description" value="{{ request('item_description') }}" placeholder="Filter by item" class="search-input" />
            </div>

            <div>
                <label for="categoryFilter" class="sr-only">Category</label>
                <input id="categoryFilter" type="text" name="category" value="{{ request('category') }}" placeholder="Filter by category" class="search-input" />
            </div>

            <div class="table-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </section>

    <section class="card" style="padding: 1.25rem; margin-bottom: 1.25rem; background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(124, 58, 237, 0.08)); border: 1px solid rgba(37, 99, 235, 0.12);">
        <div class="section-header compact" style="padding: 0 0 1rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(15, 23, 42, 0.08);">
            <div>
                <h2 class="section-card-title" style="margin: 0; font-size: 1.08rem;">Report Summary</h2>
                <p class="page-description" style="margin: 0.25rem 0 0;">Overview of the selected liquidation period.</p>
            </div>
        </div>

        <div class="dashboard-content-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <div class="kpi-card kpi-card--blue" style="padding: 1rem 1.1rem;">
                <div class="kpi-card-header">
                    <div class="kpi-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    </div>
                    <div>
                        <div class="kpi-card-label">Total Releases</div>
                    </div>
                </div>
                <p class="kpi-card-value">{{ $releases->total() }}</p>
                <p class="kpi-card-foot">Release slips processed</p>
            </div>

            <div class="kpi-card kpi-card--green" style="padding: 1rem 1.1rem;">
                <div class="kpi-card-header">
                    <div class="kpi-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </div>
                    <div>
                        <div class="kpi-card-label">Total Quantity</div>
                    </div>
                </div>
                <p class="kpi-card-value">{{ number_format($totalQuantity) }}</p>
                <p class="kpi-card-foot">Units liquidated</p>
            </div>

            <div class="kpi-card kpi-card--violet" style="padding: 1rem 1.1rem;">
                <div class="kpi-card-header">
                    <div class="kpi-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v12M9 9h6a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-6a2 2 0 0 0-2-2v-2a2 2 0 0 0 2-2z"/></svg>
                    </div>
                    <div>
                        <div class="kpi-card-label">Total Cost</div>
                    </div>
                </div>
                <p class="kpi-card-value">₱ {{ number_format($totalCost, 2) }}</p>
                <p class="kpi-card-foot">Total value released</p>
            </div>
        </div>
    </section>

    <section class="card" style="padding: 0.75rem;" id="resultsTable">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>PTR Number</th>
                        <th>Facility / End-user</th>
                        <th>Date Released</th>
                        <th>Item Description</th>
                        <th>Category</th>
                        <th style="text-align: center;">Quantity</th>
                        <th style="text-align: center;">UOM</th>
                        <th style="text-align: right;">Unit Cost</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if($releases->count() > 0)
                        @forelse ($releases as $release)
                            @if($release->items->count() > 0)
                                @forelse($release->items as $item)
                                    <tr>
                                        <td>{{ $release->ptr_itr_ris_no ?? $release->release_number }}</td>
                                        <td>{{ $release->facility_name }}</td>
                                        <td>{{ optional($release->date_released)->format('M d, Y') ?? '—' }}</td>
                                        <td>{{ $item->item_description }}</td>
                                        <td>{{ $item->item?->category ?? '—' }}</td>
                                        <td style="text-align: center;">{{ $item->quantity_released }}</td>
                                        <td style="text-align: center;">{{ $item->uom }}</td>
                                        <td style="text-align: right;">₱ {{ isset($item->unit_cost) ? number_format($item->unit_cost, 2) : '—' }}</td>
                                        <td style="text-align: right; font-weight: 600; color: var(--primary);">₱ {{ isset($item->unit_cost) ? number_format($item->unit_cost * $item->quantity_released, 2) : '—' }}</td>
                                    </tr>
                                @empty
                                @endforelse
                            @endif
                        @empty
                        @endforelse
                    @endif
                    @if($releases->count() === 0 || $releases->sum(function($r) { return $r->items->count(); }) === 0)
                        <tr>
                            <td colspan="9" style="padding: 1.25rem;">
                                <div class="empty-state">
                                    <strong>No liquidation records found.</strong>
                                    <div style="margin-top: 0.35rem;">No released items match your filter criteria. Try adjusting your filters or create new releases.</div>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </section>

    <div class="pagination-wrapper">
        {{ $releases->links() }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hasFilters = {{ request()->hasAny(['ptr_number','facility','start_date','end_date','item_description','category']) ? 'true' : 'false' }};
            if (hasFilters) {
                document.getElementById('resultsTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    </script>
@endsection
