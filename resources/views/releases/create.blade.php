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
                            <option value="{{ $item->name }}" data-item-id="{{ $item->id }}"></option>
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
                                        <input type="text" class="item-description-input" list="item-options" name="items[{{ $index }}][item_description]" value="{{ $oldItem['item_description'] ?? '' }}" autocomplete="off">
                                    </div>
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" name="items[{{ $index }}][quantity_released]" value="{{ $oldItem['quantity_released'] ?? '' }}" min="0">
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
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}" data-uom="{{ $item->unit }}" data-unit-cost="{{ $item->unit_cost ?? '' }}" @selected(($oldItem['item_id'] ?? '') == $item->id)>{{ $item->item_code }} - {{ $item->name }}</option>
                                            @endforeach
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
                                <input type="text" class="item-description-input" list="item-options" name="items[0][item_description]" value="" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="number" name="items[0][quantity_released]" value="" min="0">
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
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_code }} - {{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div></div>
                        </div>
                    </div>
                </div>
            </template>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const releaseItems = document.getElementById('release-items');
                    const addItemButton = document.getElementById('add-item-button');
                    const togglePtrButton = document.getElementById('toggle-ptr-button');
                    const ptrSection = document.getElementById('ptr-section');
                    const itemTemplate = document.getElementById('release-item-template');

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

                    function bindRowEvents(row) {
                        const body = row.querySelector('.item-row-body');
                        const toggleButton = row.querySelector('.item-toggle-button');
                        const removeButton = row.querySelector('.remove-item-button');
                        const descriptionInput = row.querySelector('.item-description-input');
                        const itemIdSelect = row.querySelector('.item-id-select');
                        const uomInput = row.querySelector('.item-uom-input');
                        const unitCostInput = row.querySelector('.item-unit-cost-input');

                        if (descriptionInput && itemIdSelect) {
                            const syncItemSelection = () => {
                                const typedText = descriptionInput.value.trim().toLowerCase();
                                const match = Array.from(document.querySelectorAll('#item-options option')).find((option) => {
                                    return option.value.toLowerCase().includes(typedText);
                                });

                                if (match && match.dataset.itemId) {
                                    itemIdSelect.value = match.dataset.itemId;

                                    const selectedOption = itemIdSelect.options[itemIdSelect.selectedIndex];
                                    if (selectedOption) {
                                        if (uomInput) {
                                            uomInput.value = selectedOption.dataset.uom || '';
                                        }
                                        if (unitCostInput) {
                                            unitCostInput.value = selectedOption.dataset.unitCost || '';
                                        }
                                    }
                                }
                            };

                            descriptionInput.addEventListener('input', syncItemSelection);
                            descriptionInput.addEventListener('change', syncItemSelection);
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

                    addItemButton.addEventListener('click', addItemRow);

                    if (togglePtrButton) {
                        togglePtrButton.addEventListener('click', function () {
                            if (ptrSection.style.display === 'none' || ptrSection.style.display === '') {
                                ptrSection.style.display = 'block';
                                togglePtrButton.textContent = 'Hide PTR/ITR/RIS No.';
                            } else {
                                ptrSection.style.display = 'none';
                                togglePtrButton.textContent = 'Add PTR/ITR/RIS No.';
                            }
                        });
                    }
                });
            </script>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Release Slip</button>
                <a href="{{ route('releases.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
@endsection

