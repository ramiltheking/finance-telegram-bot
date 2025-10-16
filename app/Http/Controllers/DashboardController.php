<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;

use Illuminate\Http\Request;
use App\Models\Operation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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

        $period = $request->input('period', '30days');
        $page = $request->input('page', 1);
        $perPage = 15;

        switch ($period) {
            case '7days':
                $from = Carbon::now()->subDays(7);
                break;
            case '30days':
                $from = Carbon::now()->subDays(30);
                break;
            case '90days':
                $from = Carbon::now()->subDays(90);
                break;
            case 'all':
                $from = null;
                break;
            default:
                $from = Carbon::now()->subDays(30);
        }

        $query = Operation::where('user_id', $telegramId)
            ->where('status', 'confirmed')
            ->with(['categoryRelation']);

        if ($from) {
            $query->where('occurred_at', '>=', $from);
        }

        $totalOperations = $query->count();

        $operations = $query->orderByDesc('occurred_at')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $chartQuery = Operation::where('user_id', $telegramId)
            ->where('status', 'confirmed');

        if ($from) {
            $chartQuery->where('occurred_at', '>=', $from);
        }

        $operationsForChart = $chartQuery->get();

        $categories = [];
        foreach ($operationsForChart as $operation) {
            $categoryName = $this->categoryService->getCategoryName($operation, $locale);
            if (!isset($categories[$categoryName])) {
                $categories[$categoryName] = 0;
            }
            $categories[$categoryName] += $operation->amount;
        }

        $formattedOperations = $operations->map(function ($operation) use ($locale) {
            return [
                'id' => $operation->id,
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
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $totalOperations,
                    'has_more' => false,
                    'period' => $period
                ]
            ]);
        }

        $hasMore = ($page * $perPage) < $totalOperations;

        return response()->json([
            'emptyOperations' => false,
            'messageOperations' => null,
            'categories' => $categories,
            'operations' => $formattedOperations,
            'subscription' => $subscription ?? ['status' => 'expired', 'message' => __('dashboard.pay_again')],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalOperations,
                'has_more' => $hasMore,
                'period' => $period
            ]
        ]);
    }

    public function delete(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('miniapp.index')->withErrors(['auth' => 'Пользователь не аутентифицирован']);
            }

            $userId = $user->telegram_id;
            $operationId = $request->input('operationId');

            $operation = Operation::findOrFail($operationId);

            if ($operation->user_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }

            $operation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Операция успешно удалена'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка удаления операции'
            ], 500);
        }
    }
}
