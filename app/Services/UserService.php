<?php

namespace App\Services;

use App\Models\User;
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

            if ($dirty) {
                $user->save();
                Log::info("Пользователю {$user->telegram_id} обновлены данные в БД: ", $fieldsToCheck);
            }
        }

        return $user;
    }
}
