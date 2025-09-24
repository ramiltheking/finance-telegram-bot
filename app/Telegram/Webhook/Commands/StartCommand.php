<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;

class StartCommand extends Webhook
{
    public function run()
    {
        $first_name = $this->request->input('message')['from']['first_name'];
        $miniapp_url = env('APP_URL') . '/miniapp';
        InlineButton::link('Публичная оферта', 'https://docs.google.com/document/d/1J3drUDOdKG2JgOuqDpYcFE8tdC1IPFO2wOtigitv40Y/edit?usp=sharing', 1);
        InlineButton::link('Политика конфиденциальности', 'https://docs.google.com/document/d/1H6EKhbYHNcoV7w5Yr6vcgtE2868MIHxujTNtbOrddUE/edit?usp=sharing', 2);
        InlineButton::add('Информация о работе бота', 'WorkInfo', [], 3);
        InlineButton::add('Доступные тарифы', 'Tarifs', [], 4);
        InlineButton::web_app('Финансовая статистика', $miniapp_url, 5);
        return Telegram::inlineButtons($this->chat_id, __('messages.welcome', ['name' => $first_name]), InlineButton::$buttons)->send();
    }
}
