<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Release Records</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; }
        h1 { text-align: center; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 5px; vertical-align: top; }
        th { background: #eee; }
        @media print { @page { margin: 1cm; } }
    </style>
</head>
<body>
    <h1>Release Records</h1>
    <table>
        <thead><tr><th>PTR Number</th><th>PAS No.</th><th>Product Code</th><th>Facility / End-user</th><th>Program</th><th>Item Description</th><th>Date Released</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($releases as $release)
            @php $releaseItems = $release->items->isNotEmpty() ? $release->items : collect([null]); @endphp
            @foreach($releaseItems as $index => $item)
                <tr>
                    @if($index === 0)
                        <td rowspan="{{ $releaseItems->count() }}">{{ $release->ptr_itr_ris_no ?? $release->release_number }}</td>
                        <td rowspan="{{ $releaseItems->count() }}">{{ $release->pas_number ?? '—' }}</td>
                    @endif
                    <td>{{ $item?->item?->receivingItems?->pluck('item_code')->filter()->unique()->implode(', ') ?: '—' }}</td>
                    @if($index === 0)
                        <td rowspan="{{ $releaseItems->count() }}">{{ $release->facility_name ?? '—' }}</td>
                        <td rowspan="{{ $releaseItems->count() }}">{{ $release->health_program_coordinator ?? '—' }}</td>
                    @endif
                    <td>{{ $item?->item_description ?? '—' }}</td>
                    @if($index === 0)
                        <td rowspan="{{ $releaseItems->count() }}">{{ optional($release->date_released)->format('M d, Y') ?? '—' }}</td>
                        <td rowspan="{{ $releaseItems->count() }}">{{ $release->status ?? '—' }}</td>
                    @endif
                </tr>
            @endforeach
        @empty
            <tr><td colspan="8">No release records found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <script>window.onload = () => window.print();</script>
</body>
</html>
