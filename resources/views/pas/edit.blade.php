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
@endsection