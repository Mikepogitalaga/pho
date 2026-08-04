<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PTR – {{ $release->ptr_itr_ris_no ?? $release->release_number }}</title>
    <style>
        @page {
            size: legal landscape;
            margin: 6mm 5mm;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 6.8pt;
            color: #000;
            background: #fff;
        }

        .page {
            width: 100%;
            page-break-after: always;
            padding: 6mm 5mm;
            position: relative;
            min-height: calc(100vh - 12mm);
        }
        .page:last-child { page-break-after: avoid; }

        .page-footer {
            position: absolute;
            left: 5mm;
            right: 5mm;
            bottom: 4mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 6.5pt;
            color: #444;
        }
        .page-footer .form-id { text-align: left; }
        .page-footer .page-num { text-align: right; }

        /* ── Header block ── */
        .ptr-header {
            position: relative;
            margin-bottom: 4px;
        }
        .ptr-annex {
            position: absolute;
            top: 0;
            right: 0;
            border: 0.5pt solid #000;
            padding: 2px 6px;
            font-size: 6.5pt;
            font-weight: bold;
            background: #fff;
            white-space: nowrap;
        }
        .ptr-logo {
            display: block;
            margin: 0 auto 4px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
        }
        .ptr-meta {
            font-size: 6.5pt;
            margin-bottom: 1px;
        }
        .ptr-meta div { margin-bottom: 0.5px; }
        .ptr-title {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 2px 0 1px;
        }
        .ptr-subtitle {
            display: block;
            width: 100%;
            text-align: right;
            font-size: 6.5pt;
            margin-bottom: 4px;
            padding-right: 6px;
        }
        .ptr-info-grid {
            margin-bottom: 4px;
            font-size: 6.5pt;
        }
        .ptr-info-grid div { margin-bottom: 1px; }
        .ptr-info-grid span { font-weight: bold; }

        .item-border {
            border: 0.5pt solid #000;
            padding: 0;
            margin-bottom: 8px;
            box-sizing: border-box;
        }
        .item-border table {
            border-collapse: collapse;
            border: none;
            width: 100%;
            margin: 0;
            font-size: 6.5pt;
        }
        .item-border th,
        .item-border td {
            border: 0.5pt solid #000;
            padding: 4px 6px;
            vertical-align: middle;
            min-height: 20px;
        }
        .item-border tr:first-child th {
            padding: 8px 6px;
            min-height: 28px;
            font-size: 7pt;
        }
        .item-border tbody td {
            min-height: 24px;
            padding-top: 6px;
            padding-bottom: 6px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 6.5pt;
        }
        .table th,
        .table td {
            border: 0.5pt solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .table tfoot td {
            font-weight: bold;
            height: 22px;
        }
        .grand-total-row td {
            text-align: center;
            padding: 3px 4px;
        }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .mb-3 { margin-bottom: 8px; }
        .form-label { display: block; margin-bottom: 3px; font-weight: bold; }
        .form-control {
            width: 100%;
            min-height: 26px;
            border: 0.5pt solid #000;
            padding: 2px 4px;
            font-size: 6.5pt;
            font-family: Arial, sans-serif;
            resize: none;
        }
        .row { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; align-items: stretch; }
        .col-md-3 { flex: 1 1 23%; min-width: 120px; display: flex; flex-direction: column; }
        .text-center { text-align: center; }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.5pt;
            border: 0.5pt solid #000;
            flex: 1;
        }
        .sig-table td {
            border: none;
            padding: 3px 4px;
            vertical-align: middle;
            line-height: 1.3;
        }
        .sig-table .sig-title {
            width: 30%;
            font-weight: bold;
            text-align: left;
            padding-right: 4px;
            white-space: nowrap;
        }
        .sig-table .sig-line {
            width: 70%;
            min-height: 28px;
            border-bottom: 0.5pt solid #000;
            padding-bottom: 4px;
            box-sizing: border-box;
            display: block;
        }
        .sig-table .sig-full {
            font-weight: bold;
            padding-bottom: 4px;
            text-align: left;
            padding-left: 0;
        }
        .sig-table .sig-space td {
            border: none;
            padding: 6px 0;
            height: 10px;
        }
        .mt-4 { margin-top: 16px; }
        .text-muted { color: #555; font-size: 6.5pt; }

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
        <div class="ptr-annex">Annex 21</div>
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
    <div class="item-border">
    <table class="table table-bordered" cellpadding="3" cellspacing="0" width="100%">
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
        @if($pageNum === $totalPages)
          <tfoot>
            <tr class="grand-total-row">
              <td colspan="8" class="text-end fw-bold">GRAND TOTAL</td>
              <td class="col-amt text-end fw-bold">₱ {{ number_format($grandTotal, 2) }}</td>
              <td colspan="2" class="text-center fw-bold">Receiving Facility's Action</td>
            </tr>
          </tfoot>
        @endif
    </table>

    {{-- ── Total (last page only) ── --}}
    @if($pageNum === $totalPages)
        <!-- Grand Total Section -->
        <div class="mb-3">
          <label class="form-label fw-bold">Reason for Transfer:</label>
          <textarea class="form-control" rows="2"></textarea>
        </div>

        <!-- Approval & Signatories -->
        <div class="row text-center">
          <div class="col-md-3">
            <table class="sig-table">
              <tr>
                <td></td>
                <td class="sig-full">Approved by:</td>
              </tr>
              <tr>
                <td class="sig-title">Signature:</td>
                <td class="sig-line"></td>
              </tr>
              <tr>
                <td class="sig-title">Name:</td>
                <td class="sig-line"></td>
              </tr>
              <tr>
                <td class="sig-title">Designation:</td>
                <td class="sig-line"></td>
              </tr>
              <tr>
                <td class="sig-title">Date:</td>
                <td class="sig-line"></td>
              </tr>
              <tr class="sig-space">
                <td colspan="2"></td>
              </tr>
            </table>
          </div>
          <div class="col-md-3">
            <table class="sig-table">
              <tr>
                <td></td>
                <td class="sig-full">Released by:</td>
              </tr>
              <tr>
                <td></td>
                <td class="sig-line"></td>
              </tr>
              <tr>
                <td></td>
                <td class="sig-line"></td>
              </tr>
              <tr>
                <td></td>
                <td class="sig-line"></td>
              </tr>
              <tr>
                <td></td>
                <td class="sig-line"></td>
              </tr>
              <tr class="sig-space">
                <td colspan="2"></td>
              </tr>
            </table>
          </div>
          <div class="col-md-3">
            <table class="sig-table">
              <tr>
                <td></td>
                <td class="sig-full">Delivered by:</td>
              </tr>
              <tr>
                <td></td>
                <td class="sig-line"></td>
              </tr>
              <tr>
                <td></td>
                <td class="sig-line"></td>
              </tr>
              <tr>
                <td></td>
                <td class="sig-line"></td>
              </tr>
              <tr>
                <td></td>
                <td class="sig-line"></td>
              </tr>
              <tr class="sig-space">
                <td colspan="2"></td>
              </tr>
            </table>
          </div>
          <div class="col-md-3">
            <table class="sig-table">
              <tr>
                <td></td>
                <td class="sig-full">Received by:</td>
              </tr>
              <tr>
                <td></td>
                <td class="sig-line"></td>
              </tr>
              <tr>
                <td></td>
                <td class="sig-line"></td>
              </tr>
              <tr>
                <td></td>
                <td class="sig-line"></td>
              </tr>
              <tr>
                <td></td>
                <td class="sig-line"></td>
              </tr>
              <tr class="sig-space">
                <td colspan="2"></td>
              </tr>
            </table>
          </div>
        </div>

        </div>
        @endif

    <div class="page-footer">
        <div class="form-id">Form ID: PHO-Ap-SCM-FORM 1</div>
        <div class="page-num">Page {{ $pageNum }} of {{ $totalPages }}</div>
    </div>
</div>
@endforeach

<script>
    window.addEventListener('load', function () { window.print(); });
</script>
</body>
</html>
