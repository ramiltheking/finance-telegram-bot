<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Models\Operation;
use App\Models\User;
use App\Telegram\Webhook\Webhook;

class BalanceCommand extends Webhook
{
    public function run()
    {
        $userId = $this->chat_id;

        $user = User::where('telegram_id', $userId)->first();
        if (!$user) {
            Telegram::message($userId, '❗ Пользователь не найден', $this->message_id)->send();
            return;
        }

        $operations = Operation::where('user_id', $user->telegram_id)
            ->where('occurred_at', '>=', now()->subDays(30))
            ->where('status', 'confirmed')
            ->orderByDesc('occurred_at')
            ->get();

        if ($operations->isEmpty()) {
            Telegram::message($userId, '❗ Нет операций за последний месяц', $this->message_id)->send();
            return;
        }

        $income = $operations->where('type', 'income')->sum('amount');
        $expense = $operations->where('type', 'expense')->sum('amount');
        $balance = $income - $expense;

        $message = "📊 <b>Баланс за последние 30 дней:</b>\n\n";
        $message .= "📈 Доходы: <b>" . number_format($income, 2, '.', ' ') . "₸</b>\n";
        $message .= "📉 Расходы: <b>" . number_format($expense, 2, '.', ' ') . "₸</b>\n";
        $message .= "💰 Остаток: <b>" . number_format($balance, 2, '.', ' ') . "₸</b>";

        Telegram::message($userId, $message)->send();
    }
}
