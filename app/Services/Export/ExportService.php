<?php

namespace App\Services\Export;

use App\Models\Operation;

class ExportService
{
    protected ExcelExportService $excel;
    protected PdfExportService $pdf;
    protected DocxExportService $docx;

    public function __construct(ExcelExportService $excel, PdfExportService $pdf, DocxExportService $docx)
    {
        $this->excel = $excel;
        $this->pdf = $pdf;
        $this->docx = $docx;
    }

    public function export(string $format, $filename, $operations)
    {
        switch ($format) {
            case 'xlsx':
                return $this->excel->generate($filename, $operations);
            case 'pdf':
                return $this->pdf->generate($filename, $operations);
            case 'docx':
                return $this->docx->generate($filename, $operations);
            default:
                throw new \InvalidArgumentException("Данный формат экспорта не поддерживается: {$format}");
        }
    }
}
