<?php

namespace App\Telegram\Webhook\Voice;

use App\Facades\Telegram;
use App\Models\User;
use App\Models\Category;
use App\Models\UserCategory;
use App\Services\ActionService;
use App\Services\CategoryService;
use App\Services\OpenAIService;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Helpers\KeyboardButton;
use App\Telegram\Webhook\Actions\Possibilities;
use App\Telegram\Webhook\Webhook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoiceMessage extends Webhook
{
    public function run()
    {
        try {
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
                KeyboardButton::add(__('buttons.start'), 1);
                KeyboardButton::add(__('buttons.balance'), 2);
                KeyboardButton::add(__('buttons.operations_list'), 2);
                KeyboardButton::add(__('buttons.weekly_report'), 3);
                KeyboardButton::add(__('buttons.full_report'), 3);
                KeyboardButton::add(__('buttons.subscription'), 4);

                Telegram::inlineButtons($this->chat_id, __('messages.operation_parse_failed'), KeyboardButton::$buttons)->send();
                return ['error' => 'operation_parse_failed', 'text' => $text];
            }

            if (isset($operation['action'])) {
                $actionService = new ActionService($this->request, $this->chat_id);
                return $actionService->handle($operation['action'], $operation['parameters'] ?? []);
            }

            $operation = array_merge([
                'type' => 'expense',
                'amount' => 0,
                'currency' => 'KZT',
                'title' => '',
                'category' => null,
                'occurred_at' => now(),
            ], $operation);

            $currencyMap = [
                'тенге' => 'KZT',
                'рубли' => 'RUB',
                'доллары' => 'USD',
                'евро' => 'EUR',
            ];

            if (isset($operation['currency']) && isset($currencyMap[$operation['currency']])) {
                $operation['currency'] = $currencyMap[$operation['currency']];
            }

            if (isset($operation['currency']) && in_array($operation['currency'], ['USD', 'EUR', 'RUB'])) {
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
            $type = $operation['type'] ?? 'expense';

            $categoryService = new CategoryService();

            $categoryData = $categoryService->resolveCategory($operation['category'] ?? null, $type, $this->chat_id);
            $categoryType = $categoryData['type'];
            $categoryName = $categoryData['name'];

            $userText = $type === 'income'
                ? __('messages.income_text', [
                    'amount'   => number_format($amount, 2, '.', ' '),
                    'currency' => $currency,
                    'title'    => $title,
                ])
                : __('messages.expense_text', [
                    'amount'   => number_format($amount, 2, '.', ' '),
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
                'type'          => $type,
                'amount'        => $amount,
                'currency'      => $currency,
                'category'      => $categoryName,
                'category_type' => $categoryType,
                'description'   => $title,
                'occurred_at'   => $operation['occurred_at'] ?? now(),
                'meta'          => json_encode(['source' => 'voice', 'transcribed_text' => $text]),
                'status'        => 'pending',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $buttons = InlineButton::create()
                ->add(__('messages.confirm'), 'Confirm', ['operation_id' => $operationId], 1)
                ->add(__('messages.decline'), 'Decline', ['operation_id' => $operationId], 1)
                ->get();

            Telegram::inlineButtons($this->chat_id, $userText, $buttons)->send();

            Log::info("Операция для подтверждения (голос)", $operation);

            return $operation;
        } catch (\Throwable $e) {
            Log::error('Ошибка обработки голосового сообщения: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            Telegram::message($this->chat_id, __('messages.general_error'), $this->message_id)->send();
            return ['error' => 'voice_processing_error', 'message' => $e->getMessage()];
        }
    }
}
