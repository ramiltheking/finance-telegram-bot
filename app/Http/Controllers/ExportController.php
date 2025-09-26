<?php

namespace App\Http\Controllers;

use App\Facades\Telegram;
use App\Models\Category;
use App\Models\Operation;
use App\Services\Export\ExportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    protected ExportService $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function export(Request $request, $format)
    {
        $telegramId = Auth::user()->telegram_id;

        $operations = Operation::where('user_id', $telegramId)
            ->where('occurred_at', '>=', now()->subDays(30))
            ->where('status', 'confirmed')
            ->orderByDesc('occurred_at')
            ->get();

        if ($operations->isEmpty()) {
            return redirect()->route('miniapp.index')->with('fail', __('export.no_operations'));
        }

        $locale = Auth::user()->settings?->language ?? app()->getLocale();
        app()->setLocale($locale);

        $nameColumn = $locale === 'ru' ? 'name_ru' : 'name_en';
        $categoryMapById = Category::pluck($nameColumn, 'id')->toArray();
        $categoryMapByName = Category::pluck($nameColumn, 'name_en')->toArray();

        $operations = $operations->map(function ($op) use ($categoryMapById, $categoryMapByName) {
            $cat = $op->category;
            $op->category_name = $categoryMapById[$cat] ?? $categoryMapByName[$cat] ?? $cat;
            return $op;
        });

        $filename = "operations_{$telegramId}_" . now()->format('Y-m-d_His') . ".{$format}";

        try {
            $filePath = $this->exportService->export($format, $filename, $operations);

            $this->sendToTelegram($telegramId, $filePath, $filename);

            return redirect()->route('miniapp.index')->with('success', __("export.{$format}_success"));
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());

            return response()->json(['error' => 'Ошибка при создании файла: ' . $e->getMessage()], 500);
        }
    }

    protected function sendToTelegram($chatId, $filePath, $filename)
    {
        if (!file_exists($filePath) || filesize($filePath) === 0) {
            throw new \Exception("Файл не существует или пустой: {$filePath}");
        }

        $response = Telegram::document($chatId, $filePath, $filename)->send();

        unlink($filePath);

        return $response;
    }
}
