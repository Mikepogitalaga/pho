@extends('layouts.app')

@section('title', 'Facility Item Analytics')
@section('pageHeading', 'Facility Item Analytics')
@section('pageSubheading', 'Top 10 Most Released Items per Facility — DM vs MDL')

@section('content')
@php
    $backParams = request()->only(['start_date', 'end_date', 'facility_category']);
@endphp
<a href="{{ route('analytics.facility-categories', $backParams) }}" class="btn btn-secondary" style="margin-bottom:1rem;">← Back to Analytics</a>

    {{-- Filters --}}
    <section class="section-card" style="margin-bottom: 1.5rem;">
        <form method="GET" action="{{ route('analytics.facility-categories.view') }}" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:end;">
            <div class="form-group" style="margin:0;">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" style="min-width:160px;">
            </div>
            <div class="form-group" style="margin:0;">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" style="min-width:160px;">
            </div>
            <div class="form-group" style="margin:0;">
                <label for="facility_category">Facility Category</label>
                <select id="facility_category" name="facility_category" style="min-width:180px;">
                    <option value="All" {{ $activeCategory === 'All' ? 'selected' : '' }}>All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $activeCategory === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="{{ route('analytics.facility-categories.view') }}" class="btn btn-ghost">Reset</a>
        </form>
    </section>

    {{-- Charts Grid --}}
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:1.5rem;">
        @forelse($chartData as $facilityId => $data)
            @php
                $facility = $data['facility'];
                $items = $data['items'];
                $hasData = count($items) > 0;
                $labels = [];
                $dmData = [];
                $mdlData = [];
                foreach ($items as $item) {
                    $labels[] = $item['item_description'];
                    $dmData[] = (int) $item['dm_released'];
                    $mdlData[] = (int) $item['mdl_released'];
                }
            @endphp
            <div class="section-card">
                <h3 style="margin:0 0 0.25rem;font-size:1rem;font-weight:700;color:var(--text);">{{ $facility->name }}</h3>
                <p style="margin:0 0 1rem;font-size:0.78rem;color:var(--text-muted);">{{ $facility->category }} — Top 10 Most Released Items</p>

                @if($hasData)
                    <div style="position:relative;height:{{ max(280, count($items) * 38) }}px;">
                        <canvas id="facility-chart-{{ $facilityId }}"></canvas>
                    </div>
                    <div style="display:flex;gap:1rem;margin-top:0.75rem;font-size:0.78rem;color:var(--text-muted);">
                        <span style="display:flex;align-items:center;gap:0.35rem;"><span style="width:12px;height:12px;border-radius:3px;background:#2563eb;display:inline-block;"></span> DM</span>
                        <span style="display:flex;align-items:center;gap:0.35rem;"><span style="width:12px;height:12px;border-radius:3px;background:#0d9488;display:inline-block;"></span> MDL</span>
                    </div>
                @else
                    <div class="empty-state" style="padding:2rem 1rem;">
                        <strong>No release data</strong>
                        <div>No released items recorded for this facility in the selected period.</div>
                    </div>
                @endif
            </div>
        @empty
            <div class="section-card" style="grid-column:1/-1;">
                <div class="empty-state">
                    <strong>No facilities found.</strong>
                    <div>No facilities match the selected criteria.</div>
                </div>
            </div>
        @endforelse
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($chartData as $facilityId => $data)
                @if(count($data['items']) > 0)
                    (function() {
                        const ctx = document.getElementById('facility-chart-{{ $facilityId }}').getContext('2d');
                        const items = @json($data['items']);

                        const labels = items.map(d => d.item_description);
                        const dmData = items.map(d => parseInt(d.dm_released) || 0);
                        const mdlData = items.map(d => parseInt(d.mdl_released) || 0);

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: 'DM Released',
                                        data: dmData,
                                        backgroundColor: '#2563eb',
                                        borderRadius: 4,
                                        barPercentage: 0.7,
                                    },
                                    {
                                        label: 'MDL Released',
                                        data: mdlData,
                                        backgroundColor: '#0d9488',
                                        borderRadius: 4,
                                        barPercentage: 0.7,
                                    }
                                ]
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            footer: function(tooltipItems) {
                                                const idx = tooltipItems[0].dataIndex;
                                                const dm = dmData[idx] || 0;
                                                const mdl = mdlData[idx] || 0;
                                                const total = dm + mdl;
                                                const item = items[idx];
                                                let lines = ['Total Released: ' + total.toLocaleString()];
                                                if (item.transaction_count) lines.push('Release Transactions: ' + item.transaction_count);
                                                return lines;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        stacked: false,
                                        ticks: { font: { size: 11 } },
                                        grid: { color: 'rgba(0,0,0,0.06)' }
                                    },
                                    y: {
                                        stacked: false,
                                        ticks: { font: { size: 10 } },
                                        grid: { display: false }
                                    }
                                }
                            }
                        });
                    })();
                @endif
            @endforeach
        });
    </script>
    @endpush

    @push('styles')
    <style>
        @media (max-width: 768px) {
            div[style*="grid-template-columns:repeat(2"] {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
    @endpush
@endsection
