@extends('layouts.app')

@section('title', 'New OP Distribution')
@section('pageHeading', 'New OP Distribution')
@section('pageSubheading', 'Record Office of the President medicine distribution per patient.')

@section('content')
<section class="card">
    <div class="section-header">
        <a href="{{ route('op-distribution.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <form action="{{ route('op-distribution.store') }}" method="POST" class="stack" id="opForm">
        @csrf

        <div class="form-grid-3">
            <div class="form-group">
                <label>Reference No.</label>
                <input name="reference_number" value="{{ old('reference_number', $refNumber) }}" readonly style="background:var(--surface-strong);cursor:not-allowed;">
            </div>
            <div class="form-group">
                <label>Date Distributed <span style="color:var(--danger);">*</span></label>
                <input type="date" name="date_distributed" value="{{ old('date_distributed', now()->toDateString()) }}" required>
            </div>
            <div class="form-group">
                <label>Distributed By</label>
                <input name="distributed_by" value="{{ old('distributed_by') }}" placeholder="Name of distributor">
            </div>
        </div>

        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="2">{{ old('notes') }}</textarea>
        </div>

        <div>
            <h2 class="section-title">Patient Records <span style="color:var(--danger);">*</span></h2>
            @error('items')<span style="color:var(--danger);font-size:0.82rem;display:block;margin-bottom:0.5rem;">{{ $message }}</span>@enderror

            <div id="op-items" class="stack">
                @php
                    $oldItems = collect(old('items', []))->values()->all();
                    if (empty($oldItems)) {
                        $oldItems = [['patient_name'=>'','patient_age'=>'','patient_gender'=>'','item_description'=>'','quantity'=>'','uom'=>'','unit_cost'=>'','lot_number'=>'','item_id'=>'']];
                    }
                @endphp

                @foreach($oldItems as $index => $oi)
                    <div class="section-card op-item-row" data-index="{{ $index }}">
                        <div class="item-row-header">
                            <div class="item-row-title">Patient {{ $index + 1 }}</div>
                            <div class="item-row-actions">
                                <button type="button" class="btn btn-link item-toggle-button">Hide</button>
                                <button type="button" class="btn btn-danger remove-item-button" @if($index === 0) style="display:none;" @endif>Delete</button>
                            </div>
                        </div>
                        <div class="item-row-body">
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>Full Name <span style="color:var(--danger);">*</span></label>
                                    <input class="op-patient-name" name="items[{{ $index }}][patient_name]" value="{{ $oi['patient_name'] ?? '' }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Age</label>
                                    <input type="number" class="op-patient-age" name="items[{{ $index }}][patient_age]" value="{{ $oi['patient_age'] ?? '' }}" min="0" max="150" placeholder="Years">
                                </div>
                                <div class="form-group">
                                    <label>Gender</label>
                                    <select class="op-patient-gender" name="items[{{ $index }}][patient_gender]">
                                        <option value="">— Select —</option>
                                        <option value="Male" @selected(($oi['patient_gender'] ?? '') === 'Male')>Male</option>
                                        <option value="Female" @selected(($oi['patient_gender'] ?? '') === 'Female')>Female</option>
                                        <option value="Other" @selected(($oi['patient_gender'] ?? '') === 'Other')>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-grid-3">
                                <div class="form-group" style="position:relative;">
                                    <label>Item Description <span style="color:var(--danger);">*</span></label>
                                    <div style="position:relative;display:flex;align-items:center;">
                                        <input type="text" class="op-desc-input" name="items[{{ $index }}][item_description]" value="{{ $oi['item_description'] ?? '' }}" autocomplete="off" style="width:100%;padding-right:2rem;" required>
                                        <button type="button" class="op-desc-clear" title="Clear" style="position:absolute;right:0.5rem;background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;line-height:1;padding:0.2rem 0.3rem;">&times;</button>
                                    </div>
                                    <input type="hidden" class="op-item-id" name="items[{{ $index }}][item_id]" value="{{ $oi['item_id'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Quantity <span style="color:var(--danger);">*</span></label>
                                    <input type="number" class="op-qty" name="items[{{ $index }}][quantity]" value="{{ $oi['quantity'] ?? '' }}" min="1" required>
                                </div>
                                <div class="form-group">
                                    <label>UOM</label>
                                    <input class="op-uom" name="items[{{ $index }}][uom]" value="{{ $oi['uom'] ?? '' }}">
                                </div>
                            </div>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>Unit Cost</label>
                                    <input type="number" step="0.01" class="op-cost" name="items[{{ $index }}][unit_cost]" value="{{ $oi['unit_cost'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Lot / Batch No.</label>
                                    <input class="op-lot" name="items[{{ $index }}][lot_number]" value="{{ $oi['lot_number'] ?? '' }}">
                                </div>
                            </div>
                            <hr style="border:none;border-top:1px dashed var(--border);margin:0.5rem 0;">
                            <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.4rem;">2nd Medicine (optional)</p>
                            <div class="form-grid-3">
                                <div class="form-group" style="position:relative;">
                                    <label>Item Description 2</label>
                                    <div style="position:relative;display:flex;align-items:center;">
                                        <input type="text" class="op-desc-input-2" name="items[{{ $index }}][item_description_2]" value="{{ $oi['item_description_2'] ?? '' }}" autocomplete="off" style="width:100%;padding-right:2rem;">
                                        <button type="button" class="op-desc-clear-2" title="Clear" style="position:absolute;right:0.5rem;background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;line-height:1;padding:0.2rem 0.3rem;">&times;</button>
                                    </div>
                                    <input type="hidden" class="op-item-id-2" name="items[{{ $index }}][item_id_2]" value="{{ $oi['item_id_2'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Quantity 2</label>
                                    <input type="number" class="op-qty-2" name="items[{{ $index }}][quantity_2]" value="{{ $oi['quantity_2'] ?? '' }}" min="1">
                                </div>
                                <div class="form-group">
                                    <label>UOM 2</label>
                                    <input class="op-uom-2" name="items[{{ $index }}][uom_2]" value="{{ $oi['uom_2'] ?? '' }}">
                                </div>
                            </div>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>Unit Cost 2</label>
                                    <input type="number" step="0.01" class="op-cost-2" name="items[{{ $index }}][unit_cost_2]" value="{{ $oi['unit_cost_2'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Lot / Batch No. 2</label>
                                    <input class="op-lot-2" name="items[{{ $index }}][lot_number_2]" value="{{ $oi['lot_number_2'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-op-item" class="btn btn-secondary" style="margin-top:0.75rem;">+ Add Patient</button>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Record</button>
            <a href="{{ route('op-distribution.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</section>

@include('op-distribution._item_script', ['items' => $items, 'itemLotNumbers' => $itemLotNumbers, 'formId' => 'opForm', 'isEdit' => false])
@endsection
