@extends('layouts.app')

@section('title', 'Item Details')
@section('pageHeading', 'Item Details')
@section('pageSubheading', 'View inventory item information. Stock is updated through Receivings and Releases only.')

@section('content')
    {{-- Supplier KPIs at top --}}
    <div style="display: flex; gap: 1rem; align-items: flex-start; flex-wrap: wrap; margin-bottom: 1.5rem;">
        <section style="flex: 2; min-width: 300px; display: flex; gap: 1rem; flex-wrap: wrap;" aria-label="Supplier statistics">
            @foreach(['DOH' => 'kpi-card--blue', 'GSO' => 'kpi-card--teal'] as $supplierType => $cardClass)
                <article class="kpi-card {{ $cardClass }}" style="flex: 1; min-width: 180px; padding: 1.5rem;">
                    <div class="kpi-card-header">
                        <span class="kpi-card-label" style="font-size: 1rem; font-weight: 600;">{{ $supplierType }} Supplier</span>
                    </div>
                    <p class="kpi-card-value" style="font-size: 2.5rem; font-weight: 700; margin: 0.5rem 0 0.25rem;">{{ number_format($supplierStats[$supplierType]->item_count) }}</p>
                    <p class="kpi-card-foot" style="font-size: 0.95rem;">{{ number_format($supplierStats[$supplierType]->units_received) }} units received</p>
                </article>
            @endforeach
        </section>
    </div>

    {{-- Stats Row --}}
    <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
        <article class="kpi-card" style="flex:1; min-width:160px; padding:1.25rem 1.5rem;">
            <div class="kpi-card-header">
                <span class="kpi-card-label" style="font-size:0.85rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Total Released</span>
            </div>
            <p class="kpi-card-value" style="font-size:2rem; font-weight:700; margin:0.4rem 0 0.2rem; color:var(--danger);">{{ number_format($totalReleased) }}</p>
            <p class="kpi-card-foot" style="font-size:0.85rem;">units released (active)</p>
        </article>
        <article class="kpi-card" style="flex:1; min-width:160px; padding:1.25rem 1.5rem;">
            <div class="kpi-card-header">
                <span class="kpi-card-label" style="font-size:0.85rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Current Stock</span>
            </div>
            <p class="kpi-card-value" style="font-size:2rem; font-weight:700; margin:0.4rem 0 0.2rem; color:{{ $totalStock > 0 ? 'var(--success, #16a34a)' : 'var(--danger)' }};">{{ number_format($totalStock) }}</p>
            <p class="kpi-card-foot" style="font-size:0.85rem;">units on hand</p>
        </article>
        <article class="kpi-card" style="flex:1; min-width:160px; padding:1.25rem 1.5rem;">
            <div class="kpi-card-header">
                <span class="kpi-card-label" style="font-size:0.85rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Deduction %</span>
            </div>
            <p class="kpi-card-value" style="font-size:2rem; font-weight:700; margin:0.4rem 0 0.2rem; color:{{ $deductionPercentage >= 80 ? 'var(--danger)' : ($deductionPercentage >= 50 ? 'var(--warning, #d97706)' : 'var(--success, #16a34a)') }};">{{ $deductionPercentage }}%</p>
            <p class="kpi-card-foot" style="font-size:0.85rem;">of total received</p>
        </article>
    </div>

    {{-- Item Description Banner --}}
    <div style="background: linear-gradient(135deg, var(--primary), #1d4ed8); color: #fff; padding: 1.25rem 1.5rem; border-radius: 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="margin: 0; font-size: 1.6rem; font-weight: 800; letter-spacing: -0.02em;">{{ $item->name }}</h1>
            <p style="margin: 0.25rem 0 0; opacity: 0.85; font-size: 0.95rem;">{{ $items->count() }} product code record(s) &middot; {{ $item->category ?? 'Uncategorized' }}</p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <span style="background: rgba(255,255,255,0.2); padding: 0.35rem 0.9rem; border-radius: 999px; font-size: 0.85rem; font-weight: 600;">{{ $item->display_unit }}</span>
            <span class="status-pill" style="background: rgba(255,255,255,0.2); color: #fff; border: none; font-size: 0.85rem; font-weight: 600;">{{ $item->status }}</span>
        </div>
    </div>

    <div class="section-card" style="margin-top: 0;">
        <div class="section-header">
            <div>
                <h2 class="section-card-title" style="margin: 0;">Product Codes</h2>
                <p class="page-description" style="margin-top: 0.25rem;">All product codes for {{ $item->name }}. View and manage deductions per product code.</p>
            </div>
            <div style="display:flex; gap:0.5rem;">
                <a href="{{ route('items.index') }}" class="btn btn-secondary">Back to Items</a>
            </div>
        </div>

        {{-- Search/Filter --}}
        <div style="display:flex; gap:0.75rem; padding:0.75rem 0; margin-bottom:0.75rem; align-items:end; flex-wrap:wrap;">
            <div style="display:flex; flex-direction:column; gap:0.3rem; flex:1; min-width:200px;">
                <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Search Product Code</label>
                <input id="productCodeSearch" type="text" class="search-input" placeholder="Type to filter product codes..." />
            </div>
            <div style="display:flex; flex-direction:column; gap:0.3rem; min-width:160px;">
                <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Location</label>
                <select id="locationFilter" class="search-input">
                    <option value="">All locations</option>
                    @foreach($items->pluck('location')->unique()->filter()->sort() as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; flex-direction:column; gap:0.3rem; min-width:140px;">
                <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Status</label>
                <select id="statusFilter" class="search-input">
                    <option value="">All statuses</option>
                    <option value="Available">Available</option>
                    <option value="Low Stock">Low Stock</option>
                    <option value="Out of Stock">Out of Stock</option>
                </select>
            </div>
            <button id="clearProductCodeFilters" type="button" class="btn btn-ghost" style="padding:0.5rem 0.8rem;" title="Clear filters">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Product Code</th>
                        <th>Location</th>
                        <th>Current Stock</th>
                        <th>Unit Cost</th>
                        <th>Program (SKU)</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Expiry</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody id="productCodesTableBody">
                    @forelse($items as $groupedItem)
                        <tr class="product-code-row"
                            data-productcode="{{ $groupedItem->item_code }}"
                            data-location="{{ $groupedItem->location ?? '' }}"
                            data-status="{{ $groupedItem->status }}">
                            <td><span style="font-weight:600;">{{ $groupedItem->item_code }}</span></td>
                            <td style="color:var(--text-muted);">{{ $groupedItem->location ?? '—' }}</td>
                            <td>
                                <span style="font-weight:700; color:{{ $groupedItem->quantity_on_hand > 0 ? 'var(--text)' : 'var(--danger)' }};">
                                    {{ number_format($groupedItem->quantity_on_hand) }}
                                </span>
                            </td>
                            <td>{{ $groupedItem->unit_cost ? number_format($groupedItem->unit_cost, 2) : '0.00' }}</td>
                            <td style="color:var(--text-muted); font-size:0.9rem;">{{ $groupedItem->stock_keeping_unit ?? '—' }}</td>
                            <td>
                                @php
                                    $stypes = $groupedItem->receivingItems->map(fn($ri) => $ri->receiving?->supplier?->supplier_type)->filter()->unique()->sort()->values();
                                    $hasDoh = $stypes->contains('DOH');
                                    $hasGso = $stypes->contains('GSO');
                                @endphp
                                @if($hasDoh && $hasGso)
                                    <span style="padding:0.2rem 0.55rem; border-radius:999px; font-size:0.75rem; font-weight:700; background:linear-gradient(90deg,rgba(37,99,235,0.15),rgba(8,145,178,0.15)); color:var(--primary); border:1px solid rgba(37,99,235,0.2);">DOH-GSO</span>
                                @elseif($hasDoh)
                                    <span style="padding:0.2rem 0.55rem; border-radius:999px; font-size:0.75rem; font-weight:700; background:rgba(37,99,235,0.12); color:var(--primary);">DOH</span>
                                @elseif($hasGso)
                                    <span style="padding:0.2rem 0.55rem; border-radius:999px; font-size:0.75rem; font-weight:700; background:rgba(8,145,178,0.12); color:#0891b2;">GSO</span>
                                @else
                                    <span style="color:var(--text-muted); font-size:0.85rem;">—</span>
                                @endif
                            </td>
                            <td><span class="status-pill {{ $groupedItem->status_class }}">{{ $groupedItem->status }}</span></td>
                            <td><span class="status-pill {{ $groupedItem->expiry_badge_class }}" style="font-size:0.78rem;">{{ $groupedItem->expiry_label }}</span></td>
                            <td style="text-align:center;">
                                <a href="{{ route('items.productcode.show', ['item' => $item, 'productCode' => $groupedItem->item_code]) }}" class="btn btn-secondary" style="min-height:auto; padding:0.35rem 0.85rem; font-size:0.82rem; gap:0.3rem;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding:2.5rem 1.25rem; text-align:center;">
                                <div style="display:flex; flex-direction:column; align-items:center; gap:0.75rem; color:var(--text-muted);">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.4"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                                    <div style="text-align:center;">
                                        <strong style="display:block; color:var(--text); font-size:1rem;">No records found</strong>
                                        <span style="font-size:0.88rem;">No product codes available for this item.</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (function() {
            const searchInput = document.getElementById('productCodeSearch');
            const locationFilter = document.getElementById('locationFilter');
            const statusFilter = document.getElementById('statusFilter');
            const clearBtn = document.getElementById('clearProductCodeFilters');
            const rows = document.querySelectorAll('.product-code-row');

            function filterRows() {
                const q = searchInput.value.toLowerCase().trim();
                const loc = locationFilter.value;
                const st = statusFilter.value;

                rows.forEach(row => {
                    const code = row.dataset.productcode.toLowerCase();
                    const location = row.dataset.location.toLowerCase();
                    const status = row.dataset.status;

                    const matchSearch = !q || code.includes(q);
                    const matchLocation = !loc || location === loc.toLowerCase();
                    const matchStatus = !st || status === st;

                    row.style.display = (matchSearch && matchLocation && matchStatus) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterRows);
            locationFilter.addEventListener('change', filterRows);
            statusFilter.addEventListener('change', filterRows);

            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                locationFilter.value = '';
                statusFilter.value = '';
                filterRows();
                searchInput.focus();
            });
        })();
    </script>
@endsection
