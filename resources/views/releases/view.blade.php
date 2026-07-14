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

        <form action="{{ route('releases.update', $release) }}" method="POST" class="stack">
            @csrf
            @method('PUT')

            <div class="grid-cols-2" style="margin-top: 1.5rem; gap: 1.5rem;">
                <div class="form-group">
                    <label>Facility / End-user</label>
                    <input type="text" name="facility_name" value="{{ old('facility_name', $release->facility_name ?? '') }}" placeholder="Enter facility name" />
                </div>

                <div class="form-group">
                    <label>Date Released</label>
                    <input type="date" name="date_released" value="{{ old('date_released', isset($release->date_released) ? $release->date_released->toDateString() : '') }}" />
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="Unreleased" @selected(($release->status ?? '') === 'Unreleased')>Unreleased</option>
                        <option value="Released" @selected(($release->status ?? '') === 'Released')>Released</option>
                        <option value="Released through pass" @selected(($release->status ?? '') === 'Released through pass')>Released through pass</option>
                        <option value="Canceled" @selected(($release->status ?? '') === 'Canceled')>Canceled</option>
                        <option value="Returned" @selected(($release->status ?? '') === 'Returned')>Returned</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Received By</label>
                    <input type="text" name="received_by" value="{{ old('received_by', $release->received_by ?? '') }}" placeholder="Enter receiver name" />
                </div>

                <div class="form-group">
                    <label>PTR/ITR/RIS No.</label>
                    <input type="text" name="ptr_itr_ris_no" value="{{ old('ptr_itr_ris_no', $release->ptr_itr_ris_no ?? '') }}" placeholder="Enter PTR/ITR/RIS No." />
                </div>

                <div class="form-group">
                    <label>Health Program</label>
                    <input type="text" name="health_program_coordinator" value="{{ old('health_program_coordinator', $release->health_program_coordinator ?? '') }}" placeholder="Enter health program" />
                </div>

                <div class="form-group">
                    <label>PAS No.</label>
                    <input type="text" name="pas_number" value="{{ old('pas_number', $release->pas_number ?? '') }}" placeholder="Enter PAS No." />
                </div>

                <div class="form-group">
                    <label>PHO Code</label>
                    <input type="text" name="pho_code" value="{{ old('pho_code', $release->pho_code ?? '') }}" placeholder="Enter PHO Code" />
                </div>

                <div class="form-group">
                    <label>Source Docs. PTR/PO No.</label>
                    <input type="text" name="source_docs_ptr_po_no" value="{{ old('source_docs_ptr_po_no', $release->source_docs_ptr_po_no ?? '') }}" placeholder="Enter source docs" />
                </div>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3" placeholder="Enter notes">{{ old('notes', $release->notes ?? '') }}</textarea>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('releases.view', $release) }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('form');
                const formInputs = Array.from(form.querySelectorAll('input, select, textarea')).filter(el => {
                    return el.offsetParent !== null;
                });

                formInputs.forEach((input, index) => {
                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            const nextIndex = index + 1;
                            if (nextIndex < formInputs.length) {
                                formInputs[nextIndex].focus();
                            }
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            const prevIndex = index - 1;
                            if (prevIndex >= 0) {
                                formInputs[prevIndex].focus();
                            }
                        }
                    });
                });
            });
        </script>


        @if(!empty($release->notes))
            <div style="margin-top: 1.25rem;">
                <div class="metric-label">Notes</div>
                <div class="page-description" style="margin-top: 0.4rem;">{{ $release->notes }}</div>
            </div>
        @endif

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
            <h2 class="section-card-title" style="margin-bottom: 1.25rem;">Released Items</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="text-align: left;">Item Description</th>
                            <th style="text-align: center;">Quantity</th>
                            <th style="text-align: center;">UOM</th>
                            <th style="text-align: right;">Unit Cost</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($release->items as $releaseItem)
                            <tr>
                                <td style="text-align: left; font-weight: 500;">{{ $releaseItem->item_description ?? '—' }}</td>
                                <td style="text-align: center;">{{ $releaseItem->quantity_released }}</td>
                                <td style="text-align: center;">{{ $releaseItem->uom ?? '—' }}</td>
                                <td style="text-align: right;">₱ {{ isset($releaseItem->unit_cost) ? number_format($releaseItem->unit_cost, 2) : '—' }}</td>
                                <td style="text-align: right; font-weight: 600; color: var(--primary);">₱ {{ isset($releaseItem->unit_cost) && isset($releaseItem->quantity_released) ? number_format($releaseItem->unit_cost * $releaseItem->quantity_released, 2) : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 2rem; text-align: center;">
                                    <div class="empty-state">
                                        <strong style="font-size: 1rem;">No items found</strong>
                                        <div style="margin-top: 0.5rem; color: var(--text-muted);">This release slip does not contain any released items.</div>
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

