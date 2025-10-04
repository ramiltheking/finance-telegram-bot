<?php

namespace App\Http\Controllers;

use App\Models\UserCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $request->validate([
            'type' => 'required|in:INCOME,EXPENSE',
            'name' => 'required|string|max:50'
        ]);

        $existingCategory = UserCategory::where('user_id', $telegramId)
            ->where('name', $request->name)
            ->where('type', $request->type)
            ->first();

        if ($existingCategory) {
            return response()->json([
                'success' => false,
                'error' => 'category_exists'
            ], 422);
        }

        $category = UserCategory::create([
            'user_id' => $telegramId,
            'type' => $request->type,
            'name' => $request->name,
            'title' => $request->title
        ]);

        return response()->json([
            'success' => true,
            'category' => $category
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $telegramId = $user->telegram_id;

        $request->validate([
            'type' => 'required|in:INCOME,EXPENSE',
            'name' => 'required|string|max:50'
        ]);

        $category = UserCategory::where('id', $id)
            ->where('user_id', $telegramId)
            ->firstOrFail();

        $existingCategory = UserCategory::where('user_id', $telegramId)
            ->where('name', $request->name)
            ->where('type', $request->type)
            ->where('id', '!=', $id)
            ->first();

        if ($existingCategory) {
            return response()->json([
                'success' => false,
                'error' => 'category_exists'
            ], 422);
        }

        $category->update([
            'type' => $request->type,
            'name' => $request->name,
            'title' => $request->title
        ]);

        return response()->json([
            'success' => true,
            'category' => $category
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $telegramId = $user->telegram_id;

        $category = UserCategory::where('id', $id)
            ->where('user_id', $telegramId)
            ->firstOrFail();

        $usedInOperations = DB::table('operations')
            ->where('user_id', $telegramId)
            ->where('category', $category->name)
            ->where('category_type', 'custom')
            ->exists();

        if ($usedInOperations) {
            return response()->json([
                'success' => false,
                'error' => 'category_in_use'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
