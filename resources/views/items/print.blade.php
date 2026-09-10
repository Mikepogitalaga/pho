<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; padding: 20px; }
        h1 { font-size: 15px; text-align: center; margin-bottom: 4px; }
        p.subtitle { text-align: center; font-size: 10px; color: #555; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f0f0f0; font-weight: 700; text-align: left; padding: 5px 6px; border: 1px solid #ccc; font-size: 10px; text-transform: uppercase; }
        td { padding: 4px 6px; border: 1px solid #ddd; vertical-align: top; }
        tr:nth-child(even) td { background: #fafafa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        @media print {
            body { padding: 0; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="subtitle">Generated: {{ now()->format('F d, Y h:i A') }} &mdash; {{ $items->count() }} item(s)</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>PHO Code</th>
                <th>Item Description</th>
                <th>Category</th>
                <th>UOM</th>
                <th class="text-right">Current Stock</th>
                <th class="text-right">Unit Cost</th>
                <th>Location</th>
                <th>Program</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item->item_code ?? '—' }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->category ?? '—' }}</td>
                    <td>{{ $item->display_unit }}</td>
                    <td class="text-right">{{ number_format($item->quantity_on_hand) }}</td>
                    <td class="text-right">{{ $item->unit_cost ? number_format($item->unit_cost, 2) : '—' }}</td>
                    <td>{{ $item->location ?? '—' }}</td>
                    <td>{{ $item->stock_keeping_unit ?? '—' }}</td>
                    <td>{{ $item->status }}</td>
                </tr>
            @empty
                <tr><td colspan="10" style="text-align:center; padding:12px;">No items found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <script>window.onload = () => window.print();</script>
</body>
</html>
