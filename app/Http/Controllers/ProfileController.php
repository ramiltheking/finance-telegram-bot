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
        $userId = Auth::user()->telegram_id;

        $dbUser = User::where('telegram_id', $userId)->first();
        $payments = Payment::where('user_id', $userId)->latest()->get();

        return response()->json([
            'emptyPayments'   => $payments->isEmpty(),
            'messagePayments' => $payments->isEmpty() ? __('profile.payments_not_found') : null,
            'payments'        => $payments->take(10),
            'status'          => $dbUser->subscription_status ?? 'none',
            'trial_ends_at'   => optional($dbUser->trial_ends_at)->format('d.m.Y'),
            'subscription_ends_at' => optional($dbUser->subscription_ends_at)->format('d.m.Y'),
        ]);

        return response()->json([
            'emptyPayments'   => $payments->isEmpty(),
            'messagePayments'   => $payments->isEmpty() ? __('profile.payments_not_found') : null,
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
}
