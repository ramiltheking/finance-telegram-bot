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
            Telegram::message($userId, trans('commands.balance.user_not_found'), $this->message_id)->send();
            return;
        }

        $operations = Operation::where('user_id', $user->telegram_id)
            ->where('occurred_at', '>=', now()->subDays(30))
            ->where('status', 'confirmed')
            ->orderByDesc('occurred_at')
            ->get();

        if ($operations->isEmpty()) {
            Telegram::message($userId, trans('commands.balance.no_operations'), $this->message_id)->send();
            return;
        }

        $income = $operations->where('type', 'income')->sum('amount');
        $expense = $operations->where('type', 'expense')->sum('amount');
        $balance = $income - $expense;

        $message = trans('commands.balance.title') . "\n\n";
        $message .= trans('commands.balance.income', ['amount' => number_format($income, 2, '.', ' ')]) . "\n";
        $message .= trans('commands.balance.expense', ['amount' => number_format($expense, 2, '.', ' ')]) . "\n";

        if ($balance > 0) {
            $message .= trans('commands.balance.balance_positive', ['amount' => number_format($balance, 2, '.', ' ')]);
        } elseif ($balance < 0) {
            $message .= trans('commands.balance.balance_negative', ['amount' => number_format(abs($balance), 2, '.', ' ')]);
        }

        Telegram::message($userId, $message)->send();
    }
}
