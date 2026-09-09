<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ItemsExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected Collection $items,
        protected string $sheetTitle = 'Items'
    ) {}

    public function collection(): Collection
    {
        return $this->items->flatMap(function ($item) {
            $codes = $item->product_codes ?? collect([$item->item_code])->filter();

            return ($codes->isNotEmpty() ? $codes : collect([null]))->map(fn ($code) => [
                $code,
                $item->name,
                $item->category,
                $item->display_unit,
                $item->quantity_on_hand,
                $item->unit_cost,
                $item->location,
                $item->stock_keeping_unit,
                $item->program_coordinator,
                $item->status,
            ]);
        });
    }

    public function headings(): array
    {
        return [
            'Product Code',
            'Item Description',
            'Category',
            'UOM',
            'Current Stock',
            'Unit Cost',
            'Location',
            'Program',
            'Program Coordinator',
            'Status',
        ];
    }

    public function title(): string
    {
        return mb_substr($this->sheetTitle, 0, 31);
    }
}
