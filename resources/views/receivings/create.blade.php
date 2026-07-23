@extends('layouts.app')

@section('title', 'New Receiving')
@section('pageHeading', 'New Receiving')
@section('pageSubheading', 'Record supply receipts and update stock automatically.')

@section('content')
    <section class="card">

        <div class="section-header">
            <a href="{{ route('receivings.index') }}" class="btn btn-secondary">Back to Receivings</a>
        </div>

        <form action="{{ route('receivings.store') }}" method="POST" class="stack">
            @csrf

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

            <div class="form-grid-3">
                <div class="form-group">
                    <label>Date Received</label>
                    <input type="date" name="date_received" value="{{ old('date_received', now()->toDateString()) }}" required />
                </div>
                <div class="form-group">
                    <label>Received By</label>
                    <input name="received_by" value="{{ old('received_by') }}" />
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input name="location" value="{{ old('location') }}" />
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Stock Keeping Unit (Program)</label>
                    <input name="stock_keeping_unit" value="{{ old('stock_keeping_unit') }}" />
                </div>
                <div class="form-group">
                    <label>Program Coordinator</label>
                    <input name="program_coordinator" value="{{ old('program_coordinator') }}" />
                </div>
            </div>

            <div>
                <h3 class="section-card-title">Received Items <span style="color: var(--danger);">*</span></h3>
                @error('items')
                    <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
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
                                        <input class="item-code-input" name="items[0][item_code]" readonly style="background: var(--surface-strong); cursor: not-allowed;" />
                                    </div>
                                    <div class="form-group" style="position: relative;">
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
                                        <input class="item-code-input" name="items[{{ $index }}][item_code]" value="{{ $oldItem['item_code'] ?? ($index === 0 ? ($nextItemCode ?? '') : '') }}" readonly style="background: var(--surface-strong); cursor: not-allowed;" />
                                    </div>
                                    <div class="form-group" style="position: relative;">
                                        <label>Item Description</label>
                                        <input class="item-description-input" name="items[{{ $index }}][item_description]" value="{{ $oldItem['item_description'] ?? '' }}" required autocomplete="off" />
                                    </div>
                                    <div class="form-group">
                                        <label>Category</label>
                                        <input class="item-category-input" name="items[{{ $index }}][category]" value="{{ $oldItem['category'] ?? '' }}" />
                                    </div>
                                    <div class="form-group">
                                        <label>UOM</label>
                                        <input class="item-uom-input" name="items[{{ $index }}][uom]" value="{{ $oldItem['uom'] ?? '' }}" />
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
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-receiving-item-button" class="btn btn-secondary" style="margin-top: 0.75rem;">Add another item</button>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Receiving</button>
                <a href="{{ route('receivings.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </section>

    {{-- Autocomplete data source --}}
    <datalist id="item-options-receiving" style="display:none;">
        @foreach($items as $item)
            <option value="{{ $item->name }}" data-code="{{ $item->item_code }}" data-category="{{ $item->category }}" data-uom="{{ $item->unit }}" data-cost="{{ $item->unit_cost }}"></option>
        @endforeach
    </datalist>

    @push('scripts')
    <script>
    (function() {
        var nextCodeBase = '{{ $nextItemCode }}';
        var nextSeq = parseInt('{{ substr($nextItemCode, 5) }}', 10) || 1;

        var itemsData = Array.from(document.querySelectorAll('#item-options-receiving option')).map(function(opt) {
            return {
                name: opt.value,
                nameLower: opt.value.toLowerCase(),
                code: opt.dataset.code,
                category: opt.dataset.category,
                uom: opt.dataset.uom,
                cost: opt.dataset.cost,
            };
        });

        function generateNextCode() {
            var seq = String(nextSeq).padStart(4, '0');
            nextSeq++;
            return 'ITEM-' + seq;
        }

        function createAutocompleteDropdown(descriptionInput, codeInput, categoryInput, uomInput, unitCostInput) {
            var dropdown = descriptionInput.parentElement.querySelector('.autocomplete-dropdown');
            if (dropdown) dropdown.remove();

            dropdown = document.createElement('div');
            dropdown.className = 'autocomplete-dropdown';
            dropdown.style.cssText = [
                'position: absolute; background: white; border: 1px solid #ddd;',
                'max-height: 200px; overflow-y: auto; width: 100%; z-index: 1000;',
                'display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);',
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

            if (!descriptionInput) return;

            // Set initial auto-generated code if empty
            if (codeInput && !codeInput.value) {
                codeInput.value = generateNextCode();
            }

            var dropdown = createAutocompleteDropdown(descriptionInput, codeInput, categoryInput, uomInput, unitCostInput);

            function syncFromSelection(item) {
                descriptionInput.value = item.name;
                // Product Code always keeps its auto-generated value
                if (categoryInput) categoryInput.value = item.category || '';
                if (uomInput) uomInput.value = item.uom || '';
                if (unitCostInput) unitCostInput.value = item.cost || '';
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
                    return item.nameLower.indexOf(searchLower) !== -1;
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
            });
        }

        function recalcNextSeq() {
            var container = document.getElementById('receiving-items');
            var maxSeq = 0;
            Array.from(container.querySelectorAll('.item-code-input')).forEach(function(input) {
                var val = input.value || '';
                var match = val.match(/ITEM-(\d+)/);
                if (match) {
                    var seq = parseInt(match[1], 10);
                    if (seq > maxSeq) maxSeq = seq;
                }
            });
            nextSeq = maxSeq + 1;
        }

        function updateIndexes() {
            var container = document.getElementById('receiving-items');
            Array.from(container.querySelectorAll('.receiving-item-row')).forEach(function(row, index) {
                row.dataset.index = index;
                row.querySelector('.item-row-title').textContent = 'Item ' + (index + 1);

                row.querySelectorAll('input, select').forEach(function(field) {
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

            // Set auto-generated code for the new item
            var codeInput = row.querySelector('.item-code-input');
            if (codeInput) codeInput.value = generateNextCode();

            bindAutocomplete(row);
            bindRowActions(row);
            container.appendChild(row);
            updateIndexes();
        }

        // Initialize existing rows
        Array.from(document.querySelectorAll('#receiving-items .receiving-item-row')).forEach(function(row) {
            bindAutocomplete(row);
            bindRowActions(row);
        });
        updateIndexes();
        recalcNextSeq();

        // Add item button
        document.getElementById('add-receiving-item-button').addEventListener('click', addItemRow);
    })();
    </script>
    @endpush
@endsection

