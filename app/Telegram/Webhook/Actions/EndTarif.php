<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;

class EndTarif extends Webhook
{
    public function run()
    {
        $priceInKZT = '2500.00';
        $tariffName = "Стандартный";
        $url = env("APP_URL") . "/miniapp/tarifs";

        InlineButton::web_app("💰 Оплатить {$tariffName} тариф", $url, 1);
        Telegram::inlineButtons($this->chat_id, "⏳ У вас закончился пробный период или подписка.\n\nПожалуйста, оплатите для дальнейшего использования.\n\n📦 Тариф: <b>{$tariffName}</b> — {$priceInKZT} ₸ в месяц", InlineButton::$buttons)->send();
    }
}
