@extends('layouts.app')

@section('title', 'My PAS Requests')
@section('pageHeading', 'My PAS Requests')

@section('content')
<section class="card pas-card">
    <div class="section-header">
        <div>
            <h2 class="section-title" style="margin:0;">My PAS Requests</h2>
            <p class="page-description">Track the status of your PAS allocation requests.</p>
        </div>
        <a href="{{ route('pas.create') }}" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Request
        </a>
    </div>

    <form method="GET" action="{{ route('pas.my-requests') }}" class="pas-filter-bar">
        <div class="pas-search-wrap">
            <svg class="pas-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search PAS number, program, coordinator…" class="pas-input">
        </div>
        <select name="request_status" class="pas-select">
            <option value="">All Request Statuses</option>
            <option value="pending_approval" {{ request('request_status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
            <option value="approved" {{ request('request_status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('request_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="completed" {{ request('request_status') === 'completed' ? 'selected' : '' }}>Completed</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request('search') || request('request_status'))
            <a href="{{ route('pas.my-requests') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="table-wrapper pas-table-wrap">
        <table class="data-table pas-table">
            <thead>
                <tr>
                    <th>PAS Number</th>
                    <th class="col-hide-md">Date of PASS</th>
                    <th>Program</th>
                    <th>Facility / Coordinator</th>
                    <th>Request Status</th>
                    <th>PAS Status</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    @php
                        $linkedRelease = $req->release;
                        $pasStatus = $linkedRelease ? $linkedRelease->status : $req->status;
                        $requestStatus = $req->request_status ?? 'pending_approval';
                        $requestBadgeClass = match($requestStatus) {
                            'approved' => 'badge-success',
                            'rejected' => 'badge-danger',
                            'completed' => 'badge-success',
                            default    => 'badge-warning',
                        };
                        $pasBadgeClass = match($pasStatus) {
                            'Released', 'Released through pass' => 'badge-success',
                            'Canceled' => 'badge-danger',
                            'Returned' => 'badge-warning',
                            default    => 'badge-secondary',
                        };
                    @endphp
                     <tr>
                         <td class="mobile-card-header">
                             <strong class="pas-name">{{ $req->pas_number }}</strong>
                         </td>
                         <td data-label="Date of PASS" class="col-hide-md pas-muted">{{ $req->date_of_pass?->format('M d, Y') }}</td>
                         <td data-label="Program" class="pas-muted">{{ $req->program ?? '—' }}</td>
                         <td data-label="Facility / Coordinator" class="pas-name">{{ $req->facility_coordinator }}</td>
                         <td data-label="Request Status">
                              <span class="pas-badge pas-badge--{{ in_array($requestStatus, ['approved', 'completed']) ? 'green' : ($requestStatus === 'rejected' ? 'red' : 'amber') }}">
                                 <span class="pas-badge-dot"></span>
                                 {{ ucfirst(str_replace('_', ' ', $requestStatus)) }}
                              </span>
                         </td>
                         <td data-label="PAS Status">
                              <span class="pas-badge pas-badge--{{ in_array($pasStatus, ['Released', 'Released through pass']) ? 'green' : ($pasStatus === 'Canceled' ? 'red' : 'amber') }}">
                                 <span class="pas-badge-dot"></span>
                                 {{ $pasStatus }}
                              </span>
                         </td>
                        <td class="mobile-card-actions" style="text-align:center;">
                            <a href="{{ route('pas.view', $req) }}" class="btn btn-sm btn-outline">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                <tr class="pas-empty-row">
                    <td colspan="7">
                        <div class="pas-empty">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                            <p>No requests found.</p>
                            <a href="{{ route('pas.create') }}" class="btn btn-primary btn-sm">Submit a request</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination.modern :paginator="$requests" />
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
        min-width: 180px; cursor: pointer; transition: border-color 200ms ease;
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
