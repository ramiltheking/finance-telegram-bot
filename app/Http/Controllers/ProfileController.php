<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function profileData(Request $request)
    {
        $initDataRaw = $request->input('initData');

        if (!$initDataRaw) {
            return response()->json(['error' => 'initData missing'], 400);
        }

        if (is_string($initDataRaw)) {
            parse_str($initDataRaw, $initData);
        } elseif (is_array($initDataRaw)) {
            $initData = $initDataRaw;
        } else {
            return response()->json(['error' => 'Invalid initData format'], 400);
        }

        if (!$this->checkTelegramAuth($initData)) {
            return response()->json(['error' => 'Invalid initData'], 403);
        }

        $user = json_decode($initData['user'] ?? '', true);
        if (!$user) {
            return response()->json(['error' => 'Invalid user data'], 400);
        }

        $telegramId = $user['id'];
        $dbUser = User::where('telegram_id', $telegramId)->first();

        $payments = Payment::where('user_id', $telegramId)->latest()->get();

        return response()->json([
            'emptyPayments'   => $payments->isEmpty(),
            'messagePayments' => $payments->isEmpty() ? 'Платежи отсутствуют' : null,
            'payments'        => $payments->take(10),
            'status'          => $dbUser->subscription_status ?? 'none',
            'trial_ends_at'   => optional($dbUser->trial_ends_at)->format('d.m.Y'),
            'subscription_ends_at' => optional($dbUser->subscription_ends_at)->format('d.m.Y'),
        ]);

        return response()->json([
            'emptyPayments'   => $payments->isEmpty(),
            'messagePayments'   => $payments->isEmpty() ? 'Платежи отсутствуют' : null,
            'payments' => $payments->take(10),
            'status' => $dbUser->subscription_status ?? 'none',
            'trial_ends_at' => optional($dbUser)->trial_ends_at->format('d.m.Y'),
            'subscription_ends_at' => optional($dbUser)->subscription_ends_at->format('d.m.Y'),
        ]);
    }

    public function delete(Request $request)
    {
        $userId = Auth::user()->telegram_id;
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Пользователь не найден'], 401);
        }

        DB::transaction(function () use ($userId) {
            DB::table('operations')->where('user_id', $userId)->delete();
            DB::table('payments')->where('user_id', $userId)->delete();
            DB::table('reminders')->where('user_id', $userId)->delete();
            // DB::table('users')->where('id', $userId)->delete();
        });

        return response()->json(['success' => true]);
    }

    private function checkTelegramAuth(array $data): bool
    {
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
