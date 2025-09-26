<?php

namespace App\Http\Middleware;

use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class TelegramAuth
{
    public function handle(Request $request, Closure $next)
    {
        $initDataRaw = $request->input('initData') ?? $request->header('X-Init-Data');

        if (Auth::check()) {
            return $next($request);
        }

        if ($initDataRaw && $this->checkTelegramAuth($initDataRaw)) {
            parse_str($initDataRaw, $data);
            $userData = json_decode($data['user'], true);

            if ($userData) {
                $user = UserService::registerOrUpdate($userData);
                Auth::login($user, true);

                return $next($request)->withCookie(cookie(
                    'telegram_auth',
                    $initDataRaw,
                    60 * 24 * 30,
                    null,
                    null,
                    false,
                    true,
                    false,
                    'None'
                ));
            }
        }

        if ($request->hasCookie('telegram_auth')) {
            $initDataRaw = $request->cookie('telegram_auth');
            if ($this->checkTelegramAuth($initDataRaw)) {
                parse_str($initDataRaw, $data);
                $userData = json_decode($data['user'], true);

                if ($userData) {
                    $user = UserService::registerOrUpdate($userData);
                    Auth::login($user, true);
                }
            }
        }

        return $next($request);
    }

    private function checkTelegramAuth(string $initDataRaw): bool
    {
        parse_str($initDataRaw, $data);

        if (!isset($data['hash'])) {
            return false;
        }

        $botToken = config('services.telegram.bot_token');
        if (!$botToken) {
            return false;
        }

        $hash = $data['hash'];
        unset($data['hash']);

        ksort($data);
        $checkString = collect($data)
            ->map(fn($v, $k) => "$k=$v")
            ->implode("\n");

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $calculatedHash = hash_hmac('sha256', $checkString, $secretKey);

        return hash_equals($hash, $calculatedHash);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true])->withoutCookie('telegram_auth');
    }
}
