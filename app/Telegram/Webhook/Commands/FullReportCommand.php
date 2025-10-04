<?php

namespace App\Telegram\Webhook\Commands;

use App\Models\Operation;
use App\Facades\Telegram;
use App\Models\User;
use App\Services\CategoryService;
use App\Telegram\Webhook\Webhook;
use Carbon\Carbon;

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

        $operations = Operation::where('user_id', $userId)
            ->where('status', 'confirmed')
            ->orderBy('occurred_at')
            ->get();

        if ($operations->isEmpty()) {
            Telegram::message($this->chat_id, trans('commands.full_report.no_operations'), $this->message_id)->send();
            return;
        }

        $totalSpent = 0;
        $totalClaimed = 0;
        $expenseCategories = [];
        $incomeCategories = [];
        $monthlyData = [];

        $userSettings = $user->settings;
        $userLang = $userSettings?->language ?? 'ru';

        $categoryService = new CategoryService();

        foreach ($operations as $op) {
            $amount = (float)$op->amount;

            $categoryName = $categoryService->getCategoryName($op, $userLang) ?? trans('commands.full_report.no_category');

            $monthYear = $op->occurred_at->format('Y-m');
            if (!isset($monthlyData[$monthYear])) {
                $monthlyData[$monthYear] = [
                    'income' => 0,
                    'expense' => 0,
                    'operations' => 0
                ];
            }

            if ($op->type === 'expense') {
                $totalSpent += $amount;
                $monthlyData[$monthYear]['expense'] += $amount;

                if (!isset($expenseCategories[$categoryName])) {
                    $expenseCategories[$categoryName] = 0;
                }
                $expenseCategories[$categoryName] += $amount;
            } elseif ($op->type === 'income') {
                $totalClaimed += $amount;
                $monthlyData[$monthYear]['income'] += $amount;

                if (!isset($incomeCategories[$categoryName])) {
                    $incomeCategories[$categoryName] = 0;
                }
                $incomeCategories[$categoryName] += $amount;
            }

            $monthlyData[$monthYear]['operations']++;
        }

        arsort($expenseCategories);
        arsort($incomeCategories);
        krsort($monthlyData);

        $currency = $operations->first()->currency;
        $firstOperation = $operations->first();
        $lastOperation = $operations->last();

        $message = "📊 " . trans('commands.full_report.title') . "\n";
        $message .= trans('commands.full_report.period', [
            'from' => $firstOperation->occurred_at->format('d.m.Y'),
            'to' => $lastOperation->occurred_at->format('d.m.Y')
        ]) . "\n";
        $message .= "📈 " . trans('commands.full_report.total_operations') . ": " . $operations->count() . "\n\n";

        $message .= "💰 " . trans('commands.report.total_income', [
            'amount' => number_format($totalClaimed, 2, '.', ' '),
            'currency' => $currency
        ]) . "\n";

        $message .= "➖ " . trans('commands.report.total_expenses', [
            'amount' => number_format($totalSpent, 2, '.', ' '),
            'currency' => $currency
        ]) . "\n";

        $balance = $totalClaimed - $totalSpent;
        $balanceIcon = $balance > 0 ? '💹' : ($balance < 0 ? '🔻' : '⚖️');
        $balanceText = $balance > 0
            ? trans('commands.full_report.balance_positive', ['amount' => number_format($balance, 2, '.', ' ')])
            : ($balance < 0
                ? trans('commands.full_report.balance_negative', ['amount' => number_format(abs($balance), 2, '.', ' ')])
                : trans('commands.full_report.balance_zero'));

        $message .= "{$balanceIcon} " . trans('commands.full_report.balance') . ": {$balanceText} {$currency}\n\n";

        if (count($monthlyData) > 1) {
            $message .= "📅 " . trans('commands.full_report.monthly_stats') . ":\n";
            foreach ($monthlyData as $month => $data) {
                $monthName = Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y');
                $monthBalance = $data['income'] - $data['expense'];
                $balanceIcon = $monthBalance > 0 ? '📈' : ($monthBalance < 0 ? '📉' : '➖');

                $message .= "{$monthName} ({$data['operations']} " . trans('commands.full_report.operations') . "):\n";
                $message .= "💰 " . trans('commands.report.total_income', [
                    'amount' => number_format($data['income'], 2, '.', ' '),
                    'currency' => $currency
                ]) . "\n";

                $message .= "➖ " . trans('commands.report.total_expenses', [
                    'amount' => number_format($data['expense'], 2, '.', ' '),
                    'currency' => $currency
                ]) . "\n";
                $message .= "  {$balanceIcon} " . number_format($monthBalance, 2, '.', ' ') . " {$currency}\n\n";
            }
        }

        if (!empty($expenseCategories)) {
            $message .= "📉 " . trans('commands.full_report.top_expense_categories') . ":\n";
            $counter = 1;
            foreach ($expenseCategories as $category => $total) {
                $percentage = $totalSpent > 0 ? round(($total / $totalSpent) * 100) : 0;
                $message .= "{$counter}. {$category}: " . number_format($total, 2, '.', ' ') . " {$currency} ({$percentage}%)\n";
                $counter++;

                if ($counter > 15) break;
            }
            $message .= "\n";
        }

        if (!empty($incomeCategories)) {
            $message .= "📈 " . trans('commands.full_report.top_income_categories') . ":\n";
            $counter = 1;
            foreach ($incomeCategories as $category => $total) {
                $percentage = $totalClaimed > 0 ? round(($total / $totalClaimed) * 100) : 0;
                $message .= "{$counter}. {$category}: " . number_format($total, 2, '.', ' ') . " {$currency} ({$percentage}%)\n";
                $counter++;

                if ($counter > 15) break;
            }
        }

        $currencies = $operations->pluck('currency')->unique();
        if ($currencies->count() > 1) {
            $message .= "\n💱 " . trans('commands.full_report.currencies_used') . ": " . $currencies->implode(', ');
        }

        Telegram::message($this->chat_id, $message)->send();
    }
}
