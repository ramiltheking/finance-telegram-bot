<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
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
            Telegram::message($userId, trans('commands.remind.invalid_format'), $this->message_id)->send();
            return;
        }

        $parts = explode(' ', $messageText, 2);
        $timePart = $parts[0];
        $customText = $parts[1] ?? null;

        [$hours, $minutes] = array_pad(explode(':', $timePart), 2, null);

        if (!is_numeric($hours) || !is_numeric($minutes)) {
            Telegram::message($userId, trans('commands.remind.time_format_error'), $this->message_id)->send();
            return;
        }

        $hours = (int) $hours;
        $minutes = (int) $minutes;

        if ($hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59) {
            Telegram::message($userId, trans('commands.remind.time_range_error'), $this->message_id)->send();
            return;
        }

        $time = sprintf('%02d:%02d:00', $hours, $minutes);

        $existingReminder = Reminder::where('user_id', $userId)
            ->where('time', $time)
            ->where('status', 'pending')
            ->first();

        if ($existingReminder) {
            $reminderText = $customText
                ? trans('commands.remind.custom_text', ['text' => $customText])
                : trans('commands.remind.default_text');

            $existingReminder->update(['text' => $reminderText]);
        } else {
            $reminderText = $customText
                ? trans('commands.remind.custom_text', ['text' => $customText])
                : trans('commands.remind.default_text');

            Reminder::create([
                'user_id' => $userId,
                'time' => $time,
                'text' => $reminderText,
                'status' => 'pending',
            ]);
        }

        $displayTime = Carbon::createFromTime($hours, $minutes)->format('H:i');
        Telegram::message($userId, trans('commands.remind.success', ['time' => $displayTime]))->send();
    }
}
