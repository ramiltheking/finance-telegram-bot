<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Models\User;
use App\Models\Operation;
use App\Telegram\Webhook\Webhook;

class EditCommand extends Webhook
{
    public function run()
    {
        $userId = $this->chat_id;
        $text = $this->request->input('message.text');
        $args = explode(' ', $text);

        if (count($args) < 3) {
            Telegram::message($userId, '❗ Неверный формат команды. Используйте /edit (номер) (сумма)')->send();
            return;
        }

        $index = (int)$args[1];
        $newAmount = (float)$args[2];

        if ($index <= 0 || $newAmount <= 0) {
            Telegram::message($userId, '❗ Неверный формат команды. Используйте /edit (номер) (сумма)')->send();
            return;
        }

        $user = User::where('telegram_id', $userId)->first();
        if (!$user) {
            Telegram::message($userId, '❗ Пользователь не найден')->send();
            return;
        }

        $operations = Operation::where('user_id', $user->telegram_id)
            ->where('occurred_at', '>=', now()->subDays(7))
            ->where('status', 'confirmed')
            ->orderByDesc('occurred_at')
            ->get();

        if (!isset($operations[$index - 1])) {
            Telegram::message($userId, "❗ Операция номер {$index} не найдена")->send();
            return;
        }

        $operation = $operations[$index - 1];
        $operation->update(['amount' => $newAmount]);

        Telegram::message($userId, "✅ Операция номер {$index} обновлена. Новая сумма: {$newAmount}")->send();
    }
}
