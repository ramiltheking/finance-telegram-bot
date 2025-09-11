<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Models\User;
use App\Models\Operation;
use App\Telegram\Webhook\Webhook;

class ListCommand extends Webhook
{
    public function run()
    {
        $userId = $this->chat_id;

        $user = User::where('telegram_id', $userId)->first();
        if (!$user) {
            Telegram::message($userId, '❗ Пользователь не найден')->send();
            return;
        }

        $operations = Operation::where('user_id', $user->telegram_id)
            ->where('occurred_at', '>=', now()->subDays(7))
            ->orderBy('occurred_at', 'desc')
            ->get();

        if ($operations->isEmpty()) {
            Telegram::message($userId, '❗ Нет операций за последнюю неделю')->send();
            return;
        }

        $message = "📋 Список операций за последнюю неделю:\n\n";

        foreach ($operations as $index => $op) {
            $message .= sprintf(
                "%d. %s %s (%s), сумма: %s, дата: %s\n",
                $index + 1,
                $op->type === 'income' ? '➕' : '➖',
                $op->category ?? 'Без категории',
                $op->currency,
                $op->amount,
                $op->occurred_at->format('d.m.Y')
            );
        }

        $message .= "\nЧтобы удалить запись: /delete (номер)\n";
        $message .= "Чтобы редактировать запись: /edit (номер) (сумма)\n";

        Telegram::message($userId, $message)->send();
    }
}
