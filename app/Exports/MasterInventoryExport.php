<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class MasterInventoryExport implements FromView, WithEvents
{
    public function __construct(private array $rows, private array $summary) {}

    public function view(): View
    {
        return view('exports.master-inventory-excel', ['rows' => $this->rows, 'summary' => $this->summary]);
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $lastRow = $sheet->getHighestRow();
            $lastColumn = $sheet->getHighestColumn();
            $sheet->freezePane('D5');
            foreach (['D2:E2', 'F2:K2', 'L2:M2', 'N2:R2', 'S2:S3', 'T2:U2', 'V2:V3', 'W2:W3', 'X2:X3', 'Y2:Z2'] as $range) {
                $sheet->mergeCells($range);
            }
            $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $sheet->getStyle("A1:{$lastColumn}4")->getFont()->setBold(true)->setName('Arial');
            $sheet->getStyle("A1:{$lastColumn}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2F0D9');
            $sheet->getStyle("A2:{$lastColumn}4")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAF7');
            $sheet->getStyle("A5:{$lastColumn}{$lastRow}")->getFont()->setName('Arial');
            $sheet->getStyle("D1:Z{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("C5:C{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            foreach (['E', 'G', 'I', 'K', 'M', 'R', 'S', 'U', 'V', 'Y', 'Z'] as $column) {
                $sheet->getStyle("{$column}1:{$column}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            }
            $sheet->getStyle("C1:Z{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A5:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->setAutoFilter("A4:Z{$lastRow}");
            foreach (['A' => 30, 'B' => 12, 'C' => 13] as $column => $width) $sheet->getColumnDimension($column)->setWidth($width);
            foreach (range(4, 26) as $column) $sheet->getColumnDimensionByColumn($column)->setWidth(13);
            $sheet->getRowDimension(1)->setRowHeight(22);
            $sheet->getRowDimension(2)->setRowHeight(42);
            $sheet->getRowDimension(3)->setRowHeight(24);
            $sheet->getRowDimension(4)->setRowHeight(32);
            $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setFitToWidth(1)->setFitToHeight(0);
        }];
    }
}