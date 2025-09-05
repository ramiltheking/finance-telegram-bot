<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    public function __construct() {
        $this->token = env('TELEGRAM_BOT_TOKEN');
    }

    public function sendMessage(int|string $chatId, string $text, array $keyboard = null, array $options = []) {
        $payload = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $options);

        if ($keyboard) {
            $payload['reply_markup'] = json_encode($keyboard, JSON_UNESCAPED_UNICODE);
        }

        $res = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", $payload);
        Log::info('Ответ TG sendMessage: ', $res->json());
        return $res->json();
    }

    public function answerCallback(int|string $callbackQueryId, string $text = '') {
        return Http::post("https://api.telegram.org/bot{$this->token}/answerCallbackQuery", [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => false
        ])->json();
    }

    public function deleteMessage($chatId, $messageId) {
        return Http::post("https://api.telegram.org/bot{$this->token}/deleteMessage", [
            'chat_id' => $chatId, 'message_id' => $messageId
        ])->json();
    }

    public function getFileContent(string $filePath) : ?string {
        $fileUrl = "https://api.telegram.org/file/bot{$this->token}/{$filePath}";
        $resp = Http::get($fileUrl);
        if ($resp->ok()) return $resp->body();
        return null;
    }
}
