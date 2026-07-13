@extends('layouts.app')

@section('title', 'Release Details')
@section('pageHeading', 'Release Details')
@section('pageSubheading', 'Review release slip information and released items.')

@section('content')
    <section class="card">
        <div class="section-header">
            <div>
                <h1 class="page-heading">{{ $release->release_number }}</h1>
                <p class="page-description">PAS: {{ $release->pas_number ?? '—' }} · PHO: {{ $release->pho_code ?? '—' }}</p>
            </div>
            <a href="{{ route('releases.index') }}" class="btn btn-secondary">Back to Releases</a>
        </div>

        <div class="grid-cols-2" style="margin-top: 1rem;">
            <div>
                <div class="metric-label">Facility / End-user</div>
                <div class="page-description" style="margin-top: 0.4rem;">{{ $release->facility_name ?? '—' }}</div>
            </div>

            <div>
                <div class="metric-label">Date Released</div>
                <div class="page-description" style="margin-top: 0.4rem;">{{ isset($release->date_released) ? $release->date_released->format('M d, Y') : '—' }}</div>
            </div>
            <div>
                <div class="metric-label">Status</div>
                <div class="page-description" style="margin-top: 0.4rem;">{{ $release->status ?? '—' }}</div>
            </div>
            <div>
                <div class="metric-label">Received By</div>
                <div class="page-description" style="margin-top: 0.4rem;">{{ $release->received_by ?? '—' }}</div>
            </div>
            <div>
                <div class="metric-label" style="margin-top: 0.25rem;">PTR/ITR/RIS</div>
                <div class="page-description" style="margin-top: 0.4rem; font-size: 1.05rem; font-weight: 700;">
                    <strong style="display: block;">{{ $release->ptr_itr_ris_no ?? '—' }}</strong>
                </div>
            </div>
        </div>

        <div style="margin-top: 1rem;">
            <div class="section-card-title">Update Status</div>
            <div class="table-actions" style="margin-top: 0.75rem;">
                <form action="{{ route('releases.status', [$release, 'released-through-pass']) }}" method="POST" class="stack" style="gap: 0.5rem; margin-top: 0.5rem;">
@csrf
                    <input type="hidden" name="_status" value="released-through-pass">
                    <div class="form-grid-2">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Receiver</label>
                            <input name="received_by" value="{{ old('received_by', $release->received_by ?? '') }}" />
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Received Date</label>
                            <input type="date" name="date_released" value="{{ old('date_released', isset($release->date_released) ? $release->date_released->toDateString() : '') }}" />
                        </div>
                    </div>
                    <button type="submit" class="btn btn-secondary">Released Through Pass</button>
                </form>



                <form action="{{ route('releases.status', [$release, 'released']) }}" method="POST" class="stack" style="gap: 0.5rem; margin-top: 0.5rem;">
                    @csrf
                    <input type="hidden" name="_status" value="released">
                    <div class="form-grid-2">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Receiver</label>
                            <input name="received_by" value="{{ old('received_by', $release->received_by ?? '') }}" />
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Received Date</label>
                            <input type="date" name="date_released" value="{{ old('date_released', isset($release->date_released) ? $release->date_released->toDateString() : '') }}" />
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Released</button>
                </form>


                <form action="{{ route('releases.status', [$release, 'canceled']) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-ghost">Canceled</button>
                </form>

                <form action="{{ route('releases.status', [$release, 'returned']) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-ghost">Returned</button>
                </form>

                <form action="{{ route('releases.status', [$release, 'unreleased']) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-ghost">Unreleased</button>
                </form>
            </div>
        </div>


        @if(!empty($release->notes))
            <div style="margin-top: 1.25rem;">
                <div class="metric-label">Notes</div>
                <div class="page-description" style="margin-top: 0.4rem;">{{ $release->notes }}</div>
            </div>
        @endif

        <div style="margin-top: 1.5rem;">
            <h2 class="section-card-title">Released Items</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity Released</th>
                            <th>UOM</th>
                            <th>Unit Cost</th>
                            <th>No PTR/ITR/RIS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($release->items as $releaseItem)
                            <tr>
                                <td>{{ $releaseItem->item_description ?? $releaseItem->item_description ?? '—' }}</td>
                                <td>{{ $releaseItem->quantity_released }}</td>
                                <td>{{ $releaseItem->uom ?? '—' }}</td>
                                <td>{{ isset($releaseItem->unit_cost) ? number_format($releaseItem->unit_cost, 2) : '—' }}</td>
                                <td>
                                    <input
                                        name="ptr_itr_ris_no"
                                        value="{{ old('ptr_itr_ris_no', $release->ptr_itr_ris_no ?? '') }}"
                                        placeholder="Enter PTR/ITR/RIS No."
                                        {{ empty($release->ptr_itr_ris_no) ? '' : 'readonly' }}
                                    />
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="4" style="padding: 1.25rem;">
                                    <div class="empty-state">
                                        <strong>No items found.</strong>
                                        <div style="margin-top: 0.35rem;">This release slip does not contain any released items.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

