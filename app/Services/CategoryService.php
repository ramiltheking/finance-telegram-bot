<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Operation;
use App\Models\UserCategory;

class CategoryService
{
    public function getCategoryName(Operation $operation, $language = 'ru'): ?string
    {
        if (!$operation->category) {
            return null;
        }

        if ($operation->category_type === 'system') {
            $category = Category::where('slug', $operation->category)->first();
            if ($category) {
                return $language === 'ru' ? $category->name_ru : ($category->name_en ?? $category->name_ru);
            }

            $category = Category::where('name_ru', $operation->category)
                ->orWhere('name_en', $operation->category)
                ->first();

            if ($category) {
                return $language === 'ru' ? $category->name_ru : ($category->name_en ?? $category->name_ru);
            }
        } elseif ($operation->category_type === 'custom') {
            $category = UserCategory::where('name', $operation->category)
                ->where('user_id', $operation->user_id)
                ->first();
            if ($category) {
                return $category->name;
            }
        }

        return $operation->category;
    }

    public function resolveCategory(?string $categoryName, string $requestedType, int $userId): array
    {
        if (!$categoryName) {
            return ['type' => 'system', 'name' => 'other'];
        }

        $normalizedRequestedType = strtoupper($requestedType);

        $existingUserCategory = UserCategory::where('user_id', $userId)
            ->where('name', $categoryName)
            ->first();

        if ($existingUserCategory) {
            $correctedType = strtolower($existingUserCategory->type);
            $hasTypeConflict = $existingUserCategory->type !== $normalizedRequestedType;

            return [
                'type' => 'custom',
                'name' => $existingUserCategory->name,
                'corrected_type' => $correctedType,
                'has_type_conflict' => $hasTypeConflict,
                'original_requested_type' => $requestedType
            ];
        }

        $systemCategory = Category::where(function ($query) use ($categoryName) {
            $query->where('name_ru', $categoryName)
                ->orWhere('name_en', $categoryName)
                ->orWhere('slug', $categoryName);
        })->where('type', $requestedType)->first();

        if ($systemCategory) {
            return ['type' => 'system', 'name' => $systemCategory->slug];
        }

        $newUserCategory = UserCategory::create([
            'user_id' => $userId,
            'name' => $categoryName,
            'type' => $normalizedRequestedType,
            'title' => $categoryName,
        ]);

        return [
            'type' => 'custom',
            'name' => $newUserCategory->name,
            'corrected_type' => $requestedType,
            'has_type_conflict' => false
        ];
    }

    public function generateUniqueCategoryName(string $title, int $userId): string
    {
        $baseName = preg_replace('/[^a-z0-9]/', '_', strtolower($title));
        $baseName = preg_replace('/_{2,}/', '_', $baseName);
        $baseName = trim($baseName, '_');

        $name = $baseName;
        $counter = 1;

        while (UserCategory::where('name', $name)->where('user_id', $userId)->exists()) {
            $name = $baseName . '_' . $counter;
            $counter++;
        }

        return $name;
    }

    public function getAvailableCategories(?int $userId = null): string
    {
        $allSystemCategories = Category::with('children')->get();

        $systemIncomeCategories = [];
        $systemExpenseCategories = [];

        foreach ($allSystemCategories as $category) {
            if (!$category->parent_id) {
                $categoryName = $category->name_ru;
                if ($category->type === 'income') {
                    $systemIncomeCategories[] = $categoryName;
                } else {
                    $systemExpenseCategories[] = $categoryName;
                }
            }

            foreach ($category->children as $child) {
                $childName = $child->name_ru;
                if ($category->type === 'income') {
                    $systemIncomeCategories[] = $childName;
                } else {
                    $systemExpenseCategories[] = $childName;
                }
            }
        }

        $systemIncomeCategories = array_unique($systemIncomeCategories);
        $systemExpenseCategories = array_unique($systemExpenseCategories);
        sort($systemIncomeCategories);
        sort($systemExpenseCategories);

        $userIncomeCategories = [];
        $userExpenseCategories = [];

        if ($userId) {
            $userIncomeCategories = UserCategory::where('user_id', $userId)
                ->where('type', 'INCOME')
                ->pluck('name')
                ->toArray();

            $userExpenseCategories = UserCategory::where('user_id', $userId)
                ->where('type', 'EXPENSE')
                ->pluck('name')
                ->toArray();
        }

        $prompt = "**SYSTEM CATEGORIES (use exact names as shown):**\n";
        $prompt .= "INCOME: " . implode(', ', $systemIncomeCategories) . "\n\n";
        $prompt .= "EXPENSE: " . implode(', ', $systemExpenseCategories) . "\n\n";

        if (!empty($userIncomeCategories) || !empty($userExpenseCategories)) {
            $prompt .= "**CUSTOM CATEGORIES (user-created, use exact names as shown):**\n";
            if (!empty($userIncomeCategories)) {
                $prompt .= "INCOME: " . implode(', ', $userIncomeCategories) . "\n";
            }
            if (!empty($userExpenseCategories)) {
                $prompt .= "EXPENSE: " . implode(', ', $userExpenseCategories) . "\n";
            }
        }

        return $prompt;
    }

    public function formatSystemCategories(array $categories): array
    {
        $result = [];

        foreach ($categories as $category) {
            $childrenNames = collect($category['children'] ?? [])
                ->pluck('name_ru')
                ->filter()
                ->toArray();

            if (!empty($childrenNames)) {
                foreach ($childrenNames as $childName) {
                    $result[] = $childName;
                }
            }
        }

        return $result;
    }
}
