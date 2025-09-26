<?php

namespace App\Services\Export;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class DocxExportService
{
    public function generate(string $filename, $operations): string
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText(__('export.title'), ['bold' => true, 'size' => 14]);
        $section->addTextBreak(1);
        $section->addText(
            __('export.period', [
                'from' => now()->subDays(30)->format('d.m.Y'),
                'to'   => now()->format('d.m.Y')
            ])
        );
        $section->addTextBreak(2);

        $section->addText(__('export.incomes'), ['bold' => true, 'size' => 12]);
        $this->addTable($section, $operations->where('type', 'income'), 'total_incomes');

        $section->addTextBreak(2);

        $section->addText(__('export.expenses'), ['bold' => true, 'size' => 12]);
        $this->addTable($section, $operations->where('type', 'expense'), 'total_expenses');

        $tempPath = storage_path("app/temp/{$filename}");
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return $tempPath;
    }

    protected function addTable($section, $operations, $totalLabel)
    {
        $table = $section->addTable();
        $table->addRow();
        $table->addCell(2000)->addText(__('export.date'), ['bold' => true]);
        $table->addCell(3000)->addText(__('export.category'), ['bold' => true]);
        $table->addCell(2000)->addText(__('export.amount'), ['bold' => true]);

        foreach ($operations as $op) {
            $table->addRow();
            $table->addCell(2000)->addText($op->occurred_at->format('d.m.Y H:i'));
            $table->addCell(3000)->addText($op->category_name ?? '-');
            $table->addCell(2000)->addText(number_format($op->amount, 2) . ' ' . $op->currency);
        }

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 2])->addText(__("export.{$totalLabel}"), ['bold' => true]);
        $table->addCell(2000)->addText(
            number_format($operations->sum('amount'), 2) . ' ' . optional($operations->first())->currency,
            ['bold' => true]
        );
    }
}
