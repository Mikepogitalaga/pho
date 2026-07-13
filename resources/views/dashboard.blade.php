@extends('layouts.app')

@section('title', 'Dashboard')
@section('pageHeading', 'Dashboard')
@section('pageSubheading', 'Quick overview of inventory and supply movements.')

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

    <section class="dashboard-kpi-grid" role="region" aria-label="Key performance indicators">
        <article class="kpi-card kpi-card--blue">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </span>
                <span class="kpi-card-label">Total Items</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalItems) }}</p>
            <p class="kpi-card-foot">Active inventory records</p>
        </article>

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

        <article class="kpi-card kpi-card--green">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                </span>
                <span class="kpi-card-label">Received This Month</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalReceivedThisMonth) }}</p>
            <p class="kpi-card-foot">{{ now()->format('F Y') }}</p>
        </article>

        <article class="kpi-card kpi-card--violet">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/></svg>
                </span>
                <span class="kpi-card-label">Released This Month</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalReleasedThisMonth) }}</p>
            <p class="kpi-card-foot">{{ now()->format('F Y') }}</p>
        </article>

        <article class="kpi-card kpi-card--amber {{ $lowStockItems->count() > 0 ? 'kpi-card--alert' : '' }}">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </span>
                <span class="kpi-card-label">Low Stock Items</span>
            </div>
            <p class="kpi-card-value">{{ number_format($lowStockItems->count()) }}</p>
            <p class="kpi-card-foot">At or below reorder level</p>
        </article>
    </section>

    <div class="dashboard-analytics-row">
        <section class="section-card chart-card" aria-label="Monthly supply movement chart">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Supply Movement</h3>
                    <p class="page-description">Received vs released this month.</p>
                </div>
                <span class="chart-legend" aria-hidden="true">
                    <span class="chart-legend-item"><span class="chart-legend-swatch chart-legend-swatch--received"></span> Received</span>
                    <span class="chart-legend-item"><span class="chart-legend-swatch chart-legend-swatch--released"></span> Released</span>
                </span>
            </div>
            <div class="chart-container">
                <canvas id="supplyMovementChart" role="img" aria-label="Bar chart comparing received and released supplies this month"></canvas>
            </div>
        </section>

        <section class="section-card" aria-label="Low stock alerts">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Low Stock Alerts</h3>
                    <p class="page-description">Items at or below reorder level.</p>
                </div>
                <a href="{{ route('items.index', ['status' => 'low']) }}" class="section-link">View all</a>
            </div>

            <ul class="activity-list">
                @forelse($lowStockItems as $item)
                    <li class="activity-item">
                        <span class="activity-dot activity-dot--warning" aria-hidden="true"></span>
                        <div class="activity-body">
                            <a href="{{ route('items.show', $item) }}" class="activity-title">{{ $item->item_code }} · {{ $item->name }}</a>
                            <p class="activity-meta">{{ $item->quantity_on_hand }} on hand · reorder at {{ $item->reorder_level ?? '—' }}</p>
                        </div>
                        <span class="badge badge-warning">{{ $item->status }}</span>
                    </li>
                @empty
                    <li class="empty-state" role="status">
                        <strong>All stock levels healthy.</strong>
                        <div>No items are currently below reorder level.</div>
                    </li>
                @endforelse
            </ul>
        </section>
    </div>

    <div class="dashboard-content-grid">
        <section class="section-card" aria-label="Recently received">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Recently Received</h3>
                    <p class="page-description">Latest supply receipts across suppliers.</p>
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

        <section class="section-card" aria-label="Recently released">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Recently Released</h3>
                    <p class="page-description">Latest release slips and distribution.</p>
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
                                <p class="activity-meta">{{ $release->facility_name }} · {{ $release->date_released->format('M d, Y') }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="section-card" aria-label="Expiring soon">
            <div class="section-header compact">
                <div>
                    <h3 class="section-card-title">Expiring Soon</h3>
                    <p class="page-description">Items nearing expiry within 30 days.</p>
                </div>
                <a href="{{ route('items.index') }}" class="section-link">View items</a>
            </div>

            <ul class="activity-list">
                @forelse($upcomingExpiries as $expiry)
                    <li class="activity-item">
                        <span class="activity-dot activity-dot--danger" aria-hidden="true"></span>
                        <div class="activity-body">
                            <a href="{{ route('items.show', $expiry->item) }}" class="activity-title">{{ $expiry->item->item_code }} · {{ $expiry->item->name }}</a>
                            <p class="activity-meta">{{ $expiry->expiry_date->format('M d, Y') }} · {{ $expiry->item->expiry_status }}</p>
                        </div>
                        <span class="badge badge-warning">Expiring</span>
                    </li>
                @empty
                    <li class="empty-state" role="status">
                        <strong>No upcoming expiries.</strong>
                        <div>No items expire in the next 30 days.</div>
                    </li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/chart.umd.min.js') }}" defer></script>
    <script defer>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('supplyMovementChart');
            if (!canvas || typeof Chart === 'undefined') return;

            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            const gridColor = isDark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(148, 163, 184, 0.25)';
            const textColor = isDark ? '#cbd5e1' : '#64748b';

            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: ['This Month'],
                    datasets: [
                        {
                            label: 'Received',
                            data: [{{ $totalReceivedThisMonth }}],
                            backgroundColor: 'rgba(22, 163, 74, 0.85)',
                            borderRadius: 8,
                            barThickness: 48,
                        },
                        {
                            label: 'Released',
                            data: [{{ $totalReleasedThisMonth }}],
                            backgroundColor: 'rgba(124, 58, 237, 0.85)',
                            borderRadius: 8,
                            barThickness: 48,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: prefersReducedMotion ? false : { duration: 220 },
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
                            ticks: { color: textColor, font: { weight: '600' } },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                precision: 0,
                            },
                        },
                    },
                },
            });
        });
    </script>
@endpush
