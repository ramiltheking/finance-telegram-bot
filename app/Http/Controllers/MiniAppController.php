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
    public function dashboard(Request $request)
    {
        return view('miniapp.dashboard');
    }

    public function dashboardData(Request $request)
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

        $locale = Auth::user()->settings?->language ?? 'ru';

        $categoryMapById = Category::pluck('name_ru', 'id')->toArray();
        $categoryMapByName = $locale === 'ru' ? Category::pluck('name_ru', 'name_en')->toArray() : Category::pluck('name_en', 'name_en')->toArray();

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
                'status' => $user->subscription_status,
                'trial_started_at' => $user->trial_started_at?->format('d.m.Y'),
                'trial_ends_at' => $user->trial_ends_at?->format('d.m.Y'),
                'subscription_started_at' => $user->subscription_started_at?->format('d.m.Y'),
                'subscription_ends_at' => $user->subscription_ends_at?->format('d.m.Y'),
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
                'messageOperations' => __('dashboard.operations_not_found'),
                'categories' => [],
                'operations' => [],
                'payments' => [],
                'subscription' => $subscription ?? ['status' => 'expired', 'message' => __('dashboard.pay_again')],
            ]);
        }

        return response()->json([
            'emptyOperations' => $operations->isEmpty(),
            'messageOperations' => $operations->isEmpty() ? __('dashboard.operations_not_found') : null,
            'categories' => $operations->isEmpty() ? [] : $categories,
            'operations' => $operations->take(10),
            'payments' => $payments->take(10),
            'subscription' => $subscription ?? ['status' => 'expired', 'message' => __('dashboard.pay_again')],
        ]);
    }
}
