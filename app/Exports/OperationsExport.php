<?php

namespace App\Exports;

use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OperationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $operations;
    protected $locale;

    public function __construct($operations)
    {
        $this->operations = $operations;
        $this->locale = App::getLocale();
    }

    public function collection()
    {
        return $this->operations;
    }

    public function headings(): array
    {
        if ($this->locale === 'ru') {
            return [
                'Дата и время',
                'Категория',
                'Сумма'
            ];
        } else {
            return [
                'Date and Time',
                'Category',
                'Amount'
            ];
        }
    }

    public function map($operation): array
    {
        return [
            $operation->occurred_at->format('d.m.Y H:i'),
            $operation->category_name,
            $operation->type === "income"
                ? "+" . number_format($operation->amount, 2, '.', '')
                : "-" . number_format($operation->amount, 2, '.', '')
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
