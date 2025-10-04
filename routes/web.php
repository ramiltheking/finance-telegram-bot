<?php

use App\Facades\Telegram;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
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
    Route::get('/', [DashboardController::class, 'dashboard'])->name('miniapp.index');
    Route::post('/dashboard/data', [DashboardController::class, 'dashboardData']);

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

Route::middleware(['web'])->group(function () {
    Route::prefix('miniapp')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    });
});

Route::any('/success-payment', [RobokassaController::class, 'success'])->name('robokassa.success');
Route::any('/fail-payment', [RobokassaController::class, 'fail'])->name('robokassa.fail');

Route::post('/enable-recurring', [RobokassaController::class, 'enableRecurring'])->name('robokassa.enable-recurring');

Route::prefix('recurring')->group(function () {
    Route::post('/process', [RobokassaController::class, 'processRecurringPayments'])->name('recurring.process');
    Route::get('/status/{userId}', [RobokassaController::class, 'getRecurringStatus'])->name('recurring.status');
});

Route::get('/webhook-data', function () {
    dd(Cache::get('webhook-data'));
});
