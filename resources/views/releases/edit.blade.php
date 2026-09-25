@extends('layouts.app')

@section('title', 'Edit Release Slip')
@section('pageHeading', 'Edit Release Slip')
@section('pageSubheading', 'Update release slip details. Stock will be adjusted automatically.')

@section('content')
    <section class="card">

        <div class="section-header">
            <div>
                <h1 class="page-heading">{{ $release->release_number }}</h1>
                <p class="page-description">Edit release slip #{{ $release->release_number }} — PAS: {{ $release->pas_number ?? '—' }}</p>
            </div>
            <a href="{{ route('releases.view', $release) }}" class="btn btn-secondary">Back to Release</a>
        </div>

        <form action="{{ route('releases.update', $release) }}" method="POST" class="stack" id="releaseForm">
            @csrf
            @method('PUT')

            {{-- Row 1: Reference Numbers --}}
            <div class="form-grid-3">
                <div class="form-group">
                    <label>PAS No. <span style="color: var(--danger);">*</span></label>
                    <input name="pas_number" value="{{ old('pas_number', $release->pas_number) }}" required>
                    @error('pas_number')
                        <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label>PTR/ITR/RIS No. <span style="color: var(--danger);">*</span></label>
                    <div style="display: flex; gap: 0.5rem; align-items: stretch;">
                        <select id="ptrTypeSelect" style="width: auto; min-width: 80px; padding: 0.8rem 0.9rem; border: 1px solid var(--border); border-radius: 0.85rem; background: var(--surface-muted); color: var(--text);">
                            <option value="PTR">PTR</option>
                            <option value="ITR">ITR</option>
                            <option value="RIS">RIS</option>
                            <option value="ELMIS">ELMIS</option>
                        </select>
                        <input name="ptr_itr_ris_no" id="ptrNumberInput" value="{{ old('ptr_itr_ris_no', $release->ptr_itr_ris_no) }}" required style="flex: 1;">
                    </div>
                    @error('ptr_itr_ris_no')
                        <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Source Docs. PTR/PO No. <span style="color: var(--danger);">*</span></label>
                    <input name="source_docs_ptr_po_no" value="{{ old('source_docs_ptr_po_no', $release->source_docs_ptr_po_no) }}" required>
                    @error('source_docs_ptr_po_no')
                        <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Row 2: Facility & Program --}}
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Name of Facility / End-user <span style="color: var(--danger);">*</span></label>
                    <select name="facility_name" id="facilityName" required>
                        <option value="">— Select Facility —</option>
                        @foreach($facilities->groupBy('category') as $cat => $group)
                            <optgroup label="{{ $cat ?: 'Other' }}">
                                @foreach($group as $f)
                                    <option value="{{ $f->name }}" {{ old('facility_name', $release->facility_name) === $f->name ? 'selected' : '' }}>{{ $f->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('facility_name')
                        <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Stock Keeping Unit (Program) <span style="color: var(--danger);">*</span></label>
                    <div style="position:relative;">
                        <input name="health_program_coordinator" id="releaseProgramInput"
                            value="{{ old('health_program_coordinator', $release->health_program_coordinator) }}" autocomplete="off" required style="width:100%;">
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
                            value="{{ old('release_coordinator') }}" autocomplete="off" style="width:100%;">
                        <div id="releaseCoordinatorDropdown" style="position:absolute;top:100%;left:0;width:100%;z-index:1000;display:none;"></div>
                    </div>
                </div>
            </div>

            {{-- Reason for Transfer --}}
            <div class="form-group">
                <label>Reason for Transfer</label>
                <select name="reason_for_transfer" id="reasonForTransferSelect" required>
                    <option value="">— Select Reason —</option>
                    <option value="Donation" {{ old('reason_for_transfer', $release->reason_for_transfer) === 'Donation' ? 'selected' : '' }}>Donation</option>
                    <option value="Reassignment" {{ old('reason_for_transfer', $release->reason_for_transfer) === 'Reassignment' ? 'selected' : '' }}>Reassignment</option>
                    <option value="Relocate" {{ old('reason_for_transfer', $release->reason_for_transfer) === 'Relocate' ? 'selected' : '' }}>Relocate</option>
                    <option value="Allocation" {{ old('reason_for_transfer', $release->reason_for_transfer) === 'Allocation' ? 'selected' : '' }}>Allocation</option>
                    <option value="Others" {{ old('reason_for_transfer', $release->reason_for_transfer) === 'Others' ? 'selected' : '' }}>Others (specify)</option>
                </select>
                @error('reason_for_transfer')
                    <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group" id="reasonOthersGroup" style="display: none;">
                <label for="reason_for_transfer_others">Reason (Others)</label>
                <input type="text" name="reason_for_transfer_others" id="reason_for_transfer_others" value="{{ old('reason_for_transfer_others', $release->reason_for_transfer_others ?? '') }}" placeholder="Specify the reason...">
            </div>

            {{-- Status --}}
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="releaseStatusSelect">
                        <option value="Unreleased" @selected(($release->status ?? '') === 'Unreleased')>Unreleased</option>
                        <option value="Released" @selected(($release->status ?? '') === 'Released')>Released</option>
                        <option value="Released through pass" @selected(($release->status ?? '') === 'Released through pass')>Released through pass</option>
                        <option value="Canceled" @selected(($release->status ?? '') === 'Canceled')>Canceled</option>
                        <option value="Returned" @selected(($release->status ?? '') === 'Returned')>Returned</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date Released</label>
                    <input type="date" name="date_released" value="{{ old('date_released', $release->date_released?->format('Y-m-d') ?? '') }}">
                </div>
                <div class="form-group">
                    <label>Received By</label>
                    <input type="text" name="received_by" value="{{ old('received_by', $release->received_by ?? '') }}" placeholder="Enter receiver name">
                </div>
            </div>

            <div class="form-group" id="statusReasonGroup" style="display:none;">
                <label>Reason <span style="color: var(--danger);">*</span></label>
                <input type="text" name="status_reason" value="{{ old('status_reason', $release->status_reason ?? '') }}" placeholder="Enter reason">
                @error('status_reason')
                    <span style="color: var(--danger); font-size: 0.875rem; margin-top: 0.25rem; display:block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- Released Items --}}
            <div>
                <h2 class="section-title">Released Items <span style="color: var(--danger);">*</span></h2>
                @error('items')
                    <span style="color: var(--danger); font-size: 0.82rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
                <div id="release-items" class="stack">
                    @php
                        $oldItems = collect(old('items', []))->values()->all();
                        if (!empty($oldItems)) {
                            $displayItems = $oldItems;
                        } else {
                            $displayItems = $release->items->map(function($ri) {
                                return [
                                    '_release_item_id' => $ri->id,
                                    'item_id'          => $ri->item_id,
                                    'item_description' => $ri->item_description ?? ($ri->item?->name ?? ''),
                                    'lot_number'       => $ri->lot_number ?? '',
                                    'expiry_date'      => $ri->expiry_date ?? '',
                                    'quantity_released' => $ri->quantity_released,
                                    'uom'              => $ri->uom ?? '',
                                    'unit_cost'        => $ri->unit_cost ?? '',
                                ];
                            })->all();
                        }
                        if (empty($displayItems)) {
                            $displayItems = [['_release_item_id' => '', 'item_description' => '', 'quantity_released' => '', 'uom' => '', 'unit_cost' => '', 'item_id' => '', 'lot_number' => '', 'expiry_date' => '', 'item_code' => '']];
                        }
                    @endphp

                    @foreach($displayItems as $index => $oldItem)
                        <div class="section-card release-item-row" data-index="{{ $index }}">
                            <div class="item-row-header">
                                <div class="item-row-title">Item {{ $index + 1 }}</div>
                                <div class="item-row-actions">
                                    <button type="button" class="btn btn-link item-toggle-button">Hide</button>
                                    <button type="button" class="btn btn-danger remove-item-button" @if($index === 0) style="display:none;" @endif>Delete</button>
                                </div>
                            </div>
                                <div class="item-row-body">
                                    <input type="hidden" class="release-item-id" name="items[{{ $index }}][_release_item_id]" value="{{ $oldItem['_release_item_id'] ?? '' }}">
                                    <input type="hidden" class="item-id-select" name="items[{{ $index }}][item_id]" value="{{ $oldItem['item_id'] ?? '' }}">
                                    <div class="form-grid-3">
                                        <div class="form-group">
                                            <label>Item Description <span style="color: var(--danger);">*</span></label>
                                            <div style="position:relative; display:flex; align-items:center;">
                                                <input type="text" class="item-description-input" name="items[{{ $index }}][item_description]"
                                                    value="{{ $oldItem['item_description'] ?? '' }}" autocomplete="off" style="width:100%; padding-right:2rem;" required>
                                                <button type="button" class="item-description-clear" title="Clear"
                                                    style="position:absolute; right:0.5rem; background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:1rem; line-height:1; padding:0.2rem 0.3rem;">&times;</button>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>PHO Code</label>
                                            <div style="position:relative; display:flex; align-items:center;">
                                                <input type="text" class="item-phocode-input" autocomplete="off" style="width:100%; padding-right:2rem;" value="{{ $oldItem['item_code'] ?? '' }}">
                                                <input type="hidden" class="item-code-select" name="items[{{ $index }}][item_code]" value="{{ $oldItem['item_code'] ?? '' }}">
                                                <input type="hidden" class="item-id-select" name="items[{{ $index }}][item_id]" value="{{ $oldItem['item_id'] ?? '' }}">
                                                <button type="button" class="item-description-clear" title="Clear"
                                                    style="position:absolute; right:0.5rem; background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:1rem; line-height:1; padding:0.2rem 0.3rem;">&times;</button>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Batch/Lot No.</label>
                                            <input class="item-lot-input" name="items[{{ $index }}][lot_number]" value="{{ $oldItem['lot_number'] ?? '' }}">
                                        </div>
                                    </div>
                                <div class="form-grid-3">
                                    <div class="form-group">
                                        <label>Expiration Date</label>
                                        <input type="date" class="item-expiry-input" name="items[{{ $index }}][expiry_date]" value="{{ $oldItem['expiry_date'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Quantity <span style="color: var(--danger);">*</span></label>
                                        <input type="number" class="item-quantity-input" name="items[{{ $index }}][quantity_released]" value="{{ $oldItem['quantity_released'] ?? '' }}" min="1" placeholder="Available: 0" required>
                                    </div>
                                    <div class="form-group">
                                        <label>UOM <span style="color: var(--danger);">*</span></label>
                                        <input class="item-uom-input" name="items[{{ $index }}][uom]" value="{{ $oldItem['uom'] ?? '' }}" required>
                                    </div>
                                </div>
                                <div class="form-grid-3">
                                    <div class="form-group">
                                        <label>Unit Cost <span style="color: var(--danger);">*</span></label>
                                        <input class="item-unit-cost-input" type="number" step="0.01" name="items[{{ $index }}][unit_cost]" value="{{ $oldItem['unit_cost'] ?? '' }}" min="0" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Total Cost</label>
                                        <input type="text" class="item-totalcost-display" readonly placeholder="Auto-calculated" style="background:var(--surface-strong); cursor:not-allowed;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-item-button" class="btn btn-secondary" style="margin-top:0.75rem;">+ Add Item</button>
            </div>

            {{-- Purpose / Activity --}}
            <div class="form-group">
                <label>Purpose / Activity</label>
                <textarea name="notes" rows="3">{{ old('notes', $release->notes) }}</textarea>
            </div>

            {{-- Template for new item rows --}}
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
                        <input type="hidden" class="release-item-id" name="items[0][_release_item_id]" value="">
                        <input type="hidden" class="item-id-select" name="items[0][item_id]" value="">
                        <div class="form-grid-3">
                            <div class="form-group">
                                <label>Item Description <span style="color: var(--danger);">*</span></label>
                                <div style="position:relative; display:flex; align-items:center;">
                                    <input type="text" class="item-description-input" name="items[0][item_description]" value="" autocomplete="off" style="width:100%; padding-right:2rem;" required>
                                    <button type="button" class="item-description-clear" title="Clear"
                                        style="position:absolute; right:0.5rem; background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:1rem; line-height:1; padding:0.2rem 0.3rem;">&times;</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>PHO Code</label>
                                <div style="position:relative; display:flex; align-items:center;">
                                    <input type="text" class="item-phocode-input" autocomplete="off" style="width:100%; padding-right:2rem;" value="">
                                    <button type="button" class="item-description-clear" title="Clear"
                                        style="position:absolute; right:0.5rem; background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:1rem; line-height:1; padding:0.2rem 0.3rem;">&times;</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Batch/Lot No.</label>
                                <input class="item-lot-input" name="items[0][lot_number]" value="">
                            </div>
                        </div>
                        <div class="form-grid-3">
                            <div class="form-group">
                                <label>Expiration Date</label>
                                <input type="date" class="item-expiry-input" name="items[0][expiry_date]" value="">
                            </div>
                            <div class="form-group">
                                <label>Quantity <span style="color: var(--danger);">*</span></label>
                                <input type="number" class="item-quantity-input" name="items[0][quantity_released]" value="" min="1" placeholder="Available: 0" required>
                            </div>
                            <div class="form-group">
                                <label>UOM <span style="color: var(--danger);">*</span></label>
                                <input class="item-uom-input" name="items[0][uom]" value="" required>
                            </div>
                        </div>
                        <div class="form-grid-3">
                            <div class="form-group">
                                <label>Unit Cost <span style="color: var(--danger);">*</span></label>
                                <input class="item-unit-cost-input" type="number" step="0.01" name="items[0][unit_cost]" value="" min="0" required>
                            </div>
                            <div class="form-group">
                                <label>Total Cost</label>
                                <input type="text" class="item-totalcost-display" readonly placeholder="Auto-calculated" style="background:var(--surface-strong); cursor:not-allowed;">
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('releases.view', $release) }}" class="btn btn-ghost" id="cancelBtn">Cancel</a>
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
    const allItemsData = {!! json_encode($items->flatMap(fn($i) => $i->receivingItems->isNotEmpty()
        ? $i->receivingItems->map(fn($receivingItem) => [
            'id'         => $i->id,
            'code'       => $receivingItem->item_code,
            'name'       => $i->name,
            'uom'        => $receivingItem->uom ?: $i->unit,
            'cost'       => $receivingItem->unit_cost ?? $i->unit_cost,
            'qty'        => $receivingItem->available_quantity ?? $receivingItem->quantity_received,
            'category'   => $receivingItem->category ?: $i->category,
            'lot_number' => $receivingItem->lot_number,
            'expiry'     => $receivingItem->expiry_date?->format('Y-m-d'),
        ])
        : [[
            'id'         => $i->id,
            'code'       => $i->item_code ?? '',
            'name'       => $i->name,
            'uom'        => $i->unit,
            'cost'       => $i->unit_cost,
            'qty'        => $i->quantity_on_hand,
            'category'   => $i->category,
            'lot_number' => '',
            'expiry'     => '',
        ]]
    )->filter(fn($item) => filled($item['code']) || filled($item['name']))->values()->toArray()) !!};

    (function () {
        const ptrTypeSelect  = document.getElementById('ptrTypeSelect');
        const ptrNumberInput = document.getElementById('ptrNumberInput');
        const currentPtrValue = '{{ old('ptr_itr_ris_no', $release->ptr_itr_ris_no) }}';

        function setManualEntry(enabled, focus) {
            if (enabled) {
                ptrNumberInput.removeAttribute('readonly');
                ptrNumberInput.style.background = 'var(--surface)';
                ptrNumberInput.style.cursor = 'text';
                ptrNumberInput.placeholder = 'Enter ELMIS No.';
                if (focus) {
                    ptrNumberInput.focus();
                }
            } else {
                ptrNumberInput.setAttribute('readonly', 'readonly');
                ptrNumberInput.style.background = 'var(--surface-strong)';
                ptrNumberInput.style.cursor = 'not-allowed';
                ptrNumberInput.placeholder = '';
            }
        }

        if (ptrTypeSelect && ptrNumberInput) {
            ptrTypeSelect.addEventListener('change', function () {
                if (this.value === 'ELMIS') {
                    setManualEntry(true, true);
                    return;
                }
                setManualEntry(false, false);
            });

            if (currentPtrValue && !/^(PTR|ITR|RIS)-\d{4}-\d{2}-/i.test(currentPtrValue)) {
                ptrTypeSelect.value = 'ELMIS';
                setManualEntry(true, false);
            } else {
                const upperVal = (currentPtrValue || '').toUpperCase();
                if (upperVal.includes('ITR')) {
                    ptrTypeSelect.value = 'ITR';
                } else if (upperVal.includes('RIS')) {
                    ptrTypeSelect.value = 'RIS';
                } else {
                    ptrTypeSelect.value = 'PTR';
                }
                setManualEntry(false, false);
            }
        }

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

        const releaseItems  = document.getElementById('release-items');
        const addItemButton = document.getElementById('add-item-button');
        const itemTemplate  = document.getElementById('release-item-template');

        function updateIndexes() {
            Array.from(releaseItems.querySelectorAll('.release-item-row')).forEach((row, index) => {
                row.dataset.index = index;
                row.querySelector('.item-row-title').textContent = 'Item ' + (index + 1);
                row.querySelectorAll('input, select').forEach(f => {
                    f.name = f.name.replace(/items\[\d+\]/, 'items[' + index + ']');
                });
                const del = row.querySelector('.remove-item-button');
                if (del) del.style.display = index === 0 ? 'none' : '';
            });
        }

        function createDropdown(descInput) {
            let dd = descInput.parentElement.querySelector('.autocomplete-dropdown');
            if (dd) dd.remove();
            dd = document.createElement('div');
            dd.className = 'autocomplete-dropdown';
            Object.assign(dd.style, {
                position: 'absolute',
                background: 'var(--surface, #fff)',
                border: '1px solid var(--border, #ddd)',
                maxHeight: '200px',
                overflowY: 'auto',
                width: '100%',
                zIndex: '1000',
                display: 'none',
                boxShadow: '0 4px 6px rgba(0,0,0,.1)',
                top: '100%',
                left: '0',
                marginTop: '4px'
            });
            descInput.parentElement.style.position = 'relative';
            descInput.parentElement.appendChild(dd);
            return dd;
        }

        function showItemOptions(descInput, dd, searchText, onSelect) {
            dd.innerHTML = '';
            const lower = searchText.toLowerCase().trim();
            const seen = new Set();
            const filtered = allItemsData.filter(it => {
                if (lower && !it.name.toLowerCase().includes(lower)) return false;
                if (seen.has(it.name.toLowerCase())) return false;
                seen.add(it.name.toLowerCase());
                return true;
            });
            if (!filtered.length) { dd.style.display = 'none'; return; }
            filtered.forEach(item => {
                const opt = document.createElement('div');
                opt.textContent = item.name;
                Object.assign(opt.style, { padding: '10px 12px', cursor: 'pointer', borderBottom: '1px solid #f0f0f0' });
                opt.addEventListener('mouseover', () => opt.style.background = '#f5f5f5');
                opt.addEventListener('mouseout',  () => opt.style.background = 'transparent');
                opt.addEventListener('click', () => {
                    descInput.value = item.name;
                    dd.style.display = 'none';
                    if (onSelect) onSelect();
                });
                dd.appendChild(opt);
            });
            dd.style.display = 'block';
        }

        function applyItemToRow(row, item) {
            const descInput     = row.querySelector('.item-description-input');
            const phocodeInput  = row.querySelector('.item-phocode-input');
            const itemIdHidden  = row.querySelector('.item-id-select');
            const uomInput      = row.querySelector('.item-uom-input');
            const unitCostInput = row.querySelector('.item-unit-cost-input');
            const quantityInput = row.querySelector('.item-quantity-input');
            const lotInput      = row.querySelector('.item-lot-input');
            const expiryInput   = row.querySelector('.item-expiry-input');

            if (descInput)     descInput.value     = item.name;
            if (phocodeInput)  phocodeInput.value  = item.code || '';
            if (itemIdHidden)  itemIdHidden.value  = item.id || '';
            if (uomInput)      uomInput.value      = item.uom || '';
            if (unitCostInput) unitCostInput.value = item.cost || '';
            if (lotInput)      lotInput.value      = item.lot_number || '';
            if (expiryInput)   expiryInput.value   = item.expiry || '';
            if (quantityInput) quantityInput.placeholder = 'Available: ' + (item.qty || 0);
            calcTotal(row);
        }

        function calcTotal(row) {
            const qty  = parseFloat(row.querySelector('.item-quantity-input').value) || 0;
            const cost = parseFloat(row.querySelector('.item-unit-cost-input').value) || 0;
            const disp = row.querySelector('.item-totalcost-display');
            if (disp) {
                disp.value = qty && cost ? (qty * cost).toFixed(2) : '';
            }
        }

        function bindPhocodeAutocomplete(row) {
            const phocodeInput = row.querySelector('.item-phocode-input');
            if (!phocodeInput) return;

            const dd = document.createElement('div');
            dd.className = 'autocomplete-dropdown';
            Object.assign(dd.style, {
                position: 'absolute',
                background: 'var(--surface,#fff)',
                border: '1px solid var(--border,#ddd)',
                maxHeight: '200px',
                overflowY: 'auto',
                width: '100%',
                zIndex: '1000',
                display: 'none',
                boxShadow: '0 4px 6px rgba(0,0,0,.1)',
                top: '100%',
                left: '0',
                marginTop: '4px'
            });
            phocodeInput.parentElement.style.position = 'relative';
            phocodeInput.parentElement.appendChild(dd);

            function showOptions(query) {
                dd.innerHTML = '';
                const q = query.toLowerCase().trim();
                const seen = new Set();
                const filtered = allItemsData.filter(function(item) {
                    if (q && !((item.code && item.code.toLowerCase().includes(q)) || item.name.toLowerCase().includes(q))) return false;
                    if (seen.has(item.code)) return false;
                    seen.add(item.code);
                    return true;
                });
                if (!filtered.length) { dd.style.display = 'none'; return; }
                filtered.forEach(function(item) {
                    const opt = document.createElement('div');
                    Object.assign(opt.style, { padding: '10px 12px', cursor: 'pointer', borderBottom: '1px solid #f0f0f0' });
                    opt.textContent = item.code + ' — ' + item.name + ' (' + (item.qty || 0) + ' available' + (item.expiry ? ' | Exp: ' + item.expiry : '') + ')';
                    opt.addEventListener('mouseover', function() { this.style.background = '#f5f5f5'; });
                    opt.addEventListener('mouseout',  function() { this.style.background = 'transparent'; });
                    opt.addEventListener('click', function() {
                        phocodeInput.value = item.code;
                        dd.style.display = 'none';
                        applyItemToRow(row, item);
                    });
                    dd.appendChild(opt);
                });
                dd.style.display = 'block';
            }

            phocodeInput.addEventListener('input', function () { showOptions(this.value); });
            phocodeInput.addEventListener('focus', function () { showOptions(this.value); });
            phocodeInput.addEventListener('blur',  function () { setTimeout(function () { dd.style.display = 'none'; }, 200); });
        }

        function bindRowEvents(row) {
            const body          = row.querySelector('.item-row-body');
            const toggleButton  = row.querySelector('.item-toggle-button');
            const removeButton  = row.querySelector('.remove-item-button');
            const descInput     = row.querySelector('.item-description-input');
            const phocodeInput  = row.querySelector('.item-phocode-input');
            const quantityInput = row.querySelector('.item-quantity-input');
            const unitCostInput = row.querySelector('.item-unit-cost-input');
            const clearBtns     = row.querySelectorAll('.item-description-clear');

            if (descInput) {
                const dd = createDropdown(descInput);

                const syncItemSelection = () => {
                    const typed = descInput.value.trim().toLowerCase();
                    if (!typed) return;

                    let match = allItemsData.find(i => i.name.toLowerCase() === typed || (i.code && i.code.toLowerCase() === typed) || i.id.toString() === typed);
                    if (!match) {
                        match = allItemsData.find(i => i.name.toLowerCase().includes(typed) || (i.code && i.code.toLowerCase().includes(typed)));
                    }
                    if (match) {
                        applyItemToRow(row, match);
                        const expiryInput = row.querySelector('.item-expiry-input');
                        if (expiryInput && match.expiry) expiryInput.value = match.expiry;
                    }
                };

                descInput.addEventListener('input',  e => showItemOptions(descInput, dd, e.target.value, syncItemSelection));
                descInput.addEventListener('focus',  () => showItemOptions(descInput, dd, descInput.value, syncItemSelection));
                descInput.addEventListener('blur',   () => setTimeout(() => dd.style.display = 'none', 200));
                descInput.addEventListener('change', syncItemSelection);
            }

            bindPhocodeAutocomplete(row);

            if (quantityInput) quantityInput.addEventListener('input',  () => calcTotal(row));
            if (unitCostInput) unitCostInput.addEventListener('input', () => calcTotal(row));

            clearBtns.forEach(clearBtn => {
                clearBtn.addEventListener('mousedown', e => e.preventDefault());
                clearBtn.addEventListener('click', () => {
                    if (descInput) descInput.value = '';
                    if (phocodeInput) phocodeInput.value = '';
                    const itemIdHidden = row.querySelector('.item-id-select');
                    if (itemIdHidden) itemIdHidden.value = '';
                    const releaseItemId = row.querySelector('.release-item-id');
                    if (releaseItemId) releaseItemId.value = '';
                    const uomInput = row.querySelector('.item-uom-input');
                    if (uomInput) uomInput.value = '';
                    const unitCostInput = row.querySelector('.item-unit-cost-input');
                    if (unitCostInput) unitCostInput.value = '';
                    const lotInput = row.querySelector('.item-lot-input');
                    if (lotInput) lotInput.value = '';
                    const expiryInput = row.querySelector('.item-expiry-input');
                    if (expiryInput) expiryInput.value = '';
                    const totalDisp = row.querySelector('.item-totalcost-display');
                    if (totalDisp) totalDisp.value = '';
                    if (quantityInput) {
                        quantityInput.value = '';
                        quantityInput.placeholder = 'Available: 0';
                    }
                    const dd = descInput ? descInput.parentElement.querySelector('.autocomplete-dropdown') : null;
                    if (dd) dd.style.display = 'none';
                    if (descInput) descInput.focus();
                });
            });

            if (toggleButton) {
                toggleButton.addEventListener('click', () => {
                    body.style.display = body.style.display === 'none' ? '' : 'none';
                    toggleButton.textContent = body.style.display === 'none' ? 'Show' : 'Hide';
                });
            }
            if (removeButton) {
                removeButton.addEventListener('click', () => { row.remove(); updateIndexes(); });
            }

            calcTotal(row);
        }

        Array.from(releaseItems.querySelectorAll('.release-item-row')).forEach(row => {
            bindRowEvents(row);
            const descInput    = row.querySelector('.item-description-input');
            const phocodeInput = row.querySelector('.item-phocode-input');
            const itemIdHidden = row.querySelector('.item-id-select');
            const qtyInput     = row.querySelector('.item-quantity-input');
            if (descInput && descInput.value.trim()) {
                const lower = descInput.value.trim().toLowerCase();
                const match = allItemsData.find(i => i.name.toLowerCase() === lower || (i.code && i.code.toLowerCase() === lower));
                if (match) {
                    if (phocodeInput && !phocodeInput.value) phocodeInput.value = match.code;
                    if (itemIdHidden && !itemIdHidden.value) itemIdHidden.value = match.id;
                    if (qtyInput && (!qtyInput.placeholder || qtyInput.placeholder === 'Available: 0')) {
                        qtyInput.placeholder = 'Available: ' + (match.qty || 0);
                    }
                }
            }
        });
        updateIndexes();

        if (addItemButton) {
            addItemButton.addEventListener('click', () => {
                const clone = itemTemplate.content.cloneNode(true);
                const row   = clone.querySelector('.release-item-row');
                bindRowEvents(row);
                releaseItems.appendChild(row);
                updateIndexes();
            });
        }

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
                const itemIdSelect = row.querySelector('.item-id-select');
                if (itemIdSelect && itemIdSelect.value) {
                    return;
                }
                const descInput = row.querySelector('.item-description-input');
                if (descInput && descInput.value.trim()) {
                    const typed = descInput.value.trim().toLowerCase();
                    let match = allItemsData.find(i => i.name.toLowerCase() === typed || (i.code && i.code.toLowerCase() === typed) || i.id.toString() === typed);
                    if (!match) {
                        match = allItemsData.find(i => i.name.toLowerCase().includes(typed) || (i.code && i.code.toLowerCase().includes(typed)));
                    }
                    if (match) {
                        applyItemToRow(row, match);
                    }
                }
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
    })();
    </script>

    <script>
    (function () {
        var statusSelect = document.getElementById('releaseStatusSelect');
        var statusReasonGroup = document.getElementById('statusReasonGroup');
        if (statusSelect && statusReasonGroup) {
            var update = function () {
                statusReasonGroup.style.display = ['Canceled', 'Returned'].includes(statusSelect.value) ? '' : 'none';
            };
            statusSelect.addEventListener('change', update);
            update();
        }
    })();
    </script>
    @endpush
@endsection
