<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SetUserLocale
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $lang = 'ru';

            if (Auth::check() && Auth::user()) {
                if (Auth::user()->settings && Auth::user()->settings->language) {
                    $lang = Auth::user()->settings->language;
                } elseif (Auth::user()->language_code) {
                    $lang = Auth::user()->language_code;
                }
            }

            $supportedLocales = ['ru', 'en', 'kz'];
            if (in_array($lang, $supportedLocales)) {
                App::setLocale($lang);
            } else {
                App::setLocale('ru');
            }

        } catch (\Exception $e) {
            App::setLocale('ru');
            Log::error('Locale setting error: ' . $e->getMessage());
        }

        return $next($request);
    }
}
