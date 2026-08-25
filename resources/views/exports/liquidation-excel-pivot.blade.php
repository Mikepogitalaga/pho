<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Liquidation Report</title>
    <style>
        table { border-collapse: collapse; font-family: Arial, sans-serif; font-size: 11pt; }
        td, th { border: 1px solid #000; padding: 4px; vertical-align: middle; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
<table>
    <tr>
        <td></td>
        <td class="fw-bold text-center" colspan="3">{{ $facility }}</td>
        <td></td>
        <td></td>
        @foreach($releases as $release)
            <td class="text-right">{{ $summaryQtys[$release->id] > 0 ? $summaryQtys[$release->id] : '-' }}</td>
            <td class="text-right">{{ $summaryCosts[$release->id] > 0 ? number_format($summaryCosts[$release->id], 2) : '-' }}</td>
            <td></td>
            <td></td>
        @endforeach
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td class="fw-bold text-center" colspan="2">EXPIRED/REJECTED BY END USER</td>
        @foreach($releases as $release)
            <td class="fw-bold text-center" colspan="2">{{ $ptrs[$release->id] ?? '—' }}</td>
            <td class="fw-bold text-center" colspan="2">RETURNED TO STOCKROOM</td>
        @endforeach
        <td class="fw-bold text-center">TOTAL RIS</td>
        <td class="fw-bold text-center">TOTAL RETURNED</td>
        <td class="fw-bold text-right">TOTAL COST</td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        @foreach($releases as $release)
            <td class="fw-bold text-center" colspan="2">{{ $receivers[$release->id] ?? '—' }}</td>
            <td></td>
            <td></td>
        @endforeach
    </tr>

    <tr>
        <td></td>
        <td class="fw-bold text-center">ITEM</td>
        <td class="fw-bold text-center">UNIT</td>
        <td class="fw-bold text-right">UNIT COST</td>
        <td></td>
        <td></td>
        @foreach($releases as $release)
            <td class="fw-bold text-center">QTY</td>
            <td class="fw-bold text-right">TOTAL COST</td>
            <td></td>
            <td></td>
        @endforeach
        <td></td>
        <td></td>
        <td></td>
    </tr>

    @foreach($allItems as $item)
        @php
            $itemTotalQty = array_sum($item['qtys']);
            $itemTotalCost = array_sum($item['totals']);
        @endphp
        <tr>
            <td></td>
            <td class="text-left">{{ $item['description'] }}</td>
            <td class="text-center">{{ $item['uom'] }}</td>
            <td class="text-right">{{ $item['unit_cost'] !== null && $item['unit_cost'] !== '' ? $item['unit_cost'] : '-' }}</td>
            <td></td>
            <td></td>
            @foreach($releases as $release)
                @php
                    $qty = $item['qtys'][$release->id] ?? 0;
                    $total = $item['totals'][$release->id] ?? 0;
                @endphp
                <td class="text-right">{{ $qty > 0 ? $qty : '-' }}</td>
                <td class="text-right">{{ $total > 0 ? $total : '-' }}</td>
                <td></td>
                <td></td>
            @endforeach
            <td class="text-right">{{ $itemTotalQty > 0 ? $itemTotalQty : '-' }}</td>
            <td class="text-right">-</td>
            <td class="text-right">{{ $itemTotalCost > 0 ? number_format($itemTotalCost, 2) : '-' }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>
