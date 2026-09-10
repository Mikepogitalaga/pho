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
        .col-ics    { width: 8%; }
        .col-po     { width: 8%; }
        .col-supp   { width: 12%; }
        .col-docd   { width: 8%; }
        .col-dater  { width: 8%; }
        .col-rcvr   { width: 8%; }
        .col-prog   { width: 10%; }
        .col-crds   { width: 6%; }
        .col-loc    { width: 8%; }
        .col-cost   { width: 7%; text-align: right; }
        .col-amt    { width: 9%; text-align: right; }
        .col-rmk    { width: 10%; text-align: left; }
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
                <th class="col-docd">Doc. Date</th>
                <th class="col-dater">Date Received</th>
                <th class="col-rcvr">Received By</th>
                <th class="col-prog">Program</th>
                <th class="col-crds">Coordinator</th>
                <th class="col-loc">Location</th>
                <th class="col-cost">Unit Cost</th>
                <th class="col-amt">Amount</th>
                <th class="col-rmk">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receivings as $receiving)
                @php
                    $firstItem = $receiving->items->first();
                    $amount = $receiving->items->sum(fn($i) => ($i->unit_cost ?? 0) * $i->quantity_received);
                @endphp
                <tr>
                    <td>{{ $receiving->ics_ptr_ris ?? '—' }}</td>
                    <td class="left">{{ $receiving->po_number ?? '—' }}</td>
                    <td class="left">{{ $receiving->supplier?->company_name ?? '—' }}</td>
                    <td>{{ $receiving->document_date?->format('m/d/Y') ?? '—' }}</td>
                    <td>{{ $receiving->date_received?->format('m/d/Y') ?? '—' }}</td>
                    <td>{{ $receiving->received_by ?? '—' }}</td>
                    <td class="left">{{ $receiving->stock_keeping_unit ?? '—' }}</td>
                    <td class="left">{{ $receiving->program_coordinator ?? '—' }}</td>
                    <td class="left">{{ $receiving->location ?? '—' }}</td>
                    <td align="right">{{ $firstItem && $firstItem->unit_cost !== null ? number_format($firstItem->unit_cost, 2) : '—' }}</td>
                    <td align="right">{{ $amount > 0 ? number_format($amount, 2) : '—' }}</td>
                    <td class="left">{{ $receiving->notes ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="12" style="text-align:center;padding:8px;">No receiving records found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <script>window.onload = () => window.print();</script>
</body>
</html>