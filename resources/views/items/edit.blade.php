@extends('layouts.app')

@section('content')
    <section class="card">
        <div class="section-header">
            <div>
                <h1 class="page-heading">Edit Item</h1>
                <p class="page-description">Update item details and stock information.</p>
            </div>
            <a href="{{ route('items.index') }}" class="btn btn-secondary">Back to Items</a>
        </div>

        <form action="{{ route('items.update', $item) }}" method="POST" class="stack">

            @csrf
            @method('PUT')

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Item Code</label>
                    <input name="item_code" value="{{ old('item_code', $item->item_code) }}" required>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input name="name" value="{{ old('name', $item->name) }}" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="DM" @selected(old('category', $item->category) === 'DM')>DM</option>
                        <option value="MDL" @selected(old('category', $item->category) === 'MDL')>MDL</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <input name="unit" value="{{ old('unit', $item->unit) }}">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3">{{ old('description', $item->description) }}</textarea>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Quantity on Hand</label>
                    <input name="quantity_on_hand" type="number" value="{{ old('quantity_on_hand', $item->quantity_on_hand) }}" required>
                </div>
                <div class="form-group">
                    <label>Reorder Level</label>
                    <input name="reorder_level" type="number" value="{{ old('reorder_level', $item->reorder_level) }}">
                </div>
            </div>

            <div class="form-group">
                <label>Location</label>
                <input name="location" value="{{ old('location', $item->location) }}">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Item</button>
                <a href="{{ route('items.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
@endsection

