<?php

namespace App\Http\Controllers;

use App\Facades\Telegram;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Exports\OperationsExport;
use App\Models\Category;
use App\Models\Operation;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class ExportController extends Controller
{
    public function export(Request $request, $format)
    {
        $telegramId = Auth::user()->telegram_id;

        $operations = Operation::where('user_id', $telegramId)
            ->where('occurred_at', '>=', now()->subDays(30))
            ->where('status', 'confirmed')
            ->orderByDesc('occurred_at')
            ->get();

        if ($operations->isEmpty()) {
            return response()->json(['error' => 'Нет операций для экспорта'], 400);
        }

        $categoryMapById = Category::pluck('name_ru', 'id')->toArray();
        $categoryMapByName = Category::pluck('name_ru', 'name_en')->toArray();

        $operations = $operations->map(function ($op) use ($categoryMapById, $categoryMapByName) {
            $cat = $op->category;
            $op->category_name = $categoryMapById[$cat] ?? $categoryMapByName[$cat] ?? $cat;
            return $op;
        });

        $filename = "operations_{$telegramId}_" . now()->format('Y-m-d_His') . ".{$format}";
        $directory = 'exports';

        try {
            switch ($format) {
                case 'xlsx':
                    $filePath = "{$directory}/{$filename}";

                    Excel::store(new OperationsExport($operations), $filePath, 'local');

                    $fullPath = Storage::path($filePath);

                    if (!Storage::exists($filePath) || Storage::size($filePath) === 0) {
                        throw new \Exception("Excel файл не был создан или пустой");
                    }

                    $this->sendToTelegram($telegramId, $fullPath, $filename);

                    return redirect()->route('miniapp.index')->with('success', 'Файл Excel отправлен в Telegram');
                    break;

                case 'pdf':
                    $pdf = Pdf::loadView('exports.operations', [
                        'operations' => $operations,
                        'title'      => 'История операций'
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

                    $this->sendToTelegram($telegramId, $tempPath, $filename);

                    return redirect()->route('miniapp.index')->with('success', 'Файл PDF отправлен в Telegram');

                case 'docx':
                    $phpWord = new PhpWord();
                    $section = $phpWord->addSection();

                    $section->addText('История операций', ['bold' => true, 'size' => 14]);
                    $section->addTextBreak(1);
                    $section->addText('Период: ' . now()->subDays(30)->format('d.m.Y') . ' - ' . now()->format('d.m.Y'));
                    $section->addTextBreak(2);

                    $section->addText('Доходы', ['bold' => true, 'size' => 12]);
                    $table = $section->addTable();
                    $table->addRow();
                    $table->addCell(2000)->addText('Дата', ['bold' => true]);
                    $table->addCell(3000)->addText('Категория', ['bold' => true]);
                    $table->addCell(2000)->addText('Сумма', ['bold' => true]);

                    $categoryMapByName = Category::pluck('name_ru', 'name_en')->toArray();

                    foreach ($operations->where('type', 'income') as $op) {
                        $table->addRow();
                        $table->addCell(2000)->addText($op->occurred_at->format('d.m.Y H:i'));
                        $table->addCell(3000)->addText($categoryMapByName[$op->category] ?? $op->category ?? '-');
                        $table->addCell(2000)->addText(number_format($op->amount, 2) . ' ' . $op->currency);
                    }

                    $table->addRow();
                    $table->addCell(5000, ['gridSpan' => 2])->addText('Итого доходов', ['bold' => true]);
                    $table->addCell(2000)->addText(
                        number_format($operations->where('type', 'income')->sum('amount'), 2) . ' KZT',
                        ['bold' => true]
                    );

                    $section->addTextBreak(2);

                    $section->addText('Расходы', ['bold' => true, 'size' => 12]);
                    $table = $section->addTable();
                    $table->addRow();
                    $table->addCell(2000)->addText('Дата', ['bold' => true]);
                    $table->addCell(3000)->addText('Категория', ['bold' => true]);
                    $table->addCell(2000)->addText('Сумма', ['bold' => true]);

                    foreach ($operations->where('type', 'expense') as $op) {
                        $table->addRow();
                        $table->addCell(2000)->addText($op->occurred_at->format('d.m.Y H:i'));
                        $table->addCell(3000)->addText($categoryMapByName[$op->category] ?? $op->category ?? '-');
                        $table->addCell(2000)->addText(number_format($op->amount, 2) . ' ' . $op->currency);
                    }

                    $table->addRow();
                    $table->addCell(5000, ['gridSpan' => 2])->addText('Итого расходов', ['bold' => true]);
                    $table->addCell(2000)->addText(
                        number_format($operations->where('type', 'expense')->sum('amount'), 2) . ' KZT',
                        ['bold' => true]
                    );

                    $tempPath = storage_path("app/temp/{$filename}");
                    if (!file_exists(dirname($tempPath))) {
                        mkdir(dirname($tempPath), 0777, true);
                    }

                    $writer = IOFactory::createWriter($phpWord, 'Word2007');
                    $writer->save($tempPath);

                    $this->sendToTelegram($telegramId, $tempPath, $filename);

                    return redirect()->route('miniapp.index')->with('success', 'Файл DOCX отправлен в Telegram');
                    break;

                default:
                    abort(400, 'Неверный формат. Доступные: xlsx, pdf, docx');
            }
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            Log::error('File path: ' . ($fullPath ?? 'N/A'));

            return response()->json(['error' => 'Ошибка при создании файла: ' . $e->getMessage()], 500);
        }
    }

    protected function sendToTelegram($chatId, $filePath, $filename)
    {
        try {
            if (!file_exists($filePath)) {
                throw new \Exception("Файл не существует: {$filePath}");
            }

            $fileSize = filesize($filePath);
            if ($fileSize === 0) {
                throw new \Exception("Файл пустой: {$filePath}");
            }

            Log::info("Sending file to Telegram: {$filePath}, Size: {$fileSize} bytes");

            $response = Telegram::document($chatId, $filePath, $filename)->send();

            unlink($filePath);

            return $response;
        } catch (\Exception $e) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            throw $e;
        }
    }
}
