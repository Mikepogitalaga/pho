@extends('layouts.app')

@section('title', 'New Property Allocation Slip')
@section('pageHeading', 'New PAS')

@php
    $isProgramUser = (bool) auth()->user()?->program_id;
@endphp

@push('styles')
<style>
/* Program users only fill in Item Description, Quantity and Unit: let those
   three fields flow into a single grid row. */
.pas-items--program .item-row-body {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
    align-items: start;
}
.pas-items--program .form-grid-3 { display: contents; }

@media (max-width: 1024px) {
    .pas-items--program .item-row-body { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 768px) {
    .pas-items--program .item-row-body { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<section class="card">
    <div class="section-header">
        <div>
            <h1 class="page-heading">New Property Allocation Slip</h1>
            <p class="page-description">Items added here will NOT be deducted from inventory.</p>
        </div>
        <a href="{{ route('pas.index') }}" class="btn btn-secondary">Back to PAS</a>
    </div>

    <form action="{{ route('pas.store') }}" method="POST" class="stack" id="pasForm">
        @csrf

        {{-- Row 1: Identification & Dates --}}
        <div class="form-grid-3">
            <div class="form-group">
                <label>PAS Number <span style="color:var(--danger)">*</span></label>
                @if(auth()->user()?->program_id)
                    <input type="hidden" name="pas_number" value="{{ old('pas_number', $pasNumber) }}">
                    <p style="font-size:0.85rem; color:var(--text-muted);">PAS Number will be assigned by the administrator upon approval.</p>
                @else
                    <input name="pas_number" value="{{ old('pas_number', $pasNumber) }}" required readonly style="background: var(--surface-strong); cursor: not-allowed;">
                @endif
                @error('pas_number')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Date of PASS <span style="color:var(--danger)">*</span></label>
                <input type="date" name="date_of_pass" value="{{ old('date_of_pass', now()->toDateString()) }}" required>
                @error('date_of_pass')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            @if(! auth()->user()?->program_id)
            <div class="form-group">
                <label>Date Released</label>
                <input type="date" name="date_released" value="{{ old('date_released') }}">
                @error('date_released')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            @endif
        </div>

        {{-- Row 2: Facility Info --}}
        <div class="form-grid-3">
            <div class="form-group">
                <label>Facility / End-user <span style="color:var(--danger)">*</span></label>
                <select name="facility_name" id="facilityName" required>
                    <option value="">— Select Facility —</option>
                    @foreach($facilities->groupBy('category') as $cat => $group)
                        <optgroup label="{{ $cat ?: 'Other' }}">
                            @foreach($group as $f)
                                <option value="{{ $f->name }}" {{ old('facility_name') === $f->name ? 'selected' : '' }}>{{ $f->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @if(! auth()->user()?->program_id)
                <a href="{{ route('facilities.index') }}" class="section-link" style="font-size:0.78rem;margin-top:0.35rem;display:inline-block;">+ Manage Facilities</a>
                @endif
                @error('facility_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Facility Coordinator <span style="color:var(--danger)">*</span></label>
                <div style="position:relative;">
                    <input name="facility_coordinator" id="pasCoordinatorInput"
                        value="{{ old('facility_coordinator', auth()->user()?->program_id ? auth()->user()->name : '') }}" required autocomplete="off" style="width:100%;">
                    <div id="pasCoordinatorDropdown" style="position:absolute;top:100%;left:0;width:100%;z-index:1000;display:none;"></div>
                </div>
                @error('facility_coordinator')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            @if(! auth()->user()?->program_id)
            <div class="form-group">
                <label>Select Type <span style="color:var(--danger)">*</span></label>
                <select name="transfer_type" id="pasTransferTypeSelect">
                    <option value="PTR" {{ old('transfer_type', 'PTR') === 'PTR' ? 'selected' : '' }}>PTR</option>
                    <option value="ITR" {{ old('transfer_type') === 'ITR' ? 'selected' : '' }}>ITR</option>
                    <option value="RIS" {{ old('transfer_type') === 'RIS' ? 'selected' : '' }}>RIS</option>
                </select>
                @error('transfer_type')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            @endif
        </div>

        {{-- Row 3: Program & Purpose --}}
        <div class="form-grid-3">
            <div class="form-group">
                <label>Stock Keeping Unit (Program)</label>
                @if(auth()->user()?->program_id)
                    <select name="program" style="width:100%;">
                        @foreach($programs as $program)
                            <option value="{{ $program->name }}" {{ old('program', $programs->first()->name ?? '') === $program->name ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <div style="position:relative;">
                        <input name="program" id="pasProgramInput" value="{{ old('program') }}" autocomplete="off" style="width:100%;">
                        <div id="pasProgramDropdown" style="position:absolute;top:100%;left:0;width:100%;z-index:1000;display:none;"></div>
                    </div>
                @endif
                @error('program')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Purpose / Activity</label>
                <input name="purpose_activity" value="{{ old('purpose_activity') }}" placeholder="e.g. Immunization Drive, Health Program Distribution">
                @error('purpose_activity')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        {{-- Items --}}
        <div>
            <h2 class="section-title">Items <span style="color:var(--danger)">*</span></h2>
            <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">
                @if($isProgramUser)
                    Enter the item description, quantity and unit. PHO code, lot number, expiry date and unit cost are completed by the administrator.
                @else
                    These items are for tracking only — inventory stock will NOT be deducted.
                @endif
            </p>
            @error('items')<span class="field-error" style="display:block;margin-bottom:0.5rem;">{{ $message }}</span>@enderror

            <div id="pas-items" class="stack{{ $isProgramUser ? ' pas-items--program' : '' }}">
                @php
                    $oldItems = collect(old('items', []))->values()->all();
                    if (empty($oldItems)) {
                        $oldItems = [['item_description'=>'','product_code'=>'','lot_number'=>'','expiration_date'=>'','quantity'=>'','unit'=>'','unit_cost'=>'','item_id'=>'']];
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
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>Item Description <span style="color:var(--danger)">*</span></label>
                                    <div style="position:relative;display:flex;align-items:center;">
                                        <input type="text" class="pas-desc-input" name="items[{{ $index }}][item_description]"
                                            value="{{ $oldItem['item_description'] ?? '' }}" autocomplete="off" style="width:100%;padding-right:2rem;">
                                        <button type="button" class="item-description-clear" title="Clear"
                                            style="position:absolute;right:0.5rem;background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;line-height:1;padding:0.2rem 0.3rem;">&times;</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    @if(! $isProgramUser)
                                    <label>PHO Code</label>
                                    <div style="position:relative;display:flex;align-items:center;">
                                        <input type="text" class="pas-phocode-input" autocomplete="off" style="width:100%;padding-right:2rem;" value="{{ $oldItem['item_code'] ?? '' }}">
                                        <button type="button" class="item-description-clear" title="Clear"
                                            style="position:absolute;right:0.5rem;background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;line-height:1;padding:0.2rem 0.3rem;">&times;</button>
                                    </div>
                                    @endif
                                    <input type="hidden" class="pas-item-code-hidden" name="items[{{ $index }}][item_code]" value="{{ $oldItem['item_code'] ?? '' }}">
                                    <input type="hidden" class="pas-item-id-hidden" name="items[{{ $index }}][item_id]" value="{{ $oldItem['item_id'] ?? '' }}">
                                    <input type="hidden" class="pas-product-code-input" name="items[{{ $index }}][product_code]" value="{{ $oldItem['product_code'] ?? '' }}">
                                </div>
                                @if(! $isProgramUser)
                                <div class="form-group">
                                    <label>Lot Number</label>
                                    <input class="pas-lot-input" name="items[{{ $index }}][lot_number]" value="{{ $oldItem['lot_number'] ?? '' }}">
                                </div>
                                @endif
                            </div>
                            <div class="form-grid-3">
                                @if(! $isProgramUser)
                                <div class="form-group">
                                    <label>Expiration Date</label>
                                    <input type="date" class="pas-expiry-input" name="items[{{ $index }}][expiration_date]" value="{{ $oldItem['expiration_date'] ?? '' }}">
                                </div>
                                @endif
                                <div class="form-group">
                                    <label>Quantity <span style="color:var(--danger)">*</span></label>
                                    <input type="number" class="pas-qty-input" name="items[{{ $index }}][quantity]" value="{{ $oldItem['quantity'] ?? '' }}" min="1" placeholder="Available: 0" required>
                                </div>
                                <div class="form-group">
                                    <label>Unit <span style="color:var(--danger)">*</span></label>
                                    <input class="pas-unit-input" name="items[{{ $index }}][unit]" value="{{ $oldItem['unit'] ?? '' }}" required>
                                </div>
                            </div>
                            @if(! $isProgramUser)
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
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-pas-item" class="btn btn-secondary" style="margin-top:0.75rem;">+ Add Item</button>
        </div>

        @if(! auth()->user()?->program_id)
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="3">{{ old('notes') }}</textarea>
        </div>
        @endif

        <div class="form-actions" style="gap:0.75rem;display:flex;flex-wrap:wrap;align-items:center;">
            <button type="submit" class="btn btn-primary">Save PAS</button>
            @if(! auth()->user()?->program_id)
            <button type="button" id="openReleaseCreateBtn" class="btn btn-secondary">Open Release Create</button>
            @endif
            <a href="{{ route('pas.index') }}" class="btn btn-ghost" id="cancelBtn">Cancel</a>
        </div>
    </form>
</section>

{{-- Autocomplete data sources --}}
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
        <option value="{{ $f->name }}"></option>
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
                        <button type="button" class="item-description-clear" title="Clear"
                            style="position:absolute;right:0.5rem;background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;line-height:1;padding:0.2rem 0.3rem;">&times;</button>
                    </div>
                </div>
                <div class="form-group">
                    @if(! $isProgramUser)
                    <label>PHO Code</label>
                    <div style="position:relative;display:flex;align-items:center;">
                        <input type="text" class="pas-phocode-input" autocomplete="off" style="width:100%;padding-right:2rem;" value="">
                        <button type="button" class="item-description-clear" title="Clear"
                            style="position:absolute;right:0.5rem;background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;line-height:1;padding:0.2rem 0.3rem;">&times;</button>
                    </div>
                    @endif
                    <input type="hidden" class="pas-item-code-hidden" name="items[0][item_code]" value="">
                    <input type="hidden" class="pas-item-id-hidden" name="items[0][item_id]" value="">
                    <input type="hidden" class="pas-product-code-input" name="items[0][product_code]" value="">
                </div>
                @if(! $isProgramUser)
                <div class="form-group">
                    <label>Lot Number</label>
                    <input class="pas-lot-input" name="items[0][lot_number]" value="">
                </div>
                @endif
            </div>
            <div class="form-grid-3">
                @if(! $isProgramUser)
                <div class="form-group">
                    <label>Expiration Date</label>
                    <input type="date" class="pas-expiry-input" name="items[0][expiration_date]" value="">
                </div>
                @endif
                <div class="form-group">
                    <label>Quantity <span style="color:var(--danger)">*</span></label>
                    <input type="number" class="pas-qty-input" name="items[0][quantity]" value="" min="1" required>
                </div>
                <div class="form-group">
                    <label>Unit <span style="color:var(--danger)">*</span></label>
                    <input class="pas-unit-input" name="items[0][unit]" value="" required>
                </div>
            </div>
            @if(! $isProgramUser)
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
            @endif
        </div>
    </div>
</template>

@push('scripts')

<script>
const pasAllItems = {!! json_encode($items->flatMap(fn($i) => $i->receivingItems->isNotEmpty()
    ? $i->receivingItems->map(fn($receivingItem) => [
        'id'         => $i->id,
        'code'       => $receivingItem->item_code,
        'name'       => $i->name,
        'sku'        => $i->stock_keeping_unit,
        'unit'       => $receivingItem->uom ?: $i->unit,
        'cost'       => $receivingItem->unit_cost ?? $i->unit_cost,
        'qty'        => $receivingItem->available_quantity ?? $receivingItem->quantity_received,
        'lot_number' => $receivingItem->lot_number,
        'expiry'     => $receivingItem->expiry_date?->format('Y-m-d'),
    ])
    : [[
        'id'         => $i->id,
        'code'       => $i->item_code ?? '',
        'name'       => $i->name,
        'sku'        => $i->stock_keeping_unit,
        'unit'       => $i->unit,
        'cost'       => $i->unit_cost,
        'qty'        => $i->quantity_on_hand,
        'lot_number' => '',
        'expiry'     => '',
    ]]
)->filter(fn($item) => filled($item['code']) || filled($item['name']))->values()->toArray()) !!};
const pasIsProgramUser = {{ auth()->user()?->program_id ? 'true' : 'false' }};
const pasUsageData      = {!! json_encode($pasUsage ?? []) !!};
const pasUsageByCode      = pasUsageData.by_code || {};
const pasUsageByDescription = pasUsageData.by_description || {};
const pasUsageById        = pasUsageData.by_id || {};

document.addEventListener('DOMContentLoaded', function () {
    // ---- Program & Coordinator Autocomplete ----
    const pasPrograms = Array.from(document.querySelectorAll('#pas-program-options option')).map(o => ({
        name: o.value, nameLower: o.value.toLowerCase()
    }));
    const pasCoordinators = Array.from(document.querySelectorAll('#pas-coordinator-options option')).map(o => ({
        name: o.value, nameLower: o.value.toLowerCase(), assignedPrograms: o.dataset.programs || ''
    }));

    const pasProgramInput       = document.getElementById('pasProgramInput');
    const pasCoordinatorInput   = document.getElementById('pasCoordinatorInput');
    const pasProgramDropdown    = document.getElementById('pasProgramDropdown');
    const pasCoordinatorDropdown = document.getElementById('pasCoordinatorDropdown');

    function bindPasAutocomplete(input, dataList, dropdown, onSelect) {
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

    const pasFacilities = Array.from(document.querySelectorAll('#pas-facility-options option')).map(o => ({
        name: o.value, nameLower: o.value.toLowerCase()
    }));

    const pasFacilityInput    = document.getElementById('pasFacilityInput');
    const pasFacilityDropdown = document.getElementById('pasFacilityDropdown');
    if (pasFacilityInput && pasFacilityDropdown) {
        bindPasAutocomplete(pasFacilityInput, pasFacilities, pasFacilityDropdown, null);
    }

    if (pasProgramInput && pasProgramDropdown) {
        bindPasAutocomplete(pasProgramInput, pasPrograms, pasProgramDropdown, function (item) {
            const matched = pasCoordinators.find(c => c.assignedPrograms.toLowerCase().includes(item.nameLower));
            if (matched && pasCoordinatorInput && !pasCoordinatorInput.value.trim()) pasCoordinatorInput.value = matched.name;
        });
    }

    if (pasCoordinatorInput && pasCoordinatorDropdown) {
        bindPasAutocomplete(pasCoordinatorInput, pasCoordinators, pasCoordinatorDropdown, function (item) {
            if (item.assignedPrograms && pasProgramInput && !pasProgramInput.value.trim()) {
                pasProgramInput.value = item.assignedPrograms.split(', ')[0] || '';
            }
        });
    }
    // ---- End Program & Coordinator Autocomplete ----

    const container = document.getElementById('pas-items');
    const template  = document.getElementById('pas-item-template');
    const addBtn    = document.getElementById('add-pas-item');

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
        const filtered = itemsForProgram().filter(it => {
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
            opt.addEventListener('mouseout', () => opt.style.background = 'transparent');
            opt.addEventListener('click', () => { descInput.value = it.name; dd.style.display = 'none'; onSelect(); });
            dd.appendChild(opt);
        });
        dd.style.display = 'block';
    }

    function applyItemToRow(row, item) {
        const descInput    = row.querySelector('.pas-desc-input');
        const phocodeInput = row.querySelector('.pas-phocode-input');
        const codeHidden   = row.querySelector('.pas-item-code-hidden');
        const itemIdHidden = row.querySelector('.pas-item-id-hidden');
        const codeInput    = row.querySelector('.pas-product-code-input');
        const unitInput    = row.querySelector('.pas-unit-input');
        const costInput    = row.querySelector('.pas-unitcost-input');
        const lotInput     = row.querySelector('.pas-lot-input');
        const expiryInput  = row.querySelector('.pas-expiry-input');
        const qtyInput     = row.querySelector('.pas-qty-input');
        if (descInput)    descInput.value    = item.name;
        if (phocodeInput) phocodeInput.value = item.code || '';
        if (codeHidden)   codeHidden.value   = item.code || '';
        if (itemIdHidden) itemIdHidden.value = item.id || '';
        if (codeInput)    codeInput.value    = item.code || '';
        if (unitInput)    unitInput.value    = item.unit || '';
        if (costInput)    costInput.value    = item.cost || '';
        if (lotInput)     lotInput.value     = item.lot_number || '';
        if (expiryInput)  expiryInput.value  = item.expiry || '';
        if (qtyInput)     qtyInput.placeholder = 'Available: ' + (item.qty || 0);
        calcTotal(row);
    }

    function calcTotal(row) {
        const qtyInput  = row.querySelector('.pas-qty-input');
        const costInput = row.querySelector('.pas-unitcost-input');
        const qty  = parseFloat(qtyInput && qtyInput.value) || 0;
        const cost = parseFloat(costInput && costInput.value) || 0;
        const disp = row.querySelector('.pas-totalcost-display');
        if (disp) disp.value = qty && cost ? (qty * cost).toFixed(2) : '';
    }

    // Program users do not see the PHO code, lot, expiry and unit cost fields,
    // so those inputs may be absent from a row.
    function setRowValue(row, selector, value) {
        const el = row.querySelector(selector);
        if (el) el.value = value;
    }

    // Resolve the inventory entry behind a row (by PHO code, then item id, then description)
    function getRowItemEntry(row) {
        const codeHidden = row.querySelector('.pas-item-code-hidden');
        const idHidden   = row.querySelector('.pas-item-id-hidden');
        const descInput  = row.querySelector('.pas-desc-input');
        if (codeHidden && codeHidden.value) {
            const byCode = pasAllItems.find(it => (it.code || '') === codeHidden.value);
            if (byCode) return byCode;
        }
        if (idHidden && idHidden.value) {
            const byId = pasAllItems.find(it => String(it.id) === String(idHidden.value));
            if (byId) return byId;
        }
        if (descInput && descInput.value.trim()) {
            const v = descInput.value.trim().toLowerCase();
            const byName = pasAllItems.find(it => (it.name || '').toLowerCase() === v);
            if (byName) return byName;
        }
        return null;
    }

    // Row-level availability key. Admins track stock per PHO code, so each row
    // competes only with other rows carrying the same code. Program users have
    // no code, so rows pool by item description instead.
    const usageKey = e => {
        if (!e) return '';
        if (!pasIsProgramUser) {
            if (e.code) return 'code:' + String(e.code);
            return 'id:' + String(e.id);
        }
        return 'desc:' + String(e.name || '').toLowerCase();
    };

    // Total stock a row may draw from. For program users this sums every code
    // behind the description, because the program cannot choose between them.
    function stockForKey(entry, key) {
        if (pasIsProgramUser) {
            const name = String(entry.name || '').toLowerCase();
            return pasAllItems.reduce(
                (sum, it) => sum + ((String(it.name || '').toLowerCase() === name) ? (parseFloat(it.qty) || 0) : 0),
                0
            );
        }

        // Admin: prefer the entry's own code, falling back to item id.
        if (String(key).startsWith('code:')) {
            const code = String(key).slice(5);
            return pasAllItems.reduce(
                (sum, it) => sum + ((String(it.code || '') === code) ? (parseFloat(it.qty) || 0) : 0),
                0
            );
        }

        return parseFloat(entry.qty) || 0;
    }

    // Past PAS usage for a row, matched the same way (per code for admins,
    // per description for program users). Missing keys mean no usage recorded.
    function pastUsageForKey(key) {
        if (!key) return 0;
        if (String(key).startsWith('desc:')) {
            const name = String(key).slice(5);
            if (Object.prototype.hasOwnProperty.call(pasUsageByDescription, name)) {
                return parseFloat(pasUsageByDescription[name]) || 0;
            }
        }
        if (String(key).startsWith('code:')) {
            const code = String(key).slice(5);
            if (Object.prototype.hasOwnProperty.call(pasUsageByCode, code)) {
                return parseFloat(pasUsageByCode[code]) || 0;
            }
        }
        if (String(key).startsWith('id:')) {
            const id = String(key).slice(3);
            if (Object.prototype.hasOwnProperty.call(pasUsageById, id)) {
                return parseFloat(pasUsageById[id]) || 0;
            }
        }
        return 0;
    }

    // Each row's "Available" placeholder shows the stock it can draw from minus
    // the quantities already allocated in other PAS records and in the other
    // rows of this form competing for the same stock.
    function refreshAvailability() {
        const rows    = Array.from(container.querySelectorAll('.pas-item-row'));
        const entries = rows.map(getRowItemEntry);
        const keys    = entries.map(usageKey);
        rows.forEach((row, i) => {
            const qtyInput = row.querySelector('.pas-qty-input');
            if (!qtyInput) return;
            if (!entries[i] || !keys[i]) { qtyInput.placeholder = 'Available: 0'; return; }
            const baseQty = Math.max(stockForKey(entries[i], keys[i]) - pastUsageForKey(keys[i]), 0);
            let used = 0;
            rows.forEach((r, j) => {
                if (i === j || keys[j] !== keys[i]) return;
                const q = r.querySelector('.pas-qty-input');
                used += parseFloat(q && q.value) || 0;
            });
            qtyInput.placeholder = 'Available: ' + Math.max(baseQty - used, 0);
        });
    }

    // --- Stock Keeping Unit (Program) filtering ---
    // The program(s) currently chosen on the form.
    function selectedPrograms() {
        const select = document.querySelector('select[name="program"]');
        const raw = select ? (select.value || '') : (document.getElementById('pasProgramInput')?.value || '');
        return raw.split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
    }

    // Items offered in the description / PHO code pickers, narrowed to the
    // selected Stock Keeping Unit (Program). No program = show everything.
    function itemsForProgram() {
        const names = selectedPrograms();
        if (!names.length) return pasAllItems;
        return pasAllItems.filter(it => names.includes((it.sku || '').toLowerCase()));
    }

    // Clear item rows whose item does not belong to the selected program.
    function enforceProgramOnRows() {
        const names = selectedPrograms();
        if (!names.length) return;
        container.querySelectorAll('.pas-item-row').forEach(row => {
            const entry = getRowItemEntry(row);
            if (!entry || names.includes((entry.sku || '').toLowerCase())) return;
            const descInput = row.querySelector('.pas-desc-input');
            const phocodeInput = row.querySelector('.pas-phocode-input');
            if (descInput) descInput.value = '';
            if (phocodeInput) phocodeInput.value = '';
            setRowValue(row, '.pas-item-id-hidden', '');
            setRowValue(row, '.pas-item-code-hidden', '');
            setRowValue(row, '.pas-product-code-input', '');
            setRowValue(row, '.pas-unit-input', '');
            setRowValue(row, '.pas-unitcost-input', '');
            setRowValue(row, '.pas-lot-input', '');
            setRowValue(row, '.pas-expiry-input', '');
            setRowValue(row, '.pas-totalcost-display', '');
        });
        refreshAvailability();
    }

    const pasProgramSelect = document.querySelector('select[name="program"]');
    if (pasProgramSelect) pasProgramSelect.addEventListener('change', enforceProgramOnRows);
    if (pasProgramInput) pasProgramInput.addEventListener('change', enforceProgramOnRows);

    function bindRow(row) {
        const body       = row.querySelector('.item-row-body');
        const toggleBtn  = row.querySelector('.item-toggle-button');
        const removeBtn  = row.querySelector('.remove-item-button');
        const descInput  = row.querySelector('.pas-desc-input');
        const phocodeInput = row.querySelector('.pas-phocode-input');
        const qtyInput   = row.querySelector('.pas-qty-input');
        const costInput  = row.querySelector('.pas-unitcost-input');
        const clearBtn   = row.querySelector('.item-description-clear');

        const dd = buildDropdown(descInput);

        const syncFromDesc = () => {
            const name = descInput.value.trim();
            const lower = name.toLowerCase();
            const pool = itemsForProgram();
            let match = pool.find(it => it.name.toLowerCase() === lower || (it.code || '').toLowerCase() === lower);
            if (!match) {
                match = pool.find(it => it.name.toLowerCase().includes(lower) || (it.code || '').toLowerCase().includes(lower));
            }
            if (match) {
                applyItemToRow(row, match);
                refreshAvailability();
            }
        };

        descInput.addEventListener('input',  e => showOptions(descInput, dd, e.target.value, syncFromDesc));
        descInput.addEventListener('focus',  () => showOptions(descInput, dd, descInput.value, syncFromDesc));
        descInput.addEventListener('blur',   () => setTimeout(() => dd.style.display = 'none', 200));
        descInput.addEventListener('change', syncFromDesc);

        bindPhocodeAutocomplete(row);

        qtyInput.addEventListener('input',  () => { calcTotal(row); refreshAvailability(); });
        if (costInput) costInput.addEventListener('input', () => calcTotal(row));

        clearBtn.addEventListener('mousedown', e => e.preventDefault());
        clearBtn.addEventListener('click', () => {
            descInput.value = '';
            if (phocodeInput) phocodeInput.value = '';
            setRowValue(row, '.pas-item-id-hidden', '');
            setRowValue(row, '.pas-item-code-hidden', '');
            setRowValue(row, '.pas-product-code-input', '');
            setRowValue(row, '.pas-unit-input', '');
            setRowValue(row, '.pas-unitcost-input', '');
            setRowValue(row, '.pas-lot-input', '');
            setRowValue(row, '.pas-expiry-input', '');
            setRowValue(row, '.pas-totalcost-display', '');
            dd.style.display = 'none';
            descInput.focus();
            refreshAvailability();
        });

        toggleBtn.addEventListener('click', () => {
            body.style.display = body.style.display === 'none' ? '' : 'none';
            toggleBtn.textContent = body.style.display === 'none' ? 'Show' : 'Hide';
        });

        removeBtn.addEventListener('click', () => { row.remove(); updateIndexes(); refreshAvailability(); });

        refreshAvailability();
    }

    function bindPhocodeAutocomplete(row) {
        const phocodeInput = row.querySelector('.pas-phocode-input');
        if (!phocodeInput) return;

        const descInput = row.querySelector('.pas-desc-input');
        const codeHidden = row.querySelector('.pas-item-code-hidden');

        const dd = document.createElement('div');
        dd.className = 'autocomplete-dropdown';
        Object.assign(dd.style, {
            position:'absolute', background:'var(--surface, #fff)', border:'1px solid var(--border, #ddd)',
            maxHeight:'200px', overflowY:'auto', width:'100%', zIndex:'1000',
            display:'none', boxShadow:'0 4px 6px rgba(0,0,0,.1)', top:'100%', left:'0', marginTop:'4px'
        });
        phocodeInput.parentElement.style.position = 'relative';
        phocodeInput.parentElement.appendChild(dd);

        function showOptions(query) {
            dd.innerHTML = '';
            const q = query.toLowerCase().trim();
            const descValue = descInput ? descInput.value.trim() : '';
            const descLower = descValue.toLowerCase();
            const seen = new Set();

            let exactNameIds = null;
            if (descValue) {
                const exactMatches = itemsForProgram().filter(function(item) { return item.name.toLowerCase() === descLower; });
                if (exactMatches.length > 0) {
                    exactNameIds = new Set(exactMatches.map(function(item) { return item.id; }));
                }
            }

            const filtered = itemsForProgram().filter(function(item) {
                if (q && !((item.code || '').toLowerCase().includes(q) || item.name.toLowerCase().includes(q))) return false;
                if (exactNameIds && !exactNameIds.has(item.id)) return false;
                const seenKey = item.code || ('#' + item.id);
                if (seen.has(seenKey)) return false;
                seen.add(seenKey);
                return true;
            });
            if (!filtered.length) { dd.style.display = 'none'; return; }
            filtered.forEach(function(item) {
                const opt = document.createElement('div');
                Object.assign(opt.style, { padding:'10px 12px', cursor:'pointer', borderBottom:'1px solid #f0f0f0' });
                opt.textContent = (item.code ? item.code + ' — ' + item.name : item.name) + ' (' + (item.qty || 0) + ' available' + (item.expiry ? ' | Exp: ' + item.expiry : '') + ')';
                opt.addEventListener('mouseover', function() { this.style.background = '#f5f5f5'; });
                opt.addEventListener('mouseout',  function() { this.style.background = 'transparent'; });
                opt.addEventListener('click', function() {
                    phocodeInput.value = item.code;
                    dd.style.display = 'none';
                    applyItemToRow(row, item);
                    refreshAvailability();
                });
                dd.appendChild(opt);
            });
            dd.style.display = 'block';
        }

        phocodeInput.addEventListener('input', function() {
            if (codeHidden) codeHidden.value = this.value;
            refreshAvailability();
            showOptions(this.value);
        });
        phocodeInput.addEventListener('focus', function() { showOptions(''); });
        phocodeInput.addEventListener('blur',  function() { setTimeout(function() { dd.style.display = 'none'; }, 200); });
    }

    container.querySelectorAll('.pas-item-row').forEach(bindRow);
    updateIndexes();

    addBtn.addEventListener('click', () => {
        const clone = template.content.cloneNode(true);
        const row   = clone.querySelector('.pas-item-row');
        bindRow(row);
        container.appendChild(row);
        updateIndexes();
        refreshAvailability();
    });

    // Dirty-form guard
    let dirty = false;
    const form = document.getElementById('pasForm');

    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('input',  () => dirty = true);
        el.addEventListener('change', () => dirty = true);
    });

    window.addEventListener('beforeunload', e => {
        if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    // Guard ALL anchor links (Back button + Cancel)
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', e => {
            if (dirty && !confirm('You have unsaved changes. Leaving this page will discard them. Are you sure you want to leave?')) {
                e.preventDefault();
            }
        });
    });

    const openReleaseCreateBtn = document.getElementById('openReleaseCreateBtn');
    if (openReleaseCreateBtn) {
        openReleaseCreateBtn.addEventListener('click', () => {
            const params = new URLSearchParams();
            const pasNumber = document.querySelector('[name="pas_number"]').value;
            const program = document.querySelector('[name="program"]').value;
            const facility = document.querySelector('[name="facility_name"]').value;
            const coordinator = document.querySelector('[name="facility_coordinator"]').value;
            const purpose = document.querySelector('[name="purpose_activity"]').value;
            const transferType = document.querySelector('[name="transfer_type"]').value;

            if (pasNumber) params.set('pas_number', pasNumber);
            if (program) params.set('health_program_coordinator', program);
            if (facility) params.set('facility_name', facility);
            if (coordinator) params.set('release_coordinator', coordinator);
            if (purpose) params.set('purpose_activity', purpose);
            if (transferType) params.set('transfer_type', transferType);

            window.location.href = '{{ route('releases.create') }}' + '?' + params.toString();
        });
    }

    form.addEventListener('submit', () => dirty = false);

    new MutationObserver(() => {
        form.querySelectorAll('input, select, textarea').forEach(el => {
            el.removeEventListener('input',  () => dirty = true);
            el.removeEventListener('change', () => dirty = true);
            el.addEventListener('input',  () => dirty = true);
            el.addEventListener('change', () => dirty = true);
        });
    }).observe(container, { childList: true, subtree: true });
});
</script>
<style>
@media (max-width: 768px) {
    .pas-item-row .item-row-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
}
</style>
@endpush
@endsection
