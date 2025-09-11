<?php

namespace App\Telegram\Webhook\Text;

use App\Facades\Telegram;
use App\Models\User;
use App\Services\OpenAIService;
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
        $operation = $openai->parseOperationFromText($text);

        if (!$operation) {
            Telegram::message($this->chat_id, "❗ Не удалось распознать операцию")->send();
            return ['error' => 'operation_parse_failed', 'text' => $text];
        }

        $currencyMap = [
            'тенге'   => 'KZT',
            'рубли'   => 'RUB',
            'доллары' => 'USD',
            'евро'    => 'EUR',
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
                Log::warning("Ошибка конвертации валюты: ".$e->getMessage());
            }
        }

        $amount   = $operation['amount'] ?? 0;
        $currency = $operation['currency'] ?? 'KZT';
        $title    = $operation['title'] ?? '';

        $userText = $operation['type'] === 'income'
            ? "✅ Добавить запись: Получил(-a) {$amount} {$currency} — {$title}"
            : "✅ Добавить запись: Потратил(-a) {$amount} {$currency} — {$title}";

        $user = User::where('telegram_id', $this->chat_id)->first();

        if (!$user) {
            Telegram::message($this->chat_id, "❗ Пользователь не найден.")->send();
            return;
        }

        $operationId = DB::table('operations')->insertGetId([
            'user_id'     => $this->chat_id,
            'type'        => $operation['type'],
            'amount'      => $operation['amount'],
            'currency'    => $operation['currency'],
            'category'    => $operation['category'] ?? null,
            'description' => $operation['title'] ?? null,
            'occurred_at' => now(),
            'meta'        => null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        InlineButton::add('✅ Подтвердить', 'Confirm', ['operation_id' => $operationId,], 1);
        InlineButton::add('❌ Отклонить', 'Decline', ['operation_id' => $operationId,], 1);
        Telegram::inlineButtons($this->chat_id, $userText, InlineButton::$buttons)->send();

        Log::info("Операция для подтверждения (текст)", $operation);

        return $operation;
    }
}
