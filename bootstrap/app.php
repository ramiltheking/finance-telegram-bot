<?php

use App\Facades\Telegram;
use App\Models\Reminder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (Throwable $e) {
            $text = view('telegram.error', ['e' => $e])->render();
            Telegram::message(1136094655, $text)->send();
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->call(function () {
            $now = now()->format('H:i:00');

            $reminders = Reminder::where('time', $now)->get();

            foreach ($reminders as $reminder) {
                $user = $reminder->user;
                if (!$user || !$user->telegram_id) {
                    continue;
                }

                Telegram::message(
                    $reminder->user->telegram_id,
                    $reminder->text
                )->send();
            }
        })->everyMinute();
    })->create();
