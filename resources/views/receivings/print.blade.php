<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receiving Slip – {{ $receiving->po_number ?? 'Receiving' }}</title>
    <style>
        @page {
            size: A4 Landscape;
            margin: 10mm 8mm;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
        }

        .page {
            width: 100%;
            padding: 6mm 4mm;
            position: relative;
            min-height: calc(100vh - 20mm);
        }

        .page-footer {
            position: absolute;
            left: 8mm;
            right: 8mm;
            bottom: 6mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9pt;
            color: #444;
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

        .doc-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 2px 0 4px;
        }

        .doc-subtitle {
            text-align: center;
            font-size: 9pt;
            color: #333;
            margin-bottom: 6px;
        }

        .info-gridleft,
        .info-gridright {
            width: 48%;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .info-gridleft {
            float: left;
        }

        .info-gridright {
            float: right;
        }

        .info-row {
            display: flex;
            align-items: flex-end;
            margin-bottom: 12px;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .info-label {
            width: 150px;
            font-weight: 600;
            white-space: nowrap;
            border: none !important;
        }

        .info-value {
            flex: 1;
            min-height: 22px;
            padding: 0 5px 3px;
            border: none !important;
            border-bottom: 1px solid #333 !important;
        }
        .info-gridleft::after,
        .info-gridright::after {
            content: "";
            display: table;
            clear: both;
        }

        .item-border {
            clear: both;
            border: 1px solid #000;
            margin-top: 10px;
            margin-bottom: 8px;
            padding: 0;
        }
         

       /* ONLY the items section gets an outer border */
        .item-border {
            border: 1px solid #000;
            margin-top: 10px;
            margin-bottom: 8px;
            padding: 0;
        }

        .item-border table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .item-border th,
        .item-border td {
            border: 0.5px solid #000;
            padding: 3px 5px;
            vertical-align: middle;
        }
        .item-border thead th {
            background: #d9d9d9;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
        }
        .item-border tbody td {
            text-align: center;
        }
        .item-border tbody td.left {
            text-align: left;
        }
        .item-border tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        .item-border tfoot td {
            font-weight: bold;
            text-align: right;
            padding: 3px 5px;
        }

        .col-no    { width: 3%; }
        .col-code  { width: 9%; }
        .col-desc  { width: 22%; text-align: left; }
        .col-batch { width: 8%; }
        .col-exp   { width: 8%; }
        .col-qty   { width: 6%; }
        .col-uom   { width: 5%; }
        .col-price { width: 8%; text-align: right; }
        .col-amt   { width: 10%; text-align: right; }
        .col-loc   { width: 10%; text-align: left; }
        .col-cat   { width: 8%; }

        .sig-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
            font-size: 8.5pt;
        }
        .sig-block {
            border-top: 0.5pt solid #000;
            padding-top: 3px;
            min-height: 60px;
        }
        .sig-block .sig-label { color: #555; font-size: 7.5pt; }
        .sig-block .sig-line { margin-top: 28px; border-bottom: 0.5pt solid #000; padding-bottom: 2px; }

        .form-id {
            font-size: 9pt;
            font-weight: bold;
        }

        /* ── Print controls (screen only) ── */
        @media screen {
            body { background: #e5e5e5; padding: 20px; }
            .page {
                background: #fff;
                box-shadow: 0 2px 8px rgba(0,0,0,.2);
                padding: 10mm 10mm;
                margin-bottom: 20px;
                max-width: 210mm;
                margin-left: auto;
                margin-right: auto;
            }
            .print-controls {
                max-width: 210mm;
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
    <button class="btn-print" onclick="window.print()">Print</button>
    <a class="btn-back" href="{{ route('receivings.view', $receiving) }}">Back</a>
</div>

<div class="page">
    <img class="ptr-logo" src="{{ asset('logo.jpg') }}" alt="Logo">
    <div class="doc-title">RECEIVING SLIP</div>
    <div class="doc-subtitle">Provincial Health Office – Supply Inventory</div>

    <div class="info-gridleft">
        <div class="info-row">
            <div class="info-label">Receiving No.:</div>
            <div class="info-value">{{ $receiving->po_number ?? '—' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Supplier / Dealer:</div>
            <div class="info-value">{{ $receiving->supplier?->company_name ?? '—' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">ICS / PTR / RIS:</div>
            <div class="info-value">{{ $receiving->ics_ptr_ris ?? '—' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Document Date:</div>
            <div class="info-value">{{ $receiving->document_date?->format('F d, Y') ?? '—' }}</div>
        </div>
    </div>
    <div class="info-gridright">
        <div class="info-row">
            <div class="info-label">Date Received:</div>
            <div class="info-value">{{ $receiving->date_received?->format('F d, Y') ?? '—' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Received By:</div>
            <div class="info-value">{{ $receiving->received_by ?? '—' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Stock Keeping Unit:</div>
            <div class="info-value">{{ $receiving->stock_keeping_unit ?? '—' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Program Coordinator:</div>
            <div class="info-value">{{ $receiving->program_coordinator ?? '—' }}</div>
        </div>
    </div>

    <div class="item-border">
        <table>
            <thead>
                <tr>
                    <th class="col-no">#</th>
                    <th class="col-code">Product Code</th>
                    <th class="col-desc">Item Description</th>
                    <th class="col-cat">Category</th>
                    <th class="col-batch">Lot / Batch No.</th>
                    <th class="col-exp">Expiry Date</th>
                    <th class="col-qty">Quantity</th>
                    <th class="col-uom">UOM</th>
                    <th class="col-price">Unit Cost</th>
                    <th class="col-amt">Amount</th>
                    <th class="col-loc">Location</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receiving->items as $index => $receivingItem)
                    @php
                        $lineTotal = ($receivingItem->unit_cost ?? 0) * $receivingItem->quantity_received;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="left" style="font-family:monospace;">{{ $receivingItem->item_code ?? '—' }}</td>
                        <td class="left">{{ $receivingItem->item_description ?? $receivingItem->item?->name ?? '—' }}</td>
                        <td>{{ $receivingItem->category ?? $receivingItem->item?->category ?? '—' }}</td>
                        <td>{{ $receivingItem->lot_number ?? '—' }}</td>
                        <td>{{ $receivingItem->expiry_date?->format('m/d/Y') ?? '—' }}</td>
                        <td>{{ number_format($receivingItem->quantity_received) }}</td>
                        <td>{{ $receivingItem->uom ?? $receivingItem->item?->unit ?? '—' }}</td>
                        <td align="right">{{ $receivingItem->unit_cost !== null ? number_format($receivingItem->unit_cost, 2) : '—' }}</td>
                        <td align="right">{{ $receivingItem->unit_cost !== null ? number_format($lineTotal, 2) : '—' }}</td>
                        <td class="left">{{ $receivingItem->location ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align:center;padding:8px;">No items recorded.</td>
                    </tr>
                @endforelse
                @if($receiving->items->isNotEmpty())
                    @php
                        $grandTotal = $receiving->items->sum(fn($i) => ($i->unit_cost ?? 0) * $i->quantity_received);
                    @endphp
                    <tfoot>
                        <tr>
                            <td colspan="9" style="text-align:right;font-weight:bold;">GRAND TOTAL</td>
                            <td style="text-align:right;font-weight:bold;">₱ {{ number_format($grandTotal, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </tbody>
        </table>
    </div>

    @if(!empty($receiving->notes))
        <div style="margin-top:4px;font-size:9pt;">
            <strong>Notes:</strong> {{ $receiving->notes }}
        </div>
    @endif

    

    <div class="page-footer">
        <div class="form-id">PHO-Ap-SCM-Form 2</div>
        
        <div>{{ now()->format('F d, Y') }}</div>
    </div>
</div>

<script>
    window.addEventListener('load', function () { window.print(); });
</script>
</body>
</html>