<?php

namespace App\Telegram\Webhook\Action;

use App\Facades\Telegram;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Telegram\Webhook\Webhook;

class Tarifs extends Webhook
{
    public function run() {
        $chatId = $this->request->input('callback_query')['from']['id'];
        $priceInKZT = '2500.00';
        $tariffName = "Стандартный";
        $invId = random_int(100000, 99999999);
        $login = env('ROBOKASSA_MERCHANT_LOGIN');
        $pass1 = env('ROBOKASSA_PASSWORD1');
        $signature = PaymentService::makeSignature($login, $priceInKZT, (string)$invId, $pass1);
        $url = PaymentService::buildRobokassaUrl($login, $priceInKZT, (string)$invId, 'Тариф использования бота VoiceFinance', $signature);

        Payment::create(['user_id' => $chatId, 'inv_id' => $invId, 'amount' => $priceInKZT, 'status' => 'pending']);

        $keyboard = ['inline_keyboard' => [[['text' => "💰 Оплатить {$tariffName} тариф", 'url' => $url]]]];
        Telegram::inlineButtons($chatId, "🎁 Стартовый период — 2 недели.\n\n📦 Далее взимается тарифная помесячная оплата:\n\n💼 <b>{$tariffName}</b> — {$priceInKZT} ₸ в месяц\n\nНажимая оплатить я даю согласие на регулярные списания, на обработку персональных данных и принимаю условия публичной оферты", $keyboard)->send();
    }
}
