@extends('layouts.app')

@section('title', 'Release Records')
@section('pageHeading', 'Release Records')
@section('pageSubheading', 'Track supply release slips and end-user distribution.')

@section('content')
    <div class="section-header">
        <div>
            <h1 class="page-heading">Release Records</h1>
            <p class="page-description">Track outgoing supplies and distribution status.</p>
        </div>
        <div class="table-actions">
            <a href="{{ route('releases.create') }}" class="btn btn-primary">New Release Slip</a>
        </div>
    </div>

    <section class="card" style="padding: 0.75rem; margin-bottom: var(--space-4);" aria-label="Release filters">
        <div class="section-header" style="padding: 0; background: transparent; border-bottom: none;">
            <div>
                <h2 class="section-card-title" style="margin: 0;">Filters</h2>
                <p class="page-description" style="margin-top: 0.25rem;">Search and filter release records.</p>
            </div>
            <div class="table-actions">
                @if(request()->hasAny(['search','status']))
                    <a href="{{ route('releases.index') }}" class="btn btn-secondary" style="min-height: 44px;">Clear</a>
                @endif
            </div>
        </div>

        <form id="releasesFilterForm" method="GET" class="search-panel" style="grid-template-columns: 1fr 1fr auto; margin-top: var(--space-4);">
            <div>
                <label for="releaseSearch" class="sr-only">Search releases</label>
                <input id="releaseSearch" type="text" name="search" value="{{ request('search') }}" placeholder="Search releases…" class="search-input" />
            </div>

            <div>
                <label for="releaseStatus" class="sr-only">Filter by status</label>
            <select id="releaseStatus" name="status" class="search-input">
                <option value="">All statuses</option>
                <option value="unreleased" @selected(request('status') === 'unreleased')>Unreleased</option>
                <option value="released" @selected(request('status') === 'released')>Released</option>
                <option value="released-through-pass" @selected(request('status') === 'released-through-pass')>Released through pass</option>
                <option value="canceled" @selected(request('status') === 'canceled')>Canceled</option>
                <option value="returned" @selected(request('status') === 'returned')>Returned</option>
            </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="min-height: 44px;">Apply Filters</button>
            </div>
        </form>
    </section>


    <section class="card" style="padding: 0.75rem;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Release No.</th>
                        <th>PAS No.</th>
                        <th>PHO Code</th>
                        <th>Facility / End-user</th>
                        <th>Date</th>
                        <th>Status</th>
                    <th>PTR/ITR/RIS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($releases as $release)
                        <tr>
                            <td>{{ $release->release_number }}</td>
                            <td>{{ $release->pas_number }}</td>
                            <td>{{ $release->pho_code }}</td>
                            <td>{{ $release->facility_name }}</td>
                            <td>{{ $release->date_released->format('M d, Y') }}</td>
                            <td>
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
                                    <span>{{ $release->status }}</span>
                                    <a href="{{ route('releases.view', $release) }}" class="table-link" aria-label="View release">View</a>

                                </div>
                            </td>
<td>{{ empty($release->ptr_itr_ris_no) ? 'NO PTR/ITR/RIS' : e($release->ptr_itr_ris_no) }}</td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" style="padding: 1.25rem;">
                                <div class="empty-state">
                                    <strong>No release records found.</strong>
                                    <div style="margin-top: 0.35rem;">Create a new release slip to track outgoing supplies.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="pagination-wrapper">
        {{ $releases->links() }}
    </div>
@endsection

