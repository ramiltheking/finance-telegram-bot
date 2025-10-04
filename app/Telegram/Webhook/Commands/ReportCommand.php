<?php

namespace App\Telegram\Webhook\Commands;

use App\Models\Operation;
use Carbon\Carbon;
use App\Facades\Telegram;
use App\Models\User;
use App\Services\CategoryService;
use App\Telegram\Webhook\Webhook;

class ReportCommand extends Webhook
{
    public function run()
    {
        $userId = $this->chat_id;

        $user = User::where('telegram_id', $userId)->first();
        if (!$user) {
            Telegram::message($userId, trans('commands.report.user_not_found'), $this->message_id)->send();
            return;
        }

        $oneWeekAgo = Carbon::now()->subDays(7);

        $operations = Operation::where('user_id', $userId)
            ->where('occurred_at', '>=', $oneWeekAgo)
            ->where('status', 'confirmed')
            ->get();

        if ($operations->isEmpty()) {
            Telegram::message($this->chat_id, trans('commands.report.no_operations'), $this->message_id)->send();
            return;
        }

        $totalSpent = 0;
        $totalClaimed = 0;
        $expenseCategories = [];
        $incomeCategories = [];

        $userSettings = $user->settings;
        $userLang = $userSettings?->language ?? 'ru';

        $categoryService = new CategoryService();

        foreach ($operations as $op) {
            $amount = (float)$op->amount;

            $categoryName = $categoryService->getCategoryName($op, $userLang) ?? trans('commands.report.no_category');

            if ($op->type === 'expense') {
                $totalSpent += $amount;
                if (!isset($expenseCategories[$categoryName])) {
                    $expenseCategories[$categoryName] = 0;
                }
                $expenseCategories[$categoryName] += $amount;
            } elseif ($op->type === 'income') {
                $totalClaimed += $amount;
                if (!isset($incomeCategories[$categoryName])) {
                    $incomeCategories[$categoryName] = 0;
                }
                $incomeCategories[$categoryName] += $amount;
            }
        }

        arsort($expenseCategories);
        arsort($incomeCategories);

        $currency = $operations->first()->currency;

        $message = trans('commands.report.title') . "\n";
        $message .= trans('commands.report.period', [
            'from' => $oneWeekAgo->format('d.m.Y'),
            'to' => Carbon::now()->format('d.m.Y')
        ]) . "\n\n";

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
            ? trans('commands.report.balance_positive', ['amount' => number_format($balance, 2, '.', ' ')])
            : ($balance < 0
                ? trans('commands.report.balance_negative', ['amount' => number_format(abs($balance), 2, '.', ' ')])
                : trans('commands.report.balance_zero'));

        $message .= "{$balanceIcon} " . trans('commands.report.balance') . ": {$balanceText} {$currency}\n\n";

        if (!empty($expenseCategories)) {
            $message .= "📊 " . trans('commands.report.expense_by_categories') . ":\n";
            $counter = 1;
            foreach ($expenseCategories as $category => $total) {
                $percentage = $totalSpent > 0 ? round(($total / $totalSpent) * 100) : 0;
                $message .= "{$counter}. {$category}: " . number_format($total, 2, '.', ' ') . " {$currency} ({$percentage}%)\n";
                $counter++;

                if ($counter > 10) break;
            }
            $message .= "\n";
        }

        if (!empty($incomeCategories)) {
            $message .= "📈 " . trans('commands.report.income_by_categories') . ":\n";
            $counter = 1;
            foreach ($incomeCategories as $category => $total) {
                $percentage = $totalClaimed > 0 ? round(($total / $totalClaimed) * 100) : 0;
                $message .= "{$counter}. {$category}: " . number_format($total, 2, '.', ' ') . " {$currency} ({$percentage}%)\n";
                $counter++;

                if ($counter > 10) break;
            }
        }

        $currencies = $operations->pluck('currency')->unique();
        if ($currencies->count() > 1) {
            $message .= "\n💱 " . trans('commands.report.currencies_used') . ": " . $currencies->implode(', ');
        }

        Telegram::message($this->chat_id, $message)->send();
    }
}
