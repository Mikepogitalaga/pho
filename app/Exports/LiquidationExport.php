<?php

namespace App\Exports;

use App\Models\Release;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class LiquidationExport implements FromView, WithEvents
{
    public function __construct(protected iterable $releases) {}

    protected $summaryQtys = [];
    protected $summaryCosts = [];
    protected $ptrs = [];
    protected $receivers = [];
    protected $facility = '';
    protected $sortedReleases;

    private function getPtrSequence(string $ptr): int
    {
        if (preg_match('/(\d+)(?!.*\d)/', $ptr, $matches)) {
            return (int) $matches[1];
        }
        return 0;
    }

    public function view(): View
    {
        $releases = $this->releases;

        $allItems = [];
        foreach ($releases as $release) {
            foreach ($release->items as $item) {
                $key = $item->item_description . '|' . $item->uom . '|' . $item->unit_cost;
                if (!isset($allItems[$key])) {
                    $allItems[$key] = [
                        'description' => $item->item_description,
                        'uom' => $item->uom,
                        'unit_cost' => $item->unit_cost,
                        'qtys' => [],
                        'totals' => [],
                    ];
                }
                $allItems[$key]['qtys'][$release->id] = (int) $item->quantity_released;
                $allItems[$key]['totals'][$release->id] = (float) $item->unit_cost * (int) $item->quantity_released;
            }
        }

        ksort($allItems);

        $this->sortedReleases = collect($this->releases)->sort(function ($a, $b) {
            $ptrA = mb_substr($a->ptr_itr_ris_no ?? $a->release_number ?? '—', 0, 31);
            $ptrB = mb_substr($b->ptr_itr_ris_no ?? $b->release_number ?? '—', 0, 31);
            return $this->getPtrSequence($ptrA) <=> $this->getPtrSequence($ptrB);
        })->values();

        $releases = $this->sortedReleases;

        $this->facility = '';
        foreach ($this->sortedReleases as $release) {
            if (!$this->facility) {
                $this->facility = $release->facility_name ?: '—';
            }

            $totalQty = 0;
            $totalCost = 0;
            foreach ($release->items as $item) {
                $totalQty += (int) $item->quantity_released;
                $totalCost += ((float) $item->unit_cost ?? 0) * (int) $item->quantity_released;
            }

            $this->summaryQtys[$release->id] = $totalQty;
            $this->summaryCosts[$release->id] = $totalCost;
            $this->ptrs[$release->id] = mb_substr($release->ptr_itr_ris_no ?? $release->release_number ?? '—', 0, 31);
            $this->receivers[$release->id] = $release->received_by ?: '—';
        }

        $summaryQtys = $this->summaryQtys;
        $summaryCosts = $this->summaryCosts;
        $ptrs = $this->ptrs;
        $receivers = $this->receivers;
        $facility = $this->facility;

        return view('exports.liquidation-excel-pivot', compact(
            'releases', 'allItems', 'summaryQtys', 'summaryCosts', 'ptrs', 'receivers', 'facility'
        ));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = $sheet->getHighestColumn();

                $sheet->getColumnDimension('A')->setWidth(3);
                $sheet->getColumnDimension('B')->setWidth(45);
                $sheet->getColumnDimension('C')->setWidth(10);
                $sheet->getColumnDimension('D')->setWidth(14);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(14);

                $colIndex = 7;
                foreach ($this->sortedReleases as $release) {
                    $sheet->getColumnDimensionByColumn($colIndex)->setWidth(10);      // QTY
                    $sheet->getColumnDimensionByColumn($colIndex + 1)->setWidth(14);  // TOTAL COST
                    $sheet->getColumnDimensionByColumn($colIndex + 2)->setWidth(14);  // RETURNED TO STOCKROOM
                    $sheet->getColumnDimensionByColumn($colIndex + 3)->setWidth(14);  // RETURNED TO STOCKROOM
                    $colIndex += 4;
                }

                $sheet->getRowDimension(1)->setRowHeight(36);
                $sheet->getRowDimension(2)->setRowHeight(28);
                $sheet->getRowDimension(3)->setRowHeight(28);
                $sheet->getRowDimension(4)->setRowHeight(28);

                for ($r = 5; $r <= $lastRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(18);
                }

                $sheet->mergeCellsByColumnAndRow(2, 1, 4, 1);
                $sheet->mergeCellsByColumnAndRow(5, 2, 6, 2);

                $colIndex = 7;
                foreach ($this->sortedReleases as $release) {
                    $sheet->mergeCellsByColumnAndRow($colIndex, 2, $colIndex + 1, 2);     // PTR number
                    $sheet->mergeCellsByColumnAndRow($colIndex, 3, $colIndex + 1, 3);     // Receiver
                    $sheet->mergeCellsByColumnAndRow($colIndex + 2, 2, $colIndex + 3, 2); // RETURNED TO STOCKROOM
                    $colIndex += 4;
                }

                $sheet->getStyle('B1:' . $lastCol . '1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFDAB9');

                $sheet->getStyle('B1:' . $lastCol . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->getStyle('B1:' . $lastCol . '1')->getFont()->setBold(true);
                $sheet->getStyle('B1:' . $lastCol . '1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->getStyle('B2:' . $lastCol . '3')->getFont()->setBold(true);
                $sheet->getStyle('B2:' . $lastCol . '3')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->getStyle('B4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('C4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $colIndex = 7;
                foreach ($this->sortedReleases as $release) {
                    $qtyCol = Coordinate::stringFromColumnIndex($colIndex);
                    $totalCol = Coordinate::stringFromColumnIndex($colIndex + 1);
                    $sheet->getStyle($qtyCol . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle($totalCol . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $colIndex += 4;
                }

                $sheet->getStyle('B5:' . $lastCol . $lastRow)->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->getStyle('B5:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('C5:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D5:D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $colIndex = 7;
                foreach ($this->sortedReleases as $release) {
                    $qtyCol = Coordinate::stringFromColumnIndex($colIndex);
                    $totalCol = Coordinate::stringFromColumnIndex($colIndex + 1);
                    $sheet->getStyle($qtyCol . '5:' . $qtyCol . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle($totalCol . '5:' . $totalCol . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $colIndex += 4;
                }

                $sheet->getStyle('D5:D' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('G5:' . $lastCol . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');

                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);

                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5);

                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 4);
            },
        ];
    }
}
