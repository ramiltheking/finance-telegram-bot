<?php

namespace App\Http\Controllers;

use App\Facades\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\UserService;
use App\Models\Operation;
use App\Models\Payment;
use App\Models\Category;
use App\Models\UserCategory;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
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
            ->with(['categoryRelation'])
            ->orderByDesc('occurred_at')
            ->get();

        $locale = Auth::user()->settings?->language ?? 'ru';

        $systemCategories = Category::all()->keyBy('slug');

        $userCategories = UserCategory::where('user_id', $telegramId)
            ->get()
            ->keyBy('name');

        $categories = [];
        foreach ($operations as $operation) {
            $categoryName = $this->getCategoryName($operation, $systemCategories, $userCategories, $locale);
            if (!isset($categories[$categoryName])) {
                $categories[$categoryName] = 0;
            }
            $categories[$categoryName] += $operation->amount;
        }

        $formattedOperations = $operations->take(10)->map(function ($operation) use ($systemCategories, $userCategories, $locale) {
            return [
                'type' => $operation->type,
                'amount' => $operation->amount,
                'currency' => $operation->currency,
                'category' => $this->getCategoryName($operation, $systemCategories, $userCategories, $locale),
                'description' => $operation->description,
                'occurred_at' => $operation->occurred_at?->format('d.m.Y H:i'),
            ];
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
            'categories' => $categories,
            'operations' => $formattedOperations,
            'payments' => $payments->take(10),
            'subscription' => $subscription ?? ['status' => 'expired', 'message' => __('dashboard.pay_again')],
        ]);
    }

    private function getCategoryName(Operation $operation, $systemCategories, $userCategories, $locale)
    {
        if ($operation->category_type === 'system') {
            $category = $systemCategories->get($operation->category);
            if ($category) {
                return $locale === 'ru' ? $category->name_ru : ($category->name_en ?? $category->name_ru);
            }
        } elseif ($operation->category_type === 'custom') {
            $category = $userCategories->get($operation->category);
            if ($category) {
                return $category->name;
            }
        }

        return $operation->category;
    }
}
