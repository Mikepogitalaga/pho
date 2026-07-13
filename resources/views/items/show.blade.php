@extends('layouts.app')

@section('title', 'Item Details')
@section('pageHeading', 'Item Details')
@section('pageSubheading', 'View inventory item information. Stock is updated through Receivings and Releases only.')

@section('content')
    <div class="section-card">
        <div class="section-header">
            <div>
                <h1 class="page-heading">{{ $item->item_code }} - {{ $item->name }}</h1>
                <p class="page-description">Current stock and item details.</p>
            </div>
            <a href="{{ route('items.index') }}" class="btn btn-secondary">Back to Items</a>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Product Code</label>
                <p>{{ $item->item_code }}</p>
            </div>
            <div class="form-group">
                <label>Item Description</label>
                <p>{{ $item->name }}</p>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Category</label>
                <p>{{ $item->category }}</p>
            </div>
            <div class="form-group">
                <label>Unit of Measure (UOM)</label>
                <p>{{ $item->unit }}</p>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Current Stock</label>
                <p>{{ $item->quantity_on_hand }}</p>
            </div>
            <div class="form-group">
                <label>Unit Cost</label>
                <p>{{ $item->unit_cost ? number_format($item->unit_cost, 2) : '0.00' }}</p>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Location</label>
                <p>{{ $item->location }}</p>
            </div>
            <div class="form-group">
                <label>Stock Keeping Unit (Program)</label>
                <p>{{ $item->stock_keeping_unit }}</p>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Program Coordinator</label>
                <p>{{ $item->program_coordinator }}</p>
            </div>
            <div class="form-group">
                <label>Status</label>
                <p><span class="status-pill {{ $item->status_class }}">{{ $item->status }}</span></p>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Expiry</label>
                <p><span class="status-pill {{ $item->expiry_badge_class }}">{{ $item->expiry_label }}</span></p>
            </div>
            <div></div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <p>{{ $item->description }}</p>
        </div>
    </div>
@endsection
