<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Models\Reminder;
use App\Telegram\Webhook\Webhook;

class RemindCommand extends Webhook
{
    public function run()
    {
        $userId = $this->chat_id;
        $text = trim(str_replace('/remind', '', $this->request->input('message.text')));

        if (!$text) {
            Telegram::message($userId, '❗ Введите команду в виде /remind 00:00')->send();
            return;
        }

        [$hours, $minutes] = array_pad(explode(':', $text), 2, null);

        if (!is_numeric($hours) || !is_numeric($minutes)) {
            Telegram::message($userId, '❗ Неверный формат. Используйте HH:MM.')->send();
            return;
        }

        $time = sprintf('%02d:%02d:00', $hours, $minutes);

        Reminder::updateOrCreate(
            ['user_id' => $userId],
            ['time' => $time, 'text' => '🔔 Напоминание: Заполните расходы/доходы']
        );

        Telegram::message($userId, "✅ Напоминание установлено на {$time}")->send();
    }
}
