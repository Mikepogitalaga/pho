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
            <div style="display:flex;gap:0.5rem;">
                <a href="{{ route('reports.liquidation.export', ['release' => $release->id]) }}" class="btn btn-secondary">Download Excel</a>
                <a href="{{ route('releases.print', $release) }}" target="_blank" class="btn btn-secondary">🖨 Print PTR</a>
                <a href="{{ route('releases.index') }}" class="btn btn-secondary">Back to Releases</a>
            </div>
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
                    <label>Status</label>
                    <select name="status" id="releaseStatusSelect">
                        <option value="Unreleased" @selected(($release->status ?? '') === 'Unreleased')>Unreleased</option>
                        <option value="Released" @selected(($release->status ?? '') === 'Released')>Released</option>
                        <option value="Released through pass" @selected(($release->status ?? '') === 'Released through pass')>Released through pass</option>
                        <option value="Canceled" @selected(($release->status ?? '') === 'Canceled')>Canceled</option>
                        <option value="Returned" @selected(($release->status ?? '') === 'Returned')>Returned</option>
                    </select>
                </div>

                <div class="form-group" style="display:flex; align-items:flex-end; gap:0.75rem;">
                    <button type="button" id="openReleaseDetailsModal" class="btn btn-secondary">Edit release details</button>
                    <span style="color: var(--text-muted); font-size: 0.95rem;">Open modal to enter Date Released and Received By.</span>
                </div>

                <div class="form-group">
                    <label>PTR/ITR/RIS No.</label>
                    <input type="text" name="ptr_itr_ris_no" value="{{ old('ptr_itr_ris_no', $release->ptr_itr_ris_no ?? '') }}" placeholder="Enter PTR/ITR/RIS No." />
                </div>

                <div id="releaseDetailsModal" style="display: none; position: fixed; inset: 0; z-index: 1000; align-items: flex-start; justify-content: center; background: rgba(0, 0, 0, 0.45); padding: 1rem 1rem 2rem; overflow-y: auto;">
                    <div style="background: #fff; border-radius: 1rem; max-width: 520px; width: 100%; padding: 1.5rem; box-shadow: 0 20px 60px rgba(0,0,0,.2); position: relative; margin-top: 1rem;">
                        <button type="button" id="closeReleaseDetailsModal" style="position: absolute; top: 0.9rem; right: 0.9rem; border: none; background: transparent; font-size: 1.25rem; cursor: pointer;">&times;</button>
                        <h2 style="margin-top: 0; margin-bottom: 1rem;">Release details</h2>
                        <div class="form-group" id="releaseDateGroup">
                            <label>Date Released <span style="color: var(--danger);">*</span></label>
                            <input type="date" name="date_released" value="{{ old('date_released', isset($release->date_released) ? $release->date_released->toDateString() : '') }}" />
                            @error('date_released')
                                <span style="color: var(--danger); font-size: 0.875rem; margin-top: 0.25rem; display:block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group" id="receivedByGroup">
                            <label>Received By <span style="color: var(--danger);">*</span></label>
                            <input type="text" name="received_by" value="{{ old('received_by', $release->received_by ?? '') }}" placeholder="Enter receiver name" />
                            @error('received_by')
                                <span style="color: var(--danger); font-size: 0.875rem; margin-top: 0.25rem; display:block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group" id="statusReasonGroup" style="display:none;">
                            <label>Reason <span style="color: var(--danger);">*</span></label>
                            <input type="text" name="status_reason" value="{{ old('status_reason', $release->status_reason ?? '') }}" placeholder="Enter reason" />
                            @error('status_reason')
                                <span style="color: var(--danger); font-size: 0.875rem; margin-top: 0.25rem; display:block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1rem;">
                            <button type="button" id="cancelReleaseDetailsModal" class="btn btn-ghost">Close</button>
                            <button type="button" id="saveReleaseDetailsModal" class="btn btn-primary">Save details</button>
                        </div>
                    </div>
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
                <label>Purpose / Activity</label>
                <textarea name="notes" rows="3" placeholder="Enter purpose / activity">{{ old('notes', $release->notes ?? '') }}</textarea>
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

                const releaseStatusSelect = document.getElementById('releaseStatusSelect');
                const releaseDetailsModal = document.getElementById('releaseDetailsModal');
                const openReleaseDetailsModal = document.getElementById('openReleaseDetailsModal');
                const closeReleaseDetailsModal = document.getElementById('closeReleaseDetailsModal');
                const cancelReleaseDetailsModal = document.getElementById('cancelReleaseDetailsModal');
                const saveReleaseDetailsModal = document.getElementById('saveReleaseDetailsModal');
                const dateInput = document.querySelector('#releaseDetailsModal input[name="date_released"]');
                const receivedInput = document.querySelector('#releaseDetailsModal input[name="received_by"]');
                const reasonInput = document.querySelector('#releaseDetailsModal input[name="status_reason"]');
                const releaseDateGroup = document.getElementById('releaseDateGroup');
                const receivedByGroup = document.getElementById('receivedByGroup');
                const statusReasonGroup = document.getElementById('statusReasonGroup');

                function isReleaseStatus(status) {
                    return ['Released', 'Released through pass'].includes(status);
                }

                function needsReasonStatus(status) {
                    return ['Canceled', 'Returned'].includes(status);
                }

                function updateReleaseDetailsState() {
                    const status = releaseStatusSelect.value;
                    const showFields = isReleaseStatus(status);
                    const needReason = needsReasonStatus(status);

                    dateInput.required = showFields;
                    receivedInput.required = showFields;
                    if (reasonInput) reasonInput.required = needReason;

                    if (releaseDateGroup) releaseDateGroup.style.display = showFields ? '' : 'none';
                    if (receivedByGroup) receivedByGroup.style.display = showFields ? '' : 'none';
                    if (statusReasonGroup) statusReasonGroup.style.display = needReason ? '' : 'none';

                    // Show modal if we need either released metadata or a reason for cancel/return
                    if ((showFields && !dateInput.value && !receivedInput.value) || (needReason && !reasonInput.value)) {
                        openReleaseDetailsModal.click();
                    }
                }

                function showModal() {
                    releaseDetailsModal.style.display = 'flex';
                }

                function hideModal() {
                    releaseDetailsModal.style.display = 'none';
                }

                releaseStatusSelect.addEventListener('change', function() {
                    const status = releaseStatusSelect.value;
                    if (isReleaseStatus(status) || needsReasonStatus(status)) {
                        showModal();
                    } else {
                        hideModal();
                    }
                    updateReleaseDetailsState();
                });

                openReleaseDetailsModal.addEventListener('click', function() {
                    showModal();
                });

                closeReleaseDetailsModal.addEventListener('click', hideModal);
                cancelReleaseDetailsModal.addEventListener('click', hideModal);

                saveReleaseDetailsModal.addEventListener('click', function() {
                    const validDate = !dateInput.required || dateInput.checkValidity();
                    const validReceived = !receivedInput.required || receivedInput.checkValidity();
                    const validReason = !(reasonInput && reasonInput.required) || (reasonInput && reasonInput.checkValidity());

                    if (validDate && validReceived && validReason) {
                        hideModal();
                    } else {
                        if (!validDate) dateInput.reportValidity();
                        if (!validReceived) receivedInput.reportValidity();
                        if (!validReason && reasonInput) reasonInput.reportValidity();
                    }
                });

                releaseDetailsModal.addEventListener('click', function(event) {
                    if (event.target === releaseDetailsModal) {
                        hideModal();
                    }
                });

                updateReleaseDetailsState();
            });
        </script>


       

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
            <h2 class="section-card-title" style="margin-bottom: 1.25rem;">Released Items</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
<th style="text-align: left;">Item Description</th>
                            <th style="text-align: center;">Batch/Lot No.</th>
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
                                <td style="text-align: center;">{{ $releaseItem->lot_number ?? '—' }}</td>
                                <td style="text-align: center;">{{ $releaseItem->quantity_released }}</td>
                                <td style="text-align: center;">{{ $releaseItem->uom ?? '—' }}</td>
                                <td style="text-align: right;">₱ {{ isset($releaseItem->unit_cost) ? number_format($releaseItem->unit_cost, 2) : '—' }}</td>
                                <td style="text-align: right; font-weight: 600; color: var(--primary);">₱ {{ isset($releaseItem->unit_cost) && isset($releaseItem->quantity_released) ? number_format($releaseItem->unit_cost * $releaseItem->quantity_released, 2) : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 2rem; text-align: center;">
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
