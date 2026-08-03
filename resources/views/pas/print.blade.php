<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAS – {{ $pas->pas_number }}</title>
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

        /* ── Header ── */
        .pas-top {
            margin-bottom: 4px;
        }
        .pas-meta {
            font-size: 7.5pt;
            margin-bottom: 2px;
        }
        .pas-meta div { margin-bottom: 1px; }

        .pas-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 4px 0 1px;
        }
        .pas-subtitle {
            text-align: center;
            font-size: 7.5pt;
            margin-bottom: 5px;
        }

        .pas-info {
            font-size: 7.5pt;
            margin-bottom: 6px;
        }
        .pas-info div { margin-bottom: 1px; }
        .pas-info span { font-weight: bold; }

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

        .col-no    { width: 2%; }
        .col-desc  { width: 22%; }
        .col-code  { width: 8%; }
        .col-lot   { width: 8%; }
        .col-exp   { width: 8%; }
        .col-qty   { width: 5%; }
        .col-unit  { width: 5%; }
        .col-price { width: 8%; }
        .col-total { width: 9%; }

        /* ── Footer ── */
        .pas-grand-total {
            margin-top: 4px;
            font-size: 8pt;
            font-weight: bold;
            text-align: right;
        }
        .pas-notes {
            margin-top: 6px;
            font-size: 7.5pt;
        }
        .pas-acceptance {
            margin-top: 8px;
            font-size: 7.5pt;
            font-style: italic;
            text-align: center;
            border-top: 0.5pt solid #000;
            padding-top: 4px;
        }
        .pas-signatories {
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

        /* ── Screen preview ── */
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
            .print-controls button,
            .print-controls a {
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
    <a class="btn-back" href="{{ route('pas.view', $pas) }}">← Back</a>
</div>

@php
    $items      = $pas->items;
    $perPage    = 20;
    $chunks     = $items->chunk($perPage);
    $totalPages = $chunks->count() ?: 1;
    $grandTotal = $items->sum(fn($i) => (float) $i->total_cost);
@endphp

@foreach($chunks as $pageIndex => $chunk)
@php $pageNum = $pageIndex + 1; @endphp
<div class="page">

    {{-- ── Header ── --}}
    <div class="pas-top">
        <div class="pas-meta">
            <div>PAS Number: <strong>{{ $pas->pas_number }}</strong></div>
            <div>Date of PASS: <strong>{{ $pas->date_of_pass?->format('F d, Y') }}</strong></div>
        </div>

        <div class="pas-title">PROPERTY ALLOCATION SLIP</div>
        <div class="pas-subtitle">Date Prepared: {{ $pas->date_of_pass?->format('F d, Y') ?? now()->format('F d, Y') }}</div>

        <div class="pas-info">
            <div>Supplier: <span>{{ $pas->supplier?->company_name ?? '—' }}</span></div>
            <div>Facility / Coordinator: <span>{{ $pas->facility_coordinator }}</span></div>
            <div>Program: <span>{{ $pas->program ?? '—' }}</span></div>
            <div>Purpose / Activity: <span>{{ $pas->purpose_activity ?? '—' }}</span></div>
            <div>Date Released: <span>{{ $pas->date_released?->format('F d, Y') ?? '—' }}</span></div>
            <div>Status: <span>{{ $pas->status }}</span></div>
        </div>
    </div>

    {{-- ── Items Table ── --}}
    <table>
        <thead>
            <tr>
                <th class="col-no">No.</th>
                <th class="col-desc">Item Description</th>
                <th class="col-code">Product Code</th>
                <th class="col-lot">Lot Number</th>
                <th class="col-exp">Expiration Date</th>
                <th class="col-qty">Quantity</th>
                <th class="col-unit">Unit</th>
                <th class="col-price">Unit Cost</th>
                <th class="col-total">Total Cost</th>
            </tr>
        </thead>
        <tbody>
            @php $rowNum = $pageIndex * $perPage; @endphp
            @forelse($chunk as $item)
            @php $rowNum++; @endphp
            <tr>
                <td>{{ $rowNum }}</td>
                <td class="left">{{ $item->item_description }}</td>
                <td>{{ $item->product_code ?? $item->item?->item_code ?? '—' }}</td>
                <td>{{ $item->lot_number ?? '—' }}</td>
                <td>{{ $item->expiration_date?->format('m/d/Y') ?? '—' }}</td>
                <td>{{ number_format($item->quantity) }}</td>
                <td>{{ $item->unit }}</td>
                <td style="text-align:right;">{{ number_format($item->unit_cost, 2) }}</td>
                <td style="text-align:right;">{{ number_format($item->total_cost, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:8px;">No items recorded.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Grand Total + Footer (last page only) ── --}}
    @if($pageNum === $totalPages)
        <div class="pas-grand-total">TOTAL: ₱ {{ number_format($grandTotal, 2) }}</div>

        @if($pas->notes)
        <div class="pas-notes">
            <strong>Notes:</strong> {{ $pas->notes }}
        </div>
        @endif

        <div class="pas-acceptance">
            GOODS ARE CHECKED, DELIVERED AND ACCEPTED IN GOOD CONDITION EXCEPT ITEMS SPECIFIED IN THE ABOVE REMARKS (if any)
        </div>

        <div class="pas-signatories">
            <div class="signatory-block">
                <div class="sig-label">Prepared by / Issued by</div>
                <div class="sig-name">&nbsp;</div>
                <div class="sig-label">Signature over Printed Name / Date</div>
            </div>
            <div class="signatory-block">
                <div class="sig-label">Received by / Facility Coordinator</div>
                <div class="sig-name">{{ $pas->facility_coordinator }}</div>
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
