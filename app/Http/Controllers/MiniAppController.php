<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\Payment;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MiniAppController extends Controller
{
    public function index(Request $request)
    {
        return view('miniapp.index');
    }

    public function data(Request $request)
    {
        $telegramId = $request->query('telegram_id');
        $from = Carbon::now()->subDays(30);

        $operations = Operation::where('user_id', $telegramId)
            ->where('occurred_at', '>=', $from)
            ->orderBy('occurred_at', 'desc')
            ->get();

        $categoryMapById = Category::pluck('name_ru', 'id')->toArray();
        $categoryMapByName = Category::pluck('name_ru', 'name_en')->toArray();

        $categories = $operations->groupBy('category')->mapWithKeys(function ($ops, $category) use ($categoryMapById, $categoryMapByName) {
            $translated = $categoryMapById[$category] ?? $categoryMapByName[$category] ?? $category;
            return [$translated => $ops->sum('amount')];
        });

        $operations = $operations->map(function ($op) use ($categoryMapById, $categoryMapByName) {
            $cat = $op->category;
            $op->category = $categoryMapById[$cat] ?? $categoryMapByName[$cat] ?? $cat;
            return $op;
        });

        $payments = Payment::where('user_id', $telegramId)->latest()->get();

        return response()->json([
            'categories' => $categories,
            'operations' => $operations->take(10),
            'payments'   => $payments->take(10),
        ]);
    }

    public function export(Request $request, $format) {}
}
