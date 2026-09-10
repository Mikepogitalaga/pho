<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAS – {{ $pas->pas_number }}</title>
    <style>
        @page {
            size: legal Portrait;
            margin: 10mm 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
        }

        .print-controls {
            max-width: 1100px;
            margin: 16px auto 0;
            display: flex;
            gap: 10px;
            padding: 0 16px;
        }

        .print-controls button,
        .print-controls a {
            padding: 8px 18px;
            border: 0;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print {
            background: #1a56db;
            color: #fff;
        }

        .btn-back {
            background: #6b7280;
            color: #fff;
        }

        .page {
            width: 100%;
            max-width: 1100px;
            margin: 12px auto 20px;
            padding: 12mm 10mm;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.16);
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .header-content {
            width: 100%;
            text-align: center;
        }

        .entity-name {
            margin: 0;
            font-weight: 700;
            font-size: 14px;
        }

        .program-name {
            margin-top: 4px;
            font-weight: 700;
            font-size: 12px;
            text-decoration: underline;
        }

        .logo-placeholder {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            right: 0;
            margin-right: 150px;
            margin-top: 10px;
        }

            .logo-placeholder img {
                width: 50px;
                height: 50px;
                object-fit: contain;
            }

        .title-banner {
            font-family:Baskerville Old Face;
            margin: 0;
            font-size: 24px;
            letter-spacing: 1px;
            text-align: center;
            margin-bottom: 50px;
            font-weith: bold;
        }

        .top-info {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 12px;
        }

        .top-info-left{
            margin-left: 20px;
        }

        
        .form-row1 {
            display: flex;
            align-items: baseline;
            margin-bottom: 8px;
        }

        .form-row1 label {
            min-width: 160px;
            font-weight: 1000;
            margin-right: -80px;
        }

        .form-row1 .line {
            border-bottom: 1px solid #000;
            flex-grow: 0;
            width: 200px;
            padding-left: 1px;
            text-align: left;
         
        }
        .form-row2 {
            display: flex;
            align-items: baseline;
            margin-bottom: 8px;
        }
        .form-row2 label {
            min-width: 160px;
            font-weight: 1000;
            margin-right: -100px;
        }

        .form-row2 .line {
            border-bottom: 1px solid #000;
            flex-grow: 0;
            width: 250px;
            padding-left: 1px;
            text-align: left;
            
         
        }
        .form-row3 {
            display: flex;
            align-items: baseline;
            margin-bottom: 8px;
        }

        .form-row3 label {
            min-width: 160px;
            font-weight: 1000;
            margin-right: -25px;
        }

        .form-row3 .line {
            border-bottom: 1px solid #000;
            flex-grow: 0;
            width: 250px;
            padding-left: 1px;
            text-align: left;
         
        }
        .top-info-right {
            width: 50%;
            margin-left: 200px;
        }

        .form-row {
            display: flex;
            align-items: baseline;
            margin-bottom: 8px;
        }

        .form-row label {
            min-width: 160px;
            font-weight: 1000;
            margin-right: -110px;
        }

        .form-row .line {
            border-bottom: 1px solid #000;
            flex-grow: 0;
            width: 100px;
            padding-left: 1px;
            text-align: left;
         
        }

        .notice-box {
            border: 1px solid #000;
            padding: 6px 8px;
            font-style: italic;
            font-size: 10px;
            margin-bottom: 0;
            background-color: #f9f9f9;
        }

        .lower-section {
            border: 1px solid #000;
            padding: 0;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-size: 11px;
            vertical-align: middle;
        }

        th {
            background-color: #f2f2f2;
            font-weight: 700;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .signatures {
            display: flex;
            border-top: 1px solid #000;
        }

        .sig-box {
            flex: 1;
            padding: 10px 12px 12px;
            box-sizing: border-box;
        }

        .sig-box + .sig-box {
            border-left: 1px solid #000;
        }

        .sig-title {
            text-align: center;
            font-weight: 700;
            margin-bottom: 10px;
            text-decoration: underline;
        }

        .sig-content {
            text-align: center;
            margin-top: 28px;
        }

        .sig-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            width: 80%;
            margin-bottom: 3px;
        }

        .notes {
            font-size: 10px;
            margin-top: 8px;
        }

        .grand-total-row td {
            font-weight: 700;
        }

        .footer-info {
            margin-top: 8px;
            font-size: 10px;
            text-align: center;
        }
        .empty-item-row td {
            height: 30px;
        }

        @media screen {
            body {
                background: #e5e5e5;
                padding: 0 0 20px;
            }
        }

        @media print {
            .print-controls {
                display: none;
            }

            body {
                background: #fff;
            }

            .page {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: none;
            }
        }
    </style>
</head>
<body>

<div class="print-controls">
    <button class="btn-print" onclick="window.print()">🖨 Print</button>
    <a class="btn-back" href="{{ route('pas.view', ['pas' => $pas->getKey()]) }}">← Back</a>
</div>

@php
    $items = $pas->items ?? collect();
    $grandTotal = $items->sum(fn ($item) => (float) $item->total_cost);
    $pasNumber = $pas->pas_number ?? '—';
    $ptrNumber = $pas->release?->release_number ?? $pas->release?->ptr_itr_ris_no ?? '—';
    $documentDate = $pas->date_of_pass?->format('F d, Y') ?? now()->format('F d, Y');
    $programName = $pas->program ?: 'STI/HIV AIDS PREVENTION AND CONTROL PROGRAM (NASPCP)';
    $entityName = 'Provincial Health Office';
    $facilityName = $pas->facility_name ?: $pas->facility_coordinator;
    $supplierName = $pas->supplier?->company_name ?? '—';
    $purposeActivity = $pas->purpose_activity ?: 'For the participants of World Hepatitis Day Celebration';
    $chunks = $items->chunk(20);
    $totalPages = max($chunks->count(), 1);
@endphp

@foreach($chunks as $pageIndex => $chunk)
    @php
        $pageNum = $pageIndex + 1;
    @endphp
    <div class="page">
        <div class="header">
            
            <div class="logo-placeholder">
                <img src="{{ asset('logo.jpg') }}" alt="Logo">
            </div>
        </div>

        <div class="title-banner">PROPERTY ALLOCATION SLIP</div>

        <div class="top-info">
            <div class="top-info-left">
                <div class="form-row1">
                    <label>Entity Name:</label>
                    <span class="line" style="text-align: left; font-weight: bold;">{{ $entityName }}</span>
                </div>
                <div class="form-row2">
                    <label>Program:</label>
                    <span style="font-weight: bold; text-decoration: underline;">{{ $programName }}</span>
                </div>
                <div class="form-row3">
                    <label>Activity and Purpose:</label>
                    <span class="line">{{ $purposeActivity }}</span>
                </div>
            </div>
            <div class="top-info-right">
                <div class="form-row">
                    <label>PTR #:</label>
                   
                </div>
                <div class="form-row">
                    <label>PAS #:</label>
                    <span class="line">{{ $pasNumber }}</span>
                </div>
                <div class="form-row">
                    <label>Date:</label>
                    <span class="line">{{ $documentDate }}</span>
                </div>
            </div>
        </div>

        <div class="lower-section">
            <div class="notice-box">
                The following properties/supplies/materials and/or equipment are hereby allocated and requested to be transferred to the herein identified agency and/or facility for their official use.
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">Qty</th>
                        <th style="width: 10%;">Unit</th>
                        <th style="width: 38%;">Description</th>
                        <th style="width: 12%;">Lot/Batch No.</th>
                        <th style="width: 12%;">Expiry Date</th>
                        <th style="width: 10%;">Unit Cost</th>
                        <th style="width: 10%;">Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chunk as $item)
                        <tr>
                            <td>{{ number_format($item->quantity) }}</td>
                            <td>{{ $item->unit }}</td>
                            <td class="text-left">{{ $item->item_description }}</td>
                            <td>{{ $item->lot_number ?? '—' }}</td>
                            <td>{{ $item->expiration_date?->format('m/d/Y') ?? '—' }}</td>
                            <td class="text-right">
                                {{ number_format((float) $item->unit_cost, 2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format((float) $item->total_cost, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;">
                                No items recorded.
                            </td>
                        </tr>
                    @endforelse

                    {{-- ADD EMPTY ITEM BOXES --}}
                    @php
                        $emptyRows = max(0, 13 - $chunk->count());
                    @endphp

                    @for($i = 0; $i < $emptyRows; $i++)
                        <tr class="empty-item-row">
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="text-left">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor

                    @if($pageNum === $totalPages)
                        <tr class="grand-total-row">
                            <td colspan="6" style="text-align: right;">
                                GRAND TOTAL
                            </td>
                            <td class="text-right">
                                {{ number_format($grandTotal, 2) }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            @if($pageNum === $totalPages)
                <div class="signatures">
                    <div class="sig-box">
                        <div class="sig-title">ALLOCATION REQUEST</div>
                        <div style="font-size: 11px; text-align: center; margin-bottom: 15px;">
                            I hereby certify that I have this day allocated to<br>
                            <strong style="text-decoration: underline;">{{ strtoupper((string) $facilityName) }}</strong><br>
                            <span style="font-size: 10px; font-style: italic;">(Name of Agency/Facility)</span><br>
                            The above supplies &/or property/ies.
                        </div>

                        <div class="sig-content">
                            <span class="sig-line"></span><br>
                            <strong>{{ strtoupper((string) $pas->facility_coordinator) }}</strong><br>
                            <span style="font-size: 10px;">Program Coordinator</span>
                        </div>

                        <div style="margin-top: 15px; font-size: 11px;">
                            Date:
                            <span style="border-bottom: 1px solid #000; display: inline-block; width: 150px;"></span>
                        </div>
                    </div>

                    <div class="sig-box">
                        <div class="sig-title">APPROVED</div>
                        <div style="font-size: 11px; text-align: center; margin-bottom: 15px;">
                            This is to certify that I have approved the<br>
                            foregoing transfer of supplies &/or property/ies.
                        </div>

                        <div class="sig-content" style="margin-top: 45px;">
                            <span class="sig-line"></span><br>
                            <strong>MARK JOLEEN M. CALBAN, MD, MPM-HSD</strong><br>
                            <span style="font-size: 10px;">Provincial Health Officer II</span>
                        </div>

                        <div style="margin-top: 15px; font-size: 11px;">
                            Date:
                            <span style="border-bottom: 1px solid #000; display: inline-block; width: 150px;"></span>
                        </div>
                    </div>
                </div>
            @endif

            
        </div>

        <div class="notes">
            <strong>Note:</strong> Accomplished in duplicate copies<br>
            * Original copy for PHO Supply Section<br>
            * Second copy for Employee file
        </div>

        <div style="text-align:center; font-size: 10px; margin-top: 6px; color:#444;">Page {{ $pageNum }} of {{ $totalPages }}</div>
    </div>
@endforeach

<script>
    window.addEventListener('load', function () { window.print(); });
</script>
</body>
</html>
