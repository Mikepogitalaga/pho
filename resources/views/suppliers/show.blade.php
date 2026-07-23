@extends('layouts.app')

@section('title', $supplier->company_name)
@section('pageHeading', $supplier->company_name)
@section('pageSubheading', 'Supplier dashboard — view performance, receivings, and contact details.')

@php
    $dashboardRoute = $supplier->isDoh() ? route('doh.dashboard', $supplier) : ($supplier->isGso() ? route('gso.dashboard', $supplier) : null);
@endphp

@section('content')
    {{-- Supplier Info Card --}}
    <section class="card" aria-label="Supplier information">
        <div class="section-header" style="border-bottom: none; padding-bottom: 0;">
            <div>
                <h2 class="section-card-title" style="font-size: 1.1rem;">Supplier Details</h2>
            </div>
            <div class="table-actions">
                @if($dashboardRoute)
                    <a href="{{ $dashboardRoute }}" class="btn btn-secondary">View Full Dashboard</a>
                @endif
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-secondary">Edit Supplier</a>
                <a href="{{ route('receivings.create', ['supplier' => $supplier->id]) }}" class="btn btn-primary">New Receiving</a>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 0.5rem 1.15rem 1.15rem;">
            <div>
                <p style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--text-muted); font-weight: 600;">Company Name</p>
                <p style="margin: 0.3rem 0 0; font-weight: 600;">{{ $supplier->company_name }}</p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--text-muted); font-weight: 600;">Supplier Type</p>
                <p style="margin: 0.3rem 0 0;">
                    <span class="badge {{ $supplier->isDoh() ? 'badge-success' : 'badge-warning' }}">{{ $supplier->supplier_type }}</span>
                </p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--text-muted); font-weight: 600;">Contact Person</p>
                <p style="margin: 0.3rem 0 0;">{{ $supplier->contact_person ?? '—' }}</p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--text-muted); font-weight: 600;">Phone</p>
                <p style="margin: 0.3rem 0 0;">{{ $supplier->phone_number ?? '—' }}</p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--text-muted); font-weight: 600;">Email</p>
                <p style="margin: 0.3rem 0 0;">{{ $supplier->email ?? '—' }}</p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--text-muted); font-weight: 600;">Address</p>
                <p style="margin: 0.3rem 0 0;">{{ $supplier->address ?? '—' }}</p>
            </div>
        </div>
    </section>

    {{-- KPI Grid --}}
    <section class="dashboard-kpi-grid" role="region" aria-label="Supplier key metrics">
        <article class="kpi-card kpi-card--blue">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                </span>
                <span class="kpi-card-label">Total Receivings</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalReceivings) }}</p>
            <p class="kpi-card-foot">All records from this supplier</p>
        </article>

        <article class="kpi-card kpi-card--green">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                </span>
                <span class="kpi-card-label">Total Items Received</span>
            </div>
            <p class="kpi-card-value">{{ number_format($totalItemsReceived) }}</p>
            <p class="kpi-card-foot">Cumulative quantity</p>
        </article>

        <article class="kpi-card kpi-card--violet">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </span>
                <span class="kpi-card-label">Total Received Value</span>
            </div>
            <p class="kpi-card-value">₱{{ number_format($totalReceivedCost ?? 0, 2) }}</p>
            <p class="kpi-card-foot">Estimated value of supplies</p>
        </article>

        <article class="kpi-card kpi-card--indigo">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </span>
                <span class="kpi-card-label">This Month</span>
            </div>
            <p class="kpi-card-value">{{ number_format($receivingsThisMonth) }}</p>
            <p class="kpi-card-foot">Receivings in {{ now()->format('F Y') }}</p>
        </article>

        <article class="kpi-card kpi-card--amber">
            <div class="kpi-card-header">
                <span class="kpi-card-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                <span class="kpi-card-label">Last Received</span>
            </div>
            <p class="kpi-card-value">{{ $latestReceiving ? $latestReceiving->date_received->format('M d') : '—' }}</p>
            <p class="kpi-card-foot">{{ $latestReceiving ? $latestReceiving->receiving_number : 'No records' }}</p>
        </article>
    </section>

    {{-- Recent Receivings --}}
    <section class="section-card" aria-label="Recent receivings from this supplier">
        <div class="section-header compact">
            <div>
                <h2 class="section-card-title">Recent Receivings</h2>
                <p class="page-description">Latest supply receipts from {{ $supplier->company_name }}.</p>
            </div>
            <a href="{{ route('receivings.index', ['supplier' => $supplier->company_name]) }}" class="section-link">View all</a>
        </div>

        @if($recentReceivings->isEmpty())
            <div class="empty-state" role="status">
                <strong>No receiving records found.</strong>
                <div style="margin-top: 0.35rem;">Record incoming supplies from this supplier to see them here.</div>
            </div>
            @else
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
<th>ICS/PTR/RIS</th>
                            <th>PO No.</th>
                            <th>Date Received</th>
                            <th>Received By</th>
                            <th>Items</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentReceivings as $receiving)
                            <tr>
                                <td>{{ $receiving->ics_ptr_ris ?? '—' }}</td>
                                <td>{{ $receiving->po_number ?? '—' }}</td>
                                <td>{{ $receiving->date_received->format('M d, Y') }}</td>
                                <td>{{ $receiving->received_by }}</td>
                                <td>{{ $receiving->items->count() }}</td>
                                <td>
                                    <a href="{{ route('receivings.view', $receiving) }}" class="table-link" aria-label="View receiving">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection

