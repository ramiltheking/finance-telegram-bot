<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\TelegramPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function index(Request $request) {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('miniapp.index')->withErrors(['auth' => 'Пользователь не аутентифицирован']);
        }

        return view('miniapp.profile');
    }

    public function profileData(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Пользователь не аутентифицирован'], 401);
        }

        $userId = $user->telegram_id;
        $dbUser = User::where('telegram_id', $userId)->first();
        // $payments = Payment::where('user_id', $userId)->latest()->get();
        $payments = TelegramPayment::where('user_id', $userId)->latest()->get();

        return response()->json([
            'emptyPayments' => $payments->isEmpty(),
            'messagePayments' => $payments->isEmpty() ? __('profile.payments_not_found') : null,
            'payments' => $payments->take(10),
            'status' => $dbUser->subscription_status ?? 'none',
            'trial_ends_at' => $dbUser->trial_ends_at ? $dbUser->trial_ends_at->format('d.m.Y') : null,
            'subscription_ends_at' => $dbUser->subscription_ends_at ? $dbUser->subscription_ends_at->format('d.m.Y') : null,
        ]);
    }

    public function delete(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Пользователь не найден'], 401);
        }

        $userId = Auth::user()->telegram_id;

        DB::transaction(function () use ($userId) {
            DB::table('operations')->where('user_id', $userId)->delete();
            DB::table('payments')->where('user_id', $userId)->delete();
            DB::table('reminders')->where('user_id', $userId)->delete();
            // DB::table('users')->where('telegram_id', $userId)->delete();
        });

        return response()->json(['success' => true]);
    }
}
