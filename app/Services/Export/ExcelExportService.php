<?php

namespace App\Services\Export;

use App\Exports\OperationsExport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportService
{
    public function generate(string $filename, $operations): string
    {
        $filePath = "exports/{$filename}";

        Excel::store(new OperationsExport($operations), $filePath, 'local');

        $fullPath = Storage::path($filePath);

        if (!Storage::exists($filePath) || Storage::size($filePath) === 0) {
            throw new \Exception("Excel файл не был создан или пустой");
        }

        return $fullPath;
    }
}
