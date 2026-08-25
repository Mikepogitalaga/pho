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
                <p class="page-description" style="margin-top: 0.25rem;">Search and filter release records by various criteria.</p>
            </div>
            <div class="table-actions">
                @if(request()->hasAny(['search','status','facility','pho_code','pas_number','program']))
                    <a href="{{ route('releases.index') }}" class="btn btn-secondary" style="min-height: 44px;">Clear All</a>
                @endif
            </div>
        </div>

        <form id="releasesFilterForm" method="GET" class="search-panel" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: var(--space-4);">
            <div>
                <label for="releaseSearch" class="sr-only">Search releases</label>
                <input id="releaseSearch" type="text" name="search" value="{{ request('search') }}" placeholder="Search by PTR/Release No." class="search-input" />
            </div>

            <div>
                <label for="facilityFilter" class="sr-only">Filter by facility</label>
                <input id="facilityFilter" type="text" name="facility" value="{{ request('facility') }}" placeholder="Filter by facility" class="search-input" />
            </div>

            <div>
                <label for="phoCodeFilter" class="sr-only">Filter by PHO Code</label>
                <input id="phoCodeFilter" type="text" name="pho_code" value="{{ request('pho_code') }}" placeholder="Filter by PHO Code" class="search-input" />
            </div>

            <div>
                <label for="pasNumberFilter" class="sr-only">Filter by PAS No.</label>
                <input id="pasNumberFilter" type="text" name="pas_number" value="{{ request('pas_number') }}" placeholder="Filter by PAS No." class="search-input" />
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
                <label for="programFilter" class="sr-only">Filter by program</label>
                <select id="programFilter" name="program" class="search-input">
                    <option value="">All programs</option>
                    @foreach($programs as $programOption)
                        <option value="{{ $programOption->name }}" @selected(request('program') === $programOption->name)>{{ $programOption->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="min-height: 44px; flex: 1;">Apply Filters</button>
            </div>
        </form>
    </section>


    <section class="card" style="padding: 0.75rem;" id="releasesTable">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>PTR Number</th>
                        <th>PAS No.</th>
                        <th>PHO Code</th>
                        <th>Facility / End-user</th>
                        <th>Program</th>
                        <th>Date Released</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($releases as $release)
                        <tr>
                            <td>{{ $release->ptr_itr_ris_no ?? $release->release_number }}</td>
                            <td>{{ $release->pas_number }}</td>
                            <td>{{ $release->pho_code }}</td>
                            <td>{{ $release->facility_name }}</td>
                            <td>{{ $release->health_program_coordinator ?? '—' }}</td>
                            <td>{{ optional($release->date_released)->format('M d, Y') ?? '—' }}</td>
                            <td>
                                @php
                                    $statusClass = match($release->status) {
                                        'Released' => 'badge-success',
                                        'Released through pass' => 'badge-info',
                                        'Unreleased' => 'badge-warning',
                                        'Canceled' => 'badge-danger',
                                        'Returned' => 'badge-secondary',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span class="status-pill {{ $statusClass }}">{{ $release->status }}</span>
                            </td>
                            <td>
                                <a href="{{ route('releases.view', $release) }}" class="table-link" aria-label="View release">View</a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="8" style="padding: 1.25rem;">
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

    <x-pagination.modern :paginator="$releases" />

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hasFilters = {{ request()->hasAny(['search','status','facility','pho_code','pas_number']) ? 'true' : 'false' }};
            if (hasFilters) {
                document.getElementById('releasesTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    </script>
    @endpush
@endsection
