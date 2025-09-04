<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $update = $request->all();

        Log::info('TG Update:', $update);

        Log::info('Telegram RAW: ' . $request->getContent());

        $userId = $request->input('message.chat.id');
        $serverUrl = config('app.url');

        $authUrl = $serverUrl . '/api/auth?state=' . $userId;
        $publicOfferUrl = 'https://docs.google.com/document/d/1J3drUDOdKG2JgOuqDpYcFE8tdC1IPFO2wOtigitv40Y/edit?usp=sharing';
        $privacyPolicyUrl = 'https://docs.google.com/document/d/1H6EKhbYHNcoV7w5Yr6vcgtE2868MIHxujTNtbOrddUE/edit?usp=sharing';

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Авторизоваться через Google',
                        'url' => $authUrl
                    ]
                ],
                [
                    [
                        'text' => 'Публичная оферта',
                        'url' => $publicOfferUrl
                    ]
                ],
                [
                    [
                        'text' => 'Политика конфиденциальности',
                        'url' => $privacyPolicyUrl
                    ]
                ],
                [
                    [
                        'text' => 'Информация о работе бота',
                        'callback_data' => 'workInfo'
                    ]
                ],
                [
                    [
                        'text' => 'Доступные тарифы',
                        'callback_data' => 'tarifs'
                    ]
                ],
            ],
        ];

        if (isset($update['message']['text']) && $update['message']['text'] === '/start') {
            $chatId = $update['message']['chat']['id'];
            Http::post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage", [
                'chat_id' => $chatId,
                'text' => "👋 Добро пожаловать! Для использования бота, пожалуйста, авторизуйтесь:",
                'reply_markup' => $keyboard,
            ]);
        }
        return response()->json(['ok' => true]);
    }
}
