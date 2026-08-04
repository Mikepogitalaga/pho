<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PTR – {{ $release->ptr_itr_ris_no ?? $release->release_number }}</title>
    <style>
        @page {
            size: legal landscape;
            margin: 10mm 8mm;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 7.5pt;
            color: #000;
            background: #fff;
        }

        .page {
            width: 100%;
            page-break-after: always;
        }
        .page:last-child { page-break-after: avoid; }

        /* ── Header block ── */
        .ptr-header {
            margin-bottom: 6px;
        }
        .ptr-logo {
            display: block;
            margin: 0 auto 6px;
            max-width: 140px;
            max-height: 80px;
            object-fit: contain;
        }
        .ptr-meta {
            font-size: 7.5pt;
            margin-bottom: 2px;
        }
        .ptr-meta div { margin-bottom: 1px; }
        .ptr-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 4px 0 2px;
        }
        .ptr-subtitle {
            text-align: center;
            font-size: 7.5pt;
            margin-bottom: 6px;
        }
        .ptr-info-grid {
            margin-bottom: 6px;
            font-size: 7.5pt;
        }
        .ptr-info-grid div { margin-bottom: 1px; }
        .ptr-info-grid span { font-weight: bold; }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
        }
        th, td {
            border: 0.5pt solid #000;
            padding: 2px 3px;
            vertical-align: middle;
        }
        thead th {
            background: #d9d9d9;
            font-weight: bold;
            text-align: center;
            font-size: 6.5pt;
            line-height: 1.2;
        }
        tbody td { text-align: center; }
        tbody td.left { text-align: left; }
        tbody tr:nth-child(even) { background: #f9f9f9; }

        /* column widths */
        .col-no    { width: 2%; }
        .col-po    { width: 9%; }
        .col-prog  { width: 9%; }
        .col-desc  { width: 16%; text-align: left; }
        .col-batch { width: 6%; }
        .col-acq   { width: 6%; }
        .col-exp   { width: 6%; }
        .col-rack  { width: 5%; }
        .col-qty   { width: 4%; }
        .col-uom   { width: 4%; }
        .col-price { width: 6%; text-align: right; }
        .col-amt   { width: 7%; text-align: right; }
        .col-rcvd  { width: 5%; }
        .col-rmk   { width: 15%; text-align: left; }

        /* ── Footer ── */
        .ptr-total {
            margin-top: 4px;
            font-size: 8pt;
            font-weight: bold;
            text-align: right;
        }
        .ptr-reason {
            margin-top: 6px;
            font-size: 7.5pt;
        }
        .ptr-acceptance {
            margin-top: 8px;
            font-size: 7.5pt;
            font-style: italic;
            text-align: center;
            border-top: 0.5pt solid #000;
            padding-top: 4px;
        }
        .ptr-signatories {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            margin-top: 10px;
            font-size: 7.5pt;
        }
        .signatory-block {
            border-top: 0.5pt solid #000;
            padding-top: 3px;
        }
        .signatory-block .sig-label { color: #555; font-size: 6.5pt; }
        .signatory-block .sig-name  { font-weight: bold; margin-top: 12px; }

        .page-num {
            text-align: center;
            font-size: 7pt;
            margin-top: 6px;
            color: #444;
        }

        /* ── Print controls (screen only) ── */
        @media screen {
            body { background: #e5e5e5; padding: 20px; }
            .page {
                background: #fff;
                box-shadow: 0 2px 8px rgba(0,0,0,.2);
                padding: 12mm 10mm;
                margin-bottom: 20px;
                max-width: 1100px;
                margin-left: auto;
                margin-right: auto;
            }
            .print-controls {
                max-width: 1100px;
                margin: 0 auto 16px;
                display: flex;
                gap: 10px;
            }
            .print-controls button, .print-controls a {
                padding: 8px 20px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-size: 13px;
                text-decoration: none;
                display: inline-block;
            }
            .btn-print { background: #1a56db; color: #fff; }
            .btn-back  { background: #6b7280; color: #fff; }
        }
        @media print {
            .print-controls { display: none; }
        }
    </style>
</head>
<body>

<div class="print-controls">
    <button class="btn-print" onclick="window.print()">🖨 Print</button>
    <a class="btn-back" href="{{ route('releases.view', $release) }}">← Back</a>
</div>

@php
    $items      = $release->items;
    $perPage    = 20;
    $chunks     = $items->chunk($perPage);
    $totalPages = $chunks->count() ?: 1;
    $grandTotal = $items->sum(fn($i) => ($i->unit_cost ?? 0) * $i->quantity_released);

    // Resolve date_acquired per release item from receiving_items
    $dateAcquiredMap = [];
    $expiryMap       = [];
    foreach ($items as $ri) {
        if ($ri->item) {
            $recv = $ri->item->receivingItems
                ->sortByDesc('created_at')
                ->first();
            $dateAcquiredMap[$ri->id] = $recv?->receiving?->date_received?->format('m/d/Y') ?? '—';
            $expiryMap[$ri->id]       = $recv?->expiry_date?->format('m/d/Y') ?? '—';
        } else {
            $dateAcquiredMap[$ri->id] = '—';
            $expiryMap[$ri->id]       = '—';
        }
    }
@endphp

@foreach($chunks as $pageIndex => $chunk)
@php $pageNum = $pageIndex + 1; @endphp
<div class="page">

    {{-- ── Header ── --}}
    <div class="ptr-header">
        <img class="ptr-logo" src="{{ asset('logo.jpg') }}" alt="Logo">
        <div class="ptr-meta">
            <div><strong>Commodity Issue No:</strong> {{ $release->pho_code ?? '—' }}</div>
            <div><strong>PTR No:</strong> {{ $release->ptr_itr_ris_no ?? $release->release_number }}</div>
        </div>

        <div class="ptr-title">PROPERTY TRANSFER REPORT</div>
        <div class="ptr-subtitle"><strong>Date Prepared:</strong> {{ $release->date_released?->format('F d, Y') ?? now()->format('F d, Y') }}</div>

        <div class="ptr-info-grid">
            <div><strong>From:</strong> <span>Provincial Health Office</span></div>
            <div><strong>To:</strong> <span>{{ $release->facility_name ?? '—' }}</span></div>
            <div><strong>Transfer Type:</strong> <span>{{ $release->ptr_itr_ris_no ? explode('-', $release->ptr_itr_ris_no)[1] ?? 'PTR' : 'PTR' }}</span></div>
        </div>
    </div>

    {{-- ── Items Table ── --}}
    <table border="1" cellpadding="3" cellspacing="0" width="100%">
        <tr>
            <th colspan="9">Issuing Facility's Action</th>
            <th colspan="2">Receiving Facility's Action</th>
        </tr>
        <tr>
            <th width="12%">PO No.</th>
            <th width="14%">Program</th>
            <th width="22%">Description</th>
            <th width="6%">Batch</th>
            <th width="6%">Expiry</th>
            <th width="6%">Qty</th>
            <th width="6%">UOM</th>
            <th width="6%">Cost</th>
            <th width="6%">Amount</th>
            <th width="5%">Qty Received</th>
            <th width="17%">Remarks and /or Reason for rejection (if applicable)</th>
        </tr>
        @forelse($chunk as $releaseItem)
        @php
            $lineTotal = ($releaseItem->unit_cost ?? 0) * $releaseItem->quantity_released;
        @endphp
        <tr>
            <td>{{ $release->source_docs_ptr_po_no ?? '—' }}</td>
            <td>{{ $release->health_program_coordinator ?? '—' }}</td>
            <td>{{ $releaseItem->item_description ?? '—' }}</td>
            <td>{{ $releaseItem->lot_number ?? '—' }}</td>
            <td>{{ $expiryMap[$releaseItem->id] ?? '—' }}</td>
            <td align="center">{{ number_format($releaseItem->quantity_released) }}</td>
            <td>{{ $releaseItem->uom ?? '—' }}</td>
            <td align="right">{{ $releaseItem->unit_cost !== null ? number_format($releaseItem->unit_cost, 2) : '—' }}</td>
            <td align="right">{{ $releaseItem->unit_cost !== null ? number_format($lineTotal, 2) : '—' }}</td>
            <td></td>
            <td></td>
        </tr>
        @empty
        <tr>
            <td colspan="11" style="text-align:center;padding:8px;">No items recorded.</td>
        </tr>
        @endforelse
    </table>

    {{-- ── Total (last page only) ── --}}
    @if($pageNum === $totalPages)
        <div class="ptr-total">TOTAL: ₱ {{ number_format($grandTotal, 2) }}</div>

        @if($release->notes)
        <div class="ptr-reason" style="margin-top:6px;">
            <strong>Reason for Transfer:</strong> {{ $release->notes }}
        </div>
        @endif

        <div class="ptr-acceptance">
            GOODS ARE CHECKED, DELIVERED AND ACCEPTED IN GOOD CONDITION EXCEPT ITEMS SPECIFIED IN THE ABOVE REMARKS (if any)
        </div>

        <div class="ptr-signatories">
            <div class="signatory-block">
                <div class="sig-label">Prepared by / Released by</div>
                <div class="sig-name">&nbsp;</div>
                <div class="sig-label">Signature over Printed Name / Date</div>
            </div>
            <div class="signatory-block">
                <div class="sig-label">Received by</div>
                <div class="sig-name">{{ $release->received_by ?? '&nbsp;' }}</div>
                <div class="sig-label">Signature over Printed Name / Date</div>
            </div>
            <div class="signatory-block">
                <div class="sig-label">Noted by</div>
                <div class="sig-name">&nbsp;</div>
                <div class="sig-label">Signature over Printed Name / Date</div>
            </div>
        </div>
    @endif

    <div class="page-num">Page {{ $pageNum }} of {{ $totalPages }}</div>
</div>
@endforeach

<script>
    window.addEventListener('load', function () { window.print(); });
</script>
</body>
</html>
