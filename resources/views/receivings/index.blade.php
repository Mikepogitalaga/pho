@extends('layouts.app')

@section('title', 'Receive Supplies')
@section('pageHeading', 'Receive Supplies')
@section('pageSubheading', 'View receiving records and add new supply receipts.')

@section('content')
    <div class="section-header">
        <div>
            
        </div>
        <div class="table-actions" style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <a href="{{ route('receivings.export') }}" class="btn btn-secondary">Download Excel</a>
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
                @if(request()->hasAny(['search','supplier','po_number','start_date','end_date','program']))
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

    <section class="card" style="padding: 0.75rem;" id="receivingsTable">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ICS/PTR/RIS</th>
                        <th>Item Description</th>
                        <th>Supplier</th>
                        <th>PO No.</th>
                        <th>Date Received</th>
                        <th>Received By</th>
                        <th>Location</th>
                        <th>Program</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receivings as $receiving)
                        <tr>
                            <td>{{ $receiving->ics_ptr_ris ?? '—' }}</td>
                            <td>
                                <div class="item-desc-cell">
                                    <div class="item-desc-primary">{{ $receiving->items->first()?->item_description ?? '—' }}</div>
                                    @php
                                        $allDescriptions = $receiving->items->pluck('item_description')->filter()->values();
                                    @endphp
                                    @if($allDescriptions->count() > 1)
                                        <button type="button" class="btn btn-ghost view-items-btn" style="padding:0; min-height:auto; font-size:0.8rem;" data-items='@json($allDescriptions)'>
                                            View {{ $allDescriptions->count() }} items
                                        </button>
                                    @endif
                                    <div class="items-dropdown" style="display:none;"></div>
                                </div>
                            </td>
                            <td>{{ $receiving->supplier->company_name }}</td>
                            <td>{{ $receiving->po_number }}</td>
                            <td>{{ $receiving->date_received->format('M d, Y') }}</td>
                            <td>{{ $receiving->received_by }}</td>
                            <td>{{ $receiving->location }}</td>
                            <td>{{ $receiving->stock_keeping_unit ?? '—' }}</td>
                            <td>
                                <a href="{{ route('receivings.view', $receiving) }}" class="table-link" aria-label="View receiving">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding: 1.25rem;">
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

    <x-pagination.modern :paginator="$receivings" />

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hasFilters = {{ request()->hasAny(['search','supplier','po_number','start_date','end_date','program']) ? 'true' : 'false' }};
            if (hasFilters) {
                document.getElementById('receivingsTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            const tableContainer = document.querySelector('.table-container');

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
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,.15);
            padding: 0.35rem 0;
            min-width: 260px;
            max-width: 420px;
            max-height: 240px;
            overflow-y: auto;
            margin-top: 0.35rem;
        }
    </style>
@endsection
