<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;
use App\Facades\Telegram;

class CheckReminders extends Command
{
    protected $signature = 'reminders:check';
    protected $description = 'Проверка и отправка напоминаний пользователям';

    public function handle()
    {
        $now = now()->format('H:i:00');

        $reminders = Reminder::where('time', $now)
            ->where('status', 'pending')
            ->get();

        foreach ($reminders as $reminder) {
            Telegram::message($reminder->user_id, $reminder->text)->send();
            $reminder->update(['status' => 'send']);
            $this->info("Напоминание отправлено пользователю {$reminder->user_id} ({$reminder->time})");
        }

        return Command::SUCCESS;
    }
}
