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

    public function resolveCategory(?string $categoryName, string $type, int $userId): array
    {
        if (!$categoryName) {
            return ['type' => 'system', 'name' => 'other'];
        }

        $systemCategory = Category::where(function ($query) use ($categoryName) {
            $query->where('name_ru', $categoryName)
                ->orWhere('slug', $categoryName);
        })->where('type', $type)->first();

        if ($systemCategory) {
            return ['type' => 'system', 'name' => $systemCategory->slug];
        }

        $userCategory = UserCategory::where('name', $categoryName)
            ->where('user_id', $userId)
            ->where('type', strtoupper($type))
            ->first();

        if ($userCategory) {
            return ['type' => 'custom', 'name' => $userCategory->name];
        }

        $uniqueName = $this->generateUniqueCategoryName($categoryName, $userId);

        $newUserCategory = UserCategory::create([
            'user_id' => $userId,
            'type' => strtoupper($type),
            'name' => $uniqueName,
            'title' => $categoryName,
        ]);

        return ['type' => 'custom', 'name' => $newUserCategory->name];
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
        $incomeCategories = Category::where('type', 'income')
            ->whereNull('parent_id')
            ->with('children')
            ->get()
            ->toArray();

        $expenseCategories = Category::where('type', 'expense')
            ->whereNull('parent_id')
            ->with('children')
            ->get()
            ->toArray();

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

        $systemIncomeCategories = $this->formatSystemCategories($incomeCategories);
        $systemExpenseCategories = $this->formatSystemCategories($expenseCategories);

        $prompt = "**SYSTEM CATEGORIES (use exact names as shown):**\n";
        $prompt .= "INCOME: " . implode(', ', $systemIncomeCategories) . "\n\n";
        $prompt .= "EXPENSE: " . implode(', ', $systemExpenseCategories) . "\n\n";

        $prompt .= "**CUSTOM CATEGORIES (user-created, use exact names as shown):**\n";
        $prompt .= "INCOME: " . implode(', ', $userIncomeCategories) . "\n";
        $prompt .= "EXPENSE: " . implode(', ', $userExpenseCategories);

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
