@extends('layouts.app')

@section('title', 'Receive Supplies')
@section('pageHeading', 'Receive Supplies')
@section('pageSubheading', 'View receiving records and add new supply receipts.')

@section('content')
    <div class="section-header">
        <div>
            <h1 class="page-heading">Receive Supplies</h1>
            <p class="page-description">Review receipts and manage incoming stock.</p>
        </div>
        <div class="table-actions">
            <a href="{{ route('receivings.create') }}" class="btn btn-primary">New Receiving</a>
        </div>
    </div>

    <section class="card" style="padding: 0.75rem;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Receiving No.</th>
                        <th>Supplier</th>
                        <th>PO No.</th>
                        <th>Date Received</th>
                        <th>Received By</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receivings as $receiving)
                        <tr>
                            <td>{{ $receiving->receiving_number }}</td>
                            <td>{{ $receiving->supplier->company_name }}</td>
                            <td>{{ $receiving->po_number }}</td>
                            <td>{{ $receiving->date_received->format('M d, Y') }}</td>
                            <td>{{ $receiving->received_by }}</td>
                            <td>{{ $receiving->location }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 1.25rem;">
                                <div class="empty-state">
                                    <strong>No receiving records found.</strong>
                                    <div style="margin-top: 0.35rem;">Create a new receiving slip to start tracking incoming stock.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="pagination-wrapper">
        {{ $receivings->links() }}
    </div>
@endsection

