<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Telegram\Webhook\Webhook;
use App\Models\Operation;
use App\Models\User;
use App\Services\CategoryService;
use Carbon\Carbon;

class Decline extends Webhook
{
    public function run()
    {
        $callback = $this->request->input('callback_query');
        $data = json_decode($callback['data'], true);

        $operationId = $data['operation_id'];
        $messageId   = $callback['message']['message_id'];

        $operation = Operation::where('id', $operationId)
            ->where('user_id', $this->chat_id)
            ->first();

        if (!$operation) {
            Telegram::editButtons($this->chat_id, __('messages.record_not_found'), null, $messageId)->send();
            return;
        }

        $operation->update([
            'status' => 'declined',
            'updated_at' => now(),
        ]);

        $user = User::where('telegram_id', $this->chat_id)->first();
        $userSettings = $user->settings;
        $userLang = $userSettings?->language ?? 'ru';
        $userCurrency = $userSettings->currency ?? 'KZT';

        $categoryService = new CategoryService();
        $categoryName = $categoryService->getCategoryName($operation, $userLang);

        $text = __('messages.record_rejected') . "\n\n";
        $text .= ($operation->type === 'income' ? __('messages.income_label') : __('messages.expense_label')) . "\n";
        $text .= __('messages.amount_label', ['amount' => $operation->amount, 'currency' => $operation->currency]) . "\n";
        if ($categoryName) {
            $text .= __('messages.category_label', ['category' => $categoryName]) . "\n";
        }
        if ($operation->description) {
            $text .= __('messages.description_label', ['description' => $operation->description]) . "\n";
        }
        if ($operation->occurred_at) {
            Carbon::setLocale($userLang);
            $formattedDate = Carbon::parse($operation->occurred_at)->isoFormat('D MMM. YYYY');
            $text .= __('messages.date_label', ['date' => $formattedDate]) . "\n";
        }

        $operations = Operation::where('user_id', $user->telegram_id)
            ->where('occurred_at', '>=', now()->subDays(30))
            ->where('status', 'confirmed')
            ->orderByDesc('occurred_at')
            ->get();

        if ($operations) {
            $income = $operations->where('type', 'income')->sum('amount');
            $expense = $operations->where('type', 'expense')->sum('amount');
            $balance = $income - $expense;

            if ($balance > 0) {
                $text .= trans('messages.balance_positive', ['amount' => number_format($balance, 2, '.', ' '), 'currency' => $userCurrency]);
            } elseif ($balance < 0) {
                $text .= trans('messages.balance_negative', ['amount' => number_format(abs($balance), 2, '.', ' '), 'currency' => $userCurrency]);
            }
        }

        Telegram::editButtons($this->chat_id, $text, null, $messageId)->send();
    }
}
