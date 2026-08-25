@extends('layouts.app')

@section('title', 'Edit Receiving')
@section('pageHeading', 'Edit Receiving')
@section('pageSubheading', 'Update receiving slip details. Stock will be adjusted automatically.')

@section('content')
    <section class="card">

        <div class="section-header">
            <a href="{{ route('receivings.view', $receiving) }}" class="btn btn-secondary">Back to Receiving</a>
        </div>

        <form action="{{ route('receivings.update', $receiving) }}" method="POST" class="stack" id="editReceivingForm">
            @csrf
            @method('PUT')

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Purchase Order / Source Document No.</label>
                    <input name="po_number" value="{{ old('po_number', $receiving->po_number) }}" />
                </div>
                <div class="form-group">
                    <label>Supplier / Dealer</label>
                    <select name="supplier_id" required>
                        <option value="">Select supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $receiving->supplier_id) == $supplier->id)>{{ $supplier->company_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>ICS / PTR / RIS</label>
                    <input name="ics_ptr_ris" value="{{ old('ics_ptr_ris', $receiving->ics_ptr_ris) }}" />
                </div>
                <div class="form-group">
                    <label>Date (PTR/RIS/ICS)</label>
                    <input type="date" name="document_date" value="{{ old('document_date', $receiving->document_date?->toDateString()) }}" />
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label>Date Received</label>
                    <input type="date" name="date_received" value="{{ old('date_received', $receiving->date_received->toDateString()) }}" required />
                </div>
                <div class="form-group">
                    <label>Received By</label>
                    <input name="received_by" value="{{ old('received_by', $receiving->received_by) }}" />
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input name="location" value="{{ old('location', $receiving->location) }}" />
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Stock Keeping Unit (Program)</label>
                    <div style="position:relative;">
                        <input name="stock_keeping_unit" id="programInput" value="{{ old('stock_keeping_unit', $receiving->stock_keeping_unit) }}" autocomplete="off" style="width:100%;" />
                        <div id="programDropdown" style="position:absolute;top:100%;left:0;width:100%;z-index:1000;display:none;"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Program Coordinator</label>
                    <div style="position:relative;">
                        <input name="program_coordinator" id="coordinatorInput" value="{{ old('program_coordinator', $receiving->program_coordinator) }}" autocomplete="off" style="width:100%;" />
                        <div id="coordinatorDropdown" style="position:absolute;top:100%;left:0;width:100%;z-index:1000;display:none;"></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="2">{{ old('notes', $receiving->notes) }}</textarea>
            </div>

            <div>
                <h3 class="section-card-title">Received Items <span style="color: var(--danger);">*</span></h3>
                @error('items')
                    <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror

                <div id="receiving-items" class="stack">
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
                                <input type="hidden" class="receiving-item-id" name="items[0][receiving_item_id]" value="" />
                                <input type="hidden" class="item-id-hidden" name="items[0][item_id]" value="" />
                                <div class="form-grid-4">
                                    <div class="form-group" style="position:relative;">
                                        <label>Product Code</label>
                                        <input class="item-code-input" name="items[0][item_code]" readonly style="background:var(--surface-strong);cursor:not-allowed;" />
                                    </div>
                                    <div class="form-group" style="position:relative;">
                                        <label>Item Description</label>
                                        <input class="item-description-input" name="items[0][item_description]" required autocomplete="off" />
                                    </div>
                                    <div class="form-group">
                                        <label>Category</label>
                                        <input class="item-category-input" name="items[0][category]" />
                                    </div>
                                    <div class="form-group">
                                        <label>UOM</label>
                                        <input class="item-uom-input" name="items[0][uom]" />
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
                            </div>
                        </div>
                    </template>

                    @php $oldItems = old('items'); @endphp

                    @foreach($receiving->items as $index => $ri)
                        @php
                            $oi = $oldItems[$index] ?? null;
                        @endphp
                        <div class="section-card receiving-item-row" data-index="{{ $index }}">
                            <div class="item-row-header">
                                <div class="item-row-title">Item {{ $index + 1 }}</div>
                                <div class="item-row-actions">
                                    <button type="button" class="btn btn-link item-toggle-button">Hide</button>
                                    <button type="button" class="btn btn-danger remove-item-button" @if($index === 0) style="display:none;" @endif>Delete</button>
                                </div>
                            </div>
                            <div class="item-row-body">
                                <input type="hidden" class="receiving-item-id" name="items[{{ $index }}][receiving_item_id]" value="{{ $ri->id }}" />
                                <input type="hidden" class="item-id-hidden" name="items[{{ $index }}][item_id]" value="{{ $oi['item_id'] ?? $ri->item_id }}" />
                                <div class="form-grid-4">
                                    <div class="form-group" style="position:relative;">
                                        <label>Product Code</label>
                                        <input class="item-code-input" name="items[{{ $index }}][item_code]" value="{{ $oi['item_code'] ?? $ri->item?->item_code }}" readonly style="background:var(--surface-strong);cursor:not-allowed;" />
                                    </div>
                                    <div class="form-group" style="position:relative;">
                                        <label>Item Description</label>
                                        <input class="item-description-input" name="items[{{ $index }}][item_description]" value="{{ $oi['item_description'] ?? $ri->item_description }}" required autocomplete="off" />
                                    </div>
                                    <div class="form-group">
                                        <label>Category</label>
                                        <input class="item-category-input" name="items[{{ $index }}][category]" value="{{ $oi['category'] ?? $ri->category }}" />
                                    </div>
                                    <div class="form-group">
                                        <label>UOM</label>
                                        <input class="item-uom-input" name="items[{{ $index }}][uom]" value="{{ $oi['uom'] ?? $ri->uom }}" />
                                    </div>
                                </div>
                                <div class="form-grid-4">
                                    <div class="form-group">
                                        <label>Lot / Batch / Model No.</label>
                                        <input class="item-lot-input" name="items[{{ $index }}][lot_number]" value="{{ $oi['lot_number'] ?? $ri->lot_number }}" />
                                    </div>
                                    <div class="form-group">
                                        <label>Expiry Date / Useful Life</label>
                                        <input type="date" class="item-expiry-input" name="items[{{ $index }}][expiry_date]" value="{{ $oi['expiry_date'] ?? $ri->expiry_date?->toDateString() }}" />
                                    </div>
                                    <div class="form-group">
                                        <label>Quantity Received</label>
                                        <input type="number" class="item-quantity-input" name="items[{{ $index }}][quantity_received]" value="{{ $oi['quantity_received'] ?? $ri->quantity_received }}" min="1" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Unit Cost</label>
                                        <input type="number" step="0.01" class="item-unit-cost-input" name="items[{{ $index }}][unit_cost]" value="{{ $oi['unit_cost'] ?? $ri->unit_cost }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-receiving-item-button" class="btn btn-secondary" style="margin-top:0.75rem;">Add another item</button>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('receivings.view', $receiving) }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </section>

    <datalist id="item-options-receiving" style="display:none;">
        @foreach($items as $item)
            <option value="{{ $item->name }}" data-code="{{ $item->item_code }}" data-id="{{ $item->id }}" data-category="{{ $item->category }}" data-uom="{{ $item->unit }}" data-cost="{{ $item->unit_cost }}"></option>
        @endforeach
    </datalist>
    <datalist id="program-options" style="display:none;">
        @foreach($programs as $program)
            <option value="{{ $program->name }}"></option>
        @endforeach
    </datalist>
    <datalist id="coordinator-options" style="display:none;">
        @foreach($coordinators as $coordinator)
            <option value="{{ $coordinator->full_name }}" data-programs="{{ $coordinator->assigned_programs }}"></option>
        @endforeach
    </datalist>

    @push('scripts')
    <script>
    (function() {
        var itemsData = Array.from(document.querySelectorAll('#item-options-receiving option')).map(function(opt) {
            return {
                id: opt.dataset.id,
                name: opt.value,
                nameLower: opt.value.toLowerCase(),
                code: opt.dataset.code,
                category: opt.dataset.category,
                uom: opt.dataset.uom,
                cost: opt.dataset.cost,
            };
        });

        var programsData = Array.from(document.querySelectorAll('#program-options option')).map(function(opt) {
            return { name: opt.value, nameLower: opt.value.toLowerCase() };
        });
        var coordinatorsData = Array.from(document.querySelectorAll('#coordinator-options option')).map(function(opt) {
            return { name: opt.value, nameLower: opt.value.toLowerCase(), assignedPrograms: opt.dataset.programs || '' };
        });

        function bindAutocompleteList(input, dataList, dropdown, onSelect) {
            function showOptions(q) {
                dropdown.innerHTML = '';
                var lower = q.toLowerCase().trim();
                var seen = {};
                var filtered = (lower ? dataList.filter(function(i) { return i.nameLower.indexOf(lower) !== -1; }) : dataList)
                    .filter(function(i) { if (seen[i.nameLower]) return false; seen[i.nameLower] = true; return true; });
                if (!filtered.length) { dropdown.style.display = 'none'; return; }
                Object.assign(dropdown.style, { background: 'var(--surface,#fff)', border: '1px solid var(--border,#ddd)', maxHeight: '200px', overflowY: 'auto', boxShadow: '0 4px 6px rgba(0,0,0,.1)', marginTop: '4px' });
                filtered.forEach(function(item) {
                    var opt = document.createElement('div');
                    opt.style.cssText = 'padding:10px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0;';
                    opt.textContent = item.name;
                    opt.addEventListener('mouseover', function() { this.style.background = '#f5f5f5'; });
                    opt.addEventListener('mouseout',  function() { this.style.background = 'transparent'; });
                    opt.addEventListener('click', function() { input.value = item.name; dropdown.style.display = 'none'; if (onSelect) onSelect(item); });
                    dropdown.appendChild(opt);
                });
                dropdown.style.display = 'block';
            }
            input.addEventListener('input', function() { showOptions(this.value); });
            input.addEventListener('focus', function() { showOptions(this.value); });
            input.addEventListener('blur',  function() { setTimeout(function() { dropdown.style.display = 'none'; }, 200); });
        }

        var programInput      = document.getElementById('programInput');
        var coordinatorInput  = document.getElementById('coordinatorInput');
        var programDropdown   = document.getElementById('programDropdown');
        var coordinatorDropdown = document.getElementById('coordinatorDropdown');

        if (programInput && programDropdown) {
            bindAutocompleteList(programInput, programsData, programDropdown, function(item) {
                var matched = coordinatorsData.find(function(c) { return c.assignedPrograms.toLowerCase().indexOf(item.nameLower) !== -1; });
                if (matched && coordinatorInput && !coordinatorInput.value.trim()) coordinatorInput.value = matched.name;
            });
        }
        if (coordinatorInput && coordinatorDropdown) {
            bindAutocompleteList(coordinatorInput, coordinatorsData, coordinatorDropdown, function(item) {
                if (item.assignedPrograms && programInput && !programInput.value.trim()) {
                    programInput.value = item.assignedPrograms.split(', ')[0] || '';
                }
            });
        }

        function bindItemAutocomplete(row) {
            var descInput     = row.querySelector('.item-description-input');
            var codeInput     = row.querySelector('.item-code-input');
            var categoryInput = row.querySelector('.item-category-input');
            var uomInput      = row.querySelector('.item-uom-input');
            var costInput     = row.querySelector('.item-unit-cost-input');
            var itemIdHidden  = row.querySelector('.item-id-hidden');

            if (!descInput) return;

            var dd = document.createElement('div');
            dd.className = 'autocomplete-dropdown';
            dd.style.cssText = 'position:absolute;background:white;border:1px solid #ddd;max-height:200px;overflow-y:auto;width:100%;z-index:1000;display:none;box-shadow:0 4px 6px rgba(0,0,0,.1);top:100%;left:0;margin-top:4px;';
            descInput.parentElement.style.position = 'relative';
            descInput.parentElement.appendChild(dd);

            function syncItem(item) {
                descInput.value = item.name;
                if (codeInput)     codeInput.value     = item.code || '';
                if (categoryInput) categoryInput.value = item.category || '';
                if (uomInput)      uomInput.value      = item.uom || '';
                if (costInput)     costInput.value     = item.cost || '';
                if (itemIdHidden)  itemIdHidden.value  = item.id || '';
            }

            function showOptions(q) {
                dd.innerHTML = '';
                var lower = q.toLowerCase().trim();
                if (!lower) { dd.style.display = 'none'; return; }
                var seen = {};
                var filtered = itemsData.filter(function(i) { return i.nameLower.indexOf(lower) !== -1; })
                    .filter(function(i) { if (seen[i.nameLower]) return false; seen[i.nameLower] = true; return true; });
                if (!filtered.length) { dd.style.display = 'none'; return; }
                filtered.forEach(function(item) {
                    var opt = document.createElement('div');
                    opt.style.cssText = 'padding:10px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0;';
                    opt.textContent = item.name;
                    opt.addEventListener('mouseover', function() { this.style.background = '#f5f5f5'; });
                    opt.addEventListener('mouseout',  function() { this.style.background = 'transparent'; });
                    opt.addEventListener('click', function() { syncItem(item); dd.style.display = 'none'; });
                    dd.appendChild(opt);
                });
                dd.style.display = 'block';
            }

            descInput.addEventListener('input', function() { showOptions(this.value); });
            descInput.addEventListener('focus', function() { showOptions(this.value); });
            descInput.addEventListener('blur',  function() { setTimeout(function() { dd.style.display = 'none'; }, 200); });
        }

        function bindRowActions(row) {
            var body         = row.querySelector('.item-row-body');
            var toggleButton = row.querySelector('.item-toggle-button');
            var removeButton = row.querySelector('.remove-item-button');

            toggleButton.addEventListener('click', function() {
                var hidden = body.style.display === 'none';
                body.style.display = hidden ? '' : 'none';
                toggleButton.textContent = hidden ? 'Hide' : 'Show';
            });
            removeButton.addEventListener('click', function() { row.remove(); updateIndexes(); });
        }

        function updateIndexes() {
            Array.from(document.querySelectorAll('#receiving-items .receiving-item-row')).forEach(function(row, index) {
                row.dataset.index = index;
                row.querySelector('.item-row-title').textContent = 'Item ' + (index + 1);
                row.querySelectorAll('input, select').forEach(function(f) {
                    f.name = f.name.replace(/items\[\d+\]/, 'items[' + index + ']');
                });
                row.querySelector('.remove-item-button').style.display = index === 0 ? 'none' : '';
            });
        }

        // Init existing rows
        Array.from(document.querySelectorAll('#receiving-items .receiving-item-row')).forEach(function(row) {
            bindItemAutocomplete(row);
            bindRowActions(row);
        });
        updateIndexes();

        // Add new row
        document.getElementById('add-receiving-item-button').addEventListener('click', function() {
            var template = document.getElementById('receiving-item-template');
            var clone    = template.content.cloneNode(true);
            var row      = clone.querySelector('.receiving-item-row');
            bindItemAutocomplete(row);
            bindRowActions(row);
            document.getElementById('receiving-items').appendChild(row);
            updateIndexes();
        });

        // Dirty guard
        var formDirty = false;
        var isSubmitting = false;
        var form = document.getElementById('editReceivingForm');
        form.querySelectorAll('input, select, textarea').forEach(function(el) {
            el.addEventListener('input',  function() { formDirty = true; });
            el.addEventListener('change', function() { formDirty = true; });
        });
        window.addEventListener('beforeunload', function(e) { 
            if (formDirty && !isSubmitting) { e.preventDefault(); e.returnValue = ''; } 
        });
        document.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function(e) {
                if (isSubmitting) return;
                if (formDirty && !confirm('You have unsaved changes. Leaving this page will discard them. Are you sure?')) e.preventDefault();
            });
        });
        form.addEventListener('submit', function() { isSubmitting = true; formDirty = false; });

        new MutationObserver(function() {
            document.querySelectorAll('#receiving-items input, #receiving-items select, #receiving-items textarea').forEach(function(el) {
                el.removeEventListener('input',  function() { formDirty = true; });
                el.removeEventListener('change', function() { formDirty = true; });
                el.addEventListener('input',  function() { formDirty = true; });
                el.addEventListener('change', function() { formDirty = true; });
            });
        }).observe(document.getElementById('receiving-items'), { childList: true, subtree: true });
    })();
    </script>
    @endpush
@endsection
