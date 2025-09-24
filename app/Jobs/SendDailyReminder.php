<?php

namespace App\Jobs;

use App\Facades\Telegram;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendDailyReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $now = Carbon::now('UTC');

        $users = User::with('settings')
            ->whereHas('settings', fn($q) => $q->where('reminders_enabled', true))
            ->get();

        foreach ($users as $user) {
            $settings = $user->settings;

            if (!$settings) {
                continue;
            }

            $timezone = $settings->timezone ?? 'UTC';
            $hour     = $settings->reminder_hour ?? 22;
            $minute   = $settings->reminder_minute ?? 0;

            $userTime = $now->copy()->setTimezone($timezone);

            if ($userTime->hour == $hour && $userTime->minute == $minute) {
                Telegram::message($user->telegram_id, __('messages.reminder', locale: $user->settings->language))->send();

                Log::info("Напоминание отправлено пользователю {$user->telegram_id} ({$timezone}) в {$hour}:{$minute}");
            }
        }
    }
}
