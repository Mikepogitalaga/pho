<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class ReleaseListExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize
{
    public function __construct(private Collection $releases) {}

    public function headings(): array
    {
        return ['PTR Number', 'PAS No.', 'Product Code', 'Facility / End-user', 'Program', 'Item Description', 'Date Released', 'Status'];
    }

    public function collection(): Collection
    {
        return $this->releases->flatMap(function ($release) {
            $releaseItems = $release->items->isNotEmpty() ? $release->items : collect([null]);

            return $releaseItems->map(function ($item) use ($release) {
                return [
                    $release->ptr_itr_ris_no ?? $release->release_number,
                    $release->pas_number,
                    $item?->item?->receivingItems?->pluck('item_code')->filter()->unique()->implode(', '),
                    $release->facility_name,
                    $release->health_program_coordinator,
                    $item?->item_description,
                    optional($release->date_released)->format('Y-m-d'),
                    $release->status,
                ];
            });
        })->values();
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $row = 2;
            foreach ($this->releases as $release) {
                $count = max(1, $release->items->count());
                if ($count > 1) {
                    foreach (['A', 'B', 'D', 'E', 'G', 'H'] as $column) {
                        $sheet->mergeCells("{$column}{$row}:{$column}" . ($row + $count - 1));
                    }
                }
                $row += $count;
            }
            $sheet->getStyle('A1:H' . max(1, $row - 1))->getAlignment()->setVertical('center')->setWrapText(true);
            $sheet->getStyle('A1:H1')->getFont()->setBold(true);
            $sheet->freezePane('A2');
        }];
    }
}
