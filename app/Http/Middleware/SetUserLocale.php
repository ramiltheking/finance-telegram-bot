<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetUserLocale
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $lang = Auth::user()->settings?->language ?? 'ru';
            App::setLocale($lang);
        }

        return $next($request);
    }
}
