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

        <form action="{{ route('releases.store') }}" method="POST" class="stack" id="releaseForm">
            @csrf

            <div class="form-grid-3">
                <div class="form-group">
                    <label>PAS No. <span style="color: var(--danger);">*</span></label>
                    <input name="pas_number" value="{{ old('pas_number', request('pas_number')) }}" required>
                    @error('pas_number')
                        <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Stock Keeping Unit (Program) <span style="color: var(--danger);">*</span></label>
                    <div style="position:relative;">
                        <input name="health_program_coordinator" id="releaseProgramInput"
                            value="{{ old('health_program_coordinator', request('health_program_coordinator')) }}" autocomplete="off" required style="width:100%;">
                        <div id="releaseProgramDropdown" style="position:absolute;top:100%;left:0;width:100%;z-index:1000;display:none;"></div>
                    </div>
                    @error('health_program_coordinator')
                        <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Program Coordinator</label>
                    <div style="position:relative;">
                        <input name="release_coordinator" id="releaseCoordinatorInput"
                            value="{{ old('release_coordinator', request('release_coordinator')) }}" autocomplete="off" style="width:100%;">
                        <div id="releaseCoordinatorDropdown" style="position:absolute;top:100%;left:0;width:100%;z-index:1000;display:none;"></div>
                    </div>
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label>PTR/ITR/RIS No. <span style="color: var(--danger);">*</span></label>
                    <div style="display: flex; gap: 0.5rem; align-items: stretch;">
                        <select id="ptrTypeSelect" style="width: auto; min-width: 80px; padding: 0.8rem 0.9rem; border: 1px solid var(--border); border-radius: 0.85rem; background: var(--surface-muted); color: var(--text);">
                            <option value="PTR">PTR</option>
                            <option value="ITR">ITR</option>
                            <option value="RIS">RIS</option>
                        </select>
                        <input name="ptr_itr_ris_no" id="ptrNumberInput" value="{{ old('ptr_itr_ris_no', $ptrNumber ?? '') }}" readonly required style="flex: 1; background: var(--surface-strong); cursor: not-allowed;">
                    </div>
                    @error('ptr_itr_ris_no')
                        <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                    <p style="margin: 0.3rem 0 0; font-size: 0.82rem; color: var(--text-muted);">Auto-generated sequential number. Select type (PTR/ITR/RIS) to regenerate.</p>
                </div>
                <div class="form-group">
                    <label>PHO Code <span style="color: var(--danger);">*</span></label>
                    <input name="pho_code" value="{{ old('pho_code') }}" required>
                    @error('pho_code')
                        <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Source Docs. PTR/PO No. <span style="color: var(--danger);">*</span></label>
                    <input name="source_docs_ptr_po_no" value="{{ old('source_docs_ptr_po_no') }}" required>
                    @error('source_docs_ptr_po_no')
                        <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label>Name of Facility / End-user <span style="color: var(--danger);">*</span></label>
                    <input name="facility_name" value="{{ old('facility_name', request('facility_name')) }}" required>
                    @error('facility_name')
                        <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            @if(request('purpose_activity'))
                <div class="section-note">PAS Purpose / Activity: {{ request('purpose_activity') }}</div>
            @endif
            <div class="section-note">
                Received by, Date, and Status are assigned after saving.
            </div>
            <input type="hidden" name="received_by" value="{{ old('received_by', 'Unreleased') }}">
            <input type="hidden" name="date_released" value="{{ old('date_released') }}">
            <input type="hidden" name="status" value="{{ old('status', 'Unreleased') }}">

            <div>
                <h2 class="section-title">Released Items <span style="color: var(--danger);">*</span></h2>
                @error('items')
                    <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
                <div id="release-items" class="stack">
                    @php
                        $oldItems = collect(old('items', request('items', [])))->values()->all();
                        if (empty($oldItems)) {
                            $oldItems = [['item_description' => '', 'quantity_released' => '', 'uom' => '', 'unit_cost' => '', 'item_id' => '']];
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
                                        <div style="position:relative; display:flex; align-items:center;">
                                            <input type="text" class="item-description-input" name="items[{{ $index }}][item_description]" value="{{ $oldItem['item_description'] ?? '' }}" autocomplete="off" style="width:100%; padding-right:2rem;">
                                            <button type="button" class="item-description-clear" title="Clear" style="position:absolute; right:0.5rem; background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:1rem; line-height:1; padding:0.2rem 0.3rem;">&times;</button>
                                        </div>
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
                                    <div class="form-group">
                                        <label>Batch/Lot No.</label>
                                        <input class="item-lot-input" name="items[{{ $index }}][lot_number]" value="{{ $oldItem['lot_number'] ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-item-button" class="btn btn-secondary">Add another item</button>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3">{{ old('notes', request('purpose_activity')) }}</textarea>
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
                                <div style="position:relative; display:flex; align-items:center;">
                                    <input type="text" class="item-description-input" name="items[0][item_description]" value="" autocomplete="off" style="width:100%; padding-right:2rem;">
                                    <button type="button" class="item-description-clear" title="Clear" style="position:absolute; right:0.5rem; background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:1rem; line-height:1; padding:0.2rem 0.3rem;">&times;</button>
                                </div>
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
                            <div class="form-group">
                                <label>Batch/Lot No.</label>
                                <input class="item-lot-input" name="items[0][lot_number]" value="">
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Release Slip</button>
                <a href="{{ route('releases.index') }}" class="btn btn-ghost" id="cancelBtn">Cancel</a>
            </div>
        </form>
    </section>

    {{-- Autocomplete data sources --}}
    <datalist id="release-program-options" style="display:none;">
        @foreach($programs as $program)
            <option value="{{ $program->name }}"></option>
        @endforeach
    </datalist>
    <datalist id="release-coordinator-options" style="display:none;">
        @foreach($coordinators as $coordinator)
            <option value="{{ $coordinator->full_name }}" data-programs="{{ $coordinator->assigned_programs }}"></option>
        @endforeach
    </datalist>

@push('scripts')
<script>
const allItemsData = {!! json_encode($items->map(fn($i) => [
    'id'         => $i->id,
    'code'       => $i->item_code,
    'name'       => $i->name,
    'uom'        => $i->unit,
    'cost'       => $i->unit_cost,
    'qty'        => $i->quantity_on_hand,
    'category'   => $i->category,
    'lot_number' => $itemLotNumbers[$i->id] ?? '',
])->toArray()) !!};

(function () {
    // ---- PTR Type Switcher ----
    const ptrTypeSelect  = document.getElementById('ptrTypeSelect');
    const ptrNumberInput = document.getElementById('ptrNumberInput');
    if (ptrTypeSelect && ptrNumberInput) {
        ptrTypeSelect.addEventListener('change', function () {
            fetch('{{ url('releases/next-ptr-number') }}/' + this.value)
                .then(r => r.json())
                .then(d => { if (d.number) ptrNumberInput.value = d.number; })
                .catch(e => console.error('Failed to fetch PTR number:', e));
        });

        const initialTransferType = '{{ request('transfer_type', 'PTR') }}'.toUpperCase();
        if (['PTR', 'ITR', 'RIS'].includes(initialTransferType)) {
            ptrTypeSelect.value = initialTransferType;
            ptrTypeSelect.dispatchEvent(new Event('change'));
        }
    }

    // ---- Program & Coordinator Autocomplete ----
    const programsData = Array.from(document.querySelectorAll('#release-program-options option')).map(o => ({
        name: o.value, nameLower: o.value.toLowerCase()
    }));
    const coordinatorsData = Array.from(document.querySelectorAll('#release-coordinator-options option')).map(o => ({
        name: o.value, nameLower: o.value.toLowerCase(), assignedPrograms: o.dataset.programs || ''
    }));

    const programInput      = document.getElementById('releaseProgramInput');
    const coordinatorInput  = document.getElementById('releaseCoordinatorInput');
    const programDropdown   = document.getElementById('releaseProgramDropdown');
    const coordinatorDropdown = document.getElementById('releaseCoordinatorDropdown');

    function bindAutocompleteList(input, dataList, dropdown, onSelect) {
        function showOptions(q) {
            dropdown.innerHTML = '';
            const lower = q.toLowerCase().trim();
            const seen = {};
            const filtered = (lower ? dataList.filter(i => i.nameLower.includes(lower)) : dataList)
                .filter(i => { if (seen[i.nameLower]) return false; seen[i.nameLower] = true; return true; });
            if (!filtered.length) { dropdown.style.display = 'none'; return; }
            Object.assign(dropdown.style, {
                background:'var(--surface,#fff)', border:'1px solid var(--border,#ddd)',
                maxHeight:'200px', overflowY:'auto', boxShadow:'0 4px 6px rgba(0,0,0,.1)', marginTop:'4px'
            });
            filtered.forEach(item => {
                const opt = document.createElement('div');
                opt.style.cssText = 'padding:10px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0;';
                opt.textContent = item.name;
                opt.addEventListener('mouseover', () => opt.style.background = '#f5f5f5');
                opt.addEventListener('mouseout',  () => opt.style.background = 'transparent');
                opt.addEventListener('click', () => { input.value = item.name; dropdown.style.display = 'none'; if (onSelect) onSelect(item); });
                dropdown.appendChild(opt);
            });
            dropdown.style.display = 'block';
        }
        input.addEventListener('input',  e => showOptions(e.target.value));
        input.addEventListener('focus',  () => showOptions(input.value));
        input.addEventListener('blur',   () => setTimeout(() => dropdown.style.display = 'none', 200));
    }

    bindAutocompleteList(programInput, programsData, programDropdown, function (item) {
        const matched = coordinatorsData.find(c => c.assignedPrograms.toLowerCase().includes(item.nameLower));
        if (matched && coordinatorInput && !coordinatorInput.value.trim()) coordinatorInput.value = matched.name;
    });

    bindAutocompleteList(coordinatorInput, coordinatorsData, coordinatorDropdown, function (item) {
        if (item.assignedPrograms && programInput && !programInput.value.trim()) {
            programInput.value = item.assignedPrograms.split(', ')[0] || '';
        }
    });
    // ---- End Program & Coordinator Autocomplete ----

    // ---- Items Autocomplete ----
    const releaseItems  = document.getElementById('release-items');
    const addItemButton = document.getElementById('add-item-button');
    const itemTemplate  = document.getElementById('release-item-template');

    const itemsData = allItemsData.map(i => ({
        id: i.id,
        name: i.name,
        nameLower: i.name.toLowerCase(),
        code: i.code,
        codeLower: (i.code || '').toLowerCase(),
        category: i.category,
    }));

    function updateIndexes() {
        Array.from(releaseItems.querySelectorAll('.release-item-row')).forEach((row, index) => {
            row.dataset.index = index;
            row.querySelector('.item-row-title').textContent = 'Item ' + (index + 1);
            row.querySelectorAll('input, select').forEach(f => {
                f.name = f.name.replace(/items\[\d+\]/, 'items[' + index + ']');
            });
            row.querySelector('.remove-item-button').style.display = index === 0 ? 'none' : '';
        });
    }

    function createDropdown(descInput) {
        let dd = descInput.parentElement.querySelector('.autocomplete-dropdown');
        if (dd) dd.remove();
        dd = document.createElement('div');
        dd.className = 'autocomplete-dropdown';
        dd.style.cssText = 'position:absolute;background:white;border:1px solid #ddd;max-height:200px;overflow-y:auto;width:100%;z-index:1000;display:none;box-shadow:0 4px 6px rgba(0,0,0,.1);top:100%;left:0;margin-top:4px;';
        descInput.parentElement.style.position = 'relative';
        descInput.parentElement.appendChild(dd);
        return dd;
    }

    function showItemOptions(descInput, dd, searchText, syncCb) {
        dd.innerHTML = '';
        const lower = searchText.toLowerCase().trim();
        const seen = new Set();
        const filtered = (lower ? itemsData.filter(i => i.nameLower.includes(lower)) : itemsData)
            .filter(i => { if (seen.has(i.nameLower)) return false; seen.add(i.nameLower); return true; });
        if (!filtered.length) { dd.style.display = 'none'; return; }
        filtered.forEach(item => {
            const opt = document.createElement('div');
            opt.style.cssText = 'padding:10px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0;';
            opt.textContent = item.name;
            opt.addEventListener('mouseover', () => opt.style.background = '#f5f5f5');
            opt.addEventListener('mouseout',  () => opt.style.background = 'transparent');
            opt.addEventListener('click', () => { descInput.value = item.name; dd.style.display = 'none'; syncCb(); });
            dd.appendChild(opt);
        });
        dd.style.display = 'block';
    }

    function buildProductOptions(select) {
        select.innerHTML = '<option value="">Select product</option>';
        allItemsData.forEach(i => {
            const o = document.createElement('option');
            o.value = i.id;
            o.textContent = i.code + ' - ' + i.name;
            o.dataset.uom = i.uom;
            o.dataset.unitCost = i.cost;
            o.dataset.quantity = i.qty;
            o.dataset.lotNumber = i.lot_number || '';
            select.appendChild(o);
        });
    }

    function populateProductSelect(select, itemName) {
        buildProductOptions(select);
        const lowerName = itemName.toLowerCase().trim();
        let matchItem = allItemsData.find(i =>
            i.name.toLowerCase() === lowerName ||
            i.code.toLowerCase() === lowerName
        );
        if (!matchItem) {
            matchItem = allItemsData.find(i =>
                i.name.toLowerCase().includes(lowerName) ||
                i.code.toLowerCase().includes(lowerName)
            );
        }
        if (matchItem) {
            select.value = matchItem.id;
        }
    }

    function bindRowEvents(row) {
        const body          = row.querySelector('.item-row-body');
        const toggleButton  = row.querySelector('.item-toggle-button');
        const removeButton  = row.querySelector('.remove-item-button');
        const descInput     = row.querySelector('.item-description-input');
        const itemIdSelect  = row.querySelector('.item-id-select');
        const uomInput      = row.querySelector('.item-uom-input');
        const unitCostInput = row.querySelector('.item-unit-cost-input');
        const quantityInput = row.querySelector('.item-quantity-input');
        const lotInput      = row.querySelector('.item-lot-input');

        if (descInput && itemIdSelect) {
            const dd = createDropdown(descInput);

            const syncItemSelection = () => {
                const typed = descInput.value.trim().toLowerCase();
                if (!typed) {
                    return;
                }
                let match = itemsData.find(i => i.nameLower === typed || i.codeLower === typed || i.id.toString() === typed);
                if (!match) {
                    match = itemsData.find(i => i.nameLower.includes(typed) || i.codeLower.includes(typed));
                }
                if (match) {
                    populateProductSelect(itemIdSelect, match.name);
                    itemIdSelect.value = match.id;
                    const sel = itemIdSelect.options[itemIdSelect.selectedIndex];
                    if (sel && sel.value) {
                        if (uomInput)      uomInput.value      = sel.dataset.uom || '';
                        if (unitCostInput) unitCostInput.value = sel.dataset.unitCost || '';
                        if (quantityInput) quantityInput.placeholder = 'Available: ' + (sel.dataset.quantity || 0);
                    }
                    const itemData = allItemsData.find(i => i.id == match.id);
                    if (lotInput && itemData && itemData.lot_number) lotInput.value = itemData.lot_number;
                    if (descInput) descInput.value = match.name;
                }
            };

            descInput.addEventListener('input',  e => showItemOptions(descInput, dd, e.target.value, syncItemSelection));
            descInput.addEventListener('change', syncItemSelection);
            descInput.addEventListener('blur',   () => setTimeout(() => dd.style.display = 'none', 200));
            descInput.addEventListener('focus',  () => showItemOptions(descInput, dd, descInput.value, syncItemSelection));

            const clearBtn = row.querySelector('.item-description-clear');
            if (clearBtn) {
                clearBtn.addEventListener('mousedown', e => e.preventDefault());
                clearBtn.addEventListener('click', () => {
                    descInput.value = '';
                    itemIdSelect.innerHTML = '<option value="">Select product</option>';
                    if (uomInput)      uomInput.value = '';
                    if (unitCostInput) unitCostInput.value = '';
                    if (quantityInput) quantityInput.placeholder = 'Available: 0';
                    if (lotInput)      lotInput.value = '';
                    dd.style.display = 'none';
                    descInput.focus();
                });
            }
        }

        if (itemIdSelect) {
            buildProductOptions(itemIdSelect);
            itemIdSelect.addEventListener('change', () => {
                const sel = itemIdSelect.options[itemIdSelect.selectedIndex];
                if (sel && sel.value) {
                    if (uomInput)      uomInput.value      = sel.dataset.uom || '';
                    if (unitCostInput) unitCostInput.value = sel.dataset.unitCost || '';
                    if (quantityInput) quantityInput.placeholder = 'Available: ' + (sel.dataset.quantity || 0);
                    const itemData = allItemsData.find(i => i.id == sel.value);
                    if (lotInput && itemData && itemData.lot_number) lotInput.value = itemData.lot_number;
                    if (descInput && itemData) descInput.value = itemData.name;
                }
            });
        }

        toggleButton.addEventListener('click', () => {
            body.style.display = body.style.display === 'none' ? '' : 'none';
            toggleButton.textContent = body.style.display === 'none' ? 'Show' : 'Hide';
        });
        removeButton.addEventListener('click', () => { row.remove(); updateIndexes(); });
    }

    Array.from(releaseItems.querySelectorAll('.release-item-row')).forEach(row => {
        bindRowEvents(row);
        const descInput    = row.querySelector('.item-description-input');
        const itemIdSelect = row.querySelector('.item-id-select');
        if (itemIdSelect) {
            buildProductOptions(itemIdSelect);
        }
        if (descInput && descInput.value.trim()) {
            const match = itemsData.find(i => i.nameLower === descInput.value.trim().toLowerCase());
            if (match) populateProductSelect(itemIdSelect, match.name);
        }
    });
    updateIndexes();

    addItemButton.addEventListener('click', () => {
        const clone = itemTemplate.content.cloneNode(true);
        const row   = clone.querySelector('.release-item-row');
        bindRowEvents(row);
        releaseItems.appendChild(row);
        updateIndexes();
    });

    function syncRowItemSelection(row) {
        const descInput = row.querySelector('.item-description-input');
        const itemIdSelect = row.querySelector('.item-id-select');
        const uomInput = row.querySelector('.item-uom-input');
        const unitCostInput = row.querySelector('.item-unit-cost-input');
        const quantityInput = row.querySelector('.item-quantity-input');
        const lotInput = row.querySelector('.item-lot-input');

        if (!descInput || !itemIdSelect) {
            return;
        }

        buildProductOptions(itemIdSelect);

        if (itemIdSelect.value) {
            return;
        }

        const typed = descInput.value.trim().toLowerCase();
        if (!typed) {
            return;
        }

        let match = itemsData.find(i => i.nameLower === typed || i.codeLower === typed || i.id.toString() === typed);
        if (!match) {
            match = itemsData.find(i => i.nameLower.includes(typed) || i.codeLower.includes(typed));
        }
        if (!match) {
            return;
        }

        populateProductSelect(itemIdSelect, match.name);
        itemIdSelect.value = match.id;

        const sel = itemIdSelect.options[itemIdSelect.selectedIndex];
        if (sel && sel.value) {
            if (uomInput)      uomInput.value = sel.dataset.uom || '';
            if (unitCostInput) unitCostInput.value = sel.dataset.unitCost || '';
            if (quantityInput) quantityInput.placeholder = 'Available: ' + (sel.dataset.quantity || 0);
            const itemData = allItemsData.find(i => i.id == match.id);
            if (lotInput && itemData && itemData.lot_number) lotInput.value = itemData.lot_number;
            if (descInput) descInput.value = match.name;
        }
    }

    // ---- Dirty-form guard ----
    let formDirty = false;
    const releaseForm = document.getElementById('releaseForm');

    releaseForm.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('input',  () => formDirty = true);
        el.addEventListener('change', () => formDirty = true);
    });
    window.addEventListener('beforeunload', e => { if (formDirty) { e.preventDefault(); e.returnValue = ''; } });
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', e => {
            if (formDirty && !confirm('You have unsaved changes. Leaving this page will discard them. Are you sure you want to leave?')) e.preventDefault();
        });
    });

    releaseForm.addEventListener('submit', () => {
        Array.from(releaseItems.querySelectorAll('.release-item-row')).forEach(row => {
            syncRowItemSelection(row);
        });
        formDirty = false;
    });

    new MutationObserver(() => {
        releaseItems.querySelectorAll('input, select, textarea').forEach(el => {
            el.removeEventListener('input',  () => formDirty = true);
            el.removeEventListener('change', () => formDirty = true);
            el.addEventListener('input',  () => formDirty = true);
            el.addEventListener('change', () => formDirty = true);
        });
    }).observe(releaseItems, { childList: true, subtree: true });
    // ---- End guard ----
})();
</script>
@endpush
@endsection
