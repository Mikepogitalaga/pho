@extends('layouts.app')

@section('title', 'Suppliers')
@section('pageHeading', 'Suppliers')
@section('pageSubheading', 'Manage supplier details and contact information.')

@section('content')
    <div class="section-header">
        <div>
            
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
                        <th>Type</th>
                        <th class="col-hide-md">Contact</th>
                        <th class="col-hide-md">Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                     @forelse ($suppliers as $supplier)
                         <tr>
                              <td class="mobile-card-header">
                                  <span><a href="{{ route('suppliers.show', $supplier) }}" class="table-link" style="font-weight:600;color:var(--danger);"> {{ $supplier->company_name }}</a></span>
                              </td>
                              <td data-label="Type">
                                  <span class="badge {{ $supplier->isDoh() ? 'badge-success' : 'badge-warning' }}">{{ $supplier->supplier_type }}</span>
                              </td>
                              <td data-label="Contact" class="col-hide-md">{{ $supplier->contact_person }}</td>
                              <td data-label="Phone" class="col-hide-md">{{ $supplier->phone_number }}</td>
                              <td data-label="Email">{{ $supplier->email }}</td>
                             <td class="mobile-card-actions">
                                 <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-secondary" style="min-height:2rem;padding:0.3rem 0.7rem;font-size:0.8rem;">Edit</a>
                                 <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this supplier?');">
                                     @csrf
                                     @method('DELETE')
                                     <button type="submit" class="btn btn-danger" style="min-height:2rem;padding:0.3rem 0.7rem;font-size:0.8rem;">Delete</button>
                                 </form>
                             </td>
                         </tr>
                     @empty
                        <tr>
                            <td colspan="6" style="padding: 1.25rem;">
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

    <x-pagination.modern :paginator="$suppliers" />
@endsection

