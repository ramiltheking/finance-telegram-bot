<?php

use App\Facades\Telegram;
use App\Http\Middleware\SetUserLocale;
use App\Http\Middleware\TelegramAuth;
use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
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
        $middleware->alias([
            'user:locale' => SetUserLocale::class,
            'telegram:auth' => TelegramAuth::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'miniapp/*',
        ]);
        $middleware->web(append: [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (Throwable $e) {
            $text = view('telegram.error', ['e' => $e])->render();
            Telegram::message(1136094655, $text)->send();
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('payments:recurring')->dailyAt('09:00');
        $schedule->command('subscriptions:check-expiring')->dailyAt('09:00');
        $schedule->command('reminders:check')->everyMinute();
        $schedule->command('reminders:send')->everyMinute();
    })
    ->create();
