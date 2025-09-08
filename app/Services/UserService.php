<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class UserService
{
    public static function registerOrUpdate(array $tgUser): User
    {
        $user = User::updateOrCreate(
            ['telegram_id' => $tgUser['id']],
            [
                'username' => $tgUser['username'] ?? null,
                'first_name' => $tgUser['first_name'] ?? null,
                'last_name' => $tgUser['last_name'] ?? null,
            ]
        );

        if ($user->wasRecentlyCreated) {
            $user->trial_started_at = now();
            $user->trial_ends_at = now()->addDays(14);
            $user->subscription_status = 'trial';
            $user->save();
        }

        return $user;
    }
}
