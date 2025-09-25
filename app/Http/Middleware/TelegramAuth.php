<?php

namespace App\Http\Middleware;

use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelegramAuth
{
    public function handle(Request $request, Closure $next)
    {
        $initDataRaw = $request->input('initData') ?? $request->header('X-Init-Data');

        if ($initDataRaw && !Auth::check()) {
            parse_str($initDataRaw, $data);

            if ($this->checkTelegramAuth($data)) {
                $userData = json_decode($data['user'], true);

                if ($userData) {
                    $user = UserService::registerOrUpdate($userData);
                    Auth::login($user);
                }
            }
        }

        return $next($request);
    }

    private function checkTelegramAuth(array $data): bool
    {
        if (!isset($data['hash'])) {
            return false;
        }

        $botToken = config('services.telegram.bot_token');
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
}
