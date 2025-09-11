<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Models\Operation;
use App\Models\User;
use App\Telegram\Webhook\Webhook;

class DeleteLastCommand extends Webhook
{
    public function run()
    {
        $userId = $this->chat_id;

        $user = User::where('telegram_id', $userId)->first();
        if (!$user) {
            Telegram::message($userId, '❗ Пользователь не найден')->send();
            return;
        }

        $lastOperation = Operation::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        if (!$lastOperation) {
            Telegram::message($userId, "❗ У вас нет операций для удаления")->send();
            return;
        }

        $lastOperation->delete();

        Telegram::message($userId, "✅ Последняя операция удалена")->send();
    }
}
