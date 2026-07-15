@extends('layouts.app')

@section('content')
    <section class="card">

        <div class="section-header">
            <div>
                <h1 class="page-heading">New Release Slip</h1>
                <p class="page-description">Record outgoing supplies with release slips and tracking details.</p>
            </div>
            <a href="{{ route('releases.index') }}" class="btn btn-secondary">Back to Releases</a>
        </div>

        <form action="{{ route('releases.store') }}" method="POST" class="stack">
            @csrf

            <div class="form-grid-3">
                <div class="form-group">
                    <label>PAS No.</label>
                    <input name="pas_number" value="{{ old('pas_number') }}">
                </div>
                <div class="form-group">
                    <label>Health Program / Coordinator</label>
                    <input name="health_program_coordinator" value="{{ old('health_program_coordinator') }}">
                </div>
                <div class="form-group">
                    <label>PTR/ITR/RIS No.</label>
                    <input name="ptr_itr_ris_no" value="{{ old('ptr_itr_ris_no') }}">
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label>PHO Code</label>
                    <input name="pho_code" value="{{ old('pho_code') }}">
                </div>
                <div class="form-group">
                    <label>Source Docs. PTR/PO No.</label>
                    <input name="source_docs_ptr_po_no" value="{{ old('source_docs_ptr_po_no') }}">
                </div>
                <div class="form-group">
                    <label>Name of Facility / End-user</label>
                    <input name="facility_name" value="{{ old('facility_name') }}">
                </div>
            </div>

            <div class="section-note">
                Received by, Date, and Status are assigned after saving.
            </div>
            <input type="hidden" name="received_by" value="{{ old('received_by', '') }}">
            <input type="hidden" name="date_released" value="{{ old('date_released', now()->toDateString()) }}">
            <input type="hidden" name="status" value="{{ old('status', 'Pending') }}">

            <div>
                <h2 class="section-title">Released Items</h2>
                <div id="release-items" class="stack">
                    <datalist id="item-options">
                        @foreach($items as $item)
                            <option value="{{ $item->name }}" data-item-id="{{ $item->id }}" data-category="{{ $item->category }}"></option>
                        @endforeach
                    </datalist>

                    @php
                        $oldItems = collect(old('items', []))->values()->all();
                        if (empty($oldItems)) {
                            $oldItems = [[
                                'item_description' => '',
                                'quantity_released' => '',
                                'uom' => '',
                                'unit_cost' => '',
                                'item_id' => '',
                            ]];
                        }
                    @endphp

                    @foreach($oldItems as $index => $oldItem)
                        <div class="section-card release-item-row" data-index="{{ $index }}">
                            <div class="item-row-header">
                                <div class="item-row-title">Item {{ $index + 1 }}</div>
                                <div class="item-row-actions">
                                    <button type="button" class="btn btn-link item-toggle-button">Hide</button>
                                    <button type="button" class="btn btn-danger remove-item-button" @if($index === 0) style="display:none;" @endif>Delete</button>
                                </div>
                            </div>
                            <div class="item-row-body">
                                <div class="form-grid-3">
                                    <div class="form-group">
                                        <label>Item Description</label>
                                        <input type="text" class="item-description-input" name="items[{{ $index }}][item_description]" value="{{ $oldItem['item_description'] ?? '' }}" autocomplete="off">
                                    </div>
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" class="item-quantity-input" name="items[{{ $index }}][quantity_released]" value="{{ $oldItem['quantity_released'] ?? '' }}" min="0" placeholder="Available: 0">
                                    </div>
                                    <div class="form-group">
                                        <label>UOM</label>
                                        <input class="item-uom-input" name="items[{{ $index }}][uom]" value="{{ $oldItem['uom'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="form-grid-3">
                                    <div class="form-group">
                                        <label>Unit Cost</label>
                                        <input class="item-unit-cost-input" type="number" step="0.01" name="items[{{ $index }}][unit_cost]" value="{{ $oldItem['unit_cost'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Product Code</label>
                                        <select class="item-id-select" name="items[{{ $index }}][item_id]">
                                            <option value="">Select product</option>
                                        </select>
                                    </div>
                                    <div></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-item-button" class="btn btn-secondary">Add another item</button>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3">{{ old('notes') }}</textarea>
            </div>

            <template id="release-item-template">
                <div class="section-card release-item-row">
                    <div class="item-row-header">
                        <div class="item-row-title">Item</div>
                        <div class="item-row-actions">
                            <button type="button" class="btn btn-link item-toggle-button">Hide</button>
                            <button type="button" class="btn btn-danger remove-item-button">Delete</button>
                        </div>
                    </div>
                    <div class="item-row-body">
                        <div class="form-grid-3">
                            <div class="form-group">
                                <label>Item Description</label>
                                <input type="text" class="item-description-input" name="items[0][item_description]" value="" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="number" class="item-quantity-input" name="items[0][quantity_released]" value="" min="0" placeholder="Available: 0">
                            </div>
                            <div class="form-group">
                                <label>UOM</label>
                                <input class="item-uom-input" name="items[0][uom]" value="">
                            </div>
                        </div>
                        <div class="form-grid-3">
                            <div class="form-group">
                                <label>Unit Cost</label>
                                <input class="item-unit-cost-input" type="number" step="0.01" name="items[0][unit_cost]" value="">
                            </div>
                            <div class="form-group">
                                <label>Product Code</label>
                                <select class="item-id-select" name="items[0][item_id]">
                                    <option value="">Select product</option>
                                </select>
                            </div>
                            <div></div>
                        </div>
                    </div>
                </div>
            </template>

            <script>
                const allItemsData = {!! json_encode($items->map(fn($i) => ['id' => $i->id, 'code' => $i->item_code, 'name' => $i->name, 'uom' => $i->unit, 'cost' => $i->unit_cost, 'qty' => $i->quantity_on_hand, 'category' => $i->category])->toArray()) !!};

                document.addEventListener('DOMContentLoaded', function () {
                    const releaseItems = document.getElementById('release-items');
                    const addItemButton = document.getElementById('add-item-button');
                    const itemTemplate = document.getElementById('release-item-template');
                    const itemOptions = document.getElementById('item-options');

                    const itemsData = Array.from(itemOptions.querySelectorAll('option')).map(option => ({
                        id: option.dataset.itemId,
                        name: option.value,
                        nameLower: option.value.toLowerCase(),
                        category: option.dataset.category
                    }));

                    function updateIndexes() {
                        Array.from(releaseItems.querySelectorAll('.release-item-row')).forEach((row, index) => {
                            row.dataset.index = index;
                            row.querySelector('.item-row-title').textContent = 'Item ' + (index + 1);
                            row.querySelectorAll('input, select').forEach((field) => {
                                const fieldName = field.name;
                                const newName = fieldName.replace(/items\[\d+\]/, 'items[' + index + ']');
                                field.name = newName;
                            });

                            const deleteButton = row.querySelector('.remove-item-button');
                            if (index === 0) {
                                deleteButton.style.display = 'none';
                            } else {
                                deleteButton.style.display = '';
                            }
                        });
                    }

                    function createAutocompleteDropdown(descriptionInput) {
                        let dropdown = descriptionInput.parentElement.querySelector('.autocomplete-dropdown');
                        if (dropdown) {
                            dropdown.remove();
                        }

                        dropdown = document.createElement('div');
                        dropdown.className = 'autocomplete-dropdown';
                        dropdown.style.cssText = `
                            position: absolute;
                            background: white;
                            border: 1px solid #ddd;
                            max-height: 200px;
                            overflow-y: auto;
                            width: 100%;
                            z-index: 1000;
                            display: none;
                            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                            top: 100%;
                            left: 0;
                            margin-top: 4px;
                        `;
                        descriptionInput.parentElement.style.position = 'relative';
                        descriptionInput.parentElement.appendChild(dropdown);
                        return dropdown;
                    }

                    function showAutocompleteOptions(descriptionInput, dropdown, searchText, syncCallback) {
                        dropdown.innerHTML = '';
                        if (!searchText.trim()) {
                            dropdown.style.display = 'none';
                            return;
                        }

                        const searchLower = searchText.toLowerCase();
                        const filtered = itemsData.filter(item => item.nameLower.includes(searchLower));

                        if (filtered.length === 0) {
                            dropdown.style.display = 'none';
                            return;
                        }

                        filtered.forEach(item => {
                            const option = document.createElement('div');
                            option.className = 'autocomplete-option';
                            option.style.cssText = `
                                padding: 10px 12px;
                                cursor: pointer;
                                border-bottom: 1px solid #f0f0f0;
                            `;
                            option.textContent = item.name;
                            option.addEventListener('mouseover', () => {
                                option.style.backgroundColor = '#f5f5f5';
                            });
                            option.addEventListener('mouseout', () => {
                                option.style.backgroundColor = 'transparent';
                            });
                            option.addEventListener('click', () => {
                                descriptionInput.value = item.name;
                                dropdown.style.display = 'none';
                                syncCallback();
                            });
                            dropdown.appendChild(option);
                        });

                        dropdown.style.display = 'block';
                    }

                    function populateProductSelect(select, itemName) {
                        select.innerHTML = '<option value="">Select product</option>';
                        const filtered = allItemsData.filter(item => item.name.toLowerCase() === itemName.toLowerCase());
                        console.log('Filtering by name:', itemName, 'Found:', filtered.length, 'items');
                        filtered.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = item.code + ' - ' + item.name;
                            option.dataset.uom = item.uom;
                            option.dataset.unitCost = item.cost;
                            option.dataset.quantity = item.qty;
                            select.appendChild(option);
                        });
                    }

                    function bindRowEvents(row) {
                        const body = row.querySelector('.item-row-body');
                        const toggleButton = row.querySelector('.item-toggle-button');
                        const removeButton = row.querySelector('.remove-item-button');
                        const descriptionInput = row.querySelector('.item-description-input');
                        const itemIdSelect = row.querySelector('.item-id-select');
                        const uomInput = row.querySelector('.item-uom-input');
                        const unitCostInput = row.querySelector('.item-unit-cost-input');
                        const quantityInput = row.querySelector('.item-quantity-input');

                        if (descriptionInput && itemIdSelect) {
                            const dropdown = createAutocompleteDropdown(descriptionInput);

                            const syncItemSelection = () => {
                                const typedText = descriptionInput.value.trim();
                                const match = itemsData.find(item => item.nameLower === typedText.toLowerCase());

                                if (match) {
                                    console.log('Matched item:', match.name);
                                    populateProductSelect(itemIdSelect, match.name);
                                    itemIdSelect.value = match.id;
                                    const selectedOption = itemIdSelect.options[itemIdSelect.selectedIndex];
                                    if (selectedOption && selectedOption.value) {
                                        if (uomInput) uomInput.value = selectedOption.dataset.uom || '';
                                        if (unitCostInput) unitCostInput.value = selectedOption.dataset.unitCost || '';
                                        if (quantityInput) quantityInput.placeholder = 'Available: ' + (selectedOption.dataset.quantity || 0);
                                    }
                                }
                            };

                            descriptionInput.addEventListener('input', (e) => {
                                showAutocompleteOptions(descriptionInput, dropdown, e.target.value, syncItemSelection);
                            });

                            descriptionInput.addEventListener('change', syncItemSelection);

                            descriptionInput.addEventListener('blur', () => {
                                setTimeout(() => {
                                    dropdown.style.display = 'none';
                                }, 200);
                            });

                            descriptionInput.addEventListener('focus', () => {
                                if (descriptionInput.value.trim()) {
                                    showAutocompleteOptions(descriptionInput, dropdown, descriptionInput.value, syncItemSelection);
                                }
                            });
                        }

                        if (itemIdSelect) {
                            itemIdSelect.addEventListener('change', () => {
                                const selectedOption = itemIdSelect.options[itemIdSelect.selectedIndex];
                                if (selectedOption && selectedOption.value) {
                                    if (uomInput) uomInput.value = selectedOption.dataset.uom || '';
                                    if (unitCostInput) unitCostInput.value = selectedOption.dataset.unitCost || '';
                                    if (quantityInput) quantityInput.placeholder = 'Available: ' + (selectedOption.dataset.quantity || 0);
                                }
                            });
                        }

                        toggleButton.addEventListener('click', () => {
                            body.style.display = body.style.display === 'none' ? '' : 'none';
                            toggleButton.textContent = body.style.display === 'none' ? 'Show' : 'Hide';
                        });

                        removeButton.addEventListener('click', () => {
                            row.remove();
                            updateIndexes();
                        });
                    }

                    function addItemRow() {
                        const clone = itemTemplate.content.cloneNode(true);
                        const row = clone.querySelector('.release-item-row');
                        bindRowEvents(row);
                        releaseItems.appendChild(row);
                        updateIndexes();
                    }

                    Array.from(releaseItems.querySelectorAll('.release-item-row')).forEach(bindRowEvents);
                    updateIndexes();

                    Array.from(releaseItems.querySelectorAll('.release-item-row')).forEach(row => {
                        const descriptionInput = row.querySelector('.item-description-input');
                        const itemIdSelect = row.querySelector('.item-id-select');
                        if (descriptionInput && descriptionInput.value.trim()) {
                            const match = itemsData.find(item => item.nameLower === descriptionInput.value.trim().toLowerCase());
                            if (match) {
                                populateProductSelect(itemIdSelect, match.name);
                            }
                        }
                    });

                    addItemButton.addEventListener('click', addItemRow);
                });
            </script>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Release Slip</button>
                <a href="{{ route('releases.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
@endsection
