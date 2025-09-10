<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Telegram\Webhook\Webhook;

class StartCommand extends Webhook
{
    public function run()
    {
        $chatId = $this->request->input('message')['from']['id'];
        $first_name = $this->request->input('message')['from']['first_name'];
        $buttons = [
            'inline_keyboard' => [
                [['text' => 'Публичная оферта', 'url' => 'https://docs.google.com/document/d/1J3drUDOdKG2JgOuqDpYcFE8tdC1IPFO2wOtigitv40Y/edit?usp=sharing']],
                [['text' => 'Политика конфиденциальности', 'url' => 'https://docs.google.com/document/d/1H6EKhbYHNcoV7w5Yr6vcgtE2868MIHxujTNtbOrddUE/edit?usp=sharing']],
                [['text' => 'Информация о работе бота', 'callback_data' => 'WorkInfo']],
                [['text' => 'Доступные тарифы', 'callback_data' => 'Tarifs']],
            ]
        ];

        return Telegram::inlineButtons($chatId, "👋 Добро пожаловать, {$first_name}!", $buttons)->send();
    }
}
