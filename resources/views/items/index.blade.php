@extends('layouts.app')

@section('title', 'Items')
@section('pageHeading', 'Items')
@section('pageSubheading', 'Master inventory list. Stock is managed through Receiving and Release only.')

@section('content')

    {{-- Page Header --}}
    <div style="display:flex; justify-content:flex-end; align-items:center; gap:0.6rem; flex-wrap:wrap; margin-bottom:1.25rem;">
        <button type="button" class="btn btn-secondary" onclick="window.print()" style="gap: 0.4rem;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
            <rect x="6" y="14" width="12" height="8"/>
        </svg>
        Print
    </button>

    <a href="{{ route('items.export', request()->query()) }}" class="btn btn-secondary" style="gap: 0.4rem;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Export
    </a>
</div>

    {{-- KPI Cards --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; margin-bottom:1.25rem;">
        <article class="kpi-card kpi-card--blue" style="padding:1.1rem 1.25rem;">
            <div class="kpi-card-header" style="margin-bottom:0.5rem;">
                <div class="kpi-card-icon" style="background:rgba(37,99,235,0.14); color:var(--primary);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <span class="kpi-card-label" style="font-weight:600;">DOH Items</span>
            </div>
            <p class="kpi-card-value" style="font-size:2rem;">{{ number_format($supplierStats['DOH']->item_count) }}</p>
            <p class="kpi-card-foot">{{ number_format($supplierStats['DOH']->units_received) }} units received</p>
        </article>

        <article class="kpi-card kpi-card--teal" style="padding:1.1rem 1.25rem;">
            <div class="kpi-card-header" style="margin-bottom:0.5rem;">
                <div class="kpi-card-icon" style="background:rgba(8,145,178,0.14); color:#0891b2;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                </div>
                <span class="kpi-card-label" style="font-weight:600;">GSO Items</span>
            </div>
            <p class="kpi-card-value" style="font-size:2rem;">{{ number_format($supplierStats['GSO']->item_count) }}</p>
            <p class="kpi-card-foot">{{ number_format($supplierStats['GSO']->units_received) }} units received</p>
        </article>

        <article class="kpi-card kpi-card--green" style="padding:1.1rem 1.25rem;">
            <div class="kpi-card-header" style="margin-bottom:0.5rem;">
                <div class="kpi-card-icon" style="background:rgba(22,163,74,0.14); color:var(--success);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </div>
                <span class="kpi-card-label" style="font-weight:600;">Total Items</span>
            </div>
            <p class="kpi-card-value" style="font-size:2rem;">{{ number_format($items->total()) }}</p>
            <p class="kpi-card-foot">Unique item descriptions</p>
        </article>
    </div>

    {{-- Search & Filter Panel --}}
    <form method="GET" class="filter-panel">
        <div style="display:flex; flex-direction:column; gap:0.3rem;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Search</label>
            <div style="position:relative;">
                <svg style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--text-muted); pointer-events:none;" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search items..." class="search-input" style="padding-left:2.25rem;" />
            </div>
        </div>
        <div style="display:flex; flex-direction:column; gap:0.3rem;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Category</label>
            <select name="category" class="search-input">
                <option value="">All categories</option>
                @foreach($categories as $categoryOption)
                    <option value="{{ $categoryOption }}" @selected($categoryOption === $category)>{{ $categoryOption }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; flex-direction:column; gap:0.3rem;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Status</label>
            <select name="status" class="search-input">
                <option value="">All statuses</option>
                <option value="available" @selected($status === 'available')>Available</option>
                <option value="low" @selected($status === 'low')>Low Stock</option>
                <option value="out" @selected($status === 'out')>Out of Stock</option>
            </select>
        </div>
        <div style="display:flex; flex-direction:column; gap:0.3rem;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Program</label>
            <select name="program" class="search-input">
                <option value="">All programs</option>
                @foreach($programs as $programOption)
                    <option value="{{ $programOption->name }}" @selected($programOption->name === $program)>{{ $programOption->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <button type="submit" class="btn btn-primary" style="gap:0.4rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Filter
            </button>
            @if($search || $status || $category || $program)
                <a href="{{ route('items.index') }}" class="btn btn-ghost" title="Clear filters">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <section class="section-card" style="padding:0;">
        {{-- Table Header Bar --}}
        <div style="display:flex; justify-content:space-between; align-items:center; padding:0.9rem 1.1rem; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:0.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span style="font-weight:700; font-size:0.95rem;">Inventory List</span>
                <span style="background:rgba(37,99,235,0.1); color:var(--primary); font-size:0.78rem; font-weight:700; padding:0.15rem 0.55rem; border-radius:999px;">{{ $items->total() }} items</span>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left:1.1rem;">Item Description</th>
                        <th>Category</th>
                        <th class="col-hide-md">UOM</th>
                        <th>Current Stock</th>
                        <th>Location</th>
                        <th>Supplier</th>
                        <th class="col-hide-md">Program</th>
                        <th class="col-hide-md">Records</th>
                        <th>Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                     @forelse ($items as $item)
                         <tr style="transition:background 150ms;">
                              <td class="mobile-card-header" style="padding-left:1.1rem;">
                                  <span>#{{ $item->id }} &mdash; {{ $item->name }}</span>
                              </td>
                              <td data-label="Category">
                                  @if($item->category)
                                      <span style="background:rgba(124,58,237,0.1); color:var(--accent); font-size:0.78rem; font-weight:600; padding:0.2rem 0.55rem; border-radius:999px;">{{ $item->category }}</span>
                                  @else
                                      <span style="color:var(--text-muted);">—</span>
                                  @endif
                              </td>
                               <td data-label="UOM" class="col-hide-md" style="color:var(--text-muted); font-size:0.9rem;">{{ $item->display_unit }}</td>
                              <td data-label="Stock">
                                  <span style="font-weight:700; font-size:1rem; color:{{ $item->quantity_on_hand > 0 ? 'var(--text)' : 'var(--danger)' }};">
                                      {{ number_format($item->quantity_on_hand) }}
                                  </span>
                              </td>
                              <td data-label="Location" style="color:var(--text-muted); font-size:0.9rem;">
                                  @if($item->location)
                                      <span style="display:inline-flex; align-items:center; gap:0.3rem;">
                                          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                          {{ $item->location }}
                                      </span>
                                  @else
                                      —
                                  @endif
                              </td>
                              <td data-label="Supplier">
                                  @php
                                      $stypes = $item->supplier_types ? array_map('trim', explode(',', $item->supplier_types)) : [];
                                      $hasDoh = in_array('DOH', $stypes);
                                      $hasGso = in_array('GSO', $stypes);
                                  @endphp
                                  @if($hasDoh && $hasGso)
                                      <span style="padding:0.2rem 0.55rem; border-radius:999px; font-size:0.75rem; font-weight:700; background:linear-gradient(90deg,rgba(37,99,235,0.15),rgba(8,145,178,0.15)); color:var(--primary); border:1px solid rgba(37,99,235,0.2);">DOH-GSO</span>
                                  @elseif($hasDoh)
                                      <span style="padding:0.2rem 0.55rem; border-radius:999px; font-size:0.75rem; font-weight:700; background:rgba(37,99,235,0.12); color:var(--primary);">DOH</span>
                                  @elseif($hasGso)
                                      <span style="padding:0.2rem 0.55rem; border-radius:999px; font-size:0.75rem; font-weight:700; background:rgba(8,145,178,0.12); color:#0891b2;">GSO</span>
                                  @else
                                      <span style="color:var(--text-muted);">—</span>
                                  @endif
                              </td>
                              <td data-label="Program" class="col-hide-md" style="color:var(--text-muted); font-size:0.9rem;">{{ $item->stock_keeping_unit ?? '—' }}</td>
                              <td data-label="Records" class="col-hide-md">
                                  <span style="background:rgba(37,99,235,0.1); color:var(--primary); font-size:0.8rem; font-weight:700; padding:0.2rem 0.6rem; border-radius:999px;">
                                      {{ $item->record_count }}
                                  </span>
                              </td>
                              <td data-label="Status">
                                  <span class="status-pill {{ $item->status_class }}">{{ $item->status }}</span>
                              </td>
                             <td class="mobile-card-actions" style="text-align:center;">
                                 <a href="{{ route('items.show', $item) }}" class="btn btn-secondary" style="min-height:auto; padding:0.35rem 0.85rem; font-size:0.82rem; gap:0.3rem;">
                                     <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                     View
                                 </a>
                             </td>
                         </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="padding:2.5rem 1.25rem;">
                                <div style="display:flex; flex-direction:column; align-items:center; gap:0.75rem; color:var(--text-muted);">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.4"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                                    <div style="text-align:center;">
                                        <strong style="display:block; color:var(--text); font-size:1rem;">No items found</strong>
                                        <span style="font-size:0.88rem;">Try adjusting your search or filters.</span>
                                    </div>
            @if($search || $status || $category || $program)
                                        <a href="{{ route('items.index') }}" class="btn btn-secondary" style="min-height:auto; padding:0.4rem 1rem; font-size:0.85rem;">Clear filters</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <x-pagination.modern :paginator="$items" />

@endsection

@push('styles')
<style>
    .filter-panel {
        display: grid;
        grid-template-columns: 1.6fr 1fr 1fr 1fr auto;
        gap: 0.75rem;
        padding: 1rem 1.1rem;
        border: 1px solid var(--border);
        border-radius: 1rem;
        background: var(--surface);
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.25rem;
        align-items: end;
    }

    @media (max-width: 1024px) {
        .filter-panel {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .filter-panel {
            grid-template-columns: 1fr;
        }

        .filter-panel > div:last-child {
            flex-direction: row;
        }
    }
</style>
@endpush
