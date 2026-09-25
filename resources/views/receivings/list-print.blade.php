<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receiving Records</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #000; }
        h1 { text-align: center; font-size: 16px; margin-bottom: 4px; }
        .subtitle { text-align: center; font-size: 11px; color: #333; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 0.5pt solid #000; padding: 3px 5px; vertical-align: top; font-size: 9px; }
        th { background: #d9d9d9; font-weight: bold; text-align: center; }
        td { text-align: center; }
        td.left { text-align: left; }
        thead th { font-size: 10px; }
        tbody tr:nth-child(even) { background: #f9f9f9; }
        .col-ics    { width: 7%; }
        .col-po     { width: 7%; }
        .col-supp   { width: 10%; }
        .col-dater  { width: 7%; }
        .col-rcvr   { width: 7%; }
        .col-code   { width: 7%; }
        .col-desc   { width: 12%; }
        .col-lot    { width: 7%; }
        .col-exp    { width: 7%; }
        .col-qty    { width: 5%; }
        .col-uom    { width: 5%; }
        .col-cost   { width: 7%; text-align: right; }
        .col-amt    { width: 7%; text-align: right; }
        @media print { @page { margin: 8mm 6mm; } }
    </style>
</head>
<body>
    <h1>RECEIVING RECORDS</h1>
    <div class="subtitle">Provincial Health Office – Supply Inventory &nbsp;|&nbsp; Printed: {{ now()->format('F d, Y') }}</div>
    <table>
        <thead>
            <tr>
                <th class="col-ics">ICS/PTR/RIS</th>
                <th class="col-po">PO No.</th>
                <th class="col-supp">Supplier</th>
                <th class="col-dater">Date Received</th>
                <th class="col-rcvr">Received By</th>
                <th class="col-code">Product Code</th>
                <th class="col-desc">Item Description</th>
                <th class="col-lot">Lot Number</th>
                <th class="col-exp">Expiry Date</th>
                <th class="col-qty">Qty</th>
                <th class="col-uom">UOM</th>
                <th class="col-cost">Unit Cost</th>
                <th class="col-amt">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $rowCount = 0; @endphp
            @foreach($receivings as $receiving)
                @foreach($receiving->items as $item)
                    @php $rowCount++; @endphp
                    <tr>
                        <td>{{ $receiving->ics_ptr_ris ?? '—' }}</td>
                        <td class="left">{{ $receiving->po_number ?? '—' }}</td>
                        <td class="left">{{ $receiving->supplier?->company_name ?? '—' }}</td>
                        <td>{{ $receiving->date_received?->format('m/d/Y') ?? '—' }}</td>
                        <td>{{ $receiving->received_by ?? '—' }}</td>
                        <td>{{ $item->item_code ?? '—' }}</td>
                        <td class="left">{{ $item->item_description ?? '—' }}</td>
                        <td>{{ $item->lot_number ?? '—' }}</td>
                        <td>{{ $item->expiry_date?->format('m/d/Y') ?? '—' }}</td>
                        <td>{{ $item->quantity_received ?? '—' }}</td>
                        <td>{{ $item->uom ?? '—' }}</td>
                        <td align="right">{{ isset($item->unit_cost) ? number_format((float) $item->unit_cost, 2) : '—' }}</td>
                        <td align="right">{{ isset($item->unit_cost) && isset($item->quantity_received) ? number_format((float) $item->unit_cost * (int) $item->quantity_received, 2) : '—' }}</td>
                    </tr>
                @endforeach
            @endforeach
            @if($rowCount === 0)
                <tr><td colspan="13" style="text-align:center;padding:8px;">No receiving records found.</td></tr>
            @endif
        </tbody>
    </table>
    <script>window.onload = () => window.print();</script>
</body>
</html>
