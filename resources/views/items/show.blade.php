@extends('layouts.app')

@section('title', 'Item Details')
@section('pageHeading', 'Item Details')
@section('pageSubheading', 'View inventory item information. Stock is updated through Receivings and Releases only.')

@section('content')
    {{-- All Inventory Records + Supplier KPIs at top --}}
    <div style="display: flex; gap: 1rem; align-items: flex-start; flex-wrap: wrap; margin-bottom: 1.5rem;">
        <div class="section-card" style="flex: 1; min-width: 280px; padding: 1.5rem;">
            <h2 class="section-card-title" style="margin: 0 0 0.35rem; font-size: 1.1rem;">All Inventory Records</h2>
            <p class="page-description" style="margin: 0 0 1rem; font-size: 1.5rem; font-weight: 700; color: var(--text);">{{ $items->count() }} <span style="font-size: 0.9rem; font-weight: 400; color: var(--text-muted);">record(s) for this item description.</span></p>
            <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                <label for="productCodeInput" style="font-weight: 600; font-size: 0.875rem;">Product Code:</label>
                <div style="position: relative;">
                    <div style="position: relative; display: flex; align-items: center;">
                        <input
                            id="productCodeInput"
                            type="text"
                            class="search-input"
                            style="width: 100%; padding-right: 2rem;"
                            placeholder="Type to search..."
                            autocomplete="off"
                            value="{{ $item->item_code }} — {{ $item->location }}"
                        />
                        <button id="productCodeClear" type="button" title="Clear" style="position:absolute; right:0.5rem; background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:1rem; line-height:1; padding:0.2rem 0.3rem;">&times;</button>
                    </div>
                    <ul id="productCodeDropdown" style="display:none; position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid var(--border); border-radius:6px; margin:2px 0 0; padding:0; list-style:none; z-index:100; box-shadow:0 4px 12px rgba(0,0,0,0.1); max-height:220px; overflow-y:auto;">
                        @foreach($items as $groupedItem)
                            <li data-url="{{ route('items.show', $groupedItem) }}" data-label="{{ $groupedItem->item_code }} — {{ $groupedItem->location }}" style="padding:0.6rem 0.9rem; cursor:pointer; font-size:0.9rem;" onmouseover="this.style.background='var(--bg-subtle)'" onmouseout="this.style.background=''">
                                {{ $groupedItem->item_code }} — {{ $groupedItem->location }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

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

    <script>
        (function () {
            const input = document.getElementById('productCodeInput');
            const dropdown = document.getElementById('productCodeDropdown');
            const allItems = Array.from(dropdown.querySelectorAll('li'));

            const clearBtn = document.getElementById('productCodeClear');

            input.addEventListener('focus', () => { filterList(input.value); dropdown.style.display = 'block'; });
            input.addEventListener('input', () => filterList(input.value));
            document.addEventListener('click', (e) => { if (!input.contains(e.target) && !dropdown.contains(e.target) && !clearBtn.contains(e.target)) dropdown.style.display = 'none'; });

            clearBtn.addEventListener('click', () => {
                input.value = '';
                input.focus();
                filterList('');
            });

            allItems.forEach(li => {
                li.addEventListener('click', () => { window.location.href = li.dataset.url; });
            });

            function filterList(query) {
                const q = query.toLowerCase();
                let any = false;
                allItems.forEach(li => {
                    const match = li.dataset.label.toLowerCase().includes(q);
                    li.style.display = match ? '' : 'none';
                    if (match) any = true;
                });
                dropdown.style.display = any ? 'block' : 'none';
            }
        })();
    </script>

    <div class="section-card">
        <div class="section-header">
            <div>
                <h1 class="page-heading">{{ $item->item_code }} - {{ $item->name }}</h1>
                <p class="page-description">Current stock and item details.</p>
            </div>
            <a href="{{ route('items.index') }}" class="btn btn-secondary">Back to Items</a>
        </div>

        <div class="table-container">
            <table>
                <tbody>
                    <tr>
                        <th style="width: 30%;">Product Code</th>
                        <td>{{ $item->item_code }}</td>
                        <th style="width: 30%;">Item Description</th>
                        <td>{{ $item->name }}</td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td>{{ $item->category }}</td>
                        <th>Unit of Measure (UOM)</th>
                        <td>{{ $item->display_unit }}</td>
                    </tr>
                    <tr>
                        <th>Current Stock</th>
                        <td>{{ $item->quantity_on_hand }}</td>
                        <th>Unit Cost</th>
                        <td>{{ $item->unit_cost ? number_format($item->unit_cost, 2) : '0.00' }}</td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td>{{ $item->location }}</td>
                        <th>Stock Keeping Unit (Program)</th>
                        <td>{{ $item->stock_keeping_unit }}</td>
                    </tr>
                    <tr>
                        <th>Program Coordinator</th>
                        <td>{{ $item->program_coordinator }}</td>
                        <th>Status</th>
                        <td><span class="status-pill {{ $item->status_class }}">{{ $item->status }}</span></td>
                    </tr>
                    <tr>
                        <th>Expiry</th>
                        <td><span class="status-pill {{ $item->expiry_badge_class }}">{{ $item->expiry_label }}</span></td>
                        <th>Description</th>
                        <td>{{ $item->description }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>



    <div class="section-card" style="margin-top: 2rem; padding: 1.25rem;">
        <div class="section-header compact" style="padding: 0 0 1rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <div>
                <h2 class="section-card-title" style="margin: 0;">Stock Deduction Tracking</h2>
                <p class="page-description" style="margin-top: 0.25rem;">View all deductions from releases and receivings.</p>
            </div>
        </div>

        <div class="dashboard-content-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
            <div class="kpi-card kpi-card--alert" style="padding: 1rem 1.1rem;">
                <div class="kpi-card-header">
                    <div class="kpi-card-icon" style="background: rgba(220, 38, 38, 0.14); color: var(--danger);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                    </div>
                    <div>
                        <div class="kpi-card-label">Total Released</div>
                    </div>
                </div>
                <p class="kpi-card-value" style="color: var(--danger);">{{ $totalReleased }}</p>
                <p class="kpi-card-foot">Units deducted via releases</p>
            </div>

            <div class="kpi-card kpi-card--blue" style="padding: 1rem 1.1rem;">
                <div class="kpi-card-header">
                    <div class="kpi-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </div>
                    <div>
                        <div class="kpi-card-label">Current Stock</div>
                    </div>
                </div>
                <p class="kpi-card-value">{{ $totalStock }}</p>
                <p class="kpi-card-foot">Available in inventory</p>
            </div>

            <div class="kpi-card kpi-card--green" style="padding: 1rem 1.1rem;">
                <div class="kpi-card-header">
                    <div class="kpi-card-icon" style="background: rgba(245, 158, 11, 0.14); color: var(--warning);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                    </div>
                    <div>
                        <div class="kpi-card-label">Deduction %</div>
                    </div>
                </div>
                <p class="kpi-card-value" style="color: var(--warning);">{{ $deductionPercentage }}%</p>
                <p class="kpi-card-foot">Of total stock deducted</p>
            </div>
        </div>

        <div>
            <h3 class="section-card-title" style="margin-bottom: 0.85rem;">Deduction History</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Product Code</th>
                            <th>Reference</th>
                            <th style="text-align: center;">Quantity</th>
                            <th>Facility / Receiver</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deductionHistory as $record)
                            <tr>
                                <td>{{ $record['date']->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge" style="background: {{ $record['type'] === 'Release' ? 'rgba(220, 38, 38, 0.1)' : 'rgba(37, 99, 235, 0.1)' }}; color: {{ $record['type'] === 'Release' ? 'var(--danger)' : 'var(--primary)' }};">{{ $record['type'] }}</span>
                                </td>
                                <td>{{ $record['item_code'] }}</td>
                                <td>{{ $record['reference'] }}</td>
                                <td style="text-align: center; font-weight: 600;">{{ $record['quantity'] }}</td>
                                <td>{{ $record['facility'] }}</td>
                                <td>
                                    <span class="badge badge-success">{{ $record['status'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 1.25rem; text-align: center;">
                                    <div class="empty-state">
                                        <strong>No deduction history found.</strong>
                                        <div style="margin-top: 0.35rem;">This item has not been released yet.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
