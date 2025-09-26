<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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

        return view('miniapp.settings', compact('settings'));
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
