<?php

use App\Facades\Telegram;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MiniAppController;
use App\Http\Controllers\TarifsController;
use App\Http\Controllers\RobokassaController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/miniapp/auth', [MiniAppController::class, 'auth']);

Route::get('/miniapp', [MiniAppController::class, 'index'])->name('miniapp.index');
Route::post('/miniapp/data', [MiniAppController::class, 'data']);

Route::view('/miniapp/profile', 'miniapp.profile');
Route::post('/miniapp/profile/data', [MiniAppController::class, 'profileData']);

Route::get('/miniapp/tarifs', [TarifsController::class, 'index'])->name('tarifs');

Route::post('/robokassa/result', [RobokassaController::class, 'result'])->name('robokassa.result');
Route::get('/robokassa/success', [RobokassaController::class, 'success'])->name('robokassa.success');
Route::get('/robokassa/fail', [RobokassaController::class, 'fail'])->name('robokassa.fail');

Route::get('/miniapp/export/{format}', [MiniAppController::class, 'export'])->name('miniapp.export');

Route::get('/webhook-data', function() {
    dd(Cache::get('webhook-data'));
});
