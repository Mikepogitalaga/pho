@extends('layouts.app')

@section('content')
    <section class="card">
        <div class="section-header">
            <div>
                <h1 class="page-heading">Add Item</h1>
                <p class="page-description">Create a new inventory item and define its stock details.</p>
            </div>
            <a href="{{ route('items.index') }}" class="btn btn-secondary">Back to Items</a>
        </div>

        <form action="{{ route('items.store') }}" method="POST" class="stack">

            @csrf

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Item Code</label>
                    <input name="item_code" value="{{ old('item_code') }}" required>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input name="name" value="{{ old('name') }}" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Category</label>
                    <input name="category" value="{{ old('category') }}">
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <input name="unit" value="{{ old('unit') }}">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3">{{ old('description') }}</textarea>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Quantity on Hand</label>
                    <input name="quantity_on_hand" type="number" value="{{ old('quantity_on_hand', 0) }}" required>
                </div>
                <div class="form-group">
                    <label>Reorder Level</label>
                    <input name="reorder_level" type="number" value="{{ old('reorder_level', 0) }}">
                </div>
            </div>

            <div class="form-group">
                <label>Location</label>
                <input name="location" value="{{ old('location') }}">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Item</button>
                <a href="{{ route('items.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
@endsection

