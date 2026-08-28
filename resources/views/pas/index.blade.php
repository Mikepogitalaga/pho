@extends('layouts.app')

@section('title', 'Property Allocation Slips')
@section('pageHeading', 'Property Allocation Slips')
@section('pageSubheading', 'Manage PAS records — items are not deducted from inventory.')

@section('content')
<section class="card">
    <div class="section-header">
        <div>
            <h1 class="page-heading">Property Allocation Slips</h1>
            <p class="page-description">PAS items are tracked separately and do not affect inventory stock.</p>
        </div>
        <a href="{{ route('pas.create') }}" class="btn btn-primary">+ New PAS</a>
    </div>

    <form method="GET" action="{{ route('pas.index') }}" class="filter-bar" style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-bottom:1.25rem;">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search PAS number, coordinator, program…" style="flex:1;min-width:200px;">
        <select name="status" style="min-width:140px;">
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

    <div class="table-wrapper">
        <table class="data-table">
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
                        $displayStatus = $slip->status;
                        if ($slip->status === 'Pending' && $linkedRelease) {
                            $displayStatus = 'PTR created';
                        }
                        $badgeClass = match($displayStatus) {
                            'Released' => 'badge-success',
                            'Canceled' => 'badge-danger',
                            'PTR created' => 'badge-success',
                            default    => 'badge-warning',
                        };
                    @endphp
                     <tr>
                         <td class="mobile-card-header">
                             <strong>{{ $slip->pas_number }}</strong>
                         </td>
                         <td data-label="Date of PASS" class="col-hide-md">{{ $slip->date_of_pass?->format('M d, Y') }}</td>
                         <td data-label="Date Released">{{ $slip->date_released?->format('M d, Y') ?? '—' }}</td>
                         <td data-label="Supplier" class="col-hide-md">{{ $slip->supplier?->company_name ?? '—' }}</td>
                         <td data-label="Facility / Coordinator">{{ $slip->facility_coordinator }}</td>
                         <td data-label="Program" class="col-hide-md">{{ $slip->program ?? '—' }}</td>
                         <td data-label="Purpose / Activity">{{ $slip->purpose_activity ?? '—' }}</td>
                         <td data-label="Status">
                             <span class="badge {{ $badgeClass }}">{{ $displayStatus }}</span>
                         </td>
                        <td class="mobile-card-actions" style="text-align:center;">
                            <a href="{{ route('pas.view', $slip) }}" class="btn btn-secondary" style="min-height:auto; padding:0.35rem 0.85rem; font-size:0.82rem;">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;color:var(--text-muted);padding:2rem;">No property allocation slips found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination.modern :paginator="$slips" />
</section>
@endsection
