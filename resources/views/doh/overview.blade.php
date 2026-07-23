@extends('layouts.app')

@section('title', $dashboardTitle)
@section('pageHeading', $dashboardTitle)
@section('pageSubheading', $dashboardSubheading)

@section('content')
    {{-- KPI Grid --}}
    <section class="dashboard-kpi-grid" role="region" aria-label="DOH supply chain metrics">
        <article class="kpi-card kpi-card--blue">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </span>
                <span class="kpi-card-label">DOH Suppliers</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalSuppliers) }}</p>
            <p class="kpi-card-foot">Registered DOH vendors</p>
        </article>

        <article class="kpi-card kpi-card--teal">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                </span>
                <span class="kpi-card-label">Total Receivings</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalReceivingsAll) }}</p>
            <p class="kpi-card-foot">All DOH receiving records</p>
        </article>

        <article class="kpi-card kpi-card--green">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                </span>
                <span class="kpi-card-label">Items Received</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalItemsReceived) }}</p>
            <p class="kpi-card-foot">Cumulative qty from DOH</p>
        </article>

        <article class="kpi-card kpi-card--amber">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </span>
                <span class="kpi-card-label">Received This Month</span>
            </div>
            <p class="kpi-card-value">{{ number_format($monthlyReceivedQty) }}</p>
            <p class="kpi-card-foot">{{ number_format($monthlyReceivings) }} receipts</p>
        </article>

        <article class="kpi-card kpi-card--violet">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                </span>
                <span class="kpi-card-label">Total Releases</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalReleases) }}</p>
            <p class="kpi-card-foot">{{ number_format($totalReleasedItems) }} units total</p>
        </article>

        <article class="kpi-card kpi-card--red">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/></svg>
                </span>
                <span class="kpi-card-label">Released This Month</span>
            </div>
            <p class="kpi-card-value">{{ number_format($monthlyReleasedQty) }}</p>
            <p class="kpi-card-foot">{{ number_format($monthlyReleases) }} releases</p>
        </article>

        <article class="kpi-card kpi-card--indigo">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                </span>
                <span class="kpi-card-label">Net Inventory</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalItemsReceived - $totalReleasedItems) }}</p>
            <p class="kpi-card-foot">Balance (received − released)</p>
        </article>

        <article class="kpi-card kpi-card--primary">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <span class="kpi-card-label">Active DOH Suppliers</span>
            </div>
            <p class="kpi-card-value">{{ $topSuppliers->count() }}</p>
            <p class="kpi-card-foot">Suppliers with recent receivings</p>
        </article>
    </section>

    {{-- Charts Row --}}
    <div class="dashboard-analytics-row">
        <section class="section-card chart-card" aria-label="DOH supply movement chart">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Supply Movement</h3>
                    <p class="page-description">Monthly DOH received vs released (last 6 months).</p>
                </div>
                <span class="chart-legend" aria-hidden="true">
                    <span class="chart-legend-item"><span class="chart-legend-swatch chart-legend-swatch--received" style="background: #16a34a;"></span> Received</span>
                    <span class="chart-legend-item"><span class="chart-legend-swatch chart-legend-swatch--released" style="background: #7c3aed;"></span> Released</span>
                </span>
            </div>
            <div class="chart-container">
                <canvas id="dohSupplyMovementChart" role="img" aria-label="Bar chart: DOH received vs released"></canvas>
            </div>
        </section>

        <section class="section-card chart-card" aria-label="Top DOH suppliers chart">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Top DOH Suppliers</h3>
                    <p class="page-description">Receiving volume by supplier (last 6 months).</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="dohSupplierShareChart" role="img" aria-label="Doughnut chart showing DOH supplier receiving distribution"></canvas>
            </div>
        </section>
    </div>

    {{-- Charts Row 2 --}}
    <div class="dashboard-analytics-row">
        <section class="section-card chart-card" aria-label="Monthly receivings trend">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Monthly Receivings Trend</h3>
                    <p class="page-description">DOH receivings per month (last 6 months).</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="dohMonthlyChart" role="img" aria-label="Line chart: monthly DOH receivings"></canvas>
            </div>
        </section>

        <section class="section-card chart-card" aria-label="Release trend">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Release Trend</h3>
                    <p class="page-description">Monthly release quantity from DOH-sourced items.</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="dohReleaseTrendChart" role="img" aria-label="Line chart: DOH release trend"></canvas>
            </div>
        </section>
    </div>

    {{-- Recent Activity --}}
    <div class="dashboard-content-grid">
        <section class="section-card" aria-label="Recent DOH receivings">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Recently Received</h3>
                    <p class="page-description">Latest supply receipts from DOH suppliers.</p>
                </div>
                <a href="{{ route('receivings.index') }}" class="section-link">View all</a>
            </div>

            @if($recentReceived->isEmpty())
                <div class="empty-state" role="status">
                    <strong>No receive records found.</strong>
                    <div style="margin-top: 0.35rem;">Receipts from DOH suppliers appear here.</div>
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
                    <h3 class="section-card-title">Recently Released</h3>
                    <p class="page-description">Latest releases of DOH-sourced items.</p>
                </div>
                <a href="{{ route('releases.index') }}" class="section-link">View all</a>
            </div>

            @if($recentReleases->isEmpty())
                <div class="empty-state" role="status">
                    <strong>No release records found.</strong>
                    <div style="margin-top: 0.35rem;">Releases of DOH items appear here.</div>
                </div>
            @else
                <ul class="activity-list">
                    @foreach($recentReleases as $release)
                        <li class="activity-item">
                            <span class="activity-dot activity-dot--violet" aria-hidden="true"></span>
                            <div class="activity-body">
                                <p class="activity-title">{{ $release->release_number }}</p>
                                <p class="activity-meta">{{ $release->facility_name }} · {{ $release->date_released->format('M d, Y') }}</p>
                            </div>
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
    var movementCanvas = document.getElementById('dohSupplyMovementChart');
    var shareCanvas = document.getElementById('dohSupplierShareChart');
    var monthlyCanvas = document.getElementById('dohMonthlyChart');
    var trendCanvas = document.getElementById('dohReleaseTrendChart');

    if (typeof Chart === 'undefined') return;

    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    var gridColor = isDark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(148, 163, 184, 0.25)';
    var textColor = isDark ? '#cbd5e1' : '#64748b';

    var chartDefaults = {
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

    // 1. Supply Movement Bar Chart
    if (movementCanvas) {
        var movementData = @json($supplyMovement);
        new Chart(movementCanvas, {
            type: 'bar',
            data: {
                labels: movementData.map(function(d) { return d.month; }),
                datasets: [
                    {
                        label: 'Received',
                        data: movementData.map(function(d) { return d.received; }),
                        backgroundColor: 'rgba(22, 163, 74, 0.85)',
                        borderRadius: 6,
                        barThickness: 20,
                    },
                    {
                        label: 'Released',
                        data: movementData.map(function(d) { return d.released; }),
                        backgroundColor: 'rgba(124, 58, 237, 0.85)',
                        borderRadius: 6,
                        barThickness: 20,
                    }
                ]
            },
            options: {
                ...chartDefaults,
                plugins: {
                    ...chartDefaults.plugins,
                    legend: { display: false },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { size: 10 } },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor, precision: 0 },
                    },
                },
            },
        });
    }

    // 2. Top Suppliers Doughnut Chart
    if (shareCanvas) {
        var shareColors = ['#16a34a', '#3b82f6', '#8b5cf6', '#d97706', '#dc2626', '#06b6d4'];
        new Chart(shareCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($shareLabels),
                datasets: [{
                    data: @json($shareCounts),
                    backgroundColor: shareColors.slice(0, @json(count($shareLabels))),
                    borderColor: isDark ? '#0f172a' : '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 6,
                }]
            },
            options: {
                ...chartDefaults,
                plugins: {
                    ...chartDefaults.plugins,
                    legend: {
                        position: 'bottom',
                        labels: { color: textColor, padding: 12, usePointStyle: true, pointStyle: 'circle', font: { size: 10 } },
                    },
                },
            },
        });
    }

    // 3. Monthly Receivings Trend Line
    if (monthlyCanvas) {
        new Chart(monthlyCanvas, {
            type: 'line',
            data: {
                labels: @json($monthlyLabels),
                datasets: [{
                    label: 'Receivings',
                    data: @json($monthlyCounts),
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#16a34a',
                    pointBorderColor: isDark ? '#0f172a' : '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 3
                }]
            },
            options: {
                ...chartDefaults,
                plugins: {
                    ...chartDefaults.plugins,
                    legend: { display: false },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { size: 11 } },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor, precision: 0 },
                    },
                },
            },
        });
    }

    // 4. Release Trend Line
    if (trendCanvas) {
        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: @json($monthlyReleaseLabels),
                datasets: [{
                    label: 'Units Released',
                    data: @json($monthlyReleaseCounts),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#7c3aed',
                    pointBorderColor: isDark ? '#0f172a' : '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 3
                }]
            },
            options: {
                ...chartDefaults,
                plugins: {
                    ...chartDefaults.plugins,
                    legend: { display: false },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { size: 11 } },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor, precision: 0 },
                    },
                },
            },
        });
    }
});
</script>
@endpush

