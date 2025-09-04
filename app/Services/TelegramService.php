<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    protected string $token;

    public function __construct()
    {
        $this->token = env('TELEGRAM_BOT_TOKEN');
    }

    public function sendMessage(int|string $chatId, string $text): array
    {
        $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
            'chat_id' => $chatId,
            'text'    => $text,
        ]);

        return $response->json();
    }
}
