<?php

namespace App\Telegram\Webhook\Voice;

use App\Facades\Telegram;
use App\Services\OpenAIService;
use App\Telegram\Webhook\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoiceMessage extends Webhook
{
    public function run()
    {
        $update = $this->request->input('message');
        $voice = $update['voice'] ?? null;

        if (!$voice) {
            return;
        }

        $voiceDuration = $voice['duration'];

        if ($voiceDuration > 10) {
            Telegram::message($this->chat_id, "❗ Длительность аудиосообщения превышает 10 секунд. Пожалуйста, отправьте более короткое сообщение.")->send();
            return;
        }

        $fileId = $voice['file_id'];

        $resp = Http::get("https://api.telegram.org/bot".env('TELEGRAM_BOT_TOKEN')."/getFile", [
            'file_id' => $fileId,
        ])->json();

        if (!isset($resp['result']['file_path'])) {
            Log::error('Не удалось получить file_path для voice');
            return;
        }

        $filePath = $resp['result']['file_path'];
        $fileUrl  = "https://api.telegram.org/file/bot".env('TELEGRAM_BOT_TOKEN')."/".$filePath;

        $binary = file_get_contents($fileUrl);
        $openai = new OpenAIService();
        $text   = $openai->transcribeAudio($binary);

        if (!$text) {
            Telegram::message($this->chat_id, "❗ Не удалось распознать голосовое сообщение")->send();
            return;
        }

        $operation = $openai->parseOperationFromText($text);

        if (!$operation) {
            Telegram::message($this->chat_id, "❗ Не удалось распознать операцию")->send();
            return;
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

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Подтвердить', 'callback_data' => 'Confirm'],
                    ['text' => '❌ Отклонить', 'callback_data' => 'Decline'],
                ],
            ]
        ];

        Telegram::inlineButtons($this->chat_id, $userText, $keyboard)->send();

        Log::info("Операция для подтверждения", $operation);
        return $operation;
    }
}
