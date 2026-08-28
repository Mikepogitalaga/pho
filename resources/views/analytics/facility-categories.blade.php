@extends('layouts.app')

@section('title', 'Facility Category Release Analytics')
@section('pageHeading', 'Facility Category Release Analytics')
@section('pageSubheading', 'Top 10 Facilities by Release Volume — DM vs MDL')

@section('content')
    {{-- Filters --}}
    <section class="section-card" style="margin-bottom: 1.5rem;">
        <form method="GET" action="{{ route('analytics.facility-categories') }}" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:end;">
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
            <a href="{{ route('analytics.facility-categories') }}" class="btn btn-ghost">Reset</a>
        </form>
    </section>

    {{-- Charts Grid --}}
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:1.5rem;">
        @foreach($categories as $category)
            @if($activeCategory === 'All' || $activeCategory === $category)
                <div class="section-card">
                    <h3 style="margin:0 0 0.25rem;font-size:1.05rem;font-weight:700;color:var(--text);">{{ $category }} — Top 10 Most Released Items</h3>
                    <p style="margin:0 0 1rem;font-size:0.82rem;color:var(--text-muted);">DM vs MDL Released Quantity</p>

                    @php
                        $facilityData = $chartData[$category];
                        $hasData = count($facilityData) > 0;
                        $labels = [];
                        $dmData = [];
                        $mdlData = [];
                        $catTotalDm = 0;
                        $catTotalMdl = 0;
                        $catTotalTransactions = 0;
                        foreach ($facilityData as $item) {
                            $labels[] = $item['facility_name'];
                            $dmData[] = (int) $item['dm_released'];
                            $mdlData[] = (int) $item['mdl_released'];
                            $catTotalDm += (int) $item['dm_released'];
                            $catTotalMdl += (int) $item['mdl_released'];
                            $catTotalTransactions += (int) $item['transaction_count'];
                        }
                        $catTotalReleased = $catTotalDm + $catTotalMdl;
                    @endphp

                    @if($hasData)
                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;margin-bottom:1rem;">
                            <div style="background:rgba(37,99,235,0.08);border-radius:8px;padding:0.6rem;text-align:center;">
                                <div style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.03em;">Total DM</div>
                                <div style="font-size:1.1rem;font-weight:700;color:#2563eb;">{{ number_format($catTotalDm) }}</div>
                            </div>
                            <div style="background:rgba(13,148,136,0.08);border-radius:8px;padding:0.6rem;text-align:center;">
                                <div style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.03em;">Total MDL</div>
                                <div style="font-size:1.1rem;font-weight:700;color:#0d9488;">{{ number_format($catTotalMdl) }}</div>
                            </div>
                            <div style="background:rgba(0,0,0,0.04);border-radius:8px;padding:0.6rem;text-align:center;">
                                <div style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.03em;">Total Released</div>
                                <div style="font-size:1.1rem;font-weight:700;color:var(--text);">{{ number_format($catTotalReleased) }}</div>
                            </div>
                            <div style="background:rgba(0,0,0,0.04);border-radius:8px;padding:0.6rem;text-align:center;">
                                <div style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.03em;">Transactions</div>
                                <div style="font-size:1.1rem;font-weight:700;color:var(--text);">{{ number_format($catTotalTransactions) }}</div>
                            </div>
                        </div>

                        <div style="position:relative;height:{{ max(300, count($facilityData) * 42) }}px;">
                            <canvas id="chart-{{ $loop->index }}"></canvas>
                        </div>

                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:0.75rem;font-size:0.78rem;color:var(--text-muted);">
                            <div style="display:flex;gap:1rem;">
                                <span style="display:flex;align-items:center;gap:0.35rem;"><span style="width:12px;height:12px;border-radius:3px;background:#2563eb;display:inline-block;"></span> DM</span>
                                <span style="display:flex;align-items:center;gap:0.35rem;"><span style="width:12px;height:12px;border-radius:3px;background:#0d9488;display:inline-block;"></span> MDL</span>
                            </div>
                            <a href="{{ route('analytics.facility-categories.view', array_merge(request()->only(['start_date', 'end_date']), ['facility_category' => $category])) }}" class="btn btn-secondary" style="font-size:0.75rem;padding:0.25rem 0.6rem;">View More →</a>
                        </div>
                    @else
                        <div class="empty-state" style="padding:2rem 1rem;">
                            <strong>No release data found</strong>
                            <div>No released items recorded for this category in the selected period.</div>
                        </div>
                    @endif
                </div>
            @endif
        @endforeach
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($categories as $category)
                @if($activeCategory === 'All' || $activeCategory === $category)
                    @if(count($chartData[$category]) > 0)
                        (function() {
                            const ctx = document.getElementById('chart-{{ $loop->index }}').getContext('2d');
                            const data = @json($chartData[$category]);

                            const labels = data.map(d => d.facility_name);
                            const dmData = data.map(d => parseInt(d.dm_released) || 0);
                            const mdlData = data.map(d => parseInt(d.mdl_released) || 0);

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
                                                    const item = data[idx];
                                                    let lines = ['Total Released: ' + total.toLocaleString()];
                                                    if (item.items_count) lines.push('Items Released: ' + item.items_count);
                                                    if (item.transaction_count) lines.push('Release Transactions: ' + item.transaction_count);
                                                    if (item.top_items && item.top_items.length > 0) {
                                                        lines.push('', 'Top Items:');
                                                        item.top_items.forEach(ti => lines.push('  • ' + ti));
                                                    }
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
                                            ticks: { font: { size: 11 } },
                                            grid: { display: false }
                                        }
                                    },
                                    onClick: function(event, elements) {
                                        if (elements.length > 0) {
                                            const idx = elements[0].index;
                                            const item = data[idx];
                                            const category = encodeURIComponent('{{ $category }}');
                                            const params = new URLSearchParams();
                                            @if($startDate) params.set('start_date', '{{ $startDate }}'); @endif
                                            @if($endDate) params.set('end_date', '{{ $endDate }}'); @endif
                                            window.location.href = '{{ url('analytics/facility-categories') }}/' + category + '/' + encodeURIComponent(item.facility_name) + '?' + params.toString();
                                        }
                                    }
                                }
                            });
                        })();
                    @endif
                @endif
            @endforeach
        });
    </script>
    @endpush

    @push('styles')
    <style>
        @media (max-width: 1024px) {
            .section-card:nth-last-child(n+2) { margin-bottom: 0; }
        }
        @media (max-width: 768px) {
            div[style*="grid-template-columns:repeat(2"] {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
    @endpush
@endsection
