@extends('layouts.app')

@section('title', 'Property Allocation Slips')
@section('pageHeading', 'Property Allocation Slips')
@section('pageSubheading', 'Manage PAS records — items are not deducted from inventory.')

@section('content')
<section class="card pas-card">
    <div class="section-header">
        <div>
            <h2 class="section-title" style="margin:0;">Property Allocation Slips</h2>
            <p class="page-description">PAS items are tracked separately and do not affect inventory stock.</p>
        </div>
        <a href="{{ route('pas.create') }}" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New PAS
        </a>
    </div>

    <form method="GET" action="{{ route('pas.index') }}" class="pas-filter-bar">
        <div class="pas-search-wrap">
            <svg class="pas-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search PAS number, coordinator, program…" class="pas-input">
        </div>
        <select name="status" class="pas-select">
            <option value="">All Statuses</option>
            <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
            <option value="Released" {{ request('status') === 'Released' ? 'selected' : '' }}>Released</option>
            <option value="Canceled" {{ request('status') === 'Canceled' ? 'selected' : '' }}>Canceled</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request('search') || request('status'))
            <a href="{{ route('pas.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="table-wrapper pas-table-wrap">
        <table class="data-table pas-table">
            <thead>
                <tr>
                    <th>PAS Number</th>
                    <th class="col-hide-md">Date of PASS</th>
                    <th>Date Released</th>
                    <th class="col-hide-md">Supplier</th>
                    <th>Facility / Coordinator</th>
                    <th class="col-hide-md">Program</th>
                    <th>Purpose / Activity</th>
                    <th>Status</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slips as $slip)
                    @php
                        $linkedRelease = $slip->release;
                        $displayStatus = $linkedRelease ? $linkedRelease->status : $slip->status;
                        $badgeClass = match($displayStatus) {
                            'Released', 'Released through pass' => 'badge-success',
                            'Canceled' => 'badge-danger',
                            'Returned' => 'badge-warning',
                            default    => 'badge-secondary',
                        };
                    @endphp
                     <tr>
                         <td class="mobile-card-header">
                             <strong class="pas-name">{{ $slip->pas_number }}</strong>
                         </td>
                         <td data-label="Date of PASS" class="col-hide-md pas-muted">{{ $slip->date_of_pass?->format('M d, Y') }}</td>
                         <td data-label="Date Released" class="pas-muted">{{ $slip->date_released?->format('M d, Y') ?? '—' }}</td>
                         <td data-label="Supplier" class="col-hide-md pas-muted">{{ $slip->supplier?->company_name ?? '—' }}</td>
                         <td data-label="Facility / Coordinator" class="pas-name">{{ $slip->facility_coordinator }}</td>
                         <td data-label="Program" class="col-hide-md pas-muted">{{ $slip->program ?? '—' }}</td>
                         <td data-label="Purpose / Activity" class="pas-muted">{{ $slip->purpose_activity ?? '—' }}</td>
                         <td data-label="Status">
                              <span class="pas-badge pas-badge--{{ in_array($displayStatus, ['Released', 'Released through pass']) ? 'green' : ($displayStatus === 'Canceled' ? 'red' : 'amber') }}">
                                 <span class="pas-badge-dot"></span>
                                 {{ $displayStatus }}
                             </span>
                         </td>
                        <td class="mobile-card-actions" style="text-align:center;">
                            <a href="{{ route('pas.view', $slip) }}" class="btn btn-sm btn-outline">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                <tr class="pas-empty-row">
                    <td colspan="9">
                        <div class="pas-empty">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                            <p>No property allocation slips found.</p>
                            <a href="{{ route('pas.create') }}" class="btn btn-primary btn-sm">Create one</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination.modern :paginator="$slips" />
</section>

<style>
    .pas-card { padding: 1.5rem; }
    .pas-filter-bar {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: flex-end;
        margin-bottom: 1.25rem;
        padding: 1rem;
        background: var(--surface-muted);
        border-radius: 0.85rem;
        border: 1px solid var(--border);
    }
    .pas-search-wrap { position: relative; flex: 1; min-width: 200px; }
    .pas-search-icon {
        position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%);
        color: var(--text-muted); pointer-events: none;
    }
    .pas-input {
        width: 100%; padding: 0.7rem 0.85rem 0.7rem 2.5rem;
        border: 1px solid var(--border); border-radius: 0.75rem;
        background: var(--surface); color: var(--text); font-size: 0.9rem;
        transition: border-color 200ms ease, box-shadow 200ms ease;
    }
    .pas-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }
    .pas-select {
        padding: 0.7rem 0.85rem; border: 1px solid var(--border); border-radius: 0.75rem;
        background: var(--surface); color: var(--text); font-size: 0.9rem;
        min-width: 140px; cursor: pointer; transition: border-color 200ms ease;
    }
    .pas-select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }

    .pas-table-wrap { border-radius: 0.75rem; overflow-x: auto; border: 1px solid var(--border); }
    .pas-table thead th {
        background: var(--surface-muted); color: var(--text-muted);
        font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.1em; padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--border); white-space: nowrap;
    }
    .pas-table tbody td { padding: 0.85rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .pas-table tbody tr { transition: background-color 150ms ease; }
    .pas-table tbody tr:last-child td { border-bottom: none; }
    .pas-table tbody tr:hover { background: rgba(37,99,235,0.04); }
    .pas-name { font-weight: 600; color: var(--text); font-size: 0.9rem; }
    .pas-muted { color: var(--text-muted); font-size: 0.85rem; }

    .pas-badge {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.3rem 0.7rem; border-radius: 999px;
        font-size: 0.78rem; font-weight: 600; white-space: nowrap;
    }
    .pas-badge-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .pas-badge--green { background: rgba(22,163,74,0.12); color: #15803d; }
    .pas-badge--green .pas-badge-dot { background: #22c55e; }
    .pas-badge--red { background: rgba(220,38,38,0.12); color: #991b1b; }
    .pas-badge--red .pas-badge-dot { background: #ef4444; }
    .pas-badge--amber { background: rgba(217,119,6,0.12); color: #92400e; }
    .pas-badge--amber .pas-badge-dot { background: #f59e0b; }

    .pas-actions { display: flex; gap: 0.4rem; justify-content: center; flex-wrap: wrap; }
    .btn-outline {
        background: transparent; border: 1px solid var(--border); color: var(--text); border-radius: 0.75rem;
    }
    .btn-outline:hover {
        background: var(--surface-muted); border-color: var(--text-muted);
        transform: none; box-shadow: none; color: var(--text);
    }

    .pas-empty-row td { padding: 0 !important; }
    .pas-empty {
        display: flex; flex-direction: column; align-items: center; gap: 0.75rem;
        padding: 3rem 1.5rem; color: var(--text-muted); text-align: center;
    }
    .pas-empty svg { opacity: 0.35; margin-bottom: 0.25rem; }
    .pas-empty p { font-size: 0.95rem; font-weight: 500; margin: 0; }
</style>
@endsection
