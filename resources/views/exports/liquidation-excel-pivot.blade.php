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
    {{-- ROW 1: Facility + Summary --}}
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

    {{-- ROW 2: EXPIRED/REJECTED (one time) + PTR Numbers + RETURNED TO STOCKROOM per PTR --}}
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
    </tr>

    {{-- ROW 3: Receivers --}}
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

    {{-- ROW 4: Column Headers --}}
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
    </tr>

    {{-- ROW 5+: Data rows --}}
    @foreach($allItems as $item)
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
        </tr>
    @endforeach
</table>
</body>
</html>
