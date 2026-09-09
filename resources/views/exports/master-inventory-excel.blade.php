@php
    $sum = fn (string $key) => array_sum(array_column($rows, $key));
@endphp
<table>
    <tr>
        <th></th><th></th><th></th><th>{{ $sum('beginning_qty') }}</th><th>{{ $sum('beginning_cost') }}</th>
        <th>{{ $sum('purchase_gso_qty') }}</th><th></th><th>{{ $sum('purchase_acp_qty') }}</th><th></th><th>{{ $sum('purchase_doh_qty') }}</th><th></th>
        <th>{{ $summary['available'] }}</th><th>{{ $sum('available_cost') }}</th>
        <th>{{ $sum('disposal_implementing') }}</th><th>{{ $sum('disposal_hospitals') }}</th><th>{{ $sum('disposal_rhu') }}</th><th>{{ $sum('disposal_nla') }}</th><th>{{ $sum('disposal_cost') }}</th>
        <th>{{ $sum('expired_cost') }}</th><th>{{ $sum('ending_qty') }}</th><th>{{ $sum('ending_cost') }}</th><th>{{ $summary['average_cost'] }}</th><th>{{ $summary['check_balance'] }}</th>
        <th>{{ $sum('additional_count') }}</th><th>{{ $summary['adjusted_ending'] }}</th><th>{{ $sum('adjusted_amount') }}</th>
    </tr>
    <tr>
        <th>IMPLEMENTING</th><th>HOSPITALS</th><th>RHU</th><th>NLA</th><th></th><th></th><th></th><th></th><th></th><th></th>
        <th colspan="5">DISPOSAL<br>(NET OF TOTAL RELEASED-RTS)</th><th>EXPIRED</th><th colspan="2">ENDING INVENTORY</th><th>AVERAGE COST</th><th>CHECK BAL</th><th>ADDITIONAL COUNT</th><th colspan="2">ADJUSTED ENDING INVENTORY</th>
    </tr>
    <tr>
        <th></th><th></th><th></th><th colspan="2"></th><th>GSO</th><th></th><th>ACP</th><th></th><th>DOH</th><th></th><th colspan="2"></th>
        <th>IMPLEMENTING</th><th>HOSPITALS</th><th>RHU</th><th>NLA</th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
    </tr>
    <tr>
        <th>ITEM</th><th>UNIT</th><th>UNIT COST</th><th>QTY</th><th>TOTAL COST</th>
        <th>QTY</th><th>UNIT COST</th><th>QTY</th><th>UNIT COST</th><th>QTY</th><th>UNIT COST</th><th>QTY</th><th>TOTAL COST</th>
        <th>IMPLEMENTING</th><th>HOSPITALS</th><th>RHU</th><th>NLA</th><th>TOTAL COST</th><th>TOTAL COST</th><th>QTY</th><th>TOTAL COST</th><th>AVERAGE COST</th><th>CHECK BAL</th><th>QTY</th><th>QTY</th><th>AMOUNT</th>
    </tr>
    @foreach($rows as $row)
        <tr>
            <td>{{ $row['name'] }}</td><td>{{ $row['unit'] }}</td><td>{{ $row['unit_cost'] }}</td><td>{{ $row['beginning_qty'] }}</td><td>{{ $row['beginning_cost'] }}</td>
            <td>{{ $row['purchase_gso_qty'] }}</td><td>{{ $row['purchase_gso_cost'] }}</td><td>{{ $row['purchase_acp_qty'] }}</td><td>{{ $row['purchase_acp_cost'] }}</td><td>{{ $row['purchase_doh_qty'] }}</td><td>{{ $row['purchase_doh_cost'] }}</td><td>{{ $row['available_qty'] }}</td><td>{{ $row['available_cost'] }}</td>
            <td>{{ $row['disposal_implementing'] }}</td><td>{{ $row['disposal_hospitals'] }}</td><td>{{ $row['disposal_rhu'] }}</td><td>{{ $row['disposal_nla'] }}</td><td>{{ $row['disposal_cost'] }}</td><td>{{ $row['expired_cost'] }}</td><td>{{ $row['ending_qty'] }}</td><td>{{ $row['ending_cost'] }}</td><td>{{ $row['average_cost'] }}</td><td>{{ $row['check_balance'] }}</td><td>{{ $row['additional_count'] }}</td><td>{{ $row['adjusted_ending'] }}</td><td>{{ $row['adjusted_amount'] }}</td>
        </tr>
    @endforeach
</table>
