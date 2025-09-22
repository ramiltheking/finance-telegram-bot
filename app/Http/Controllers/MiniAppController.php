<?php

namespace App\Http\Controllers;

use App\Facades\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\UserService;
use App\Models\Operation;
use App\Models\Payment;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;

class MiniAppController extends Controller
{
    public function index(Request $request)
    {
        return view('miniapp.index');
    }

    public function data(Request $request)
    {
        $initData = $request->input('initData');

        if (!$initData) {
            return response()->json(['error' => 'initData missing'], 400);
        }

        parse_str($initData, $data);

        $userData = json_decode($data['user'], true);
        $telegramId = $userData['id'];

        $from = Carbon::now()->subDays(30);

        $operations = Operation::where('user_id', $telegramId)
            ->where('occurred_at', '>=', $from)
            ->where('status', 'confirmed')
            ->orderByDesc('occurred_at')
            ->get();

        $categoryMapById = Category::pluck('name_ru', 'id')->toArray();
        $categoryMapByName = Category::pluck('name_ru', 'name_en')->toArray();

        $categories = $operations->groupBy('category')->mapWithKeys(function ($ops, $category) use ($categoryMapById, $categoryMapByName) {
            $translated = $categoryMapById[$category] ?? $categoryMapByName[$category] ?? $category;
            return [$translated => $ops->sum('amount')];
        });

        $operations = $operations->map(function ($op) use ($categoryMapById, $categoryMapByName) {
            $cat = $op->category;
            $op->category = $categoryMapById[$cat] ?? $categoryMapByName[$cat] ?? $cat;
            return $op;
        });

        $payments = Payment::where('user_id', $telegramId)->latest()->get();

        $user = User::where('telegram_id', $telegramId)->first();
        $subscription = null;

        if ($user) {
            $subscription = [
                'status'                 => $user->subscription_status,
                'trial_started_at'       => $user->trial_started_at?->format('d.m.Y'),
                'trial_ends_at'          => $user->trial_ends_at?->format('d.m.Y'),
                'subscription_started_at' => $user->subscription_started_at?->format('d.m.Y'),
                'subscription_ends_at'   => $user->subscription_ends_at?->format('d.m.Y'),
            ];

            if ($user->subscription_status === 'trial' && $user->trial_ends_at && $user->trial_ends_at->isPast()) {
                $subscription['status'] = 'expired';
            }

            if ($user->subscription_status === 'active' && $user->subscription_ends_at && $user->subscription_ends_at->isPast()) {
                $subscription['status'] = 'expired';
            }
        }

        if ($operations->isEmpty() && $payments->isEmpty()) {
            return response()->json([
                'emptyOperations' => true,
                'emptyPayments'   => true,
                'messageOperations' => 'Операции отсутствуют',
                'messagePayments'   => 'Платежи отсутствуют',
                'categories'    => [],
                'operations'    => [],
                'payments'      => [],
                'subscription'  => $subscription ?? ['status' => 'expired', 'message' => 'Оплатите тариф'],
            ]);
        }

        return response()->json([
            'emptyOperations' => $operations->isEmpty(),
            'emptyPayments'   => $payments->isEmpty(),
            'messageOperations' => $operations->isEmpty() ? 'Операции отсутствуют' : null,
            'messagePayments'   => $payments->isEmpty() ? 'Платежи отсутствуют' : null,
            'categories'      => $operations->isEmpty() ? [] : $categories,
            'operations'      => $operations->take(10),
            'payments'        => $payments->take(10),
            'subscription'    => $subscription ?? ['status' => 'expired', 'message' => 'Оплатите тариф'],
        ]);
    }

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

    public function auth(Request $request)
    {
        $initDataRaw = $request->input('initData');

        if (!$initDataRaw) {
            return response()->json(['success' => false, 'error' => 'initData missing'], 400);
        }

        if (!is_string($initDataRaw)) {
            $initDataRaw = (string) $initDataRaw;
        }

        parse_str($initDataRaw, $data);

        if (!$this->checkTelegramAuth($data)) {
            return response()->json(['success' => false, 'error' => 'Invalid initData'], 403);
        }

        $userData = json_decode($data['user'], true);

        if (!$userData) {
            return response()->json(['success' => false, 'error' => 'Invalid user data'], 400);
        }

        $user = UserService::registerOrUpdate($userData);

        Auth::login($user);

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
