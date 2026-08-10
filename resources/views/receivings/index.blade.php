@extends('layouts.app')

@section('title', 'Receive Supplies')
@section('pageHeading', 'Receive Supplies')
@section('pageSubheading', 'View receiving records and add new supply receipts.')

@section('content')
    <div class="section-header">
        <div>
            
        </div>
        <div class="table-actions">
            <a href="{{ route('receivings.create') }}" class="btn btn-primary">New Receiving</a>
        </div>
    </div>

    <section class="card" style="padding: 0.75rem; margin-bottom: var(--space-4);" aria-label="Receiving filters">
        <div class="section-header" style="padding: 0; background: transparent; border-bottom: none;">
            <div>
                <h2 class="section-card-title" style="margin: 0;">Filters</h2>
                <p class="page-description" style="margin-top: 0.25rem;">Search and filter receiving records by various criteria.</p>
            </div>
            <div class="table-actions">
                @if(request()->hasAny(['search','supplier','po_number','start_date','end_date']))
                    <a href="{{ route('receivings.index') }}" class="btn btn-secondary" style="min-height: 44px;">Clear All</a>
                @endif
            </div>
        </div>

        <form id="receivingsFilterForm" method="GET" class="search-panel" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: var(--space-4);">
            <div>
                <label for="receivingSearch" class="sr-only">Search receivings</label>
                <input id="receivingSearch" type="text" name="search" value="{{ request('search') }}" placeholder="Search by Receiving No." class="search-input" />
            </div>
            <div>
                <label for="supplierFilter" class="sr-only">Filter by supplier</label>
                <input id="supplierFilter" type="text" name="supplier" value="{{ request('supplier') }}" placeholder="Filter by supplier" class="search-input" />
            </div>
            <div>
                <label for="poNumberFilter" class="sr-only">Filter by PO No.</label>
                <input id="poNumberFilter" type="text" name="po_number" value="{{ request('po_number') }}" placeholder="Filter by PO No." class="search-input" />
            </div>
            <div>
                <label for="startDate" class="sr-only">Start Date</label>
                <input id="startDate" type="date" name="start_date" value="{{ request('start_date') }}" placeholder="Start Date" class="search-input" />
            </div>
            <div>
                <label for="endDate" class="sr-only">End Date</label>
                <input id="endDate" type="date" name="end_date" value="{{ request('end_date') }}" placeholder="End Date" class="search-input" />
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="min-height: 44px; flex: 1;">Apply Filters</button>
            </div>
        </form>
    </section>

    <section class="card" style="padding: 0.75rem;" id="receivingsTable">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ICS/PTR/RIS</th>
                        <th>Supplier</th>
                        <th>PO No.</th>
                        <th>Date Received</th>
                        <th>Received By</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receivings as $receiving)
                        <tr>
                            <td>{{ $receiving->ics_ptr_ris ?? '—' }}</td>
                            <td>{{ $receiving->supplier->company_name }}</td>
                            <td>{{ $receiving->po_number }}</td>
                            <td>{{ $receiving->date_received->format('M d, Y') }}</td>
                            <td>{{ $receiving->received_by }}</td>
                            <td>{{ $receiving->location }}</td>
                            <td>
                                <a href="{{ route('receivings.view', $receiving) }}" class="table-link" aria-label="View receiving">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 1.25rem;">
                                <div class="empty-state">
                                    <strong>No receiving records found.</strong>
                                    <div style="margin-top: 0.35rem;">Create a new receiving slip to start tracking incoming stock.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="pagination-wrapper">
        {{ $receivings->links() }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hasFilters = {{ request()->hasAny(['search','supplier','po_number','start_date','end_date']) ? 'true' : 'false' }};
            if (hasFilters) {
                document.getElementById('receivingsTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    </script>
@endsection
