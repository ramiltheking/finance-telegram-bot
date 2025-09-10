<?php

use App\Facades\Telegram;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/webhook-data', function() {
    dd(Cache::get('webhook-data'));
});
