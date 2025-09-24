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

Route::get('/', function () {
    return view('welcome');
});

Route::post('/miniapp/auth', [MiniAppController::class, 'auth'])->name('login');

Route::get('/miniapp', [MiniAppController::class, 'index'])->name('miniapp.index');
Route::post('/miniapp/data', [MiniAppController::class, 'data']);

Route::middleware('auth')->group(function () {
    Route::view('/miniapp/profile', 'miniapp.profile')->name('miniapp.profile');
    Route::post('/miniapp/profile/data', [ProfileController::class, 'profileData']);
    Route::post('/miniapp/profile/delete', [ProfileController::class, 'delete'])->name('profile.delete');
});

Route::middleware('auth')->group(function () {
    Route::get('/miniapp/settings', [SettingsController::class, 'index'])->name('miniapp.settings')->middleware(SetUserLocale::class);
    Route::post('/miniapp/settings/update', [SettingsController::class, 'update'])->name('settings.update');
});
Route::post('/detect-timezone', [SettingsController::class, 'detectTimezone']);

Route::get('/miniapp/tarifs', [TarifsController::class, 'index'])->name('tarifs')->middleware('auth');

Route::post('/robokassa/result', [RobokassaController::class, 'result'])->name('robokassa.result');
Route::get('/robokassa/success', [RobokassaController::class, 'success'])->name('robokassa.success');
Route::get('/robokassa/fail', [RobokassaController::class, 'fail'])->name('robokassa.fail');

Route::get('/miniapp/export/{format}', [ExportController::class, 'export'])->name('miniapp.export')->middleware('auth');

Route::get('/webhook-data', function () {
    dd(Cache::get('webhook-data'));
});
