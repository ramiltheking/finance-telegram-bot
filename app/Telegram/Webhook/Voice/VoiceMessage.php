<?php

namespace App\Telegram\Webhook\Voice;

use App\Facades\Telegram;
use App\Models\User;
use App\Models\Category;
use App\Models\UserCategory;
use App\Services\CategoryService;
use App\Services\OpenAIService;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Helpers\KeyboardButton;
use App\Telegram\Webhook\Webhook;
use Illuminate\Support\Facades\DB;
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

        if ($voiceDuration > 20) {
            Telegram::message($this->chat_id, __('messages.audio_message_exceeds'), $this->message_id)->send();
            return;
        }

        $fileId = $voice['file_id'];

        $resp = Http::get("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/getFile", [
            'file_id' => $fileId,
        ])->json();

        if (!isset($resp['result']['file_path'])) {
            Log::error('Не удалось получить file_path для voice');
            return;
        }

        $filePath = $resp['result']['file_path'];
        $fileUrl  = "https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN') . "/" . $filePath;

        $binary = file_get_contents($fileUrl);
        $openai = new OpenAIService();
        $text = $openai->transcribeAudio($binary);

        if (!$text) {
            Telegram::message($this->chat_id, __('messages.audio_message_failed'), $this->message_id)->send();
            return;
        }

        $operation = $openai->parseOperationFromText($text, $this->chat_id);

        if (!$operation) {
            KeyboardButton::clear();
            KeyboardButton::add('🚀 Старт', 1);
            KeyboardButton::add('🪙 Баланс', 2);
            KeyboardButton::add('📋 Список операций', 2);
            KeyboardButton::add('📅 Недельный отчет', 3);
            KeyboardButton::add('📊 Полный отчет', 3);
            KeyboardButton::add('💰 Подписка', 4);

            Telegram::inlineButtons($this->chat_id, __('messages.operation_parse_failed'), KeyboardButton::$buttons)->send();
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
            'occurred_at'   => $operation['occurred_at'] ?? now(),
            'meta'          => null,
            'status'        => 'pending',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $buttons = InlineButton::create()->add(__('messages.confirm'), 'Confirm', ['operation_id' => $operationId,], 1)
                   ->add(__('messages.decline'), 'Decline', ['operation_id' => $operationId,], 1)->get();

        Telegram::inlineButtons($this->chat_id, $userText, $buttons)->send();

        Log::info("Операция для подтверждения (голос)", $operation);

        return $operation;
    }
}
