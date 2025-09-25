<?php

use App\Facades\Telegram;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MiniAppController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TarifsController;
use App\Http\Controllers\RobokassaController;
use App\Http\Controllers\SettingsController;
use App\Http\Middleware\SetUserLocale;
use App\Http\Middleware\TelegramAuth;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/miniapp/auth', [MiniAppController::class, 'auth']);

Route::prefix('miniapp')->middleware([TelegramAuth::class, SetUserLocale::class])->group(function () {
    Route::get('/', [MiniAppController::class, 'dashboard'])->name('miniapp.index');
    Route::post('/dashboard/data', [MiniAppController::class, 'dashboardData']);

    Route::view('/profile', 'miniapp.profile')->name('miniapp.profile');
    Route::post('/profile/data', [ProfileController::class, 'profileData']);
    Route::post('/detect-timezone', [SettingsController::class, 'detectTimezone']);
    Route::post('/profile/delete', [ProfileController::class, 'delete'])->name('profile.delete');

    Route::get('/settings', [SettingsController::class, 'index'])->name('miniapp.settings');
    Route::post('/settings/update', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/tarifs', [TarifsController::class, 'index'])->name('tarifs');

    Route::get('/export/{format}', [ExportController::class, 'export'])->name('miniapp.export');
});

Route::post('/robokassa/result', [RobokassaController::class, 'result'])->name('robokassa.result');
Route::get('/robokassa/success', [RobokassaController::class, 'success'])->name('robokassa.success');
Route::get('/robokassa/fail', [RobokassaController::class, 'fail'])->name('robokassa.fail');

Route::get('/webhook-data', function () {
    dd(Cache::get('webhook-data'));
});
