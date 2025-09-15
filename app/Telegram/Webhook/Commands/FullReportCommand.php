<?php

namespace App\Telegram\Webhook\Commands;

use App\Models\Operation;
use App\Models\Category;
use App\Facades\Telegram;
use App\Models\User;
use App\Telegram\Webhook\Webhook;

class FullReportCommand extends Webhook
{
    public function run()
    {
        $userId = $this->chat_id;

        $user = User::where('telegram_id', $userId)->first();
        if (!$user) {
            Telegram::message($userId, '❗ Пользователь не найден')->send();
            return;
        }

        $operations = Operation::where('user_id', $userId)->where('status', 'confirmed')->get();

        if ($operations->isEmpty()) {
            Telegram::message($this->chat_id, "❗ Нет операций для отображения")->send();
            return;
        }

        $totalSpent   = 0;
        $totalClaimed = 0;
        $categoryTotals = [];

        $categoryMap = Category::pluck('name_ru', 'name_en')->toArray();

        foreach ($operations as $op) {
            $amount = (float)$op->amount;

            if ($op->type === 'expense') {
                $totalSpent += $amount;
            } elseif ($op->type === 'income') {
                $totalClaimed += $amount;
            }

            $catCode = $op->category;
            $catName = $categoryMap[$catCode] ?? 'Без категории';

            if (!isset($categoryTotals[$catName])) {
                $categoryTotals[$catName] = 0;
            }
            $categoryTotals[$catName] += $amount;
        }

        $currency = $operations->first()->currency;

        $message = "📊 Полный отчет:\n\n";
        $message .= "Общая сумма расходов: " . number_format($totalSpent, 2, '.', ' ') . " {$currency}\n";
        $message .= "Общая сумма доходов: " . number_format($totalClaimed, 2, '.', ' ') . " {$currency}\n\n";
        $message .= "Суммы по категориям:\n";

        foreach ($categoryTotals as $category => $total) {
            $message .= "{$category}: " . number_format($total, 2, '.', ' ') . " {$currency}\n";
        }

        Telegram::message($this->chat_id, $message)->send();
    }
}
