@extends('layouts.app')

@section('title', 'Supplier Dashboard')
@section('pageHeading', 'Supplier Dashboard')
@section('pageSubheading', 'Overview of all suppliers and their receiving performance.')

@section('content')
    {{-- KPI Grid --}}
    <section class="dashboard-kpi-grid" role="region" aria-label="Supplier overview metrics">
        <article class="kpi-card kpi-card--indigo">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </span>
                <span class="kpi-card-label">Total Suppliers</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalSuppliers) }}</p>
            <p class="kpi-card-foot">Registered vendors</p>
        </article>

        <article class="kpi-card kpi-card--blue">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                </span>
                <span class="kpi-card-label">Total Receivings</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalReceivingsAll) }}</p>
            <p class="kpi-card-foot">All receiving records</p>
        </article>

        <article class="kpi-card kpi-card--green">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </span>
                <span class="kpi-card-label">This Month</span>
            </div>
            <p class="kpi-card-value">{{ number_format($monthlyReceivings) }}</p>
            <p class="kpi-card-foot">Receivings in {{ now()->format('F Y') }}</p>
        </article>

        <article class="kpi-card kpi-card--amber">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                </span>
                <span class="kpi-card-label">Avg. Per Supplier</span>
            </div>
            <p class="kpi-card-value">{{ $totalSuppliers > 0 ? number_format($totalReceivingsAll / $totalSuppliers, 1) : '0' }}</p>
            <p class="kpi-card-foot">Receivings per supplier</p>
        </article>

        <article class="kpi-card kpi-card--violet">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="5"/><path d="M3 21v-2a7 7 0 0 1 7-7h4a7 7 0 0 1 7 7v2"/></svg>
                </span>
                <span class="kpi-card-label">Top Suppliers</span>
            </div>
            <p class="kpi-card-value">{{ $topSuppliers->count() }}</p>
            <p class="kpi-card-foot">Most active vendors</p>
        </article>
    </section>

    {{-- Charts Row --}}
    <div class="dashboard-analytics-row">
        <section class="section-card chart-card" aria-label="Monthly receivings chart">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Monthly Receivings Trend</h3>
                    <p class="page-description">Receivings per month for the last 6 months.</p>
                </div>
                <span class="chart-legend" aria-hidden="true">
                    <span class="chart-legend-item"><span class="chart-legend-swatch chart-legend-swatch--received" style="background: var(--accent);"></span> Receivings</span>
            </div>
            <div class="chart-container">
                <canvas id="monthlyReceivingsChart" role="img" aria-label="Line chart showing receivings per month"></canvas>
            </div>
        </section>

        <section class="section-card chart-card" aria-label="Top suppliers share chart">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Top Suppliers Share</h3>
                    <p class="page-description">Receivings distribution among top suppliers.</p>
                </div>
            <div class="chart-container">
                <canvas id="supplierShareChart" role="img" aria-label="Doughnut chart showing supplier receivings distribution"></canvas>
            </div>
        </section>
    </div>

    {{-- Top Suppliers --}}
    <section class="section-card" aria-label="Top suppliers">
        <div class="section-header compact">
            <div>
                <h2 class="section-card-title">Top Suppliers</h2>
                <p class="page-description">Suppliers with the most receiving records.</p>
            </div>
            <a href="{{ route('suppliers.index') }}" class="section-link">Manage Suppliers</a>
        </div>

        @if($topSuppliers->isEmpty())
            <div class="empty-state" role="status">
                <strong>No suppliers yet.</strong>
                <div style="margin-top: 0.35rem;">Create suppliers to see them ranked here.</div>
        @else
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Company Name</th>
                            <th>Total Receivings</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topSuppliers as $index => $supplier)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="table-link" style="font-weight: 600;">
                                        {{ $supplier->company_name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ $supplier->receivings_count }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="table-link">View Dashboard</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- All Suppliers List --}}
    <section class="section-card" aria-label="All suppliers">
        <div class="section-header compact">
            <div>
                <h2 class="section-card-title">All Suppliers</h2>
                <p class="page-description">Complete list of registered suppliers with metrics.</p>
            </div>
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">New Supplier</a>
        </div>

        @if($suppliers->isEmpty())
            <div class="empty-state" role="status">
                <strong>No suppliers found.</strong>
                <div style="margin-top: 0.35rem;">Create your first supplier to get started.</div>
        @else
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Receivings</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $supplier)
                            <tr>
                                <td>
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="table-link" style="font-weight: 600;">
                                        {{ $supplier->company_name }}
                                    </a>
                                </td>
                                <td>{{ $supplier->contact_person ?? '—' }}</td>
                                <td>{{ $supplier->phone_number ?? '—' }}</td>
                                <td>{{ $supplier->email ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-primary">{{ $supplier->receivings_count }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="table-link">View</a>
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="table-link">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper">
                {{ $suppliers->withQueryString()->links() }}
            </div>
        @endif
    </section>
@endsection

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}" defer></script>
<script defer>
document.addEventListener('DOMContentLoaded', function () {
    var monthlyCanvas = document.getElementById('monthlyReceivingsChart');
    var shareCanvas = document.getElementById('supplierShareChart');

    if (!monthlyCanvas || !shareCanvas || typeof Chart === 'undefined') return;

    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    var gridColor = isDark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(148, 163, 184, 0.25)';
    var textColor = isDark ? '#cbd5e1' : '#64748b';

    // Monthly Receivings Trend Line Chart
    var monthlyLabels = @json($monthlyLabels);
    var monthlyCounts = @json($monthlyCounts);

    new Chart(monthlyCanvas, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Receivings',
                data: monthlyCounts,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#8b5cf6',
                pointBorderColor: isDark ? '#0f172a' : '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: prefersReducedMotion ? false : { duration: 300 },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#0f172a',
                    padding: 12,
                    cornerRadius: 8,
                },
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

    // Supplier Share Doughnut Chart
    var shareLabels = @json($shareLabels);
    var shareCounts = @json($shareCounts);
    var shareColors = ['#8b5cf6', '#3b82f6', '#16a34a', '#d97706', '#dc2626', '#64748b', '#06b6d4', '#f59e0b'];

    new Chart(shareCanvas, {
        type: 'doughnut',
        data: {
            labels: shareLabels,
            datasets: [{
                data: shareCounts,
                backgroundColor: shareColors.slice(0, shareLabels.length),
                borderColor: isDark ? '#0f172a' : '#ffffff',
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: prefersReducedMotion ? false : { duration: 300 },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: textColor,
                        padding: 12,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { size: 11 },
                    },
                },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#0f172a',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                            var pct = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + context.parsed + ' (' + pct + '%)';
                        }
                    }
                },
            },
        },
    });
});
</script>
@endpush
