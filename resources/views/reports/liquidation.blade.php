@extends('layouts.app')

@section('title', 'Liquidation Report')
@section('pageHeading', 'Liquidation Report')
@section('pageSubheading', 'View released items and liquidation details.')

@section('content')

    {{-- KPI Cards --}}
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

    {{-- Filters --}}
    <section class="card" aria-label="Report filters" style="padding: 1.25rem; margin-bottom: 1.25rem;">
        <div class="section-header compact" style="padding: 0 0 1rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <div>
                <h2 class="section-card-title" style="margin: 0;">Filters</h2>
                <p class="page-description" style="margin-top: 0.25rem;">Refine liquidation records by PTR number, facility, dates, item description, or category.</p>
            </div>
            <div class="table-actions">
                @php
                    $activeFilters = collect([
                        'ptr_number' => request('ptr_number'),
                        'facility' => request('facility'),
                        'start_date' => request('start_date'),
                        'end_date' => request('end_date'),
                        'item_description' => request('item_description'),
                        'category' => request('category'),
                    ])->filter();
                @endphp
                @if($activeFilters->isNotEmpty())
                    <span class="filter-count">{{ $activeFilters->count() }}</span>
                    <a href="{{ route('reports.liquidation') }}" class="btn btn-secondary">Clear All</a>
                @endif
            </div>
        </div>

        <form id="liquidationFilterForm" method="GET" class="filter-bar">
            <div class="form-grid-3">
                <div class="form-group">
                    <label for="ptrNumber">PTR / ITR / RIS Number</label>
                    <input id="ptrNumber" type="text" name="ptr_number" value="{{ request('ptr_number') }}" placeholder="Search PTR, ITR, or RIS number" class="search-input" />
                </div>

                <div class="form-group">
                    <label for="facilityFilter">Facility / End-user</label>
                    <input id="facilityFilter" type="text" name="facility" value="{{ request('facility') }}" placeholder="Filter by facility name" class="search-input" />
                </div>

                <div class="form-group">
                    <label for="startDate">Start Date</label>
                    <input id="startDate" type="date" name="start_date" value="{{ request('start_date') }}" class="search-input" />
                </div>

                <div class="form-group">
                    <label for="endDate">End Date</label>
                    <input id="endDate" type="date" name="end_date" value="{{ request('end_date') }}" class="search-input" />
                </div>

                <div class="form-group">
                    <label for="itemDescription">Item Description</label>
                    <input id="itemDescription" type="text" name="item_description" value="{{ request('item_description') }}" placeholder="Search item description" class="search-input" />
                </div>

                <div class="form-group">
                    <label for="categoryFilter">Category</label>
                    <select id="categoryFilter" name="category" class="search-input">
                        <option value="">All Categories</option>
                        @foreach($categoriesWithLiquidations as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="filter-actions">
                <div class="quick-date-bar">
                    <span class="quick-date-label">Quick:</span>
                    <button type="button" class="quick-date-btn" data-start="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" data-end="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">Today</button>
                    <button type="button" class="quick-date-btn" data-start="{{ \Carbon\Carbon::now()->startOfWeek()->format('Y-m-d') }}" data-end="{{ \Carbon\Carbon::now()->endOfWeek()->format('Y-m-d') }}">This Week</button>
                    <button type="button" class="quick-date-btn" data-start="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}" data-end="{{ \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}">This Month</button>
                    <button type="button" class="quick-date-btn" data-start="{{ \Carbon\Carbon::now()->startOfYear()->format('Y-m-d') }}" data-end="{{ \Carbon\Carbon::now()->endOfYear()->format('Y-m-d') }}">This Year</button>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </div>
        </form>

        @if($activeFilters->isNotEmpty())
            <div class="active-filters">
                <span class="active-filters-label">Active filters:</span>
                <div class="filter-chips">
                    @foreach($activeFilters as $key => $value)
                        <span class="filter-chip">
                            <span class="filter-chip-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                            <span class="filter-chip-value">{{ $value }}</span>
                            <a href="{{ route('reports.liquidation', array_filter($activeFilters->except($key)->toArray())) }}" class="filter-chip-remove" title="Remove filter">&times;</a>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <section class="card" style="padding: 0.75rem;" id="resultsTable">
        <div class="section-header compact" style="padding: 0 0 0.75rem; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border);">
            <div>
                <h2 class="section-card-title" style="margin: 0;">Liquidation Records</h2>
                @if($releases->total() > 0)
                    <p class="page-description" style="margin: 0.25rem 0 0;">Showing {{ $releases->firstItem() }}–{{ $releases->lastItem() }} of {{ $releases->total() }} records</p>
                @endif
            </div>
            <div class="table-actions">
                <a href="{{ route('reports.liquidation.export', request()->query()) }}" class="btn btn-secondary">Download Excel</a>
            </div>
        </div>

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
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($releases->count() > 0)
                        @forelse ($releases as $release)
                            @php
                                $filteredItems = $release->items;
                                if (request('category')) {
                                    $filteredItems = $filteredItems->where('category', request('category'));
                                }
                            @endphp
                            @if($filteredItems->count() > 0)
                                @foreach($filteredItems as $item)
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
                                        <td style="text-align: center;">
                                            @php
                                                $statusClass = match($release->status) {
                                                    'Released' => 'badge-success',
                                                    'Released through pass' => 'badge-success',
                                                    'Unreleased' => 'badge-secondary',
                                                    'Canceled' => 'badge-danger',
                                                    'Returned' => 'badge-warning',
                                                    default => 'badge-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ $release->status }}</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="table-row-actions">
                                                <a href="{{ route('releases.view', $release->id) }}" class="btn btn-link" title="View details">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                </a>
                                                <a href="{{ route('reports.liquidation.export', ['release' => $release->id] + request()->query()) }}" class="btn btn-link" title="Export this release">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @empty
                        @endforelse
                    @endif
                    @if($releases->count() === 0 || $releases->sum(function($r) { 
                            $items = $r->items;
                            if (request('category')) {
                                $items = $items->where('category', request('category'));
                            }
                            return $items->count(); 
                        }) === 0)
                        <tr>
                            <td colspan="11" style="padding: 1.25rem;">
                                <div class="empty-state">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto; color: var(--text-muted); opacity: 0.5;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                                    <strong style="margin-top: 0.5rem;">No liquidation records found.</strong>
                                    <div style="margin-top: 0.35rem;">No released items match your filter criteria. Try adjusting your filters or create new releases.</div>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </section>

    <x-pagination.modern :paginator="$releases" />

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hasFilters = {{ request()->hasAny(['ptr_number','facility','start_date','end_date','item_description','category']) ? 'true' : 'false' }};
            if (hasFilters) {
                document.getElementById('resultsTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            document.querySelectorAll('.quick-date-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const start = this.dataset.start;
                    const end = this.dataset.end;
                    document.getElementById('startDate').value = start;
                    document.getElementById('endDate').value = end;
                    document.getElementById('liquidationFilterForm').submit();
                });
            });
        });
    </script>
    @endpush
@endsection
