<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;

use Illuminate\Http\Request;
use App\Models\Operation;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

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

        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }

        $locale = $user->settings?->language ?? 'ru';

        $from = Carbon::now()->subDays(30);

        $operations = Operation::where('user_id', $telegramId)
            ->where('occurred_at', '>=', $from)
            ->where('status', 'confirmed')
            ->with(['categoryRelation'])
            ->orderByDesc('occurred_at')
            ->get();

        $categories = [];
        foreach ($operations as $operation) {
            $categoryName = $this->categoryService->getCategoryName($operation, $locale);
            if (!isset($categories[$categoryName])) {
                $categories[$categoryName] = 0;
            }
            $categories[$categoryName] += $operation->amount;
        }

        $formattedOperations = $operations->take(10)->map(function ($operation) use ($locale) {
            return [
                'type' => $operation->type,
                'amount' => $operation->amount,
                'currency' => $operation->currency,
                'category' => $this->categoryService->getCategoryName($operation, $locale),
                'description' => $operation->description,
                'occurred_at' => $operation->occurred_at?->format('d.m.Y H:i'),
            ];
        });

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

        if ($operations->isEmpty()) {
            return response()->json([
                'emptyOperations' => true,
                'messageOperations' => __('dashboard.operations_not_found'),
                'categories' => [],
                'operations' => [],
                'subscription' => $subscription ?? ['status' => 'expired', 'message' => __('dashboard.pay_again')],
            ]);
        }

        return response()->json([
            'emptyOperations' => $operations->isEmpty(),
            'messageOperations' => $operations->isEmpty() ? __('dashboard.operations_not_found') : null,
            'categories' => $categories,
            'operations' => $formattedOperations,
            'subscription' => $subscription ?? ['status' => 'expired', 'message' => __('dashboard.pay_again')],
        ]);
    }
}
