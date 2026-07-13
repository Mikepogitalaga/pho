@extends('layouts.app')

@section('title', 'Suppliers')
@section('pageHeading', 'Suppliers')
@section('pageSubheading', 'Manage supplier details and contact information.')

@section('content')
    <div class="section-header">
        <div>
            <h1 class="page-heading">Suppliers</h1>
            <p class="page-description">Search, edit, and manage supplier records.</p>
        </div>
        <div class="table-actions">
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">New Supplier</a>
        </div>
    </div>

    <form method="GET" class="search-panel">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search suppliers..." class="search-input" />
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <section class="card" style="padding: 0.75rem;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Contact</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->company_name }}</td>
                            <td>{{ $supplier->contact_person }}</td>
                            <td>{{ $supplier->phone_number }}</td>
                            <td>{{ $supplier->email }}</td>
                            <td class="table-actions">
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="table-link">Edit</a>
                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-link table-link-danger" onclick="return confirm('Delete this supplier?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 1.25rem;">
                                <div class="empty-state">
                                    <strong>No suppliers found.</strong>
                                    <div style="margin-top: 0.35rem;">Create your first supplier to get started.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="pagination-wrapper">
        {{ $suppliers->withQueryString()->links() }}
    </div>
@endsection

