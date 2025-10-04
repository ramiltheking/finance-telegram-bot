<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Models\Category;
use App\Models\User;
use App\Models\Operation;
use App\Models\UserCategory;
use App\Services\CategoryService;
use App\Telegram\Webhook\Webhook;

class ListCommand extends Webhook
{
    public function run()
    {
        $userId = $this->chat_id;

        $user = User::where('telegram_id', $userId)->first();

        if (!$user) {
            Telegram::message($userId, trans('commands.list.user_not_found'), $this->message_id)->send();
            return;
        }

        $operations = Operation::where('user_id', $user->telegram_id)
            ->where('occurred_at', '>=', now()->subDays(7))
            ->where('status', 'confirmed')
            ->orderByDesc('occurred_at')
            ->get();

        if ($operations->isEmpty()) {
            Telegram::message($userId, trans('commands.list.no_operations'), $this->message_id)->send();
            return;
        }

        $userSettings = $user->settings;
        $userLang = $userSettings?->language ?? 'ru';

        $categoryService = new CategoryService();
        $dateFormat = $userLang === 'ru' ? 'd.m.Y' : 'Y-m-d';

        $message = trans('commands.list.title') . "\n\n";

        foreach ($operations as $index => $op) {
            $categoryName = $categoryService->getCategoryName($op, $userLang);

            $typeIcon = $op->type === 'income'
                ? trans('commands.list.income_icon')
                : trans('commands.list.expense_icon');

            $message .= trans('commands.list.operation_format', [
                'index' => $index + 1,
                'type' => $typeIcon,
                'category' => $categoryName ?? trans('commands.list.no_category'),
                'currency' => $op->currency,
                'amount' => number_format($op->amount, 2, '.', ' '),
                'date' => $op->occurred_at->format($dateFormat)
            ]) . "\n";
        }

        $message .= "\n" . trans('commands.list.delete_hint') . "\n";
        $message .= trans('commands.list.edit_hint');

        Telegram::message($userId, $message)->send();
    }
}
