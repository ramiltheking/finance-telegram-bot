<?php

namespace App\Http\Controllers;

use App\Facades\Telegram;
use App\Models\Operation;
use App\Services\CategoryService;
use App\Services\Export\ExportService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    protected ExportService $exportService;
    protected CategoryService $categoryService;

    public function __construct(ExportService $exportService, CategoryService $categoryService)
    {
        $this->exportService = $exportService;
        $this->categoryService = $categoryService;
    }

    public function export(Request $request, $format)
    {
        $user = Auth::user();

        if (!UserService::hasAccess($user)) {
            return redirect()->route('miniapp.index')->with('fail', __('export.subscription_required'));
        }

        $telegramId = $user->telegram_id;

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

        $operations = $operations->map(function ($op) use ($locale) {
            $categoryName = $this->categoryService->getCategoryName($op, $locale);

            $exportOp = new \stdClass();
            $exportOp->occurred_at = $op->occurred_at;
            $exportOp->category_name = $categoryName;
            $exportOp->amount = $op->amount;
            $exportOp->type = $op->type;
            $exportOp->currency = $op->currency;
            $exportOp->description = $op->description;

            return $exportOp;
        });

        $filename = "operations_{$telegramId}_" . now()->format('Y-m-d_His') . ".{$format}";

        try {
            $filePath = $this->exportService->export($format, $filename, $operations);

            $this->sendToTelegram($telegramId, $filePath, $filename);

            return redirect()->route('miniapp.index')->with('success', __("export.{$format}_success"));
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());

            return response()->json(['error' => 'Ошибка при создании файла, пожалуйста попробуйте позже.'], 500);
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
