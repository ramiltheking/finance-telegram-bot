<?php

namespace App\Telegram\Webhook\Actions;

use App\Telegram\Webhook\Webhook;
use App\Facades\Telegram;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Confirm extends Webhook
{
    public function run() {
        $callback = $this->request->input('callback_query');
        $data = json_decode($callback['data'], true);

        $operationId = $data['operation_id'];
        $messageId = $callback['message']['message_id'];

        $operation = DB::table('operations')
            ->where('id', $operationId)
            ->where('user_id', $this->chat_id)
            ->first();

        if (!$operation) {
            Telegram::editButtons($this->chat_id, __('messages.record_not_found'), null, $messageId)->send();
            return;
        }

        DB::table('operations')
            ->where('id', $operationId)
            ->update([
                'status' => 'confirmed',
                'updated_at' => now(),
            ]);

        $user = User::where('telegram_id', $this->chat_id)->first();
        $userSettings = $user->settings;
        $userLang = $userSettings?->language;

        $categoriesMap = array_merge(
            Category::pluck('name_ru', 'id')->toArray(),
            $userLang === 'ru' ? Category::pluck('name_ru', 'name_en')->toArray() : Category::pluck('name_en', 'name_en')->toArray()
        );

        $categoryName = $operation->category
            ? ($categoriesMap[(string)$operation->category] ?? $operation->category)
            : null;

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
