<?php

namespace App\Telegram\Webhook\Actions;

use App\Telegram\Webhook\Webhook;
use App\Facades\Telegram;
use App\Models\Category;
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
            Telegram::editButtons($this->chat_id, "❌ Запись не найдена", null, $messageId)->send();
            return;
        }

        DB::table('operations')
            ->where('id', $operationId)
            ->update([
                'status' => 'confirmed',
                'updated_at' => now(),
            ]);

        $categoriesMap = array_merge(
            Category::pluck('name_ru', 'id')->toArray(),
            Category::pluck('name_ru', 'name_en')->toArray()
        );

        $categoryName = $operation->category
            ? ($categoriesMap[(string)$operation->category] ?? $operation->category)
            : null;

        $text = "✅ Запись добавлена:\n\n";
        $text .= ($operation->type === 'income' ? "➕ Доход" : "➖ Расход") . "\n";
        $text .= "💰 Сумма: {$operation->amount} {$operation->currency}\n";
        if ($categoryName) {
            $text .= "📂 Категория: {$categoryName}\n";
        }
        if ($operation->description) {
            $text .= "📝 {$operation->description}\n";
        }
        if ($operation->occurred_at) {
            $text .= "📅 Дата: " . Carbon::parse($operation->occurred_at)->format('d.m.Y') . "\n";
        }

        Telegram::editButtons($this->chat_id, $text, null, $messageId)->send();
    }
}
