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
                    <label>Purchase Order No.</label>
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

            <div class="form-grid-3">
                <div class="form-group">
                    <label>Source Document No.</label>
                    <input name="source_document_number" value="{{ old('source_document_number') }}" />
                </div>
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
                <h3 class="section-card-title">Received Item</h3>
                <div class="section-card">
                    <div class="form-grid-4">
                        <div class="form-group">
                            <label>Product Code</label>
                            <input name="items[0][item_code]" value="{{ old('items.0.item_code') }}" />
                        </div>
                        <div class="form-group">
                            <label>Item Description</label>
                            <input name="items[0][item_description]" value="{{ old('items.0.item_description') }}" required />
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <input name="items[0][category]" value="{{ old('items.0.category') }}" />
                        </div>
                        <div class="form-group">
                            <label>UOM</label>
                            <input name="items[0][uom]" value="{{ old('items.0.uom') }}" />
                        </div>
                    </div>
                    <div class="form-grid-4">
                        <div class="form-group">
                            <label>Lot / Batch / Model No.</label>
                            <input name="items[0][lot_number]" value="{{ old('items.0.lot_number') }}" />
                        </div>
                        <div class="form-group">
                            <label>Expiry Date / Useful Life</label>
                            <input type="date" name="items[0][expiry_date]" value="{{ old('items.0.expiry_date') }}" />
                        </div>
                        <div class="form-group">
                            <label>Quantity Received</label>
                            <input type="number" name="items[0][quantity_received]" value="{{ old('items.0.quantity_received') }}" min="1" required />
                        </div>
                        <div class="form-group">
                            <label>Unit Cost</label>
                            <input type="number" step="0.01" name="items[0][unit_cost]" value="{{ old('items.0.unit_cost') }}" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Receiving</button>
                <a href="{{ route('receivings.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
@endsection

