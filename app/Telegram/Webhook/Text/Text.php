<?php

namespace App\Telegram\Webhook\Text;

use App\Facades\Telegram;
use App\Models\User;
use App\Services\OpenAIService;
use App\Services\CategoryService;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Text extends Webhook
{
    public function run()
    {
        $text = $this->request->input('message.text') ?? '';
        $openai = new OpenAIService();
        $operation = $openai->parseOperationFromText($text, $this->chat_id);

        if (!$operation) {
            Telegram::message($this->chat_id, __('messages.operation_parse_failed'), $this->message_id)->send();
            return ['error' => 'operation_parse_failed', 'text' => $text];
        }

        $currencyMap = [
            'тенге' => 'KZT',
            'рубли' => 'RUB',
            'доллары' => 'USD',
            'евро' => 'EUR',
        ];
        if (isset($operation['currency']) && isset($currencyMap[$operation['currency']])) {
            $operation['currency'] = $currencyMap[$operation['currency']];
        }

        if (in_array($operation['currency'], ['USD', 'EUR', 'RUB'])) {
            try {
                $currencyResponse = Http::get("https://api.exchangerate-api.com/v4/latest/{$operation['currency']}")->json();
                if (isset($currencyResponse['rates']['KZT'])) {
                    $rate = $currencyResponse['rates']['KZT'];
                    $operation['amount'] = round(($operation['amount'] ?? 0) * $rate, 2);
                    $operation['currency'] = 'KZT';
                }
            } catch (\Throwable $e) {
                Log::warning("Ошибка конвертации валюты: " . $e->getMessage());
            }
        }

        $amount = $operation['amount'] ?? 0;
        $currency = $operation['currency'] ?? 'KZT';
        $title = $operation['title'] ?? '';

        $categoryService = new CategoryService();

        $categoryData = $categoryService->resolveCategory($operation['category'] ?? null, $operation['type'], $this->chat_id);
        $categoryType = $categoryData['type'];
        $categoryName = $categoryData['name'];

        $userText = $operation['type'] === 'income'
            ? __('messages.income_text', [
                'amount'   => $amount,
                'currency' => $currency,
                'title'    => $title,
            ])
            : __('messages.expense_text', [
                'amount'   => $amount,
                'currency' => $currency,
                'title'    => $title,
            ]);

        $user = User::where('telegram_id', $this->chat_id)->first();

        if (!$user) {
            Telegram::message($this->chat_id, __('messages.user_not_found'), $this->message_id)->send();
            return;
        }

        $operationId = DB::table('operations')->insertGetId([
            'user_id'       => $this->chat_id,
            'type'          => $operation['type'],
            'amount'        => $operation['amount'],
            'currency'      => $operation['currency'],
            'category'      => $categoryName,
            'category_type' => $categoryType,
            'description'   => $operation['title'] ?? null,
            'occurred_at'   => now(),
            'meta'          => null,
            'status'        => 'pending',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        InlineButton::add(__('messages.confirm'), 'Confirm', ['operation_id' => $operationId,], 1);
        InlineButton::add(__('messages.decline'), 'Decline', ['operation_id' => $operationId,], 1);
        Telegram::inlineButtons($this->chat_id, $userText, InlineButton::$buttons)->send();

        Log::info("Операция для подтверждения (текст)", $operation);

        return $operation;
    }
}
