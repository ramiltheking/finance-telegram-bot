<?php

use App\Http\Controllers\RobokassaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/webhook', [WebhookController::class, 'webhookHandler']);

Route::post('/robokassa/result', [RobokassaController::class, 'result'])->name('robokassa.result');
