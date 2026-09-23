@extends('layouts.app')

@section('title', 'PAS ' . $pas->pas_number)
@section('pageHeading', 'PAS ' . $pas->pas_number)

@section('content')
<section class="card pas-card">
    <div class="section-header">
        <div>
            <h1 class="page-heading">{{ $pas->pas_number }}</h1>
            @php
                $linkedRelease = $pas->release;
                $displayStatus = $linkedRelease ? $linkedRelease->status : $pas->status;
                $badgeClass = match($displayStatus) {
                    'Released', 'Released through pass' => 'badge-success',
                    'Canceled' => 'badge-danger',
                    'Returned' => 'badge-warning',
                    default    => 'badge-secondary',
                };
            @endphp
            <p class="page-description">
                <span class="pas-badge pas-badge--{{ in_array($displayStatus, ['Released', 'Released through pass']) ? 'green' : ($displayStatus === 'Canceled' ? 'red' : 'amber') }}">
                    <span class="pas-badge-dot"></span>
                    {{ $displayStatus }}
                </span>
                &nbsp; Property Allocation Slip — items are NOT deducted from inventory.
            </p>
        </div>
            <div style="display:flex;gap:0.5rem;">
                @php
                    $releaseParams = [
                        'pas_number'                 => $pas->pas_number,
                        'health_program_coordinator' => $pas->program,
                        'release_coordinator'        => $pas->facility_coordinator,
                        'facility_name'              => $pas->facility_name ?: $pas->facility_coordinator,
                        'transfer_type'              => $pas->transfer_type ?? 'PTR',
                        'reason_for_transfer'        => $pas->reason_for_transfer,
                    ];
                    if (!empty($pas->purpose_activity)) {
                        $releaseParams['purpose_activity'] = $pas->purpose_activity;
                    }
                    foreach ($pas->items as $index => $item) {
                        $releaseParams["items[{$index}][item_description]"] = $item->item_description;
                        $releaseParams["items[{$index}][quantity_released]"] = $item->quantity;
                        $releaseParams["items[{$index}][uom]"] = $item->unit;
                        $releaseParams["items[{$index}][unit_cost]"] = $item->unit_cost;
                        $releaseParams["items[{$index}][item_id]"] = $item->item_id;
                        $releaseParams["items[{$index}][lot_number]"] = $item->lot_number;
                    }
                @endphp
                @if($linkedRelease)
                    <a href="{{ route('releases.view', $linkedRelease) }}" class="btn btn-primary">View PTR</a>
                @else
                    @if(! auth()->user()?->program_id)
                    <a href="{{ route('releases.create', $releaseParams) }}" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Create PTR
                    </a>
                    @endif
                @endif
                <a href="{{ route('pas.edit', $pas) }}" class="btn btn-outline">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 0 4 4L7.5 20.5 4 21l.5-3.5L17 3z"/></svg>
                    Edit
                </a>
                <a href="{{ route('pas.print', $pas) }}" target="_blank" class="btn btn-outline">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 2H4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print PAS
                </a>
                <a href="{{ route('pas.index') }}" class="btn btn-outline">Back to PAS</a>
            </div>
    </div>

    {{-- Header Details --}}
    <div class="pas-details-grid" style="margin-bottom:1.5rem;">
        <div class="pas-detail-item">
            <p class="pas-detail-label">PAS Number</p>
            <p class="pas-detail-value">{{ $pas->pas_number }}</p>
        </div>
        <div class="pas-detail-item">
            <p class="pas-detail-label">Date of PASS</p>
            <p class="pas-detail-value">{{ $pas->date_of_pass?->format('M d, Y') }}</p>
        </div>
        <div class="pas-detail-item">
            <p class="pas-detail-label">Date Released</p>
            <p class="pas-detail-value">{{ $pas->date_released?->format('M d, Y') ?? '—' }}</p>
        </div>
        <div class="pas-detail-item">
            <p class="pas-detail-label">Supplier</p>
            <p class="pas-detail-value">{{ $pas->supplier?->company_name ?? '—' }}</p>
        </div>
        <div class="pas-detail-item">
            <p class="pas-detail-label">Facility / End-user</p>
            <p class="pas-detail-value">{{ $pas->facility_name ?? '—' }}</p>
        </div>
        <div class="pas-detail-item">
            <p class="pas-detail-label">Facility Coordinator</p>
            <p class="pas-detail-value">{{ $pas->facility_coordinator ?? '—' }}</p>
        </div>
        <div class="pas-detail-item">
            <p class="pas-detail-label">Program</p>
            <p class="pas-detail-value">{{ $pas->program ?? '—' }}</p>
        </div>
        <div class="pas-detail-item">
            <p class="pas-detail-label">Purpose / Activity</p>
            <p class="pas-detail-value">{{ $pas->purpose_activity ?? '—' }}</p>
        </div>
        @if($pas->reason_for_transfer)
        <div class="pas-detail-item pas-detail-item--full">
            <p class="pas-detail-label">Reason for Transfer</p>
            <p class="pas-detail-value">{{ $pas->reason_for_transfer }}</p>
        </div>
        @endif
        <div class="pas-detail-item">
            <p class="pas-detail-label">Status</p>
            <p class="pas-detail-value">
                <span class="pas-badge pas-badge--{{ in_array($displayStatus, ['Released', 'Released through pass']) ? 'green' : ($displayStatus === 'Canceled' ? 'red' : 'amber') }}">
                    <span class="pas-badge-dot"></span>
                    {{ $displayStatus }}
                </span>
            </p>
        </div>
        @if($pas->request_status)
        <div class="pas-detail-item">
            <p class="pas-detail-label">Request Status</p>
            <p class="pas-detail-value">
                @php
                    $requestStatus = $pas->request_status;
                    $requestBadgeClass = match($requestStatus) {
                        'approved', 'completed' => 'badge-success',
                        'rejected' => 'badge-danger',
                        default    => 'badge-warning',
                    };
                @endphp
                <span class="pas-badge pas-badge--{{ $requestBadgeClass }}">
                    <span class="pas-badge-dot"></span>
                    {{ ucfirst(str_replace('_', ' ', $requestStatus)) }}
                </span>
                @if($pas->requester)
                    <span style="font-size:0.8rem;color:var(--text-muted);margin-left:0.5rem;">
                        by {{ $pas->requester->name }}
                    </span>
                @endif
            </p>
        </div>
        @endif
        @if(! auth()->user()?->program_id && $pas->notes)
        <div class="pas-detail-item pas-detail-item--full">
            <p class="pas-detail-label">Notes</p>
            <p class="pas-detail-value">{{ $pas->notes }}</p>
        </div>
        @endif
    </div>

     {{-- Items Table --}}
     <h2 class="section-title" style="margin-bottom:0.75rem;">Items</h2>
     <div class="table-wrapper pas-table-wrap">
         <table class="data-table pas-table">
              <thead>
                  <tr>
                      <th style="width:50px;text-align:center;">#</th>
                      <th>Item Description</th>
                      @if(! auth()->user()?->program_id)
                      <th class="col-hide-md">PHO Code</th>
                      <th class="col-hide-md">Lot Number</th>
                      <th class="col-hide-md">Expiration</th>
                      @endif
                      <th style="text-align:center;">Qty</th>
                      @if(! auth()->user()?->program_id)
                      <th class="col-hide-md">Unit</th>
                      <th class="col-hide-md">Unit Cost</th>
                      @endif
                      <th style="text-align:right;">Total Cost</th>
                  </tr>
              </thead>
             <tbody>
                 @php $grandTotal = 0; @endphp
                  @forelse($pas->items as $i => $item)
                      @php $grandTotal += (float) $item->total_cost; @endphp
                      <tr>
                          <td class="pas-row-num">{{ $i + 1 }}</td>
                          <td data-label="Item Description">{{ $item->item_description }}</td>
                          @if(! auth()->user()?->program_id)
                          <td data-label="PHO Code" class="col-hide-md">
                              <span style="font-weight:600; font-family:monospace; color:var(--text);">{{ $item->product_code ?? $item->item?->item_code ?? '—' }}</span>
                          </td>
                          <td data-label="Lot Number" class="col-hide-md pas-muted">{{ $item->lot_number ?? '—' }}</td>
                          <td data-label="Expiration" class="col-hide-md pas-muted">{{ $item->expiration_date?->format('M d, Y') ?? '—' }}</td>
                          @endif
                          <td data-label="Qty" style="text-align:center;">
                              <span style="font-weight:600;">{{ number_format($item->quantity) }}</span>
                          </td>
                          @if(! auth()->user()?->program_id)
                          <td data-label="Unit" class="col-hide-md pas-muted">{{ $item->unit }}</td>
                          <td data-label="Unit Cost" class="col-hide-md">{{ number_format($item->unit_cost, 2) }}</td>
                          @endif
                          <td data-label="Total Cost" style="text-align:right; font-weight:700; color:var(--text);">
                              ₱ {{ number_format($item->total_cost, 2) }}
                          </td>
                      </tr>
                  @empty
                      <tr class="pas-empty-row">
                          <td colspan="{{ auth()->user()?->program_id ? 4 : 9 }}">
                             <div class="pas-empty">
                                 <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                                 <p>No items recorded.</p>
                             </div>
                         </td>
                     </tr>
                 @endforelse
             </tbody>
            @if($pas->items->count())
            <tfoot>
                <tr class="pas-total-row">
                    <td colspan="8" style="text-align:right; font-weight:700; color:var(--text-muted); text-transform:uppercase; font-size:0.8rem; letter-spacing:0.05em;">Grand Total</td>
                    <td style="font-weight:800; font-size:1rem; color:var(--text);">₱ {{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Status Actions --}}
    @if($pas->status === 'Pending')
    <div style="margin-top:1.5rem;display:flex;gap:0.75rem;flex-wrap:wrap;">
        @if(! auth()->user()?->program_id)
        <form method="POST" action="{{ route('pas.status', [$pas, 'Released']) }}">
            @csrf
            <input type="hidden" name="date_released" value="{{ now()->toDateString() }}">
            <button type="submit" class="btn btn-primary"
                onclick="return confirm('Mark this PAS as Released? This will NOT affect inventory.')">
                Mark as Released
            </button>
        </form>
        @endif
        <form method="POST" action="{{ route('pas.status', [$pas, 'Canceled']) }}">
            @csrf
            <button type="submit" class="btn btn-danger"
                onclick="return confirm('Cancel this PAS?')">
                Cancel PAS
            </button>
        </form>
    </div>
    @endif

    {{-- Request Approval Actions --}}
    @if($pas->request_status === 'pending_approval' && auth()->user()?->isAdmin())
    <div style="margin-top:1rem;display:flex;gap:0.75rem;flex-wrap:wrap;padding:1rem;background:var(--surface-muted);border-radius:0.85rem;border:1px solid var(--border);">
        <form method="POST" action="{{ route('pas.approve', $pas) }}">
            @csrf
            <button type="submit" class="btn btn-primary"
                onclick="return confirm('Approve this PAS request?')">
                Approve Request
            </button>
        </form>
        <button type="button" class="btn btn-danger" onclick="document.getElementById('rejectForm').style.display='flex'">
            Reject Request
        </button>
        <form method="POST" action="{{ route('pas.reject', $pas) }}" id="rejectForm" style="display:none;align-items:center;gap:0.5rem;flex-wrap:wrap;">
            @csrf
            <input type="text" name="rejection_reason" placeholder="Reason for rejection (optional)" style="padding:0.6rem 0.85rem;border:1px solid var(--border);border-radius:0.75rem;background:var(--surface);color:var(--text);min-width:250px;">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this PAS request?')">Confirm Reject</button>
        </form>
    </div>
    @endif
</section>

<style>
    .pas-card { padding: 1.5rem; }

    .pas-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .pas-detail-item {
        padding: 0.85rem 1rem;
        background: var(--surface-muted);
        border-radius: 0.7rem;
        border: 1px solid var(--border);
    }
    .pas-detail-item--full { grid-column: 1 / -1; }
    .pas-detail-label {
        font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.08em; color: var(--text-muted); margin: 0 0 0.25rem;
    }
    .pas-detail-value {
        font-size: 0.9rem; font-weight: 600; color: var(--text); margin: 0;
        word-break: break-word;
    }

    .pas-table-wrap { border-radius: 0.75rem; overflow-x: auto; border: 1px solid var(--border); }
    .pas-table thead th {
        background: var(--surface-muted); color: var(--text-muted);
        font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.1em; padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--border); white-space: nowrap;
    }
    .pas-table tbody td { padding: 0.85rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .pas-table tbody tr { transition: background-color 150ms ease; }
    .pas-table tbody tr:last-child td { border-bottom: none; }
    .pas-table tbody tr:hover { background: rgba(37,99,235,0.04); }
    .pas-row-num {
        text-align:center; font-weight:700; color:var(--text-muted);
        font-size:0.85rem; font-variant-numeric: tabular-nums;
    }
    .pas-muted { color: var(--text-muted); font-size: 0.85rem; }

    .pas-badge {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.3rem 0.7rem; border-radius: 999px;
        font-size: 0.78rem; font-weight: 600; white-space: nowrap;
    }
    .pas-badge-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .pas-badge--green { background: rgba(22,163,74,0.12); color: #15803d; }
    .pas-badge--green .pas-badge-dot { background: #22c55e; }
    .pas-badge--red { background: rgba(220,38,38,0.12); color: #991b1b; }
    .pas-badge--red .pas-badge-dot { background: #ef4444; }
    .pas-badge--amber { background: rgba(217,119,6,0.12); color: #92400e; }
    .pas-badge--amber .pas-badge-dot { background: #f59e0b; }

    .pas-total-row td {
        background: var(--surface-muted) !important;
        border-top: 2px solid var(--border) !important;
    }

    .btn-outline {
        background: transparent; border: 1px solid var(--border); color: var(--text); border-radius: 0.75rem;
    }
    .btn-outline:hover {
        background: var(--surface-muted); border-color: var(--text-muted);
        transform: none; box-shadow: none; color: var(--text);
    }

    .pas-empty-row td { padding: 0 !important; }
    .pas-empty {
        display: flex; flex-direction: column; align-items: center; gap: 0.75rem;
        padding: 3rem 1.5rem; color: var(--text-muted); text-align: center;
    }
    .pas-empty svg { opacity: 0.35; margin-bottom: 0.25rem; }
    .pas-empty p { font-size: 0.95rem; font-weight: 500; margin: 0; }
</style>
@endsection
