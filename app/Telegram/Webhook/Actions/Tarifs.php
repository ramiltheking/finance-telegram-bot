<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;

class Tarifs extends Webhook
{
    public function run() {
        $priceInKZT = '2500.00';
        $tariffName = "Стандартный";
        $url = env("APP_URL") . "/miniapp/tarifs";

        InlineButton::web_app("💰 Оплатить {$tariffName} тариф", $url, 1);
        Telegram::inlineButtons($this->chat_id, "🎁 Стартовый период — 2 недели.\n\n📦 Далее взимается тарифная помесячная оплата:\n\n💼 <b>{$tariffName}</b> — {$priceInKZT} ₸ в месяц\n\nНажимая оплатить я даю согласие на регулярные списания, на обработку персональных данных и принимаю условия публичной оферты", InlineButton::$buttons)->send();
    }
}
