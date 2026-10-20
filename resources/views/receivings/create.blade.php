@extends('layouts.app')

@section('title', 'New Receiving')
@section('pageHeading', 'New Receiving')
@section('pageSubheading', 'Record supply receipts and update stock automatically.')

@push('styles')
<style>
.item-description-input {
    width: 100%;
    min-height: 42px;
    height: 42px;
    padding: 0.65rem 0.85rem;
    line-height: 1.45;
    border: 1px solid var(--border, #cbd5e1);
    border-radius: 0.6rem;
    background: var(--surface-muted, #f8fafc);
    color: var(--text, #1e293b);
    resize: none;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    box-sizing: border-box;
    font-family: inherit;
    font-size: 0.88rem;
    transition: height 0.16s ease, border-color 0.16s ease, box-shadow 0.16s ease;
}
.item-description-input:focus,
.item-description-input.expanded {
    white-space: normal !important;
    overflow-y: auto !important;
    border-color: rgba(37, 99, 235, 0.45);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}
</style>
@endpush

@section('content')
    <section class="card">

        <div class="section-header">
            <a href="{{ route('receivings.index') }}" class="btn btn-secondary">Back to Receivings</a>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- EXCEL IMPORT PANEL                                          --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div id="excelImportPanel" style="margin-bottom:1.5rem;">
            <button type="button" id="toggleImportPanel" class="btn btn-secondary"
                style="display:flex;align-items:center;gap:0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" style="flex-shrink:0;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                </svg>
                Import Items from Excel / CSV
            </button>

            <div id="importPanelBody" style="display:none;margin-top:1rem;">
                <div style="background:var(--surface-raised,#f8fafc);border:1px solid var(--border,#e2e8f0);border-radius:0.75rem;padding:1.25rem;">

                    {{-- Header row --}}
                    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;margin-bottom:1rem;">
                        <div>
                            <h3 style="margin:0;font-size:1rem;font-weight:600;">Bulk Import Received Items</h3>
                            <p style="margin:0.2rem 0 0;font-size:0.82rem;color:var(--text-muted,#64748b);">
                                Upload an Excel (.xlsx) or CSV file to pre-fill the items list below. Review and correct any errors before saving.
                            </p>
                        </div>
                        <a href="{{ route('receivings.import-template') }}"
                            style="font-size:0.82rem;color:var(--primary,#2563eb);display:flex;align-items:center;gap:0.35rem;white-space:nowrap;text-decoration:none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Download Template CSV
                        </a>
                    </div>

                    {{-- File upload area --}}
                    <div id="importDropZone"
                        style="border:2px dashed var(--border,#cbd5e1);border-radius:0.6rem;padding:1.75rem 1rem;text-align:center;cursor:pointer;transition:border-color 0.2s,background 0.2s;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted,#94a3b8);margin:0 auto 0.5rem;display:block;">
                            <polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/>
                            <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                        </svg>
                        <p style="margin:0 0 0.5rem;font-weight:600;font-size:0.9rem;">
                            Drop your Excel or CSV file here
                        </p>
                        <p style="margin:0 0 0.75rem;font-size:0.8rem;color:var(--text-muted,#64748b);">
                            Accepted: .xlsx, .xls, .csv — max 5 MB
                        </p>
                        <label for="excelImportFile" class="btn btn-primary"
                            style="cursor:pointer;display:inline-flex;align-items:center;gap:0.4rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            Choose File
                        </label>
                        <input type="file" id="excelImportFile" accept=".xlsx,.xls,.csv" style="display:none;" />
                        <div id="selectedFileName" style="margin-top:0.6rem;font-size:0.82rem;color:var(--text-muted,#64748b);"></div>
                    </div>

                    {{-- Upload progress / loading --}}
                    <div id="importLoading" style="display:none;margin-top:1rem;text-align:center;padding:1rem;font-size:0.9rem;color:var(--text-muted,#64748b);">
                        <div style="display:inline-block;width:20px;height:20px;border:3px solid var(--border,#e2e8f0);border-top-color:var(--primary,#2563eb);border-radius:50%;animation:spin 0.7s linear infinite;vertical-align:middle;margin-right:0.5rem;"></div>
                        Parsing file, please wait…
                    </div>

                    {{-- Global error box --}}
                    <div id="importGlobalError" style="display:none;margin-top:1rem;padding:0.8rem 1rem;background:rgba(220,38,38,0.08);border:1px solid rgba(220,38,38,0.25);border-radius:0.6rem;font-size:0.87rem;color:var(--danger,#dc2626);"></div>

                    {{-- Row-level error summary --}}
                    <div id="importRowErrorSummary" style="display:none;margin-top:1rem;padding:0.8rem 1rem;background:rgba(234,179,8,0.08);border:1px solid rgba(234,179,8,0.35);border-radius:0.6rem;">
                        <div style="font-weight:600;font-size:0.87rem;color:#92400e;margin-bottom:0.4rem;">
                            ⚠ Some rows have validation warnings — review them below before importing.
                            Rows with errors will be highlighted in red.
                        </div>
                        <ul id="importRowErrorList" style="margin:0 0 0 1.2rem;padding:0;font-size:0.82rem;color:#92400e;"></ul>
                    </div>

                    {{-- Preview Table --}}
                    <div id="importPreviewContainer" style="display:none;margin-top:1rem;">
                        {{-- Detected Header Info Card --}}
                        <div id="importHeaderPreview" style="display:none;margin-bottom:0.75rem;padding:0.75rem 1rem;background:rgba(37,99,235,0.06);border:1px solid rgba(37,99,235,0.2);border-radius:0.5rem;font-size:0.82rem;">
                            <div style="font-weight:600;color:var(--primary,#2563eb);margin-bottom:0.35rem;">
                                Detected Receiving / Document Info (will auto-fill the form above):
                            </div>
                            <div id="importHeaderDetails" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:0.4rem;color:var(--text,#1e293b);">
                            </div>
                        </div>

                        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.6rem;margin-bottom:0.6rem;">
                            <div id="importPreviewCount" style="font-size:0.85rem;font-weight:600;"></div>
                            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                                <button type="button" id="importConfirmBtn" class="btn btn-primary"
                                    style="display:flex;align-items:center;gap:0.4rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    Fill Form with These Items
                                </button>
                                <button type="button" id="importCancelBtn" class="btn btn-secondary">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <div style="overflow-x:auto;border-radius:0.5rem;border:1px solid var(--border,#e2e8f0);">
                            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;min-width:860px;">
                                <thead>
                                    <tr style="background:var(--surface-raised,#f1f5f9);text-align:left;">
                                        <th style="padding:0.55rem 0.75rem;border-bottom:1px solid var(--border,#e2e8f0);font-weight:600;">#</th>
                                        <th style="padding:0.55rem 0.75rem;border-bottom:1px solid var(--border,#e2e8f0);font-weight:600;">Description</th>
                                        <th style="padding:0.55rem 0.75rem;border-bottom:1px solid var(--border,#e2e8f0);font-weight:600;">Code</th>
                                        <th style="padding:0.55rem 0.75rem;border-bottom:1px solid var(--border,#e2e8f0);font-weight:600;">Cat.</th>
                                        <th style="padding:0.55rem 0.75rem;border-bottom:1px solid var(--border,#e2e8f0);font-weight:600;">UOM</th>
                                        <th style="padding:0.55rem 0.75rem;border-bottom:1px solid var(--border,#e2e8f0);font-weight:600;">Lot / Batch</th>
                                        <th style="padding:0.55rem 0.75rem;border-bottom:1px solid var(--border,#e2e8f0);font-weight:600;">Expiry</th>
                                        <th style="padding:0.55rem 0.75rem;border-bottom:1px solid var(--border,#e2e8f0);font-weight:600;">Qty</th>
                                        <th style="padding:0.55rem 0.75rem;border-bottom:1px solid var(--border,#e2e8f0);font-weight:600;">Cost</th>
                                        <th style="padding:0.55rem 0.75rem;border-bottom:1px solid var(--border,#e2e8f0);font-weight:600;">Location</th>
                                        <th style="padding:0.55rem 0.75rem;border-bottom:1px solid var(--border,#e2e8f0);font-weight:600;">Reorder</th>
                                        <th style="padding:0.55rem 0.75rem;border-bottom:1px solid var(--border,#e2e8f0);font-weight:600;">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="importPreviewBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- END EXCEL IMPORT PANEL --}}

        <form action="{{ route('receivings.store') }}" method="POST" class="stack" id="receivingForm">
            @csrf

            @if(session('error'))
                <div style="color:var(--danger);padding:0.8rem 1rem;background:rgba(220,38,38,0.08);border:1px solid rgba(220,38,38,0.25);border-radius:0.6rem;">
                    <strong>Unable to save receiving</strong>
                    <div style="margin-top:0.25rem;">{{ session('error') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div style="color:var(--danger);padding:0.8rem 1rem;background:rgba(220,38,38,0.08);border:1px solid rgba(220,38,38,0.25);border-radius:0.6rem;">
                    <strong>Please correct the following:</strong>
                    <ul style="margin:0.35rem 0 0 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Purchase Order / Source Document No.</label>
                    <input name="po_number" value="{{ old('po_number') }}" />
                </div>
                <div class="form-group">
                    <label>Supplier / Dealer</label>
                    <select name="supplier_id" required>
                        <option value="">Select supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->company_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>ICS / PTR / RIS</label>
                    <input name="ics_ptr_ris" value="{{ old('ics_ptr_ris') }}" />
                </div>
                <div class="form-group">
                    <label>Date (PTR/RIS/ICS)</label>
                    <input type="date" name="document_date" value="{{ old('document_date') }}" />
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Date Received</label>
                    <input type="date" name="date_received" value="{{ old('date_received', now()->toDateString()) }}" required />
                </div>
                <div class="form-group">
                    <label>Received By</label>
                    <input name="received_by" value="{{ old('received_by') }}" />
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Stock Keeping Unit (Program)</label>
                    <div style="position:relative;">
                        <input name="stock_keeping_unit" id="programInput" value="{{ old('stock_keeping_unit') }}" autocomplete="off" style="width:100%;" />
                        <div id="programDropdown" style="position:absolute;top:100%;left:0;width:100%;z-index:1000;display:none;"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Program Coordinator</label>
                    <div style="position:relative;">
                        <input name="program_coordinator" id="coordinatorInput" value="{{ old('program_coordinator') }}" autocomplete="off" style="width:100%;" />
                        <div id="coordinatorDropdown" style="position:absolute;top:100%;left:0;width:100%;z-index:1000;display:none;"></div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="section-card-title">Received Items <span style="color: var(--danger);">*</span></h3>
                @error('items')
                    <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
                @if($errors->hasAny(array_map(fn($i) => "items.{$i}.item_code", range(0, 20))))
                    <div style="color:var(--danger);font-size:0.82rem;margin-top:0.25rem;padding:0.6rem 0.9rem;background:rgba(220,38,38,0.07);border:1px solid rgba(220,38,38,0.2);border-radius:0.6rem;">
                        @foreach($errors->get('items.*.item_code') as $msgs)
                            @foreach($msgs as $msg)
                                <div>{{ $msg }}</div>
                            @endforeach
                        @endforeach
                    </div>
                @endif
                <div id="receiving-items" class="stack">
                    {{-- Item template for JS cloning --}}
                    <template id="receiving-item-template">
                        <div class="section-card receiving-item-row">
                            <div class="item-row-header">
                                <div class="item-row-title">Item</div>
                                <div class="item-row-actions">
                                    <button type="button" class="btn btn-link item-toggle-button">Hide</button>
                                    <button type="button" class="btn btn-danger remove-item-button">Delete</button>
                                </div>
                            </div>
                            <div class="item-row-body">
                                <div class="form-grid-4">
                                    <div class="form-group" style="position: relative;">
                                        <label>Product Code</label>
                                        <input class="item-code-input" name="items[0][item_code]" />
                                        <span class="product-code-error" style="display:none;color:var(--danger);font-size:0.8rem;margin-top:0.2rem;"></span>
                                    </div>
                                    <div class="form-group" style="position: relative;">
                                        <label>Item Description</label>
                                        <textarea class="item-description-input" name="items[0][item_description]" required autocomplete="off" rows="1" placeholder="Enter or search description..."></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Category</label>
                                        <select class="item-category-input" name="items[0][category]">
                                            <option value="DM">DM</option>
                                            <option value="MDL">MDL</option>
                                            <option value="Other supplies">Other supplies</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>UOM</label>
                                        <input class="item-uom-input" name="items[0][uom]" list="uom-options-receiving" />
                                    </div>
                                </div>
                                <div class="form-grid-4">
                                    <div class="form-group">
                                        <label>Lot / Batch / Model No.</label>
                                        <input class="item-lot-input" name="items[0][lot_number]" />
                                    </div>
                                    <div class="form-group">
                                        <label>Expiry Date / Useful Life</label>
                                        <input type="date" class="item-expiry-input" name="items[0][expiry_date]" />
                                    </div>
                                    <div class="form-group">
                                        <label>Quantity Received</label>
                                        <input type="number" class="item-quantity-input" name="items[0][quantity_received]" min="1" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Unit Cost</label>
                                        <input type="number" step="0.01" class="item-unit-cost-input" name="items[0][unit_cost]" />
                                    </div>
                                </div>
                                <div class="form-grid-4">
                                    <div class="form-group">
                                        <label>Location</label>
                                        <input class="item-location-input" name="items[0][location]" />
                                    </div>
                                    <div class="form-group">
                                        <label>Reorder Level</label>
                                        <input type="number" class="item-reorder-level-input" name="items[0][reorder_level]" value="0" min="0" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Render initial items from old input or default --}}
                    @php
                        $oldReceivedItems = collect(old('items', []))->values()->all();
                        if (empty($oldReceivedItems)) {
                            $oldReceivedItems = [[
                                'item_code' => '',
                                'item_description' => '',
                                'category' => '',
                                'uom' => '',
                                'lot_number' => '',
                                'expiry_date' => '',
                                'quantity_received' => '',
                                'unit_cost' => '',
                                'location' => '',
                                'reorder_level' => '',
                            ]];
                        }
                    @endphp

                    @foreach($oldReceivedItems as $index => $oldItem)
                        <div class="section-card receiving-item-row" data-index="{{ $index }}">
                            <div class="item-row-header">
                                <div class="item-row-title">Item {{ $index + 1 }}</div>
                                <div class="item-row-actions">
                                    <button type="button" class="btn btn-link item-toggle-button">Hide</button>
                                    <button type="button" class="btn btn-danger remove-item-button" @if($index === 0) style="display:none;" @endif>Delete</button>
                                </div>
                            </div>
                            <div class="item-row-body">
                                <div class="form-grid-4">
                                    <div class="form-group" style="position: relative;">
                                        <label>Product Code</label>
                                        <input class="item-code-input" name="items[{{ $index }}][item_code]" value="{{ $oldItem['item_code'] ?? '' }}" />
                                        <span class="product-code-error" style="display:none;color:var(--danger);font-size:0.8rem;margin-top:0.2rem;"></span>
                                    </div>
                                    <div class="form-group" style="position: relative;">
                                        <label>Item Description</label>
                                        <textarea class="item-description-input" name="items[{{ $index }}][item_description]" required autocomplete="off" rows="1" placeholder="Enter or search description...">{{ $oldItem['item_description'] ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Category</label>
                                        <select class="item-category-input" name="items[{{ $index }}][category]">
                                            <option value="DM" @selected(($oldItem['category'] ?? 'DM') === 'DM')>DM</option>
                                            <option value="MDL" @selected(($oldItem['category'] ?? '') === 'MDL')>MDL</option>
                                            <option value="Other supplies" @selected(($oldItem['category'] ?? '') === 'Other supplies')>Other supplies</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>UOM</label>
                                        <input class="item-uom-input" name="items[{{ $index }}][uom]" value="{{ $oldItem['uom'] ?? '' }}" list="uom-options-receiving" />
                                    </div>
                                </div>
                                <div class="form-grid-4">
                                    <div class="form-group">
                                        <label>Lot / Batch / Model No.</label>
                                        <input class="item-lot-input" name="items[{{ $index }}][lot_number]" value="{{ $oldItem['lot_number'] ?? '' }}" />
                                    </div>
                                    <div class="form-group">
                                        <label>Expiry Date / Useful Life</label>
                                        <input type="date" class="item-expiry-input" name="items[{{ $index }}][expiry_date]" value="{{ $oldItem['expiry_date'] ?? '' }}" />
                                    </div>
                                    <div class="form-group">
                                        <label>Quantity Received</label>
                                        <input type="number" class="item-quantity-input" name="items[{{ $index }}][quantity_received]" value="{{ $oldItem['quantity_received'] ?? '' }}" min="1" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Unit Cost</label>
                                        <input type="number" step="0.01" class="item-unit-cost-input" name="items[{{ $index }}][unit_cost]" value="{{ $oldItem['unit_cost'] ?? '' }}" />
                                    </div>
                                </div>
                                <div class="form-grid-4">
                                    <div class="form-group">
                                        <label>Location</label>
                                        <input class="item-location-input" name="items[{{ $index }}][location]" value="{{ $oldItem['location'] ?? '' }}" />
                                    </div>
                                    <div class="form-group">
                                        <label>Reorder Level</label>
                                        <input type="number" class="item-reorder-level-input" name="items[{{ $index }}][reorder_level]" value="{{ $oldItem['reorder_level'] ?? '' }}" min="0" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-receiving-item-button" class="btn btn-secondary" style="margin-top: 0.75rem;">Add another item</button>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Receiving</button>
                <a href="{{ route('receivings.index') }}" class="btn btn-ghost" id="receivingCancelBtn">Cancel</a>
            </div>
        </form>
    </section>

    {{-- Autocomplete data sources --}}
    <datalist id="item-options-receiving" style="display:none;">
        @foreach($items as $item)
            <option value="{{ $item->name }}" data-category="{{ $item->category }}" data-uom="{{ $item->unit }}" data-cost="{{ $item->unit_cost }}" data-reorder-level="{{ $item->reorder_level }}"></option>
        @endforeach
    </datalist>

    <datalist id="program-options" style="display:none;">
        @foreach($programs as $program)
            <option value="{{ $program->name }}" data-description="{{ $program->description }}"></option>
        @endforeach
    </datalist>

    <datalist id="coordinator-options" style="display:none;">
        @foreach($coordinators as $coordinator)
            <option value="{{ $coordinator->full_name }}" data-programs="{{ $coordinator->assigned_programs }}"></option>
        @endforeach
    </datalist>
    <datalist id="uom-options-receiving" style="display:none;">
        @foreach($uoms as $uom)
            <option value="{{ $uom }}"></option>
        @endforeach
    </datalist>

    @push('scripts')
    <script>
    (function() {
        var currentYear = new Date().getFullYear().toString().slice(-2);
        var currentMonth = String(new Date().getMonth() + 1).padStart(2, '0');
        var currentCodePrefix = '';
        var codeSequences = {};
        var programSequences = @json($programSequences ?? []);

        var itemsData = Array.from(document.querySelectorAll('#item-options-receiving option')).map(function(opt) {
            return {
                name: opt.value,
                nameLower: opt.value.toLowerCase(),
                code: opt.dataset.code,
                category: opt.dataset.category,
                uom: opt.dataset.uom,
                cost: opt.dataset.cost,
                reorderLevel: opt.dataset.reorderLevel,
            };
        });

        // ---- Program & Coordinator Autocomplete Data ----
        var programsData = Array.from(document.querySelectorAll('#program-options option')).map(function(opt) {
            return {
                name: opt.value,
                nameLower: opt.value.toLowerCase(),
                description: (opt.dataset.description || '').trim(),
            };
        });

        var coordinatorsData = Array.from(document.querySelectorAll('#coordinator-options option')).map(function(opt) {
            return {
                name: opt.value,
                nameLower: opt.value.toLowerCase(),
                assignedPrograms: opt.dataset.programs || '',
            };
        });

        function resolveProgramPrefix(programValue) {
            var normalized = (programValue || '').trim().toLowerCase();
            if (!normalized) {
                return '';
            }

            var matched = programsData.find(function(program) {
                return program.nameLower === normalized;
            });

            return matched ? (matched.description || '') : '';
        }

        function bindAutocompleteList(input, dataList, dropdown, onSelect) {
            function showOptions(searchText) {
                dropdown.innerHTML = '';
                var searchLower = searchText.toLowerCase().trim();
                var filtered = searchLower
                    ? dataList.filter(function(item) { return item.nameLower.indexOf(searchLower) !== -1; })
                    : dataList;

                if (filtered.length === 0) {
                    dropdown.style.display = 'none';
                    return;
                }

                // Deduplicate
                var seen = {};
                var unique = filtered.filter(function(item) {
                    var lower = item.nameLower;
                    if (seen[lower]) return false;
                    seen[lower] = true;
                    return true;
                });

                Object.assign(dropdown.style, {
                    background: 'var(--surface,#fff)', border: '1px solid var(--border,#ddd)',
                    maxHeight: '200px', overflowY: 'auto', boxShadow: '0 4px 6px rgba(0,0,0,.1)', marginTop: '4px'
                });

                unique.forEach(function(item) {
                    var option = document.createElement('div');
                    option.style.cssText = [
                        'padding: 10px 12px; cursor: pointer;',
                        'border-bottom: 1px solid #f0f0f0;'
                    ].join('');
                    option.textContent = item.name;
                    option.addEventListener('mouseover', function() {
                        this.style.backgroundColor = '#f5f5f5';
                    });
                    option.addEventListener('mouseout', function() {
                        this.style.backgroundColor = 'transparent';
                    });
                    option.addEventListener('click', function() {
                        input.value = item.name;
                        dropdown.style.display = 'none';
                        if (onSelect) onSelect(item);
                    });
                    dropdown.appendChild(option);
                });
                dropdown.style.display = 'block';
            }

            input.addEventListener('input', function() {
                showOptions(this.value);
            });

            input.addEventListener('blur', function() {
                setTimeout(function() { dropdown.style.display = 'none'; }, 200);
            });

            input.addEventListener('focus', function() {
                showOptions(this.value);
            });
        }

        // ---- Program Autocomplete ----
        var programInput = document.getElementById('programInput');
        var coordinatorInput = document.getElementById('coordinatorInput');
        var programDropdown = document.getElementById('programDropdown');
        var coordinatorDropdown = document.getElementById('coordinatorDropdown');

        function refreshAllItemCodes() {
            var container = document.getElementById('receiving-items');
            var prefix = currentCodePrefix;
            if (!prefix) return;

            var regex = new RegExp('^' + prefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '-\\d{2}-\\d{2}-(\\d{4})$');
            var maxExistingSeq = 0;

            Array.from(container.querySelectorAll('.item-code-input')).forEach(function(input) {
                var val = input.value || '';
                var match = val.match(regex);
                if (match) {
                    var seq = parseInt(match[1], 10);
                    if (seq > maxExistingSeq) {
                        maxExistingSeq = seq;
                    }
                }
            });

            var dbSeq = programSequences[prefix] || 0;
            var nextSeq = Math.max(dbSeq, maxExistingSeq);
            codeSequences[prefix] = nextSeq;

            Array.from(container.querySelectorAll('.item-code-input')).forEach(function(input) {
                var shouldSync = input.dataset.generated === 'true' || !(input.value || '').trim();
                if (!shouldSync) {
                    return;
                }

                input.value = prefix + '-' + currentYear + '-' + currentMonth + '-' + String(nextSeq).padStart(4, '0');
                input.dataset.generated = 'true';
                nextSeq++;
            });

            codeSequences[prefix] = nextSeq;
        }

        function bindProgramCodeSync(programInput, onChange) {
            if (!programInput) {
                return;
            }

            function syncProgramCode() {
                currentCodePrefix = resolveProgramPrefix(programInput.value);
                if (currentCodePrefix) {
                    onChange();
                }
            }

            programInput.addEventListener('input', syncProgramCode);
            programInput.addEventListener('change', syncProgramCode);
            syncProgramCode();
        }

        if (programInput && programDropdown) {
            bindAutocompleteList(programInput, programsData, programDropdown, function(item) {
                currentCodePrefix = item.description || '';
                var matchedCoordinator = coordinatorsData.find(function(c) {
                    return c.assignedPrograms.toLowerCase().indexOf(item.nameLower) !== -1;
                });
                if (matchedCoordinator && coordinatorInput) {
                    coordinatorInput.value = matchedCoordinator.name;
                }
                refreshAllItemCodes();
            });
            bindProgramCodeSync(programInput, refreshAllItemCodes);
        }

        if (coordinatorInput && coordinatorDropdown) {
            bindAutocompleteList(coordinatorInput, coordinatorsData, coordinatorDropdown, function(item) {
                if (item.assignedPrograms && programInput) {
                    var programs = item.assignedPrograms.split(', ');
                    if (programs.length > 0) {
                        if (!programInput.value.trim() || programsData.some(function(p) { return p.nameLower === programInput.value.trim().toLowerCase(); })) {
                            programInput.value = programs[0];
                            currentCodePrefix = resolveProgramPrefix(programs[0]);
                            refreshAllItemCodes();
                        }
                    }
                }
            });
        }
        // ---- End Program & Coordinator Autocomplete ----

        function getNextSequence(prefix) {
            if (!codeSequences[prefix]) {
                var dbSeq = programSequences[prefix] || 0;
                var maxExisting = 0;
                Array.from(document.querySelectorAll('.item-code-input')).forEach(function(input) {
                    var val = input.value || '';
                    var match = val.match(new RegExp('^' + prefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '-\\d{2}-\\d{2}-(\\d{4})$'));
                    if (match) {
                        var seq = parseInt(match[1], 10);
                        if (seq > maxExisting) maxExisting = seq;
                    }
                });
                codeSequences[prefix] = Math.max(dbSeq, maxExisting);
            }
            return codeSequences[prefix]++;
        }

        function generateNextCode() {
            if (!currentCodePrefix) return '';
            var seq = getNextSequence(currentCodePrefix);
            return currentCodePrefix + '-' + currentYear + '-' + currentMonth + '-' + String(seq).padStart(4, '0');
        }

        function createAutocompleteDropdown(descriptionInput, codeInput, categoryInput, uomInput, unitCostInput) {
            var dropdown = descriptionInput.parentElement.querySelector('.autocomplete-dropdown');
            if (dropdown) dropdown.remove();

            dropdown = document.createElement('div');
            dropdown.className = 'autocomplete-dropdown';
            dropdown.style.cssText = [
                'position: absolute; background: var(--surface); color: var(--text);',
                'border: 1px solid rgba(128,128,128,0.35);',
                'max-height: 200px; overflow-y: auto; width: 100%; z-index: 1000;',
                'display: none; box-shadow: 0 4px 6px rgba(0,0,0,.15);',
                'top: 100%; left: 0; margin-top: 4px;'
            ].join('');
            descriptionInput.parentElement.style.position = 'relative';
            descriptionInput.parentElement.appendChild(dropdown);
            return dropdown;
        }

        function bindAutocomplete(row) {
            var descriptionInput = row.querySelector('.item-description-input');
            var codeInput = row.querySelector('.item-code-input');
            var categoryInput = row.querySelector('.item-category-input');
            var uomInput = row.querySelector('.item-uom-input');
            var unitCostInput = row.querySelector('.item-unit-cost-input');
            var reorderLevelInput = row.querySelector('.item-reorder-level-input');

            if (!descriptionInput) return;

            var dropdown = createAutocompleteDropdown(descriptionInput, codeInput, categoryInput, uomInput, unitCostInput);

            function syncFromSelection(item) {
                descriptionInput.value = item.name;
                descriptionInput.title = item.name;
                if (descriptionInput.classList.contains('expanded')) {
                    descriptionInput.style.whiteSpace = 'normal';
                    descriptionInput.style.height = 'auto';
                    var scrollH = descriptionInput.scrollHeight;
                    descriptionInput.style.height = Math.max(42, Math.min(scrollH + 4, 200)) + 'px';
                }
                if (categoryInput) {
                    var cat = item.category || 'DM';
                    if (cat !== 'DM' && cat !== 'MDL' && cat !== 'Other supplies') cat = 'DM';
                    categoryInput.value = cat;
                }
                if (uomInput) uomInput.value = item.uom || '';
                if (unitCostInput) unitCostInput.value = item.cost || '';
                if (reorderLevelInput) reorderLevelInput.value = item.reorderLevel || 0;
            }

            descriptionInput.addEventListener('input', function() {
                var searchText = this.value.trim();
                dropdown.innerHTML = '';

                if (!searchText) {
                    dropdown.style.display = 'none';
                    return;
                }

                var searchLower = searchText.toLowerCase();
                var filtered = itemsData.filter(function(item) {
                    return item.nameLower.indexOf(searchLower) === 0;
                });

                if (filtered.length === 0) {
                    dropdown.style.display = 'none';
                    return;
                }

                // Deduplicate by name
                var seen = {};
                var uniqueFiltered = filtered.filter(function(item) {
                    var lower = item.nameLower;
                    if (seen[lower]) return false;
                    seen[lower] = true;
                    return true;
                });

                uniqueFiltered.forEach(function(item) {
                    var option = document.createElement('div');
                    option.style.cssText = [
                        'padding: 10px 12px; cursor: pointer;',
                        'border-bottom: 1px solid #f0f0f0;'
                    ].join('');
                    option.textContent = item.name;
                    option.addEventListener('mouseover', function() {
                        this.style.backgroundColor = '#f5f5f5';
                    });
                    option.addEventListener('mouseout', function() {
                        this.style.backgroundColor = 'transparent';
                    });
                    option.addEventListener('click', function() {
                        syncFromSelection(item);
                        dropdown.style.display = 'none';
                    });
                    dropdown.appendChild(option);
                });

                dropdown.style.display = 'block';
            });

            descriptionInput.addEventListener('blur', function() {
                setTimeout(function() { dropdown.style.display = 'none'; }, 200);
            });

            descriptionInput.addEventListener('focus', function() {
                if (this.value.trim()) {
                    this.dispatchEvent(new Event('input'));
                }
            });
        }

        function bindRowActions(row) {
            var body = row.querySelector('.item-row-body');
            var toggleButton = row.querySelector('.item-toggle-button');
            var removeButton = row.querySelector('.remove-item-button');
            var container = document.getElementById('receiving-items');

            toggleButton.addEventListener('click', function() {
                if (body.style.display === 'none') {
                    body.style.display = '';
                    toggleButton.textContent = 'Hide';
                } else {
                    body.style.display = 'none';
                    toggleButton.textContent = 'Show';
                }
            });

            removeButton.addEventListener('click', function() {
                row.remove();
                updateIndexes();
                if (currentCodePrefix) {
                    renumberAllItemCodes();
                }
            });

            // Clear duplicate error on input
            var codeInput = row.querySelector('.item-code-input');
            if (codeInput) {
                codeInput.addEventListener('input', function() {
                    this.dataset.generated = 'false';
                    this.style.borderColor = '';
                    var errorSpan = this.parentElement.querySelector('.product-code-error');
                    if (errorSpan) errorSpan.style.display = 'none';
                });
            }
        }

        function bindExpiryFix(row) {
            var expiryInput = row.querySelector('.item-expiry-input');
            if (!expiryInput) return;

            expiryInput.addEventListener('blur', function() {
                var val = this.value;
                if (!val) return;
                var parts = val.split('-');
                if (parts.length !== 3) return;
                var year = parseInt(parts[0], 10);
                var month = parseInt(parts[1], 10);
                var day = parseInt(parts[2], 10);
                if (isNaN(year) || isNaN(month) || isNaN(day)) return;

                var lastDay = new Date(year, month, 0).getDate();
                if (day > lastDay) {
                    var fixedMonth = String(month).padStart(2, '0');
                    var fixedDay = String(lastDay).padStart(2, '0');
                    this.value = year + '-' + fixedMonth + '-' + fixedDay;
                }
            });
        }

        function bindDescriptionExpand(row) {
            var desc = row.querySelector('.item-description-input');
            if (!desc) return;

            function autoExpand() {
                desc.classList.add('expanded');
                desc.style.whiteSpace = 'normal';
                desc.style.height = 'auto';
                var scrollH = desc.scrollHeight;
                var targetHeight = Math.max(42, Math.min(scrollH + 4, 200));
                desc.style.height = targetHeight + 'px';
                desc.title = desc.value || '';
            }

            function collapse() {
                desc.classList.remove('expanded');
                desc.style.whiteSpace = 'nowrap';
                desc.style.height = '42px';
                desc.title = desc.value || '';
                desc.scrollTop = 0;
            }

            desc.addEventListener('focus', autoExpand);
            desc.addEventListener('click', autoExpand);
            desc.addEventListener('input', autoExpand);
            desc.addEventListener('blur', collapse);

            desc.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });

            if (desc.value) {
                desc.title = desc.value;
            }
        }

        function recalcNextSeq() {
            var container = document.getElementById('receiving-items');
            var maxNewSeqs = {};
            Array.from(container.querySelectorAll('.item-code-input')).forEach(function(input) {
                var val = input.value || '';
                var matchNew = val.match(/^([A-Za-z]+)-\d{2}-\d{2}-(\d{4})$/);
                if (matchNew) {
                    var prefix = matchNew[1];
                    var seq = parseInt(matchNew[2], 10);
                    if (!maxNewSeqs[prefix] || seq > maxNewSeqs[prefix]) {
                        maxNewSeqs[prefix] = seq;
                    }
                }
            });

            Object.keys(maxNewSeqs).forEach(function(prefix) {
                codeSequences[prefix] = maxNewSeqs[prefix] + 1;
            });
        }

        function renumberAllItemCodes() {
            var container = document.getElementById('receiving-items');
            var prefix = currentCodePrefix;
            if (!prefix) return;

            var inputs = Array.from(container.querySelectorAll('.item-code-input'));
            var dbSeq = programSequences[prefix] || 0;
            var seq = dbSeq;

            inputs.forEach(function(input) {
                if (input.dataset.generated === 'true') {
                    input.value = prefix + '-' + currentYear + '-' + currentMonth + '-' + String(seq).padStart(4, '0');
                    seq++;
                }
            });

            codeSequences[prefix] = seq;
        }

        function updateIndexes() {
            var container = document.getElementById('receiving-items');
            Array.from(container.querySelectorAll('.receiving-item-row')).forEach(function(row, index) {
                row.dataset.index = index;
                row.querySelector('.item-row-title').textContent = 'Item ' + (index + 1);

                row.querySelectorAll('input, select, textarea').forEach(function(field) {
                    var fieldName = field.name;
                    field.name = fieldName.replace(/items\[\d+\]/, 'items[' + index + ']');
                });

                var deleteButton = row.querySelector('.remove-item-button');
                if (index === 0) {
                    deleteButton.style.display = 'none';
                } else {
                    deleteButton.style.display = '';
                }
            });
            recalcNextSeq();
        }

        function addItemRow() {
            var template = document.getElementById('receiving-item-template');
            var clone = template.content.cloneNode(true);
            var row = clone.querySelector('.receiving-item-row');
            var container = document.getElementById('receiving-items');

            recalcNextSeq();

            var codeInput = row.querySelector('.item-code-input');
            if (codeInput) {
                codeInput.value = generateNextCode();
                codeInput.dataset.generated = 'true';
            }

            bindAutocomplete(row);
            bindRowActions(row);
            bindExpiryFix(row);
            bindDescriptionExpand(row);
            container.appendChild(row);
            updateIndexes();
        }

        // Initialize existing rows
        Array.from(document.querySelectorAll('#receiving-items .receiving-item-row')).forEach(function(row) {
            bindAutocomplete(row);
            bindRowActions(row);
            bindExpiryFix(row);
            bindDescriptionExpand(row);
        });
        updateIndexes();
        recalcNextSeq();

        // Add item button
        document.getElementById('add-receiving-item-button').addEventListener('click', addItemRow);

        // ---- Dirty-form / leave-site guard ----
        var formDirty = false;
        var isSubmitting = false; // Add this to track submission
        function markDirty() { formDirty = true; }

        document.getElementById('receivingForm').querySelectorAll('input, select, textarea').forEach(function(el) {
            el.addEventListener('input', markDirty);
            el.addEventListener('change', markDirty);
        });

        window.addEventListener('beforeunload', function(e) {
            // Only show warning if dirty and NOT submitting
            if (formDirty && !isSubmitting) { 
                e.preventDefault(); 
                e.returnValue = ''; 
            }
        });

        document.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function(e) {
                // If submitting, allow navigation without confirmation
                if (isSubmitting) return; 
                
                if (formDirty && !confirm('You have unsaved changes. Leaving this page will discard them. Are you sure you want to leave?')) {
                    e.preventDefault();
                }
            });
        });

        document.getElementById('receivingForm').addEventListener('submit', function(e) {
            // Mark the form as submitting immediately so beforeunload does not
            // warn while the browser is sending a valid save request.
            isSubmitting = true;

            // Duplicate product code check
            var codes = [];
            var hasDuplicate = false;

            document.querySelectorAll('.item-code-input').forEach(function(input) {
                var errorSpan = input.parentElement.querySelector('.product-code-error');
                input.style.borderColor = '';
                if (errorSpan) errorSpan.style.display = 'none';
            });

            document.querySelectorAll('.item-code-input').forEach(function(input) {
                var val = input.value.trim();
                if (val) codes.push({ val: val, input: input });
            });

            var seen = {};
            codes.forEach(function(entry) {
                seen[entry.val] = (seen[entry.val] || 0) + 1;
            });

            codes.forEach(function(entry) {
                if (seen[entry.val] > 1) {
                    hasDuplicate = true;
                    entry.input.style.borderColor = 'var(--danger)';
                    var errorSpan = entry.input.parentElement.querySelector('.product-code-error');
                    if (errorSpan) {
                        errorSpan.textContent = 'Duplicate product code: "' + entry.val + '"';
                        errorSpan.style.display = 'block';
                    }
                    // Expand the row if it is collapsed
                    var row = entry.input.closest('.receiving-item-row');
                    if (row) {
                        var body = row.querySelector('.item-row-body');
                        var toggle = row.querySelector('.item-toggle-button');
                        if (body && body.style.display === 'none') {
                            body.style.display = '';
                            if (toggle) toggle.textContent = 'Hide';
                        }
                    }
                }
            });

            if (hasDuplicate) {
                isSubmitting = false;
                e.preventDefault();
                // Scroll to first error
                var first = document.querySelector('.product-code-error[style*="block"]');
                if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            formDirty = false;
        });

        var receivingItemsContainer = document.getElementById('receiving-items');
        new MutationObserver(function() {
            receivingItemsContainer.querySelectorAll('input, select, textarea').forEach(function(el) {
                el.removeEventListener('input', markDirty);
                el.removeEventListener('change', markDirty);
                el.addEventListener('input', markDirty);
                el.addEventListener('change', markDirty);
            });
        }).observe(receivingItemsContainer, { childList: true, subtree: true });
        // ---- End guard ----
    })();

    // ════════════════════════════════════════════════════════════════
    // EXCEL IMPORT PANEL
    // ════════════════════════════════════════════════════════════════
    (function () {
        // --- Element refs ---
        var toggleBtn          = document.getElementById('toggleImportPanel');
        var panelBody          = document.getElementById('importPanelBody');
        var fileInput          = document.getElementById('excelImportFile');
        var dropZone           = document.getElementById('importDropZone');
        var loadingEl          = document.getElementById('importLoading');
        var globalErr          = document.getElementById('importGlobalError');
        var rowErrSummary      = document.getElementById('importRowErrorSummary');
        var rowErrList         = document.getElementById('importRowErrorList');
        var previewContainer   = document.getElementById('importPreviewContainer');
        var previewBody        = document.getElementById('importPreviewBody');
        var previewCount       = document.getElementById('importPreviewCount');
        var confirmBtn         = document.getElementById('importConfirmBtn');
        var cancelBtn          = document.getElementById('importCancelBtn');
        var selectedFileNameEl = document.getElementById('selectedFileName');

        var importedRows       = [];   // rows returned by the server
        var importedHeaderData = null; // detected PO, supplier, date, program etc.
        var importUrl          = '{{ route("receivings.import-preview") }}';

        // ── Toggle open/close ────────────────────────────────────────
        toggleBtn.addEventListener('click', function () {
            panelBody.style.display = (panelBody.style.display === 'none') ? 'block' : 'none';
        });

        // ── File input change ────────────────────────────────────────
        fileInput.addEventListener('change', function () {
            if (this.files[0]) handleFile(this.files[0]);
        });

        // ── Drag-and-drop ────────────────────────────────────────────
        dropZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropZone.style.borderColor  = 'var(--primary, #2563eb)';
            dropZone.style.background   = 'rgba(37,99,235,0.04)';
        });
        dropZone.addEventListener('dragleave', function () {
            dropZone.style.borderColor = '';
            dropZone.style.background  = '';
        });
        dropZone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropZone.style.borderColor = '';
            dropZone.style.background  = '';
            var file = e.dataTransfer.files[0];
            if (file) handleFile(file);
        });

        // ── Reset panel state ────────────────────────────────────────
        function resetState() {
            importedRows                   = [];
            importedHeaderData             = null;
            loadingEl.style.display        = 'none';
            globalErr.style.display        = 'none';
            globalErr.innerHTML            = '';
            rowErrSummary.style.display    = 'none';
            rowErrList.innerHTML           = '';
            previewContainer.style.display = 'none';
            previewBody.innerHTML          = '';
            previewCount.innerHTML         = '';
            var headerBox = document.getElementById('importHeaderPreview');
            if (headerBox) headerBox.style.display = 'none';
        }

        // ── Handle a picked/dropped file ─────────────────────────────
        function handleFile(file) {
            resetState();
            selectedFileNameEl.textContent = file.name;
            loadingEl.style.display = 'block';

            var fd = new FormData();
            fd.append('import_file', file);

            // Grab CSRF token from the existing form
            var tokenInput = document.querySelector('#receivingForm input[name="_token"]');
            if (tokenInput) fd.append('_token', tokenInput.value);

            fetch(importUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
            })
            .then(function (res) {
                return res.json().then(function (data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function (result) {
                loadingEl.style.display = 'none';

                if (!result.ok || !result.data.success) {
                    var msg = escHtml(result.data.message || 'Import failed.');
                    if (result.data.details && result.data.details.length) {
                        msg += '<ul style="margin:0.4rem 0 0 1rem;">' +
                            result.data.details.map(function (d) {
                                return '<li>' + escHtml(d) + '</li>';
                            }).join('') + '</ul>';
                    }
                    globalErr.innerHTML         = '<strong>Import Error:</strong> ' + msg;
                    globalErr.style.display     = 'block';
                    return;
                }

                importedRows       = result.data.rows || [];
                importedHeaderData = result.data.headerData || null;
                renderPreview(importedRows, result.data.rowErrors || [], importedHeaderData);
            })
            .catch(function (err) {
                loadingEl.style.display  = 'none';
                globalErr.textContent    = 'Network error: ' + err.message;
                globalErr.style.display  = 'block';
            });
        }

        // ── Render the preview table ─────────────────────────────────
        function renderPreview(rows, rowErrors, headerData) {
            // Display detected document / header details if any
            var headerBox     = document.getElementById('importHeaderPreview');
            var headerDetails = document.getElementById('importHeaderDetails');
            if (headerBox && headerDetails) {
                var labels = {
                    po_number:           'PO / Source Doc No.',
                    supplier_dealer:     'Supplier / Dealer',
                    ics_ptr_ris:         'ICS / PTR / RIS',
                    document_date:       'Date (PTR/RIS/ICS)',
                    date_received:       'Date Received',
                    stock_keeping_unit:  'Program (SKU)',
                    program_coordinator: 'Program Coordinator',
                };
                var items = [];
                if (headerData) {
                    for (var k in labels) {
                        if (headerData[k]) {
                            items.push(
                                '<div><span style="color:var(--text-muted,#64748b);font-weight:500;">' +
                                labels[k] + ':</span> <strong style="color:var(--text,#1e293b);">' +
                                escHtml(headerData[k]) + '</strong></div>'
                            );
                        }
                    }
                }
                if (items.length > 0) {
                    headerDetails.innerHTML = items.join('');
                    headerBox.style.display = 'block';
                } else {
                    headerBox.style.display = 'none';
                }
            }

            // Build a map: spreadsheet data row index (0-based) → error messages
            // Server returns rowErrors[i].row = spreadsheet row number (header=1, first data row=2)
            var errorsByIndex = {};
            rowErrors.forEach(function (re) {
                var idx = re.row - 2;   // convert to 0-based rows array index
                if (idx >= 0) errorsByIndex[idx] = re.errors;
            });

            previewBody.innerHTML = '';
            rows.forEach(function (row, i) {
                var hasErr = !!errorsByIndex[i];
                var tr = document.createElement('tr');
                tr.style.cssText = hasErr
                    ? 'background:rgba(220,38,38,0.06);border-left:3px solid var(--danger,#dc2626);'
                    : (i % 2 === 0 ? '' : 'background:var(--surface-raised,#f8fafc);');

                var tdBase = 'padding:0.5rem 0.75rem;border-bottom:1px solid var(--border,#f0f0f0);vertical-align:top;';
                var cells = [
                    escHtml(String(i + 1)),
                    escHtml(String(row.item_description   ?? '')),
                    escHtml(String(row.item_code          ?? '')),
                    escHtml(String(row.category           ?? '')),
                    escHtml(String(row.uom                ?? '')),
                    escHtml(String(row.lot_number         ?? '')),
                    escHtml(String(row.expiry_date        ?? '')),
                    escHtml(String(row.quantity_received  ?? '')),
                    escHtml(String(row.unit_cost          ?? '')),
                    escHtml(String(row.location           ?? '')),
                    escHtml(String(row.reorder_level      ?? '')),
                ];

                tr.innerHTML = cells.map(function (c, ci) {
                    return '<td style="' + tdBase + (ci === 1 ? 'max-width:220px;word-break:break-word;' : '') + '">' + c + '</td>';
                }).join('');

                // Status cell (last) — rendered as HTML, not escaped
                var statusTd = document.createElement('td');
                statusTd.style.cssText = tdBase;
                if (hasErr) {
                    statusTd.innerHTML =
                        '<span style="color:var(--danger,#dc2626);font-weight:600;font-size:0.78rem;" title="' +
                        escHtml((errorsByIndex[i] || []).join('; ')) +
                        '">⚠ Error</span>';
                } else {
                    statusTd.innerHTML =
                        '<span style="color:var(--success,#16a34a);font-size:0.78rem;">✓ OK</span>';
                }
                tr.appendChild(statusTd);
                previewBody.appendChild(tr);
            });

            // Row count label
            var errCount = rowErrors.length;
            var okCount  = rows.length - errCount;
            previewCount.innerHTML =
                '<span style="color:var(--success,#16a34a);">✓ ' + okCount + ' valid</span>' +
                (errCount
                    ? ' &nbsp;<span style="color:var(--danger,#dc2626);">⚠ ' + errCount + ' with errors</span>'
                    : '') +
                ' &nbsp;(total: ' + rows.length + ' rows)';
            previewContainer.style.display = 'block';

            // Row-error summary list
            if (errCount > 0) {
                rowErrList.innerHTML = '';
                rowErrors.forEach(function (re) {
                    var li        = document.createElement('li');
                    li.innerHTML  = '<strong>Row ' + re.row + ':</strong> ' +
                        re.errors.map(escHtml).join('; ');
                    rowErrList.appendChild(li);
                });
                rowErrSummary.style.display = 'block';
            }
        }

        // ── "Fill Form with These Items" button ──────────────────────
        confirmBtn.addEventListener('click', function () {
            if (!importedRows.length) return;

            var hasErrors = (rowErrSummary.style.display !== 'none');
            if (hasErrors) {
                if (!confirm(
                    'Some rows have validation warnings. They will still be added to the form — ' +
                    'you can correct them manually before saving. Proceed?'
                )) return;
            }

            // Auto-fill primary receiving header fields if present in imported file
            if (importedHeaderData) {
                var setVal = function(name, val) {
                    if (!val) return;
                    var inp = document.querySelector('#receivingForm [name="' + name + '"]');
                    if (inp) {
                        inp.value = val;
                        inp.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                };
                setVal('po_number',           importedHeaderData.po_number);
                setVal('ics_ptr_ris',         importedHeaderData.ics_ptr_ris);
                setVal('document_date',       importedHeaderData.document_date);
                setVal('date_received',       importedHeaderData.date_received);
                setVal('stock_keeping_unit',  importedHeaderData.stock_keeping_unit);
                setVal('program_coordinator', importedHeaderData.program_coordinator);

                // Auto-select matching supplier if available
                if (importedHeaderData.supplier_dealer) {
                    var supSelect = document.querySelector('#receivingForm select[name="supplier_id"]');
                    if (supSelect) {
                        var needle = importedHeaderData.supplier_dealer.toLowerCase().trim();
                        for (var s = 0; s < supSelect.options.length; s++) {
                            var opt = supSelect.options[s];
                            var optText = opt.text.toLowerCase().trim();
                            if (opt.value && (optText === needle || (needle.length > 2 && optText.includes(needle)) || (optText.length > 2 && needle.includes(optText)))) {
                                supSelect.value = opt.value;
                                supSelect.dispatchEvent(new Event('change', { bubbles: true }));
                                break;
                            }
                        }
                    }
                }
            }

            var container   = document.getElementById('receiving-items');
            var addBtn      = document.getElementById('add-receiving-item-button');
            var allRows     = container.querySelectorAll('.receiving-item-row');

            // Remove all rows except the first, then clear the first
            allRows.forEach(function (r, i) { if (i > 0) r.remove(); });
            var firstRow = container.querySelector('.receiving-item-row');
            if (firstRow) clearRow(firstRow);

            // Populate
            importedRows.forEach(function (rowData, i) {
                var targetRow;
                if (i === 0) {
                    targetRow = container.querySelector('.receiving-item-row');
                } else {
                    if (addBtn) addBtn.click();                                // uses existing addItemRow()
                    var current = container.querySelectorAll('.receiving-item-row');
                    targetRow   = current[current.length - 1];
                }
                if (targetRow) fillRow(targetRow, rowData);
            });

            // Update next code sequence based on imported items so subsequent manual "Add Item" won't collide
            if (typeof recalcNextSeq === 'function') {
                recalcNextSeq();
            }

            // Collapse the panel and scroll to the form
            panelBody.style.display = 'none';
            document.getElementById('receivingForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        function clearRow(row) {
            ['.item-code-input', '.item-description-input', '.item-uom-input',
             '.item-lot-input', '.item-expiry-input', '.item-quantity-input',
             '.item-unit-cost-input', '.item-location-input'].forEach(function (cls) {
                var el = row.querySelector(cls);
                if (el) el.value = '';
            });
            var rl = row.querySelector('.item-reorder-level-input');
            if (rl) rl.value = '0';
            var ci = row.querySelector('.item-code-input');
            if (ci) ci.dataset.generated = 'false';
        }

        function fillRow(row, data) {
            function set(cls, val) {
                var el = row.querySelector(cls);
                if (el) el.value = (val !== null && val !== undefined) ? val : '';
            }

            var codeInput = row.querySelector('.item-code-input');
            if (codeInput) {
                var codeVal = (data.item_code !== null && data.item_code !== undefined) ? String(data.item_code).trim() : '';
                if (codeVal !== '') {
                    codeInput.value = codeVal;
                }
                // Crucial: mark as not generated so selecting a program doesn't overwrite imported product codes
                codeInput.dataset.generated = 'false';
            }

            set('.item-description-input',  data.item_description);
            var descInput = row.querySelector('.item-description-input');
            if (descInput) {
                descInput.title = data.item_description || '';
            }
            set('.item-uom-input',           data.uom);
            set('.item-lot-input',           data.lot_number);
            set('.item-expiry-input',        data.expiry_date);
            set('.item-quantity-input',      data.quantity_received);
            set('.item-unit-cost-input',     data.unit_cost);
            set('.item-location-input',      data.location);
            set('.item-reorder-level-input', data.reorder_level !== undefined ? data.reorder_level : 0);

            var catSel = row.querySelector('.item-category-input');
            if (catSel && data.category) catSel.value = data.category;
        }

        // ── "Clear" button ───────────────────────────────────────────
        cancelBtn.addEventListener('click', function () {
            resetState();
            fileInput.value                = '';
            selectedFileNameEl.textContent = '';
        });

        // ── Helpers ──────────────────────────────────────────────────
        function escHtml(str) {
            return String(str)
                .replace(/&/g,  '&amp;')
                .replace(/</g,  '&lt;')
                .replace(/>/g,  '&gt;')
                .replace(/"/g,  '&quot;');
        }

        // Ensure @keyframes spin exists (used by the loading spinner)
        if (!document.getElementById('importSpinStyle')) {
            var s = document.createElement('style');
            s.id  = 'importSpinStyle';
            s.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
            document.head.appendChild(s);
        }
    })();
    // ════════════════════════════════════════════════════════════════
    // END EXCEL IMPORT PANEL
    // ════════════════════════════════════════════════════════════════
    </script>

    @endpush
@endsection

