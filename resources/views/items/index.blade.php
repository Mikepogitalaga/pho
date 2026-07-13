@extends('layouts.app')

@section('title', 'Items')
@section('pageHeading', 'Items')
@section('pageSubheading', 'Master inventory list. Stock is managed through Receiving and Release only.')

@section('content')
    <div class="section-header">
        <div>
            <h1 class="page-heading">Items</h1>
            <p class="page-description">Search, filter, and view item details.</p>
        </div>
        <div class="table-actions">
            <button type="button" class="btn btn-secondary" onclick="window.print()">Print</button>
            <a href="{{ route('items.export', request()->query()) }}" class="btn btn-secondary">Export</a>
        </div>
    </div>

    <form method="GET" class="search-panel">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search items..." class="search-input" />

        <select name="category" class="search-input">
            <option value="">All categories</option>
            @foreach($categories as $categoryOption)
                <option value="{{ $categoryOption }}" @selected($categoryOption === $category)>{{ $categoryOption }}</option>
            @endforeach
        </select>

        <select name="status" class="search-input">
            <option value="">All statuses</option>
            <option value="available" @selected($status === 'available')>Available</option>
            <option value="low" @selected($status === 'low')>Low Stock</option>
            <option value="out" @selected($status === 'out')>Out of Stock</option>
        </select>

        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <section class="card" style="padding: 0.75rem;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Product Code</th>
                        <th>Item Description</th>
                        <th>Category</th>
                        <th>UOM</th>
                        <th>Current Stock</th>
                        <th>Unit Cost</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Expiry</th>
                        <th>Actions</th>

                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $item->item_code }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>{{ $item->quantity_on_hand }}</td>
                            <td>{{ $item->unit_cost ? number_format($item->unit_cost, 2) : '0.00' }}</td>
                            <td>{{ $item->location }}</td>
                            <td><span class="status-pill {{ $item->status_class }}">{{ $item->status }}</span></td>

                            <td><span class="status-pill {{ $item->expiry_badge_class }}">{{ $item->expiry_label }}</span></td>
                            <td class="table-actions">
                                <a href="{{ route('items.show', $item) }}" class="table-link">View</a>
                            </td>



                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" style="padding: 1.25rem;">
                                <div class="empty-state">
                                    <strong>No items found.</strong>
                                    <div style="margin-top: 0.35rem;">Try adjusting your search or filters.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="pagination-wrapper">
        {{ $items->withQueryString()->links() }}
    </div>
@endsection

