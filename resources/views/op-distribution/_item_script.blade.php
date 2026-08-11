<datalist id="op-item-options" style="display:none;">
    @foreach($items as $item)
        <option value="{{ $item->name }}"
            data-id="{{ $item->id }}"
            data-code="{{ $item->item_code }}"
            data-uom="{{ $item->unit }}"
            data-cost="{{ $item->unit_cost }}"
            data-qty="{{ $item->quantity_on_hand }}"
            data-lot="{{ $itemLotNumbers[$item->id]['lot_number'] ?? '' }}">
        </option>
    @endforeach
</datalist>

<template id="op-item-template">
    <div class="section-card op-item-row">
        <div class="item-row-header">
            <div class="item-row-title">Patient</div>
            <div class="item-row-actions">
                <button type="button" class="btn btn-link item-toggle-button">Hide</button>
                <button type="button" class="btn btn-danger remove-item-button">Delete</button>
            </div>
        </div>
        <div class="item-row-body">
            @if($isEdit)
            <input type="hidden" class="op-row-id" name="items[0][op_item_id]" value="">
            @endif
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Full Name <span style="color:var(--danger);">*</span></label>
                    <input class="op-patient-name" name="items[0][patient_name]" value="" required>
                </div>
                <div class="form-group">
                    <label>Age</label>
                    <input type="number" class="op-patient-age" name="items[0][patient_age]" value="" min="0" max="150" placeholder="Years">
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select class="op-patient-gender" name="items[0][patient_gender]">
                        <option value="">— Select —</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="form-grid-3">
                <div class="form-group" style="position:relative;">
                    <label>Item Description <span style="color:var(--danger);">*</span></label>
                    <div style="position:relative;display:flex;align-items:center;">
                        <input type="text" class="op-desc-input" name="items[0][item_description]" value="" autocomplete="off" style="width:100%;padding-right:2rem;" required>
                        <button type="button" class="op-desc-clear" title="Clear" style="position:absolute;right:0.5rem;background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;line-height:1;padding:0.2rem 0.3rem;">&times;</button>
                    </div>
                    <input type="hidden" class="op-item-id" name="items[0][item_id]" value="">
                </div>
                <div class="form-group">
                    <label>Quantity <span style="color:var(--danger);">*</span></label>
                    <input type="number" class="op-qty" name="items[0][quantity]" value="" min="1" required>
                </div>
                <div class="form-group">
                    <label>UOM</label>
                    <input class="op-uom" name="items[0][uom]" value="">
                </div>
            </div>
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Unit Cost</label>
                    <input type="number" step="0.01" class="op-cost" name="items[0][unit_cost]" value="">
                </div>
                <div class="form-group">
                    <label>Lot / Batch No.</label>
                    <input class="op-lot" name="items[0][lot_number]" value="">
                </div>
            </div>
            <hr style="border:none;border-top:1px dashed var(--border);margin:0.5rem 0;">
            <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.4rem;">2nd Medicine (optional)</p>
            <div class="form-grid-3">
                <div class="form-group" style="position:relative;">
                    <label>Item Description 2</label>
                    <div style="position:relative;display:flex;align-items:center;">
                        <input type="text" class="op-desc-input-2" name="items[0][item_description_2]" value="" autocomplete="off" style="width:100%;padding-right:2rem;">
                        <button type="button" class="op-desc-clear-2" title="Clear" style="position:absolute;right:0.5rem;background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;line-height:1;padding:0.2rem 0.3rem;">&times;</button>
                    </div>
                    <input type="hidden" class="op-item-id-2" name="items[0][item_id_2]" value="">
                </div>
                <div class="form-group">
                    <label>Quantity 2</label>
                    <input type="number" class="op-qty-2" name="items[0][quantity_2]" value="" min="1">
                </div>
                <div class="form-group">
                    <label>UOM 2</label>
                    <input class="op-uom-2" name="items[0][uom_2]" value="">
                </div>
            </div>
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Unit Cost 2</label>
                    <input type="number" step="0.01" class="op-cost-2" name="items[0][unit_cost_2]" value="">
                </div>
                <div class="form-group">
                    <label>Lot / Batch No. 2</label>
                    <input class="op-lot-2" name="items[0][lot_number_2]" value="">
                </div>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
(function () {
    const allItems = Array.from(document.querySelectorAll('#op-item-options option')).map(o => ({
        id:   o.dataset.id,
        name: o.value,
        nameLower: o.value.toLowerCase(),
        uom:  o.dataset.uom,
        cost: o.dataset.cost,
        qty:  o.dataset.qty,
        lot:  o.dataset.lot,
    }));

    const container = document.getElementById('op-items');
    const template  = document.getElementById('op-item-template');
    const addBtn    = document.getElementById('add-op-item');

    function updateIndexes() {
        container.querySelectorAll('.op-item-row').forEach(function (row, i) {
            row.dataset.index = i;
            row.querySelector('.item-row-title').textContent = 'Patient ' + (i + 1);
            row.querySelectorAll('input, select').forEach(function (el) {
                el.name = el.name.replace(/items\[\d+\]/, 'items[' + i + ']');
            });
            row.querySelector('.remove-item-button').style.display = i === 0 ? 'none' : '';
        });
    }

    function bindRow(row) {
        const body      = row.querySelector('.item-row-body');
        const toggleBtn = row.querySelector('.item-toggle-button');
        const removeBtn = row.querySelector('.remove-item-button');
        const descInput = row.querySelector('.op-desc-input');
        const itemId    = row.querySelector('.op-item-id');
        const uomInput  = row.querySelector('.op-uom');
        const costInput = row.querySelector('.op-cost');
        const lotInput  = row.querySelector('.op-lot');
        const clearBtn  = row.querySelector('.op-desc-clear');

        const descInput2 = row.querySelector('.op-desc-input-2');
        const itemId2    = row.querySelector('.op-item-id-2');
        const uomInput2  = row.querySelector('.op-uom-2');
        const costInput2 = row.querySelector('.op-cost-2');
        const lotInput2  = row.querySelector('.op-lot-2');
        const clearBtn2  = row.querySelector('.op-desc-clear-2');

        // Build dropdown for item 1
        const dd = document.createElement('div');
        dd.className = 'autocomplete-dropdown';
        dd.style.cssText = 'position:absolute;background:var(--surface,#fff);border:1px solid var(--border,#ddd);max-height:200px;overflow-y:auto;width:100%;z-index:1000;display:none;box-shadow:0 4px 6px rgba(0,0,0,.1);top:100%;left:0;margin-top:4px;';
        descInput.parentElement.style.position = 'relative';
        descInput.parentElement.appendChild(dd);

        // Build dropdown for item 2
        const dd2 = document.createElement('div');
        dd2.className = 'autocomplete-dropdown';
        dd2.style.cssText = 'position:absolute;background:var(--surface,#fff);border:1px solid var(--border,#ddd);max-height:200px;overflow-y:auto;width:100%;z-index:1000;display:none;box-shadow:0 4px 6px rgba(0,0,0,.1);top:100%;left:0;margin-top:4px;';
        if (descInput2) { descInput2.parentElement.style.position = 'relative'; descInput2.parentElement.appendChild(dd2); }

        function syncItem(item) {
            descInput.value = item.name;
            if (itemId)   itemId.value   = item.id;
            if (uomInput) uomInput.value = item.uom || '';
            if (costInput) costInput.value = item.cost || '';
            if (lotInput)  lotInput.value  = item.lot || '';
            dd.style.display = 'none';
        }

        function showOptions(q) {
            dd.innerHTML = '';
            const lower = q.toLowerCase().trim();
            const seen  = new Set();
            const list  = (lower ? allItems.filter(i => i.nameLower.includes(lower)) : allItems)
                .filter(i => { if (seen.has(i.nameLower)) return false; seen.add(i.nameLower); return true; });
            if (!list.length) { dd.style.display = 'none'; return; }
            list.forEach(function (item) {
                const opt = document.createElement('div');
                opt.style.cssText = 'padding:10px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0;';
                opt.innerHTML = '<strong>' + item.name + '</strong><span style="color:var(--text-muted);font-size:0.8rem;margin-left:0.5rem;">(' + (item.qty || 0) + ' available)</span>';
                opt.addEventListener('mouseover', function () { this.style.background = '#f5f5f5'; });
                opt.addEventListener('mouseout',  function () { this.style.background = 'transparent'; });
                opt.addEventListener('click', function () { syncItem(item); });
                dd.appendChild(opt);
            });
            dd.style.display = 'block';
        }

        descInput.addEventListener('input', function () { showOptions(this.value); });
        descInput.addEventListener('focus', function () { showOptions(this.value); });
        descInput.addEventListener('blur',  function () { setTimeout(function () { dd.style.display = 'none'; }, 200); });

        clearBtn.addEventListener('mousedown', function (e) { e.preventDefault(); });
        clearBtn.addEventListener('click', function () {
            descInput.value = '';
            if (itemId)    itemId.value    = '';
            if (uomInput)  uomInput.value  = '';
            if (costInput) costInput.value = '';
            if (lotInput)  lotInput.value  = '';
            dd.style.display = 'none';
            descInput.focus();
        });

        toggleBtn.addEventListener('click', function () {
            const hidden = body.style.display === 'none';
            body.style.display = hidden ? '' : 'none';
            toggleBtn.textContent = hidden ? 'Hide' : 'Show';
        });

        removeBtn.addEventListener('click', function () { row.remove(); updateIndexes(); });
    }

    container.querySelectorAll('.op-item-row').forEach(bindRow);
    updateIndexes();

    addBtn.addEventListener('click', function () {
        const clone = template.content.cloneNode(true);
        const row   = clone.querySelector('.op-item-row');
        bindRow(row);
        container.appendChild(row);
        updateIndexes();
    });

    // Dirty guard
    var dirty = false;
    var form  = document.getElementById('{{ $formId }}');
    form.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.addEventListener('input',  function () { dirty = true; });
        el.addEventListener('change', function () { dirty = true; });
    });
    window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
    document.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (dirty && !confirm('You have unsaved changes. Leave anyway?')) e.preventDefault();
        });
    });
    form.addEventListener('submit', function () { dirty = false; });
    new MutationObserver(function () {
        container.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.removeEventListener('input',  function () { dirty = true; });
            el.removeEventListener('change', function () { dirty = true; });
            el.addEventListener('input',  function () { dirty = true; });
            el.addEventListener('change', function () { dirty = true; });
        });
    }).observe(container, { childList: true, subtree: true });
})();
</script>
@endpush
