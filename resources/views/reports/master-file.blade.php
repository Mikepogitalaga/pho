@extends('layouts.app')

@section('title', 'Master Inventory File')
@section('pageHeading', 'Master Inventory File')
@section('pageSubheading', 'Government-standard inventory matrix with live stock, cost, purchase, and disposal balances.')

@section('content')
@php
    $availableTotal = array_sum(array_column($rows, 'available_qty'));
@endphp

{{-- Filters --}}
<section class="card" style="padding: 1.25rem; margin-bottom: 1.25rem;">
    <form method="GET" action="{{ route('reports.master-file') }}">
        <div class="section-header compact" style="padding: 0 0 1rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <div>
                <h2 class="section-card-title" style="margin: 0;">Filter Master File</h2>
                <p class="page-description" style="margin: 0.25rem 0 0;">Filter by category and month.</p>
            </div>
            @if($category !== '' || $month > 0)
                <a href="{{ route('reports.master-file') }}" class="btn btn-secondary">Clear Filters</a>
            @endif
        </div>
        <div class="form-grid-3">
            <div class="form-group">
                <label for="masterCategory">Category</label>
                <select id="masterCategory" name="category" class="search-input">
                    <option value="">All Categories</option>
                    <option value="MDL" {{ strcasecmp($category, 'MDL') === 0 ? 'selected' : '' }}>MDL</option>
                    <option value="Dm" {{ strcasecmp($category, 'Dm') === 0 ? 'selected' : '' }}>Dm</option>
                </select>
            </div>
            <div class="form-group">
                <label for="masterMonth">Month</label>
                <select id="masterMonth" name="month" class="search-input">
                    <option value="">All Months</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end;">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </div>
    </form>
</section>

{{-- KPI Summary --}}
<section class="card" style="padding: 1.25rem; margin-bottom: 1.25rem;">
    <div class="section-header compact" style="padding: 0 0 1rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border);">
        <div>
            <h2 class="section-card-title" style="margin: 0;">Inventory Summary</h2>
            <p class="page-description" style="margin: 0.25rem 0 0;">{{ count($rows) }} itemized records</p>
        </div>
        <a href="{{ route('reports.master-file.export', request()->query()) }}" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download .xlsx
        </a>
    </div>
    <div class="dashboard-content-grid" style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem;">
        @foreach([
            ['Beginning', $summary['beginning'], '#2563eb'],
            ['Purchases', $summary['purchases'], '#16a34a'],
            ['Available', $availableTotal, '#7c3aed'],
            ['Disposed', $summary['disposals'], '#d97706'],
            ['Expired', $summary['expired'], '#dc2626'],
            ['Ending', $summary['adjusted_ending'], '#0891b2'],
        ] as [$label, $value, $color])
            <div style="padding: 0.9rem 1rem; border-radius: 1rem; border: 1px solid var(--border); background: var(--surface); box-shadow: var(--shadow-sm);">
                <p style="margin:0 0 0.3rem; font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-muted);">{{ $label }}</p>
                <p style="margin:0; font-size:1.5rem; font-weight:800; color:{{ $color }};">{{ number_format($value) }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- Master Table --}}
<section class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 class="section-card-title" style="margin:0;">Master Inventory Matrix</h2>
            <p class="page-description" style="margin:0.2rem 0 0;">{{ count($rows) }} records &mdash; scroll horizontally to view all columns</p>
        </div>
    </div>

    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="master-table" style="min-width: 2400px; border-collapse: collapse; width: 100%;">
            <thead>
                {{-- Group header row --}}
                <tr>
                    <th colspan="3" style="background:#1e293b; color:#fff; text-align:center; border-right:2px solid #334155;">ITEM INFO</th>
                    <th colspan="2" style="background:#1d4ed8; color:#fff; text-align:center; border-right:2px solid #1e40af;">BEGINNING INVENTORY</th>
                    <th colspan="6" style="background:#15803d; color:#fff; text-align:center; border-right:2px solid #166534;">PURCHASES</th>
                    <th colspan="2" style="background:#7c3aed; color:#fff; text-align:center; border-right:2px solid #6d28d9;">AVAILABLE STOCKS</th>
                    <th colspan="5" style="background:#b45309; color:#fff; text-align:center; border-right:2px solid #92400e;">DISPOSAL <small style="font-weight:400; font-size:0.7em;">(Net of Released–RTS)</small></th>
                    <th colspan="1" style="background:#dc2626; color:#fff; text-align:center; border-right:2px solid #991b1b;">EXPIRED</th>
                    <th colspan="2" style="background:#0891b2; color:#fff; text-align:center; border-right:2px solid #0e7490;">ENDING INVENTORY</th>
                    <th colspan="4" style="background:#475569; color:#fff; text-align:center;">ADJUSTMENTS</th>
                </tr>
                {{-- Sub-header row --}}
                <tr style="background:#f8fafc;">
                    {{-- Item Info --}}
                    <th class="mth" style="min-width:220px; position:sticky; left:0; background:#f1f5f9; z-index:2; border-right:1px solid #e2e8f0;">ITEM</th>
                    <th class="mth" style="min-width:70px;">UNIT</th>
                    <th class="mth" style="min-width:90px; border-right:2px solid #cbd5e1;">UNIT COST</th>
                    {{-- Beginning --}}
                    <th class="mth" style="min-width:70px; background:#eff6ff;">QTY</th>
                    <th class="mth" style="min-width:110px; border-right:2px solid #bfdbfe; background:#eff6ff;">TOTAL COST</th>
                    {{-- Purchases --}}
                    <th class="mth" style="min-width:70px; background:#f0fdf4;">GSO QTY</th>
                    <th class="mth" style="min-width:90px; background:#f0fdf4;">GSO COST</th>
                    <th class="mth" style="min-width:70px; background:#f0fdf4;">ACP QTY</th>
                    <th class="mth" style="min-width:90px; background:#f0fdf4;">ACP COST</th>
                    <th class="mth" style="min-width:70px; background:#f0fdf4;">DOH QTY</th>
                    <th class="mth" style="min-width:90px; border-right:2px solid #bbf7d0; background:#f0fdf4;">DOH COST</th>
                    {{-- Available --}}
                    <th class="mth" style="min-width:70px; background:#faf5ff;">QTY</th>
                    <th class="mth" style="min-width:110px; border-right:2px solid #e9d5ff; background:#faf5ff;">TOTAL COST</th>
                    {{-- Disposal --}}
                    <th class="mth" style="min-width:100px; background:#fffbeb;">IMPLEMENTING</th>
                    <th class="mth" style="min-width:90px; background:#fffbeb;">HOSPITALS</th>
                    <th class="mth" style="min-width:70px; background:#fffbeb;">RHU</th>
                    <th class="mth" style="min-width:70px; background:#fffbeb;">NLA</th>
                    <th class="mth" style="min-width:110px; border-right:2px solid #fde68a; background:#fffbeb;">TOTAL COST</th>
                    {{-- Expired --}}
                    <th class="mth" style="min-width:110px; border-right:2px solid #fecaca; background:#fff1f2;">TOTAL COST</th>
                    {{-- Ending --}}
                    <th class="mth" style="min-width:70px; background:#ecfeff;">QTY</th>
                    <th class="mth" style="min-width:110px; border-right:2px solid #a5f3fc; background:#ecfeff;">TOTAL COST</th>
                    {{-- Adjustments --}}
                    <th class="mth" style="min-width:100px; background:#f8fafc;">AVG COST</th>
                    <th class="mth" style="min-width:90px; background:#f8fafc;">CHECK BAL</th>
                    <th class="mth" style="min-width:90px; background:#f8fafc;">ADJ QTY</th>
                    <th class="mth" style="min-width:110px; background:#f8fafc;">ADJ AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $i => $row)
                    <tr style="{{ $i % 2 === 0 ? 'background:#ffffff;' : 'background:#f8fafc;' }}">
                        <td class="mtd" style="font-weight:600; position:sticky; left:0; background:inherit; z-index:1; border-right:1px solid #e2e8f0; max-width:220px; white-space:normal; word-break:break-word;">{{ $row['name'] }}</td>
                        <td class="mtd mtd-center">{{ $row['unit'] ?: '—' }}</td>
                        <td class="mtd mtd-right" style="border-right:2px solid #e2e8f0;">{{ number_format($row['unit_cost'], 2) }}</td>

                        <td class="mtd mtd-right" style="background:{{ $i%2===0?'#eff6ff':'#e8f0fe'; }}">{{ number_format($row['beginning_qty']) }}</td>
                        <td class="mtd mtd-right" style="border-right:2px solid #bfdbfe; background:{{ $i%2===0?'#eff6ff':'#e8f0fe'; }}">{{ number_format($row['beginning_cost'], 2) }}</td>

                        <td class="mtd mtd-right" style="background:{{ $i%2===0?'#f0fdf4':'#e8f5e9'; }}">{{ number_format($row['purchase_gso_qty']) }}</td>
                        <td class="mtd mtd-right" style="background:{{ $i%2===0?'#f0fdf4':'#e8f5e9'; }}">{{ number_format($row['purchase_gso_cost'], 2) }}</td>
                        <td class="mtd mtd-right" style="background:{{ $i%2===0?'#f0fdf4':'#e8f5e9'; }}">{{ number_format($row['purchase_acp_qty']) }}</td>
                        <td class="mtd mtd-right" style="background:{{ $i%2===0?'#f0fdf4':'#e8f5e9'; }}">{{ number_format($row['purchase_acp_cost'], 2) }}</td>
                        <td class="mtd mtd-right" style="background:{{ $i%2===0?'#f0fdf4':'#e8f5e9'; }}">{{ number_format($row['purchase_doh_qty']) }}</td>
                        <td class="mtd mtd-right" style="border-right:2px solid #bbf7d0; background:{{ $i%2===0?'#f0fdf4':'#e8f5e9'; }}">{{ number_format($row['purchase_doh_cost'], 2) }}</td>

                        <td class="mtd mtd-right" style="background:{{ $i%2===0?'#faf5ff':'#f3e8ff'; }}">{{ number_format($row['available_qty']) }}</td>
                        <td class="mtd mtd-right" style="border-right:2px solid #e9d5ff; background:{{ $i%2===0?'#faf5ff':'#f3e8ff'; }}">{{ number_format($row['available_cost'], 2) }}</td>

                        <td class="mtd mtd-right" style="background:{{ $i%2===0?'#fffbeb':'#fef3c7'; }}">{{ number_format($row['disposal_implementing']) }}</td>
                        <td class="mtd mtd-right" style="background:{{ $i%2===0?'#fffbeb':'#fef3c7'; }}">{{ number_format($row['disposal_hospitals']) }}</td>
                        <td class="mtd mtd-right" style="background:{{ $i%2===0?'#fffbeb':'#fef3c7'; }}">{{ number_format($row['disposal_rhu']) }}</td>
                        <td class="mtd mtd-right" style="background:{{ $i%2===0?'#fffbeb':'#fef3c7'; }}">{{ number_format($row['disposal_nla']) }}</td>
                        <td class="mtd mtd-right" style="border-right:2px solid #fde68a; background:{{ $i%2===0?'#fffbeb':'#fef3c7'; }}">{{ number_format($row['disposal_cost'], 2) }}</td>

                        <td class="mtd mtd-right" style="border-right:2px solid #fecaca; background:{{ $i%2===0?'#fff1f2':'#ffe4e6'; }}; color:#dc2626; font-weight:600;">{{ number_format($row['expired_cost'], 2) }}</td>

                        <td class="mtd mtd-right" style="background:{{ $i%2===0?'#ecfeff':'#cffafe'; }}; font-weight:700;">{{ number_format($row['ending_qty']) }}</td>
                        <td class="mtd mtd-right" style="border-right:2px solid #a5f3fc; background:{{ $i%2===0?'#ecfeff':'#cffafe'; }}">{{ number_format($row['ending_cost'], 2) }}</td>

                        <td class="mtd mtd-right">{{ number_format($row['average_cost'], 2) }}</td>
                        <td class="mtd mtd-right" style="color:{{ $row['check_balance'] != 0 ? '#dc2626' : 'inherit' }}; font-weight:{{ $row['check_balance'] != 0 ? '700' : '400' }};">{{ number_format($row['check_balance']) }}</td>
                        <td class="mtd mtd-right">{{ number_format($row['adjusted_ending']) }}</td>
                        <td class="mtd mtd-right" style="font-weight:700;">{{ number_format($row['adjusted_amount'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="26" style="text-align:center; padding:3rem 1rem; color:var(--text-muted);">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.4" style="margin:0 auto 0.75rem; display:block;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                            No inventory items found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background:#1e293b; color:#fff; font-weight:700;">
                    <td class="mtd" style="position:sticky; left:0; background:#1e293b; z-index:1; border-right:1px solid #334155; font-weight:800; letter-spacing:0.05em;">TOTAL</td>
                    <td class="mtd"></td>
                    <td class="mtd" style="border-right:2px solid #334155;"></td>

                    <td class="mtd mtd-right" style="background:#1d4ed8;">{{ number_format($summary['beginning']) }}</td>
                    <td class="mtd mtd-right" style="border-right:2px solid #1e40af; background:#1d4ed8;">{{ number_format(array_sum(array_column($rows, 'beginning_cost')), 2) }}</td>

                    <td class="mtd mtd-right" style="background:#15803d;">{{ number_format(array_sum(array_column($rows, 'purchase_gso_qty'))) }}</td>
                    <td class="mtd mtd-right" style="background:#15803d;"></td>
                    <td class="mtd mtd-right" style="background:#15803d;">{{ number_format(array_sum(array_column($rows, 'purchase_acp_qty'))) }}</td>
                    <td class="mtd mtd-right" style="background:#15803d;"></td>
                    <td class="mtd mtd-right" style="background:#15803d;">{{ number_format(array_sum(array_column($rows, 'purchase_doh_qty'))) }}</td>
                    <td class="mtd mtd-right" style="border-right:2px solid #166534; background:#15803d;"></td>

                    <td class="mtd mtd-right" style="background:#7c3aed;">{{ number_format($availableTotal) }}</td>
                    <td class="mtd mtd-right" style="border-right:2px solid #6d28d9; background:#7c3aed;">{{ number_format(array_sum(array_column($rows, 'available_cost')), 2) }}</td>

                    <td class="mtd mtd-right" style="background:#b45309;">{{ number_format(array_sum(array_column($rows, 'disposal_implementing'))) }}</td>
                    <td class="mtd mtd-right" style="background:#b45309;">{{ number_format(array_sum(array_column($rows, 'disposal_hospitals'))) }}</td>
                    <td class="mtd mtd-right" style="background:#b45309;">{{ number_format(array_sum(array_column($rows, 'disposal_rhu'))) }}</td>
                    <td class="mtd mtd-right" style="background:#b45309;">{{ number_format(array_sum(array_column($rows, 'disposal_nla'))) }}</td>
                    <td class="mtd mtd-right" style="border-right:2px solid #92400e; background:#b45309;">{{ number_format(array_sum(array_column($rows, 'disposal_cost')), 2) }}</td>

                    <td class="mtd mtd-right" style="border-right:2px solid #991b1b; background:#dc2626;">{{ number_format(array_sum(array_column($rows, 'expired_cost')), 2) }}</td>

                    <td class="mtd mtd-right" style="background:#0891b2;">{{ number_format($summary['ending']) }}</td>
                    <td class="mtd mtd-right" style="border-right:2px solid #0e7490; background:#0891b2;">{{ number_format(array_sum(array_column($rows, 'ending_cost')), 2) }}</td>

                    <td class="mtd mtd-right">{{ number_format($summary['average_cost'], 2) }}</td>
                    <td class="mtd mtd-right">{{ number_format($summary['check_balance']) }}</td>
                    <td class="mtd mtd-right">{{ number_format($summary['adjusted_ending']) }}</td>
                    <td class="mtd mtd-right">{{ number_format(array_sum(array_column($rows, 'adjusted_amount')), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</section>
@endsection

@push('styles')
<style>
    .master-table { font-size: 0.8rem; }
    .mth {
        padding: 0.55rem 0.65rem;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        white-space: nowrap;
        border-bottom: 2px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .master-table thead tr:first-child th {
        padding: 0.6rem 0.65rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    .mtd {
        padding: 0.55rem 0.65rem;
        border-bottom: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        white-space: nowrap;
        vertical-align: middle;
    }
    .mtd-right { text-align: right; }
    .mtd-center { text-align: center; }
    .master-table tbody tr:hover td { filter: brightness(0.96); }
    .master-table tfoot td { padding: 0.65rem 0.65rem; border-right: 1px solid rgba(255,255,255,0.15); white-space: nowrap; }

    html[data-theme='dark'] .master-table .mth { border-color: #334155; background: #1e293b !important; color: #94a3b8; }
    html[data-theme='dark'] .master-table tbody tr { background: #0f172a !important; }
    html[data-theme='dark'] .master-table tbody tr:nth-child(even) { background: #1e293b !important; }
    html[data-theme='dark'] .master-table .mtd { border-color: #334155; color: #e2e8f0; background: inherit !important; }
    html[data-theme='dark'] .master-table tbody td[style*="sticky"] { background: inherit !important; }
</style>
@endpush
