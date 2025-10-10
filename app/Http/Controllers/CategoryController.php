<?php

namespace App\Http\Controllers;

use App\Models\UserCategory;
use App\Models\Operation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $telegramId = $user->telegram_id;

        $categories = UserCategory::where('user_id', $telegramId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $telegramId = $user->telegram_id;

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:INCOME,EXPENSE',
            'name' => 'required|string|max:50|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'validation_error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $name = trim($request->name);

        if (empty($name)) {
            return response()->json([
                'success' => false,
                'error' => 'validation_error',
                'message' => 'Название категории не может быть пустым'
            ], 422);
        }

        $existingCategory = UserCategory::where('user_id', $telegramId)
            ->where('name', $name)
            ->where('type', $request->type)
            ->first();

        if ($existingCategory) {
            return response()->json([
                'success' => false,
                'error' => 'category_exists',
                'message' => 'Категория с таким названием уже существует'
            ], 422);
        }

        try {
            $category = UserCategory::create([
                'user_id' => $telegramId,
                'type' => $request->type,
                'name' => $name,
                'title' => $request->title ? trim($request->title) : null,
            ]);

            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        } catch (\Exception $e) {
            Log::error('Category creation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'database_error',
                'message' => 'Ошибка при создании категории'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $telegramId = $user->telegram_id;

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:INCOME,EXPENSE',
            'name' => 'required|string|max:50|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'validation_error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $category = UserCategory::where('id', $id)
            ->where('user_id', $telegramId)
            ->firstOrFail();

        $name = trim($request->name);

        if (empty($name)) {
            return response()->json([
                'success' => false,
                'error' => 'validation_error',
                'message' => 'Название категории не может быть пустым'
            ], 422);
        }

        $isTypeChanged = $category->type !== $request->type;

        if ($isTypeChanged) {
            $hasOperationsWithCurrentType = Operation::where('user_id', $telegramId)
                ->where('category', $category->name)
                ->where('category_type', 'custom')
                ->where('type', strtolower($category->type))
                ->where('status', 'confirmed')
                ->exists();

            if ($hasOperationsWithCurrentType) {
                return response()->json([
                    'success' => false,
                    'error' => 'category_has_operations',
                    'message' => 'Невозможно изменить тип категории, так как существуют операции с текущим типом'
                ], 422);
            }
        }

        $existingCategory = UserCategory::where('user_id', $telegramId)
            ->where('name', $name)
            ->where('type', $request->type)
            ->where('id', '!=', $id)
            ->first();

        if ($existingCategory) {
            return response()->json([
                'success' => false,
                'error' => 'category_exists',
                'message' => 'Категория с таким названием уже существует'
            ], 422);
        }

        try {
            $oldName = $category->name;
            $oldType = $category->type;

            $category->update([
                'type' => $request->type,
                'name' => $name,
                'title' => $request->title ? trim($request->title) : null,
            ]);

            if ($oldName !== $name) {
                Operation::where('user_id', $telegramId)
                    ->where('category', $oldName)
                    ->where('category_type', 'custom')
                    ->update(['category' => $name]);
            }

            if ($isTypeChanged) {
                Operation::where('user_id', $telegramId)
                    ->where('category', $name)
                    ->where('category_type', 'custom')
                    ->update(['type' => strtolower($request->type)]);
            }

            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        } catch (\Exception $e) {
            Log::error('Category update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'database_error',
                'message' => 'Ошибка при обновлении категории'
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $telegramId = $user->telegram_id;

        $category = UserCategory::where('id', $id)
            ->where('user_id', $telegramId)
            ->firstOrFail();

        $usedInOperations = Operation::where('user_id', $telegramId)
            ->where('category', $category->name)
            ->where('category_type', 'custom')
            ->where('status', 'confirmed')
            ->exists();

        if ($usedInOperations) {
            return response()->json([
                'success' => false,
                'error' => 'category_in_use',
                'message' => 'Категория используется в операциях и не может быть удалена'
            ], 422);
        }

        try {
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Категория успешно удалена'
            ]);
        } catch (\Exception $e) {
            Log::error('Category deletion error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'database_error',
                'message' => 'Ошибка при удалении категории'
            ], 500);
        }
    }

    public function getCategoryStats($id)
    {
        $user = Auth::user();
        $telegramId = $user->telegram_id;

        $category = UserCategory::where('id', $id)
            ->where('user_id', $telegramId)
            ->firstOrFail();

        $operationsStats = Operation::where('user_id', $telegramId)
            ->where('category', $category->name)
            ->where('category_type', 'custom')
            ->where('status', 'confirmed')
            ->selectRaw('type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        return response()->json([
            'success' => true,
            'stats' => [
                'category' => $category,
                'operations' => [
                    'income' => [
                        'count' => $operationsStats->get('income')->count ?? 0,
                        'total' => $operationsStats->get('income')->total ?? 0
                    ],
                    'expense' => [
                        'count' => $operationsStats->get('expense')->count ?? 0,
                        'total' => $operationsStats->get('expense')->total ?? 0
                    ]
                ]
            ]
        ]);
    }

    public function bulkUpdateOperationsType(Request $request, $id)
    {
        $user = Auth::user();
        $telegramId = $user->telegram_id;

        $validator = Validator::make($request->all(), [
            'new_type' => 'required|in:INCOME,EXPENSE',
            'update_existing_operations' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'validation_error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $category = UserCategory::where('id', $id)
            ->where('user_id', $telegramId)
            ->firstOrFail();

        $updateOperations = $request->boolean('update_existing_operations', false);

        if (!$updateOperations) {
            $hasConflictingOperations = Operation::where('user_id', $telegramId)
                ->where('category', $category->name)
                ->where('category_type', 'custom')
                ->where('type', '!=', strtolower($request->new_type))
                ->where('status', 'confirmed')
                ->exists();

            if ($hasConflictingOperations) {
                return response()->json([
                    'success' => false,
                    'error' => 'conflicting_operations',
                    'message' => 'Существуют операции с противоположным типом. Хотите ли вы изменить их тип?',
                    'conflicting_operations_count' => Operation::where('user_id', $telegramId)
                        ->where('category', $category->name)
                        ->where('category_type', 'custom')
                        ->where('type', '!=', strtolower($request->new_type))
                        ->where('status', 'confirmed')
                        ->count()
                ], 422);
            }
        }

        try {
            $category->update(['type' => $request->new_type]);

            if ($updateOperations) {
                Operation::where('user_id', $telegramId)
                    ->where('category', $category->name)
                    ->where('category_type', 'custom')
                    ->update(['type' => strtolower($request->new_type)]);
            }

            return response()->json([
                'success' => true,
                'category' => $category,
                'operations_updated' => $updateOperations
            ]);
        } catch (\Exception $e) {
            Log::error('Category bulk update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'database_error',
                'message' => 'Ошибка при обновлении категории и операций'
            ], 500);
        }
    }
}
