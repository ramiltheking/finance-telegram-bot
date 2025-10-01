<?php

namespace App\Telegram\Webhook\Commands;

use App\Models\Operation;
use App\Models\Category;
use App\Facades\Telegram;
use App\Models\User;
use App\Telegram\Webhook\Webhook;

class FullReportCommand extends Webhook
{
    public function run()
    {
        $userId = $this->chat_id;
        $user = User::where('telegram_id', $userId)->first();

        if (!$user) {
            Telegram::message($userId, trans('commands.full_report.user_not_found'), $this->message_id)->send();
            return;
        }

        $operations = Operation::where('user_id', $userId)->where('status', 'confirmed')->get();

        if ($operations->isEmpty()) {
            Telegram::message($this->chat_id, trans('commands.full_report.no_operations'), $this->message_id)->send();
            return;
        }

        $totalSpent = 0;
        $totalClaimed = 0;
        $expenseCategories = [];
        $incomeCategories = [];

        $userSettings = $user->settings;
        $userLang = $userSettings?->language ?? 'ru';

        $categoryField = $userLang === 'ru' ? 'name_ru' : 'name_en';
        $categoryMap = Category::pluck($categoryField, 'name_en')->toArray();

        foreach ($operations as $op) {
            $amount = (float)$op->amount;
            $catCode = $op->category;
            $catName = $categoryMap[$catCode] ?? trans('commands.full_report.no_category');

            if ($op->type === 'expense') {
                $totalSpent += $amount;
                if (!isset($expenseCategories[$catName])) {
                    $expenseCategories[$catName] = 0;
                }
                $expenseCategories[$catName] += $amount;
            } elseif ($op->type === 'income') {
                $totalClaimed += $amount;
                if (!isset($incomeCategories[$catName])) {
                    $incomeCategories[$catName] = 0;
                }
                $incomeCategories[$catName] += $amount;
            }
        }

        arsort($expenseCategories);
        arsort($incomeCategories);

        $currency = $operations->first()->currency;

        $message = trans('commands.full_report.title') . "\n\n";
        $message .= trans('commands.full_report.total_expenses', [
            'amount' => number_format($totalSpent, 2, '.', ' '),
            'currency' => $currency
        ]) . "\n";
        $message .= trans('commands.full_report.total_income', [
            'amount' => number_format($totalClaimed, 2, '.', ' '),
            'currency' => $currency
        ]) . "\n\n";

        if (!empty($expenseCategories)) {
            $message .= "📉 " . trans('commands.full_report.category_totals') . " (" . trans('commands.full_report.expenses') . "):\n";
            foreach ($expenseCategories as $category => $total) {
                $message .= "{$category}: " . number_format($total, 2, '.', ' ') . " {$currency}\n";
            }
            $message .= "\n";
        }

        if (!empty($incomeCategories)) {
            $message .= "📈 " . trans('commands.full_report.category_totals') . " (" . trans('commands.full_report.income') . "):\n";
            foreach ($incomeCategories as $category => $total) {
                $message .= "{$category}: " . number_format($total, 2, '.', ' ') . " {$currency}\n";
            }
        }

        Telegram::message($this->chat_id, $message)->send();
    }
}
