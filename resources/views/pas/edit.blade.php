@extends('layouts.app')

@section('title', 'Edit PAS ' . $pas->pas_number)
@section('pageHeading', 'Edit PAS ' . $pas->pas_number)

@section('content')
<section class="card">
    <div class="section-header">
        <div>
            <h1 class="page-heading">Edit Property Allocation Slip</h1>
            <p class="page-description">Update PAS details without affecting inventory stock.</p>
        </div>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <a href="{{ route('pas.view', $pas) }}" class="btn btn-secondary">Back to PAS</a>
        </div>
    </div>

    <form action="{{ route('pas.update', $pas) }}" method="POST" class="stack" id="pasForm">
        @csrf
        @method('PUT')

        <div class="form-grid-3">
            <div class="form-group">
                <label>PAS Number <span style="color:var(--danger)">*</span></label>
                <input name="pas_number" value="{{ old('pas_number', $pas->pas_number) }}" required>
                @error('pas_number')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Date of PASS <span style="color:var(--danger)">*</span></label>
                <input type="date" name="date_of_pass" value="{{ old('date_of_pass', optional($pas->date_of_pass)->format('Y-m-d')) }}" required>
                @error('date_of_pass')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Date Released</label>
                <input type="date" name="date_released" value="{{ old('date_released', optional($pas->date_released)->format('Y-m-d')) }}">
                @error('date_released')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-grid-3">
            <div class="form-group">
                <label>Supplier</label>
                <select name="supplier_id">
                    <option value="">— Select Supplier —</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $pas->supplier_id) == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->company_name }}
                        </option>
                    @endforeach
                </select>
                @error('supplier_id')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Stock Keeping Unit (Program)</label>
                <div style="position:relative;">
                    <input name="program" id="pasProgramInput" value="{{ old('program', $pas->program) }}" autocomplete="off" style="width:100%;">
                    <div id="pasProgramDropdown" style="position:absolute;top:100%;left:0;width:100%;z-index:1000;display:none;"></div>
                </div>
                @error('program')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Preferred Transfer Type</label>
                <select name="transfer_type" id="pasTransferTypeSelect">
                    <option value="PTR" {{ old('transfer_type', $pas->transfer_type ?? 'PTR') === 'PTR' ? 'selected' : '' }}>PTR</option>
                    <option value="ITR" {{ old('transfer_type', $pas->transfer_type) === 'ITR' ? 'selected' : '' }}>ITR</option>
                    <option value="RIS" {{ old('transfer_type', $pas->transfer_type) === 'RIS' ? 'selected' : '' }}>RIS</option>
                </select>
                @error('transfer_type')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-grid-3">
            <div class="form-group">
                <label>Facility / End-user <span style="color:var(--danger)">*</span></label>
                <div style="position:relative;">
                    <input name="facility_name" id="pasFacilityInput" value="{{ old('facility_name', $pas->facility_name) }}" required autocomplete="off" style="width:100%;">
                    <div id="pasFacilityDropdown" style="position:absolute;top:100%;left:0;width:100%;z-index:1000;display:none;"></div>
                </div>
                @error('facility_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Facility Coordinator <span style="color:var(--danger)">*</span></label>
                <div style="position:relative;">
                    <input name="facility_coordinator" id="pasCoordinatorInput" value="{{ old('facility_coordinator', $pas->facility_coordinator) }}" required autocomplete="off" style="width:100%;">
                    <div id="pasCoordinatorDropdown" style="position:absolute;top:100%;left:0;width:100%;z-index:1000;display:none;"></div>
                </div>
                @error('facility_coordinator')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Purpose / Activity</label>
                <input name="purpose_activity" value="{{ old('purpose_activity', $pas->purpose_activity) }}" placeholder="e.g. Immunization Drive, Health Program Distribution">
                @error('purpose_activity')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div>
            <h2 class="section-title">Items <span style="color:var(--danger)">*</span></h2>
            <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">These items are for tracking only — inventory stock will NOT be deducted.</p>
            @error('items')<span class="field-error" style="display:block;margin-bottom:0.5rem;">{{ $message }}</span>@enderror

            <div id="pas-items" class="stack">
                @php
                    $oldItems = collect(old('items'));
                    if ($oldItems->isEmpty()) {
                        $oldItems = $pas->items->values()->map(function ($item) {
                            return [
                                'pas_item_id' => $item->id,
                                'item_description' => $item->item_description,
                                'product_code' => $item->product_code,
                                'lot_number' => $item->lot_number,
                                'expiration_date' => optional($item->expiration_date)->format('Y-m-d'),
                                'quantity' => $item->quantity,
                                'unit' => $item->unit,
                                'unit_cost' => $item->unit_cost,
                                'item_id' => $item->item_id,
                            ];
                        });
                    } else {
                        $oldItems = $oldItems->values();
                    }
                    if ($oldItems->isEmpty()) {
                        $oldItems = collect([[ 'item_description' => '', 'product_code' => '', 'lot_number' => '', 'expiration_date' => '', 'quantity' => '', 'unit' => '', 'unit_cost' => '', 'item_id' => '', 'pas_item_id' => '' ]]);
                    }
                @endphp

                @foreach($oldItems as $index => $oldItem)
                    <div class="section-card pas-item-row" data-index="{{ $index }}">
                        <div class="item-row-header">
                            <div class="item-row-title">Item {{ $index + 1 }}</div>
                            <div class="item-row-actions">
                                <button type="button" class="btn btn-link item-toggle-button">Hide</button>
                                <button type="button" class="btn btn-danger remove-item-button" @if($index === 0) style="display:none;" @endif>Delete</button>
                            </div>
                        </div>
                        <div class="item-row-body">
                            <input type="hidden" name="items[{{ $index }}][pas_item_id]" value="{{ $oldItem['pas_item_id'] ?? '' }}">
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>Item Description <span style="color:var(--danger)">*</span></label>
                                    <div style="position:relative;display:flex;align-items:center;">
                                        <input type="text" class="pas-desc-input" name="items[{{ $index }}][item_description]" value="{{ $oldItem['item_description'] ?? '' }}" autocomplete="off" style="width:100%;padding-right:2rem;">
                                        <button type="button" class="item-description-clear" title="Clear" style="position:absolute;right:0.5rem;background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;line-height:1;padding:0.2rem 0.3rem;">&times;</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Product Code</label>
                                    <select class="pas-product-select" name="items[{{ $index }}][item_id]">
                                        <option value="">Select product</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" {{ (string)($oldItem['item_id'] ?? '') === (string)$item->id ? 'selected' : '' }}>{{ $item->item_code ?? $item->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" class="pas-product-code-input" name="items[{{ $index }}][product_code]" value="{{ $oldItem['product_code'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Lot Number</label>
                                    <input class="pas-lot-input" name="items[{{ $index }}][lot_number]" value="{{ $oldItem['lot_number'] ?? '' }}">
                                </div>
                            </div>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>Expiration Date</label>
                                    <input type="date" class="pas-expiry-input" name="items[{{ $index }}][expiration_date]" value="{{ $oldItem['expiration_date'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Quantity <span style="color:var(--danger)">*</span></label>
                                    <input type="number" class="pas-qty-input" name="items[{{ $index }}][quantity]" value="{{ $oldItem['quantity'] ?? '' }}" min="1" placeholder="Available: 0" required>
                                </div>
                                <div class="form-group">
                                    <label>Unit <span style="color:var(--danger)">*</span></label>
                                    <input class="pas-unit-input" name="items[{{ $index }}][unit]" value="{{ $oldItem['unit'] ?? '' }}" required>
                                </div>
                            </div>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>Unit Cost <span style="color:var(--danger)">*</span></label>
                                    <input type="number" step="0.01" class="pas-unitcost-input" name="items[{{ $index }}][unit_cost]" value="{{ $oldItem['unit_cost'] ?? '' }}" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label>Total Cost</label>
                                    <input type="text" class="pas-totalcost-display" readonly placeholder="Auto-calculated" style="background:var(--surface-strong);cursor:not-allowed;">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-pas-item" class="btn btn-secondary" style="margin-top:0.75rem;">+ Add Item</button>
        </div>

        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="3">{{ old('notes', $pas->notes) }}</textarea>
        </div>

        <div class="form-actions" style="gap:0.75rem;display:flex;flex-wrap:wrap;align-items:center;">
            <button type="submit" class="btn btn-primary">Update PAS</button>
            <a href="{{ route('pas.view', $pas) }}" class="btn btn-ghost" id="cancelBtn">Cancel</a>
        </div>
    </form>
</section>

<datalist id="pas-program-options" style="display:none;">
    @foreach($programs as $p)
        <option value="{{ $p->name }}"></option>
    @endforeach
</datalist>
<datalist id="pas-coordinator-options" style="display:none;">
    @foreach($coordinators as $c)
        <option value="{{ $c->full_name }}" data-programs="{{ $c->assigned_programs }}"></option>
    @endforeach
</datalist>
<datalist id="pas-facility-options" style="display:none;">
    @foreach($facilities as $f)
        <option value="{{ $f }}"></option>
    @endforeach
</datalist>

<template id="pas-item-template">
    <div class="section-card pas-item-row">
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
                    <label>Item Description <span style="color:var(--danger)">*</span></label>
                    <div style="position:relative;display:flex;align-items:center;">
                        <input type="text" class="pas-desc-input" name="items[0][item_description]" value="" autocomplete="off" style="width:100%;padding-right:2rem;">
                        <button type="button" class="item-description-clear" title="Clear" style="position:absolute;right:0.5rem;background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;line-height:1;padding:0.2rem 0.3rem;">&times;</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Product Code</label>
                    <select class="pas-product-select" name="items[0][item_id]">
                        <option value="">Select product</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ $item->item_code ?? $item->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" class="pas-product-code-input" name="items[0][product_code]" value="">
                </div>
                <div class="form-group">
                    <label>Lot Number</label>
                    <input class="pas-lot-input" name="items[0][lot_number]" value="">
                </div>
            </div>
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Expiration Date</label>
                    <input type="date" class="pas-expiry-input" name="items[0][expiration_date]" value="">
                </div>
                <div class="form-group">
                    <label>Quantity <span style="color:var(--danger)">*</span></label>
                    <input type="number" class="pas-qty-input" name="items[0][quantity]" value="" min="1" required>
                </div>
                <div class="form-group">
                    <label>Unit <span style="color:var(--danger)">*</span></label>
                    <input class="pas-unit-input" name="items[0][unit]" value="" required>
                </div>
            </div>
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Unit Cost <span style="color:var(--danger)">*</span></label>
                    <input type="number" step="0.01" class="pas-unitcost-input" name="items[0][unit_cost]" value="" min="0" required>
                </div>
                <div class="form-group">
                    <label>Total Cost</label>
                    <input type="text" class="pas-totalcost-display" readonly placeholder="Auto-calculated" style="background:var(--surface-strong);cursor:not-allowed;">
                </div>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
const pasAllItems = {!! json_encode($items->map(fn($i) => [
    'id'         => $i->id,
    'code'       => $i->item_code,
    'name'       => $i->name,
    'unit'       => $i->unit,
    'cost'       => $i->unit_cost,
    'qty'        => $i->quantity_on_hand,
    'lot_number' => $itemLotNumbers[$i->id]['lot_number'] ?? '',
    'expiry'     => $itemLotNumbers[$i->id]['expiry_date'] ?? '',
])->values()->toArray()) !!};

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('pas-items');
    const template  = document.getElementById('pas-item-template');
    const addBtn    = document.getElementById('add-pas-item');

    const pasPrograms = Array.from(document.querySelectorAll('#pas-program-options option')).map(o => ({
        name: o.value, nameLower: o.value.toLowerCase()
    }));
    const pasCoordinators = Array.from(document.querySelectorAll('#pas-coordinator-options option')).map(o => ({
        name: o.value, nameLower: o.value.toLowerCase(), assignedPrograms: o.dataset.programs || ''
    }));
    const pasFacilities = Array.from(document.querySelectorAll('#pas-facility-options option')).map(o => ({
        name: o.value, nameLower: o.value.toLowerCase()
    }));

    const pasProgramInput       = document.getElementById('pasProgramInput');
    const pasCoordinatorInput   = document.getElementById('pasCoordinatorInput');
    const pasProgramDropdown    = document.getElementById('pasProgramDropdown');
    const pasCoordinatorDropdown = document.getElementById('pasCoordinatorDropdown');
    const pasFacilityInput      = document.getElementById('pasFacilityInput');
    const pasFacilityDropdown   = document.getElementById('pasFacilityDropdown');

    function bindAutocompleteList(input, dataList, dropdown, onSelect) {
        function show(q) {
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
        input.addEventListener('input',  e => show(e.target.value));
        input.addEventListener('focus',  () => show(input.value));
        input.addEventListener('blur',   () => setTimeout(() => dropdown.style.display = 'none', 200));
    }

    if (pasFacilityInput && pasFacilityDropdown) {
        bindAutocompleteList(pasFacilityInput, pasFacilities, pasFacilityDropdown, null);
    }

    bindAutocompleteList(pasProgramInput, pasPrograms, pasProgramDropdown, function (item) {
        const matched = pasCoordinators.find(c => c.assignedPrograms.toLowerCase().includes(item.nameLower));
        if (matched && pasCoordinatorInput && !pasCoordinatorInput.value.trim()) pasCoordinatorInput.value = matched.name;
    });

    bindAutocompleteList(pasCoordinatorInput, pasCoordinators, pasCoordinatorDropdown, function (item) {
        if (item.assignedPrograms && pasProgramInput && !pasProgramInput.value.trim()) {
            pasProgramInput.value = item.assignedPrograms.split(', ')[0] || '';
        }
    });

    function updateIndexes() {
        container.querySelectorAll('.pas-item-row').forEach((row, i) => {
            row.dataset.index = i;
            row.querySelector('.item-row-title').textContent = 'Item ' + (i + 1);
            row.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace(/items\[\d+\]/, 'items[' + i + ']');
            });
            const del = row.querySelector('.remove-item-button');
            del.style.display = i === 0 ? 'none' : '';
        });
    }

    function buildDropdown(descInput) {
        let dd = descInput.parentElement.querySelector('.autocomplete-dropdown');
        if (dd) dd.remove();
        dd = document.createElement('div');
        dd.className = 'autocomplete-dropdown';
        Object.assign(dd.style, {
            position:'absolute', background:'var(--surface, #fff)', border:'1px solid var(--border, #ddd)',
            maxHeight:'200px', overflowY:'auto', width:'100%', zIndex:'1000',
            display:'none', boxShadow:'0 4px 6px rgba(0,0,0,.1)', top:'100%', left:'0', marginTop:'4px'
        });
        descInput.parentElement.style.position = 'relative';
        descInput.parentElement.appendChild(dd);
        return dd;
    }

    function showOptions(descInput, dd, query, onSelect) {
        dd.innerHTML = '';
        const q = query.toLowerCase().trim();
        const seen = new Set();
        const filtered = pasAllItems.filter(it => {
            if (q && !it.name.toLowerCase().includes(q)) return false;
            if (seen.has(it.name.toLowerCase())) return false;
            seen.add(it.name.toLowerCase());
            return true;
        });
        if (!filtered.length) { dd.style.display = 'none'; return; }
        filtered.forEach(it => {
            const opt = document.createElement('div');
            opt.textContent = it.name;
            Object.assign(opt.style, { padding:'10px 12px', cursor:'pointer', borderBottom:'1px solid #f0f0f0' });
            opt.addEventListener('mouseover', () => opt.style.background = '#f5f5f5');
            opt.addEventListener('mouseout',  () => opt.style.background = 'transparent');
            opt.addEventListener('click', () => { descInput.value = it.name; dd.style.display = 'none'; onSelect(); });
            dd.appendChild(opt);
        });
        dd.style.display = 'block';
    }

    function populateProductSelect(sel, name) {
        const lower = name.toLowerCase().trim();
        sel.innerHTML = '<option value="">Select product</option>';
        const matches = pasAllItems.filter(it =>
            it.name.toLowerCase() === lower ||
            it.code.toLowerCase() === lower ||
            it.name.toLowerCase().includes(lower) ||
            it.code.toLowerCase().includes(lower)
        );
        matches.forEach(it => {
            const o = document.createElement('option');
            o.value = it.id;
            const expiryText = it.expiry ? ' | Exp: ' + it.expiry : '';
            o.textContent = it.code + ' — ' + it.name + ' (' + (it.qty || 0) + ' available' + expiryText + ')';
            o.dataset.unit = it.unit;
            o.dataset.cost = it.cost;
            o.dataset.qty = it.qty;
            o.dataset.lot  = it.lot_number;
            o.dataset.expiry = it.expiry;
            sel.appendChild(o);
        });
        return matches;
    }

    function calcTotal(row) {
        const qty  = parseFloat(row.querySelector('.pas-qty-input').value) || 0;
        const cost = parseFloat(row.querySelector('.pas-unitcost-input').value) || 0;
        const disp = row.querySelector('.pas-totalcost-display');
        disp.value = qty && cost ? (qty * cost).toFixed(2) : '';
    }

    function autofillFromOption(row, opt) {
        if (!opt || !opt.value) return;
        const unitInput   = row.querySelector('.pas-unit-input');
        const costInput   = row.querySelector('.pas-unitcost-input');
        const lotInput    = row.querySelector('.pas-lot-input');
        const expiryInput = row.querySelector('.pas-expiry-input');
        const codeInput   = row.querySelector('.pas-product-code-input');
        const qtyInput    = row.querySelector('.pas-qty-input');
        if (unitInput  && opt.dataset.unit)   unitInput.value   = opt.dataset.unit;
        if (costInput  && opt.dataset.cost)   costInput.value   = opt.dataset.cost;
        if (lotInput   && opt.dataset.lot)    lotInput.value    = opt.dataset.lot;
        if (expiryInput) {
            if (opt.dataset.expiry) {
                expiryInput.value = opt.dataset.expiry;
            } else {
                expiryInput.value = '';
            }
        }
        if (codeInput  && opt.textContent)    codeInput.value   = opt.textContent.split(' — ')[0] ?? '';
        if (qtyInput) qtyInput.placeholder = 'Available: ' + (opt.dataset.qty || 0);
        calcTotal(row);
    }

    function bindRow(row) {
        const body       = row.querySelector('.item-row-body');
        const toggleBtn  = row.querySelector('.item-toggle-button');
        const removeBtn  = row.querySelector('.remove-item-button');
        const descInput  = row.querySelector('.pas-desc-input');
        const productSel = row.querySelector('.pas-product-select');
        const qtyInput   = row.querySelector('.pas-qty-input');
        const costInput  = row.querySelector('.pas-unitcost-input');
        const clearBtn   = row.querySelector('.item-description-clear');

        const dd = buildDropdown(descInput);

        const syncFromDesc = () => {
            const name = descInput.value.trim();
            const matches = populateProductSelect(productSel, name);
            if (productSel.options.length > 1) {
                productSel.selectedIndex = 1;
                autofillFromOption(row, productSel.options[1]);
            }
            const qtyInput = row.querySelector('.pas-qty-input');
            if (qtyInput && matches.length) {
                qtyInput.placeholder = 'Available: ' + (matches[0].qty || 0);
            }
        };

        descInput.addEventListener('input',  e => showOptions(descInput, dd, e.target.value, syncFromDesc));
        descInput.addEventListener('focus',  () => showOptions(descInput, dd, descInput.value, syncFromDesc));
        descInput.addEventListener('blur',   () => setTimeout(() => dd.style.display = 'none', 200));
        descInput.addEventListener('change', syncFromDesc);

        productSel.addEventListener('change', () => {
            const opt = productSel.options[productSel.selectedIndex];
            autofillFromOption(row, opt);
            if (opt && opt.dataset.qty) {
                qtyInput.placeholder = 'Available: ' + opt.dataset.qty;
            }
            if (opt && opt.dataset.expiry && row.querySelector('.pas-expiry-input')) {
                row.querySelector('.pas-expiry-input').value = opt.dataset.expiry;
            }
        });

        qtyInput.addEventListener('input',  () => calcTotal(row));
        costInput.addEventListener('input', () => calcTotal(row));

        clearBtn.addEventListener('mousedown', e => e.preventDefault());
        clearBtn.addEventListener('click', () => {
            descInput.value = '';
            productSel.innerHTML = '<option value="">Select product</option>';
            row.querySelector('.pas-unit-input').value  = '';
            row.querySelector('.pas-unitcost-input').value = '';
            row.querySelector('.pas-lot-input').value   = '';
            row.querySelector('.pas-expiry-input').value = '';
            row.querySelector('.pas-totalcost-display').value = '';
            dd.style.display = 'none';
            descInput.focus();
        });

        toggleBtn.addEventListener('click', () => {
            body.style.display = body.style.display === 'none' ? '' : 'none';
            toggleBtn.textContent = body.style.display === 'none' ? 'Show' : 'Hide';
        });

        removeBtn.addEventListener('click', () => { row.remove(); updateIndexes(); });
    }

    container.querySelectorAll('.pas-item-row').forEach(bindRow);
    updateIndexes();

    addBtn.addEventListener('click', () => {
        const clone = template.content.cloneNode(true);
        const row   = clone.querySelector('.pas-item-row');
        bindRow(row);
        container.appendChild(row);
        updateIndexes();
    });
});
</script>
@endpush
@endsection