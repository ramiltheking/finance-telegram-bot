<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReminderService;

class SendDailyRemindersCommand extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Отправка ежедневных напоминаний пользователям';

    public function handle(ReminderService $service)
    {
        $service->sendDailyReminders();
        $this->info('Напоминания обработаны.');
    }
}
