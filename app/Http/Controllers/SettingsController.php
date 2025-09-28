<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSetting;
use App\Services\RecurringPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('miniapp.index')->withErrors(['auth' => 'Пользователь не аутентифицирован']);
        }

        $settings = UserSetting::firstOrCreate([
            'user_id' => $user->telegram_id,
        ], [
            'currency' => 'KZT',
            'language' => 'ru',
            'timezone' => 'Asia/Almaty',
            'reminders_enabled' => true,
        ]);

        $subscriptionInfo = $this->getSubscriptionInfo($user);

        return view('miniapp.settings', compact('settings', 'subscriptionInfo'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Пользователь не аутентифицирован'], 401);
        }

        $settings = UserSetting::firstOrCreate([
            'user_id' => $user->telegram_id,
        ], [
            'currency' => 'KZT',
            'language' => 'ru',
            'timezone' => 'Asia/Almaty',
            'reminders_enabled' => true,
        ]);

        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'nullable',
        ]);

        $key = $validated['key'];
        $value = $validated['value'];

        if ($key === 'recurring_enabled') {
            return $this->updateRecurringSetting($user, $value);
        }

        if (!in_array($key, ['currency', 'language', 'timezone', 'reminders_enabled', 'reminder_hour', 'reminder_minute'])) {
            return response()->json(['error' => 'Invalid setting key'], 422);
        }

        if ($key === 'reminders_enabled') {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        $settings->update([$key => $value]);

        return response()->json([
            'success' => true,
            'key' => $key,
            'value' => $settings->$key
        ]);
    }

    public function updateRecurringSetting(User $user, $enabled)
    {
        $enabled = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);

        try {
            if ($enabled) {
                if (!$user->recurring_token) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Для включения автопродления необходимо совершить хотя бы один платеж',
                        'requires_payment' => true
                    ]);
                }

                $user->update([
                    'recurring_enabled' => true,
                    'recurring_activated_at' => now(),
                ]);

                Log::info('Recurring payments enabled', ['user_id' => $user->telegram_id]);

            } else {
                $user->update([
                    'recurring_enabled' => false,
                ]);

                Log::info('Recurring payments disabled', ['user_id' => $user->telegram_id]);
            }

            return response()->json([
                'success' => true,
                'message' => $enabled ? 'Автопродление включено' : 'Автопродление отключено',
                'recurring_enabled' => $user->recurring_enabled
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update recurring setting', [
                'user_id' => $user->telegram_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении настроек автопродления'
            ], 500);
        }
    }

    private function getSubscriptionInfo(User $user)
    {
        $info = [
            'status' => $user->subscription_status,
            'is_active' => $user->subscription_status === 'active',
            'recurring_enabled' => $user->recurring_enabled,
            'has_recurring_token' => !empty($user->recurring_token),
            'next_payment_date' => null,
            'days_until_payment' => null,
        ];

        if ($user->subscription_ends_at) {
            $info['subscription_ends_at'] = $user->subscription_ends_at->format('d.m.Y');
            $info['days_until_end'] = now()->diffInDays($user->subscription_ends_at, false);
        }

        if ($user->next_payment_date) {
            $info['next_payment_date'] = $user->next_payment_date->format('d.m.Y');
            $info['days_until_payment'] = now()->diffInDays($user->next_payment_date, false);
        }

        return $info;
    }

    public function getSubscriptionDetails()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Пользователь не аутентифицирован'], 401);
        }

        $subscriptionInfo = $this->getSubscriptionInfo($user);

        return response()->json([
            'success' => true,
            'subscription' => $subscriptionInfo
        ]);
    }

    public function detectTimezone(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Пользователь не аутентифицирован'], 401);
        }

        $lat = $request->lat;
        $lon = $request->lon;

        $apiKey = env('TIMEZONEDB_API_KEY');
        $url = "http://api.timezonedb.com/v2.1/get-time-zone?key={$apiKey}&format=json&by=position&lat={$lat}&lng={$lon}";

        $response = Http::get($url)->json();

        return response()->json([
            'timezone' => $response['zoneName'] ?? null
        ]);
    }
}
