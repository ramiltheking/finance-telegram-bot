<?php

namespace App\Telegram\Webhook\Commands;

use App\Models\Operation;
use Carbon\Carbon;
use App\Facades\Telegram;
use App\Telegram\Webhook\Webhook;

class ReportCommand extends Webhook
{
    public function run()
    {
        $userId = $this->request->input('message')['from']['id'];

        $oneWeekAgo = Carbon::now()->subDays(7);

        $operations = Operation::where('user_id', $userId)
            ->where('occurred_at', '>=', $oneWeekAgo)
            ->get();

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

        $message = "📊 Отчет за неделю:\n\n";
        $message .= "Общая сумма расходов: {$totalSpent} {$operations->first()->currency}\n";
        $message .= "Общая сумма доходов: {$totalClaimed} {$operations->first()->currency}\n\n";
        $message .= "Суммы по категориям:\n";

        foreach ($categoryTotals as $category => $total) {
            $message .= "{$category}: {$total}\n";
        }

        Telegram::message($this->chat_id, $message)->send();
    }
}
