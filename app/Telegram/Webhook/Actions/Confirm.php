<?php

namespace App\Telegram\Webhook\Actions;

use App\Telegram\Webhook\Webhook;
use App\Facades\Telegram;
use App\Models\Category;
use App\Models\UserCategory;
use App\Models\User;
use App\Models\Operation;
use App\Services\CategoryService;
use Carbon\Carbon;

class Confirm extends Webhook
{
    public function run() {
        $callback = $this->request->input('callback_query');
        $data = json_decode($callback['data'], true);

        $operationId = $data['operation_id'];
        $messageId = $callback['message']['message_id'];

        $operation = Operation::where('id', $operationId)
            ->where('user_id', $this->chat_id)
            ->first();

        if (!$operation) {
            Telegram::editButtons($this->chat_id, __('messages.record_not_found'), null, $messageId)->send();
            return;
        }

        $operation->update([
            'status' => 'confirmed',
            'updated_at' => now(),
        ]);

        $user = User::where('telegram_id', $this->chat_id)->first();
        $userSettings = $user->settings;
        $userLang = $userSettings?->language ?? 'ru';
        $categoryService = new CategoryService();

        $categoryName = $categoryService->getCategoryName($operation, $userLang);

        $text = __('messages.record_added') . "\n\n";
        $text .= ($operation->type === 'income' ? __('messages.income_label') : __('messages.expense_label')) . "\n";
        $text .= __('messages.amount_label', ['amount' => $operation->amount, 'currency' => $operation->currency]) . "\n";
        if ($categoryName) {
            $text .= __('messages.category_label', ['category' => $categoryName]) . "\n";
        }
        if ($operation->description) {
            $text .= __('messages.description_label', ['description' => $operation->description]) . "\n";
        }
        if ($operation->occurred_at) {
            $text .= __('messages.date_label', ['date' => Carbon::parse($operation->occurred_at)->format('d.m.Y')]) . "\n";
        }

        Telegram::editButtons($this->chat_id, $text, null, $messageId)->send();
    }
}
