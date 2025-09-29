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

Route::get('/telegram-required', function () {
    return view('telegram_required');
})->name('telegram.required');

Route::prefix('miniapp')->middleware([TelegramAuth::class, SetUserLocale::class])->group(function () {
    Route::get('/', [MiniAppController::class, 'dashboard'])->name('miniapp.index');
    Route::post('/dashboard/data', [MiniAppController::class, 'dashboardData']);

    Route::get('/profile', [ProfileController::class, 'index'])->name('miniapp.profile');
    Route::post('/profile/data', [ProfileController::class, 'profileData']);
    Route::post('/detect-timezone', [SettingsController::class, 'detectTimezone']);
    Route::post('/profile/delete', [ProfileController::class, 'delete'])->name('profile.delete');

    Route::get('/settings', [SettingsController::class, 'index'])->name('miniapp.settings');
    Route::post('/settings/update', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/subscription', [SettingsController::class, 'subscriptionManagement'])->name('settings.subscription');
    Route::get('/settings/subscription/details', [SettingsController::class, 'getSubscriptionDetails'])->name('settings.subscription.details');
    Route::post('/settings/subscription/enable-recurring', [SettingsController::class, 'enableRecurring'])->name('settings.subscription.enable-recurring');
    Route::post('/settings/subscription/disable-recurring', [SettingsController::class, 'disableRecurring'])->name('settings.subscription.disable-recurring');

    Route::get('/tarifs', [TarifsController::class, 'index'])->name('miniapp.tarifs');

    Route::get('/export/{format}', [ExportController::class, 'export'])->name('miniapp.export');
});

Route::get('/robokassa/success', [RobokassaController::class, 'success'])->name('robokassa.success');
Route::get('/robokassa/fail', [RobokassaController::class, 'fail'])->name('robokassa.fail');

Route::post('/robokassa/enable-recurring', [RobokassaController::class, 'enableRecurring'])->name('robokassa.enable-recurring');

Route::prefix('recurring')->group(function () {
    Route::post('/process', [RobokassaController::class, 'processRecurringPayments'])->name('recurring.process');
    Route::get('/status/{userId}', [RobokassaController::class, 'getRecurringStatus'])->name('recurring.status');
});

Route::get('/webhook-data', function () {
    dd(Cache::get('webhook-data'));
});
