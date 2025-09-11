<?php

namespace App\Telegram\Webhook\Commands;

use App\Models\Operation;
use Carbon\Carbon;
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

        $operations = Operation::where('user_id', $userId)->get();

        if ($operations->isEmpty()) {
            Telegram::message($this->chat_id, "❗ Нет операций для отображения")->send();
            return;
        }

        $totalSpent   = 0;
        $totalClaimed = 0;
        $categoryTotals = [];

        foreach ($operations as $op) {
            $amount = (float)$op->amount;

            if ($op->type === 'expense') {
                $totalSpent += $amount;
            } elseif ($op->type === 'income') {
                $totalClaimed += $amount;
            }

            $cat = $op->category ?? 'Без категории';
            if (!isset($categoryTotals[$cat])) {
                $categoryTotals[$cat] = 0;
            }
            $categoryTotals[$cat] += $amount;
        }

        $message = "📊 Полный отчет:\n\n";
        $message .= "Общая сумма расходов: {$totalSpent} {$operations->first()->currency}\n";
        $message .= "Общая сумма доходов: {$totalClaimed} {$operations->first()->currency}\n\n";
        $message .= "Суммы по категориям:\n";

        foreach ($categoryTotals as $category => $total) {
            $message .= "{$category}: {$total}\n";
        }

        Telegram::message($this->chat_id, $message)->send();
    }
}
