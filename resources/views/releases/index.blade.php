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
                        <th class="col-hide-md">PHO Code</th>
                        <th>Facility / End-user</th>
                        <th class="col-hide-md">Program</th>
                        <th>Item Description</th>
                        <th>Date Released</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                     @forelse ($releases as $release)
                         <tr>
                              <td class="mobile-card-header">
                                  <span>{{ $release->ptr_itr_ris_no ?? $release->release_number }}</span>
                              </td>
                              <td data-label="PAS No.">{{ $release->pas_number }}</td>
                              <td data-label="PHO Code" class="col-hide-md">{{ $release->pho_code }}</td>
                              <td data-label="Facility / End-user">{{ $release->facility_name }}</td>
                              <td data-label="Program" class="col-hide-md">{{ $release->health_program_coordinator ?? '—' }}</td>
                              <td data-label="Item Description">
                                  <div class="item-desc-cell">
                                      <div class="item-desc-primary">{{ $release->items->first()?->item_description ?? '—' }}</div>
                                      @php
                                          $allDescriptions = $release->items->pluck('item_description')->filter()->values();
                                      @endphp
                                      @if($allDescriptions->count() > 1)
                                          <button type="button" class="btn btn-ghost view-items-btn" style="padding:0; min-height:auto; font-size:0.8rem;" data-items='@json($allDescriptions)'>
                                              View {{ $allDescriptions->count() }} items
                                          </button>
                                      @endif
                                      <div class="items-dropdown" style="display:none;"></div>
                                  </div>
                              </td>
                              <td data-label="Date Released">{{ optional($release->date_released)->format('M d, Y') ?? '—' }}</td>
                              <td data-label="Status">
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
                             <td class="mobile-card-actions">
                                 <a href="{{ route('releases.view', $release) }}" class="btn btn-secondary" style="min-height:auto; padding:0.35rem 0.85rem; font-size:0.82rem;">View</a>
                             </td>
                         </tr>

                     @empty
                         <tr>
                             <td colspan="9" style="padding: 1.25rem;">
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
            const hasFilters = {{ request()->hasAny(['search','status','facility','pho_code','pas_number','program']) ? 'true' : 'false' }};
            if (hasFilters) {
                document.getElementById('releasesTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            document.querySelectorAll('.view-items-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const dropdown = this.nextElementSibling;
                    const items = JSON.parse(this.dataset.items || '[]');

                    document.querySelectorAll('.items-dropdown').forEach(function(d) {
                        if (d !== dropdown) {
                            d.style.display = 'none';
                        }
                    });

                    dropdown.innerHTML = items.map(function(it, idx) {
                        return '<div style="padding:0.45rem 0.75rem; border-bottom:1px solid var(--border); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' +
                            '<span style="font-weight:700; margin-right:0.5rem; color:var(--text-muted);">' + (idx + 1) + '.</span>' +
                            '<span>' + (it || '—') + '</span></div>';
                    }).join('');

                    dropdown.style.display = 'block';
                });
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.item-desc-cell')) {
                    document.querySelectorAll('.items-dropdown').forEach(function(d) {
                        d.style.display = 'none';
                    });
                }
            });
        });
    </script>
    @endpush

    <style>
        .item-desc-cell { position: relative; }
        .items-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1050;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            box-shadow: var(--shadow);
            padding: 0.35rem 0;
            min-width: 260px;
            max-width: 420px;
            max-height: 240px;
            overflow-y: auto;
            margin-top: 0.35rem;
        }
    </style>
@endsection
