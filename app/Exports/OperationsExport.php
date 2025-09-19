<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OperationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $operations;

    public function __construct($operations)
    {
        $this->operations = $operations;
    }

    public function collection()
    {
        return $this->operations;
    }

    public function headings(): array
    {
        return [
            'Дата и время',
            'Категория',
            'Сумма'
        ];
    }

    public function map($operation): array
    {
        return [
            $operation->occurred_at->format('d.m.Y H:i'),
            $operation->category_name,
            number_format($operation->amount, 2)
        ];
    }

    public function styles($sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFFF00']]
            ],
        ];
    }
}
