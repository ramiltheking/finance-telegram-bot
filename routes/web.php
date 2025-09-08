<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/set-webhook', function () {
    $botToken = env('TELEGRAM_BOT_TOKEN');
    $webhookUrl = env('APP_URL') . '/api/webhook';

    $response = Http::post("https://api.telegram.org/bot{$botToken}/setWebhook", [
        'url' => $webhookUrl,
    ]);

    return $response->json();
});
