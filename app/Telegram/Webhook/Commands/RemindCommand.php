<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Jobs\SendTelegramReminder;
use App\Models\Reminder;
use App\Telegram\Webhook\Webhook;
use Carbon\Carbon;

class RemindCommand extends Webhook
{
    public function run()
    {
        $userId = $this->chat_id;
        $messageText = trim(str_replace('/remind', '', $this->request->input('message.text')));

        if (!$messageText) {
            Telegram::message($userId, '❗ Введите команду в виде /remind HH:MM [текст напоминания]', $this->message_id)->send();
            return;
        }

        $parts = explode(' ', $messageText, 2);
        $timePart = $parts[0];
        $customText = $parts[1] ?? null;

        [$hours, $minutes] = array_pad(explode(':', $timePart), 2, null);

        if (!is_numeric($hours) || !is_numeric($minutes)) {
            Telegram::message($userId, '❗ Неверный формат времени. Используйте HH:MM.', $this->message_id)->send();
            return;
        }

        $hours = (int) $hours;
        $minutes = (int) $minutes;

        if ($hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59) {
            Telegram::message($userId, '❗ Неверное время. Используйте HH:MM.', $this->message_id)->send();
            return;
        }

        $time = sprintf('%02d:%02d:00', $hours, $minutes);

        $text = '🔔 Напоминание: ' . $customText ?: '🔔 Напоминание: Заполните расходы/доходы';

        $reminder = Reminder::create([
            'user_id' => $userId,
            'time' => $time,
            'text' => $text
        ]);

        $sendAt = Carbon::today()->setTime($hours, $minutes, 0);
        if ($sendAt->lt(now())) {
            $sendAt->addDay();
        }

        SendTelegramReminder::dispatch($userId, $text)->delay($sendAt);

        $displayTime = $sendAt->format('H:i');
        Telegram::message($userId, "✅ Напоминание установлено на {$displayTime}")->send();
    }
}
