<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class UserService
{
    public static function registerOrUpdate(array $tgUser): User
    {
        $user = User::where('telegram_id', $tgUser['id'])->first();

        if (!$user) {
            $user = User::create([
                'telegram_id'         => $tgUser['id'],
                'username'            => $tgUser['username'] ?? null,
                'first_name'          => $tgUser['first_name'] ?? null,
                'last_name'           => $tgUser['last_name'] ?? null,
                'trial_started_at'    => now(),
                'trial_ends_at'       => now()->addDays(14),
                'subscription_status' => 'trial',
            ]);
        } else {
            $fieldsToCheck = [
                'username'   => $tgUser['username'] ?? null,
                'first_name' => $tgUser['first_name'] ?? null,
                'last_name'  => $tgUser['last_name'] ?? null,
            ];

            $dirty = false;
            foreach ($fieldsToCheck as $field => $value) {
                if ($user->{$field} !== $value) {
                    $user->{$field} = $value;
                    $dirty = true;
                }
            }

            $now = now();

            if ($user->subscription_status === 'trial' && $user->trial_ends_at && $user->trial_ends_at->lt($now)) {
                $user->subscription_status = 'expired';
                $dirty = true;
                Log::info("Пробный период истёк у пользователя {$user->telegram_id}");
            }

            if ($user->subscription_status === 'active' && $user->subscription_ends_at && $user->subscription_ends_at->lt($now)) {
                $user->subscription_status = 'expired';
                $dirty = true;
                Log::info("Подписка истекла у пользователя {$user->telegram_id}");
            }

            if ($dirty) {
                $user->save();
                Log::info("Пользователю {$user->telegram_id} обновлены данные в БД: ", $fieldsToCheck);
            }
        }

        UserSetting::firstOrCreate([
            'user_id' => $user->telegram_id,
        ], [
            'currency' => 'KZT',
            'language' => 'ru',
            'timezone' => 'Asia/Almaty',
            'reminders_enabled' => true,
        ]);

        if ($user && $user->settings) {
            App::setLocale($user->settings->language ?? 'ru');
        } else {
            App::setLocale('ru');
        }

        return $user;
    }

    public static function hasAccess(User $user): bool
    {
        return in_array($user->subscription_status, ['trial', 'active']);
    }
}
