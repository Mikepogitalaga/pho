@extends('layouts.app')

@section('title', 'Dashboard')
@section('pageHeading', 'Recommended Dashboard')
@section('pageSubheading', 'Comprehensive overview of inventory, supply movements, and key metrics.')

@section('content')
    <section class="dashboard-quick-actions" aria-label="Quick actions">
        <a href="{{ route('receivings.create') }}" class="quick-action-card quick-action-card--primary">
            <span class="quick-action-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
            </span>
            <span>
                <span class="quick-action-label">New Receiving</span>
                <span class="quick-action-hint">Record incoming supplies</span>
            </span>
        </a>
        <a href="{{ route('releases.create') }}" class="quick-action-card">
            <span class="quick-action-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </span>
            <span>
                <span class="quick-action-label">New Release</span>
                <span class="quick-action-hint">Issue stock to facilities</span>
            </span>
        </a>
        <a href="{{ route('pas.create') }}" class="quick-action-card">
            <span class="quick-action-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </span>
            <span>
                <span class="quick-action-label">New Pass</span>
                <span class="quick-action-hint">Create PAS / Property Allocation</span>
            </span>
        </a>
        <a href="{{ route('items.index') }}" class="quick-action-card">
            <span class="quick-action-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            </span>
            <span>
                <span class="quick-action-label">Browse Items</span>
                <span class="quick-action-hint">View full inventory</span>
            </span>
        </a>
        <a href="{{ route('suppliers.index') }}" class="quick-action-card">
            <span class="quick-action-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </span>
            <span>
                <span class="quick-action-label">Suppliers</span>
                <span class="quick-action-hint">Manage vendor records</span>
            </span>
        </a>
    </section>

    {{-- ═══════════════ TOP SECTION: KPI GRID (8 CARDS) ═══════════════ --}}
    <section class="dashboard-kpi-grid" role="region" aria-label="Key performance indicators">
        <article class="kpi-card kpi-card--blue">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </span>
                <span class="kpi-card-label">Total Inventory Items</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalItems) }}</p>
            <p class="kpi-card-foot">Active inventory records</p>
        </article>

        <article class="kpi-card kpi-card--green">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                </span>
                <span class="kpi-card-label">Current Stock</span>
            </div>
            <p class="kpi-card-value">{{ number_format($currentStock) }}</p>
            <p class="kpi-card-foot">Units on hand</p>
        </article>

        <article class="kpi-card kpi-card--teal">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                </span>
                <span class="kpi-card-label">Total Received</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalReceived) }}</p>
            <p class="kpi-card-foot">All-time received units</p>
        </article>

        <article class="kpi-card kpi-card--violet">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/></svg>
                </span>
                <span class="kpi-card-label">Total Released</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalReleased) }}</p>
            <p class="kpi-card-foot">All-time released units</p>
        </article>

        <article class="kpi-card kpi-card--amber {{ $lowStockItems->count() > 0 ? 'kpi-card--alert' : '' }}">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </span>
                <span class="kpi-card-label">Low Stock</span>
            </div>
            <p class="kpi-card-value">{{ number_format($lowStockCount) }}</p>
            <p class="kpi-card-foot">Items below reorder level</p>
        </article>

        <article class="kpi-card kpi-card--red">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                <span class="kpi-card-label">Expiring Items</span>
            </div>
            <p class="kpi-card-value">{{ number_format($expiringItemsCount) }}</p>
            <p class="kpi-card-foot">Within 30 days</p>
        </article>

        <article class="kpi-card kpi-card--indigo">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </span>
                <span class="kpi-card-label">Inventory Value</span>
            </div>
            <p class="kpi-card-value">₱{{ number_format($inventoryValue, 2) }}</p>
            <p class="kpi-card-foot">Total stock value</p>
        </article>

        <article class="kpi-card kpi-card--primary">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <span class="kpi-card-label">Total Suppliers</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalSuppliers) }}</p>
            <p class="kpi-card-foot">Registered vendors</p>
        </article>
    </section>

    {{-- ═══════════════ MIDDLE SECTION: CHARTS (2 COLUMNS) ═══════════════ --}}
    <div class="dashboard-analytics-row">
        <section class="section-card chart-card" aria-label="Supply movement trend chart">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Supply Movement Trend</h3>
                    <p class="page-description">Monthly received vs released over the past 12 months.</p>
                </div>
                <span class="chart-legend" aria-hidden="true">
                    <span class="chart-legend-item"><span class="chart-legend-swatch chart-legend-swatch--received"></span> Received</span>
                    <span class="chart-legend-item"><span class="chart-legend-swatch chart-legend-swatch--released"></span> Released</span>
                </span>
            </div>
            <div class="chart-container">
                <canvas id="supplyMovementChart" role="img" aria-label="Line chart showing supply movement trend over 12 months"></canvas>
            </div>
        </section>

        <section class="section-card chart-card" aria-label="Inventory by category chart">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Inventory by Category</h3>
                    <p class="page-description">Item count distribution across categories.</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="inventoryByCategoryChart" role="img" aria-label="Pie chart showing inventory distribution by category"></canvas>
            </div>
        </section>
    </div>

    {{-- ═══════════════ BOTTOM SECTION: CHARTS ═══════════════ --}}
    <div class="dashboard-analytics-row">
        <section class="section-card chart-card" aria-label="Monthly receiving by supplier">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Monthly Receiving by Supplier</h3>
                    <p class="page-description">Top suppliers by received volume (last 6 months).</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="monthlyReceivingBySupplierChart" role="img" aria-label="Donut chart showing receiving by supplier"></canvas>
            </div>
        </section>

        <section class="section-card chart-card" aria-label="Top 10 most released items">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Top 10 Most Released Items</h3>
                    <p class="page-description">Highest volume items released to facilities.</p>
                </div>
            </div>
            <div class="chart-container chart-container--tall">
                <canvas id="topReleasedItemsChart" role="img" aria-label="Horizontal bar chart showing top released items"></canvas>
            </div>
        </section>
    </div>

    <div class="dashboard-analytics-row">
        <section class="section-card chart-card" aria-label="Releases by facility">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Releases by Facility</h3>
                    <p class="page-description">Distribution volume to facilities.</p>
                </div>
            </div>
            <div class="chart-container chart-container--tall">
                <canvas id="releasesByFacilityChart" role="img" aria-label="Horizontal bar chart showing releases by facility"></canvas>
            </div>
        </section>

        <section class="section-card chart-card" aria-label="Stock status">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Stock Status</h3>
                    <p class="page-description">Current inventory health distribution.</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="stockStatusChart" role="img" aria-label="Pie chart showing stock status distribution"></canvas>
            </div>
        </section>
    </div>

    {{-- ═══════════════ BOTTOM TABLES (2x2 GRID) ═══════════════ --}}
    <div class="dashboard-tables-grid">
        <section class="section-card" aria-label="Recent receivings">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Recent Receivings</h3>
                    <p class="page-description">Latest supply receipts.</p>
                </div>
                <a href="{{ route('receivings.index') }}" class="section-link">View all</a>
            </div>

            @if($recentReceived->isEmpty())
                <div class="empty-state" role="status">
                    <strong>No receive records found.</strong>
                    <div>When receipts are saved, they appear here.</div>
                </div>
            @else
                <ul class="activity-list">
                    @foreach($recentReceived as $receive)
                        <li class="activity-item">
                            <span class="activity-dot activity-dot--success" aria-hidden="true"></span>
                            <div class="activity-body">
                                <p class="activity-title">{{ $receive->receiving_number }}</p>
                                <p class="activity-meta">{{ $receive->supplier->company_name }} · {{ $receive->date_received->format('M d, Y') }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="section-card" aria-label="Recent releases">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Recent Releases</h3>
                    <p class="page-description">Latest release slips.</p>
                </div>
                <a href="{{ route('releases.index') }}" class="section-link">View all</a>
            </div>

            @if($recentReleased->isEmpty())
                <div class="empty-state" role="status">
                    <strong>No release records found.</strong>
                    <div>Release slips will show up once recorded.</div>
                </div>
            @else
                <ul class="activity-list">
                    @foreach($recentReleased as $release)
                        <li class="activity-item">
                            <span class="activity-dot activity-dot--violet" aria-hidden="true"></span>
                            <div class="activity-body">
                                <p class="activity-title">{{ $release->release_number }}</p>
                                <p class="activity-meta">{{ $release->facility_name }} · {{ optional($release->date_released)->format('M d, Y') ?? '—' }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="section-card" aria-label="Low stock alerts">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Low Stock Alerts</h3>
                    <p class="page-description">Items at or below reorder level.</p>
                </div>
                <a href="{{ route('items.index', ['status' => 'low']) }}" class="section-link">View all</a>
            </div>

            @if($lowStockItems->isEmpty())
                <div class="empty-state" role="status">
                    <strong>All stock levels healthy.</strong>
                    <div>No items are currently below reorder level.</div>
                </div>
            @else
                <ul class="activity-list">
                    @foreach($lowStockItems as $item)
                        <li class="activity-item">
                            <span class="activity-dot activity-dot--warning" aria-hidden="true"></span>
                            <div class="activity-body">
                                <a href="{{ route('items.show', $item) }}" class="activity-title">{{ $item->item_code }} · {{ $item->name }}</a>
                                <p class="activity-meta">{{ $item->quantity_on_hand }} on hand · reorder at {{ $item->reorder_level ?? '—' }}</p>
                            </div>
                            <span class="badge badge-warning">{{ $item->status }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="section-card" aria-label="Expiring items">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Expiring Items</h3>
                    <p class="page-description">Items nearing expiry within 30 days.</p>
                </div>
                <a href="{{ route('items.index') }}" class="section-link">View items</a>
            </div>

            @if($upcomingExpiries->isEmpty())
                <div class="empty-state" role="status">
                    <strong>No upcoming expiries.</strong>
                    <div>No items expire in the next 30 days.</div>
                </div>
            @else
                <ul class="activity-list">
                    @foreach($upcomingExpiries as $expiry)
                        <li class="activity-item">
                            <span class="activity-dot activity-dot--danger" aria-hidden="true"></span>
                            <div class="activity-body">
                                <a href="{{ route('items.show', $expiry->item) }}" class="activity-title">{{ $expiry->item->item_code }} · {{ $expiry->item->name }}</a>
                                <p class="activity-meta">{{ $expiry->expiry_date->format('M d, Y') }} · {{ $expiry->item->expiry_status }}</p>
                            </div>
                            <span class="badge badge-warning">Expiring</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/chart.umd.min.js') }}" defer></script>
    <script defer>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') return;

            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const gridColor = isDark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(148, 163, 184, 0.25)';
            const textColor = isDark ? '#cbd5e1' : '#64748b';

            const chartDefaults = {
                responsive: true,
                maintainAspectRatio: false,
                animation: prefersReducedMotion ? false : { duration: 300 },
                plugins: {
                    legend: {
                        labels: { color: textColor, font: { size: 11 } },
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                    },
                },
            };

            // ── 1. Supply Movement Trend (Line Chart) ──
            const supplyCtx = document.getElementById('supplyMovementChart');
            if (supplyCtx) {
                new Chart(supplyCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($supplyMovement->pluck('month')) !!},
                        datasets: [
                            {
                                label: 'Received',
                                data: {!! json_encode($supplyMovement->pluck('received')->map(function($v) { return (int) $v; })) !!},
                                borderColor: '#16a34a',
                                backgroundColor: 'rgba(22, 163, 74, 0.12)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                borderWidth: 2,
                            },
                            {
                                label: 'Released',
                                data: {!! json_encode($supplyMovement->pluck('released')->map(function($v) { return (int) $v; })) !!},
                                borderColor: '#7c3aed',
                                backgroundColor: 'rgba(124, 58, 237, 0.12)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                borderWidth: 2,
                            },
                        ],
                    },
                    options: {
                        ...chartDefaults,
                        plugins: {
                            ...chartDefaults.plugins,
                            legend: {
                                display: true,
                                position: 'top',
                                labels: { color: textColor, font: { size: 11 } },
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: textColor, maxTicksLimit: 8, maxRotation: 45 },
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 },
                            },
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                    },
                });
            }

            // ── 2. Inventory by Category (Pie Chart) ──
            const categoryCtx = document.getElementById('inventoryByCategoryChart');
            if (categoryCtx) {
                const catData = {!! json_encode($inventoryByCategory) !!};
                const colors = [
                    '#2563eb', '#7c3aed', '#16a34a', '#d97706', '#dc2626',
                    '#0891b2', '#db2777', '#65a30d', '#9333ea', '#ea580c',
                ];
                new Chart(categoryCtx, {
                    type: 'pie',
                    data: {
                        labels: catData.map(function(d) { return d.category; }),
                        datasets: [{
                            data: catData.map(function(d) { return d.count; }),
                            backgroundColor: colors.slice(0, catData.length),
                            borderWidth: 2,
                            borderColor: isDark ? '#0f172a' : '#ffffff',
                        }],
                    },
                    options: {
                        ...chartDefaults,
                        plugins: {
                            ...chartDefaults.plugins,
                            legend: {
                                position: 'bottom',
                                labels: { color: textColor, font: { size: 10 }, padding: 12 },
                            },
                        },
                    },
                });
            }

            // ── 3. Top 10 Most Released Items (Horizontal Bar) ──
            const topItemsCtx = document.getElementById('topReleasedItemsChart');
            if (topItemsCtx) {
                const topData = {!! json_encode($topReleasedItems) !!};
                new Chart(topItemsCtx, {
                    type: 'bar',
                    data: {
                        labels: topData.map(function(d) { return d.name.length > 25 ? d.name.substring(0, 25) + '…' : d.name; }).reverse(),
                        datasets: [{
                            label: 'Units Released',
                            data: topData.map(function(d) { return d.total; }).reverse(),
                            backgroundColor: 'rgba(37, 99, 235, 0.75)',
                            borderRadius: 4,
                        }],
                    },
                    options: {
                        ...chartDefaults,
                        indexAxis: 'y',
                        plugins: {
                            ...chartDefaults.plugins,
                            legend: { display: false },
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 },
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor, font: { size: 10 } },
                            },
                        },
                    },
                });
            }

            // ── 4. Monthly Receiving by Supplier (Donut Chart) ──
            const supplierCtx = document.getElementById('monthlyReceivingBySupplierChart');
            if (supplierCtx) {
                const suppData = {!! json_encode($monthlyReceivingBySupplier) !!};
                const supplierColors = [
                    '#22c55e', '#3b82f6', '#8b5cf6',
                    '#eab308', '#ef4444', '#06b6d4',
                ];
                new Chart(supplierCtx, {
                    type: 'doughnut',
                    data: {
                        labels: suppData.map(function(d) { return d.supplier.length > 18 ? d.supplier.substring(0, 18) + '…' : d.supplier; }),
                        datasets: [{
                            data: suppData.map(function(d) { return d.total; }),
                            backgroundColor: supplierColors.slice(0, suppData.length),
                            borderWidth: 2,
                            borderColor: isDark ? '#0f172a' : '#ffffff',
                        }],
                    },
                    options: {
                        ...chartDefaults,
                        plugins: {
                            ...chartDefaults.plugins,
                            legend: {
                                position: 'bottom',
                                labels: { color: textColor, font: { size: 10 }, padding: 12 },
                            },
                        },
                    },
                });
            }

            // ── 5. Releases by Facility (Horizontal Bar) ──
            const facilityCtx = document.getElementById('releasesByFacilityChart');
            if (facilityCtx) {
                const facData = {!! json_encode($releasesByFacility) !!};
                new Chart(facilityCtx, {
                    type: 'bar',
                    data: {
                        labels: facData.map(function(d) { return d.facility.length > 25 ? d.facility.substring(0, 25) + '…' : d.facility; }).reverse(),
                        datasets: [{
                            label: 'Units Released',
                            data: facData.map(function(d) { return d.total; }).reverse(),
                            backgroundColor: 'rgba(124, 58, 237, 0.75)',
                            borderRadius: 4,
                        }],
                    },
                    options: {
                        ...chartDefaults,
                        indexAxis: 'y',
                        plugins: {
                            ...chartDefaults.plugins,
                            legend: { display: false },
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 },
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor, font: { size: 10 } },
                            },
                        },
                    },
                });
            }

            // ── 6. Stock Status (Pie Chart) ──
            const statusCtx = document.getElementById('stockStatusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Available', 'Low Stock', 'Out of Stock'],
                        datasets: [{
                            data: [{{ $availableCount - $lowStockCount }}, {{ $lowStockCount }}, {{ $outOfStockCount }}],
                            backgroundColor: ['#16a34a', '#d97706', '#dc2626'],
                            borderWidth: 2,
                            borderColor: isDark ? '#0f172a' : '#ffffff',
                        }],
                    },
                    options: {
                        ...chartDefaults,
                        plugins: {
                            ...chartDefaults.plugins,
                            legend: {
                                position: 'bottom',
                                labels: { color: textColor, font: { size: 10 }, padding: 12 },
                            },
                        },
                    },
                });
            }
        });
    </script>
@endpush

