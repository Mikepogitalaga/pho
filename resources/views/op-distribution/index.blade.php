@extends('layouts.app')

@section('title', 'OP Distribution')
@section('pageHeading', 'OP Distribution')
@section('pageSubheading', 'Office of the President — patient medicine distribution records.')

@section('content')
<div class="section-card">
    <div class="section-header">
        <div>
            <h2 class="section-card-title" style="margin:0;">OP Distribution Records</h2>
            <p class="page-description" style="margin-top:0.25rem;">{{ number_format($distributions->total()) }} total records</p>
        </div>
        <a href="{{ route('op-distribution.create') }}" class="btn btn-primary">+ New Record</a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('op-distribution.index') }}" style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:flex-end; padding:0.75rem 0 1rem;">
        <div style="display:flex; flex-direction:column; gap:0.3rem; flex:2; min-width:200px;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference no., patient, distributed by…" class="search-input">
        </div>
        <div style="display:flex; flex-direction:column; gap:0.3rem; min-width:140px;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Status</label>
            <select name="status" class="search-input">
                <option value="">All statuses</option>
                <option value="Draft" @selected(request('status') === 'Draft')>Draft</option>
                <option value="Released" @selected(request('status') === 'Released')>Released</option>
                <option value="Canceled" @selected(request('status') === 'Canceled')>Canceled</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="padding:0.5rem 1rem;">Filter</button>
        <a href="{{ route('op-distribution.index') }}" class="btn btn-ghost" style="padding:0.5rem 0.8rem;">Clear</a>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Reference No.</th>
                    <th>Date</th>
                    <th>Distributed By</th>
                    <th style="text-align:center;">Patients</th>
                    <th>Status</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($distributions as $dist)
                    <tr>
                        <td style="font-weight:600;">{{ $dist->reference_number }}</td>
                        <td>{{ $dist->date_distributed->format('M d, Y') }}</td>
                        <td>{{ $dist->distributed_by ?? '—' }}</td>
                        <td style="text-align:center;">{{ number_format($dist->items_count) }}</td>
                        <td>
                            @php
                                $sc = match($dist->status) {
                                    'Released' => 'badge-success',
                                    'Canceled' => 'badge-danger',
                                    default    => 'badge-warning',
                                };
                            @endphp
                            <span class="status-pill {{ $sc }}">{{ $dist->status }}</span>
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('op-distribution.view', $dist) }}" class="btn btn-secondary" style="min-height:auto;padding:0.35rem 0.85rem;font-size:0.82rem;">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:2.5rem;text-align:center;color:var(--text-muted);">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($distributions->hasPages())
        <div style="margin-top:1rem;">{{ $distributions->links() }}</div>
    @endif
</div>
@endsection
