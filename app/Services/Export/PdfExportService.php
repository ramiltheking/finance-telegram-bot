<?php

namespace App\Services\Export;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfExportService
{
    public function generate(string $filename, $operations): string
    {
        $pdf = Pdf::loadView('exports.operations', [
            'operations' => $operations,
            'title'      => __('export.title')
        ])->setPaper('a4', 'portrait');

        $tempDir = storage_path("app/temp");
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $tempPath = $tempDir . '/' . $filename;

        file_put_contents($tempPath, $pdf->output());

        if (!file_exists($tempPath) || filesize($tempPath) === 0) {
            throw new \Exception("PDF файл не был создан или пустой: {$tempPath}");
        }

        return $tempPath;
    }
}
