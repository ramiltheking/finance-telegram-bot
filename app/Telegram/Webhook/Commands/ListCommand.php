<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Models\Category;
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
            Telegram::message($userId, '❗ Пользователь не найден', $this->message_id)->send();
            return;
        }

        $operations = Operation::where('user_id', $user->telegram_id)
            ->where('occurred_at', '>=', now()->subDays(7))
            ->where('status', 'confirmed')
            ->orderByDesc('occurred_at')
            ->get();

        if ($operations->isEmpty()) {
            Telegram::message($userId, '❗ Нет операций за последнюю неделю', $this->message_id)->send();
            return;
        }

        $categoryMapById   = Category::pluck('name_ru', 'id')->toArray();
        $categoryMapByName = Category::pluck('name_ru', 'name_en')->toArray();

        $message = "📋 Список операций за последнюю неделю:\n\n";

        foreach ($operations as $index => $op) {
            $category = $categoryMapById[$op->category]
                ?? $categoryMapByName[$op->category]
                ?? $op->category;

            $message .= sprintf(
                "%d) %s %s (%s), сумма: %s, дата: %s\n",
                $index + 1,
                $op->type === 'income' ? '➕' : '➖',
                $category ?? 'Без категории',
                $op->currency,
                number_format($op->amount, 2, '.', ' '),
                $op->occurred_at->format('d.m.Y')
            );
        }

        $message .= "\nЧтобы удалить запись: /delete (номер)\n";
        $message .= "Чтобы редактировать запись: /edit (номер) (сумма)\n";

        Telegram::message($userId, $message)->send();
    }
}
