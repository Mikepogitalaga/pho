@extends('layouts.app')

@section('title', $facilityCategory . ' — ' . $facilityName)
@section('pageHeading', $facilityCategory . ' — ' . $facilityName)
@section('pageSubheading', 'Top released items for this facility.')

@section('content')
    <a href="{{ route('analytics.facility-categories', request()->only(['start_date', 'end_date', 'facility_category'])) }}" class="btn btn-secondary" style="margin-bottom:1rem;">← Back to Analytics</a>

    {{-- Facility Summary --}}
    <section class="section-card" style="margin-bottom:1.5rem;">
        <h3 style="margin:0 0 1rem;font-size:1.05rem;font-weight:700;">Facility: {{ $facilityName }}</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;">
            <div>
                <p class="detail-label">Category</p>
                <p class="detail-value">{{ $facilityCategory }}</p>
            </div>
            <div>
                <p class="detail-label">Total Items Released</p>
                <p class="detail-value">{{ $items->count() }}</p>
            </div>
            <div>
                <p class="detail-label">Total DM Released</p>
                <p class="detail-value" style="color:#2563eb;font-weight:700;">{{ number_format($items->sum('dm_released')) }}</p>
            </div>
            <div>
                <p class="detail-label">Total MDL Released</p>
                <p class="detail-value" style="color:#0d9488;font-weight:700;">{{ number_format($items->sum('mdl_released')) }}</p>
            </div>
            <div>
                <p class="detail-label">Total Released</p>
                <p class="detail-value" style="font-weight:700;">{{ number_format($items->sum('total_released')) }}</p>
            </div>
        </div>
    </section>

    {{-- Top Items Table --}}
    <section class="section-card">
        <h3 style="margin:0 0 1rem;font-size:1.05rem;font-weight:700;">Top Released Items</h3>
        @if($items->isNotEmpty())
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="padding-left:1.1rem;">Item Description</th>
                            <th style="text-align:center;">DM</th>
                            <th style="text-align:center;">MDL</th>
                            <th style="text-align:right;">Total</th>
                            <th style="text-align:center;">Transactions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td style="padding-left:1.1rem;">{{ $item->item_description }}</td>
                                <td style="text-align:center;color:#2563eb;font-weight:600;">{{ number_format($item->dm_released) }}</td>
                                <td style="text-align:center;color:#0d9488;font-weight:600;">{{ number_format($item->mdl_released) }}</td>
                                <td style="text-align:right;font-weight:700;">{{ number_format($item->total_released) }}</td>
                                <td style="text-align:center;">{{ $item->transaction_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <strong>No release data found.</strong>
                <div>No released items recorded for this facility in the selected period.</div>
            </div>
        @endif
    </section>
@endsection
