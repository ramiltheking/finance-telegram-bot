<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Services\TelegramPaymentService;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;
use App\Models\User;

class Tarifs extends Webhook
{
    public function run()
    {
        // $priceInKZT = '2500.00';
        // $tariffName = "Стандартный";
        // $url = env("APP_URL") . "/miniapp/tarifs";

        // $message = "🎁 Стартовый период — 2 недели.\n\n📦 Далее взимается тарифная помесячная оплата:\n\n💼 <b>{$tariffName}</b> — {$priceInKZT} ₸ в месяц\n\nНажимая оплатить я даю согласие на регулярные списания, на обработку персональных данных и принимаю условия публичной оферты";
        // InlineButton::web_app("💰 Оплатить {$tariffName} тариф", $url, 1);

        $user = User::where('telegram_id', $this->chat_id)->first();
        $userLang = $user?->settings?->language ?? 'ru';

        $payload = TelegramPaymentService::createSubscriptionPayload($this->chat_id, 'monthly');

        $invoiceResponse = Telegram::createInvoiceLink(
            trans('actions.tarifs.invoice_title', [], $userLang),
            trans('actions.tarifs.invoice_description', [], $userLang),
            $payload,
            [
                [
                    'label' => trans('actions.tarifs.invoice_label', [], $userLang),
                    'amount' => 250
                ]
            ],
            'XTR'
        )->send();

        if ($invoiceResponse['ok']) {
            $invoiceUrl = $invoiceResponse['result'];

            $message = trans('actions.tarifs.title', [], $userLang) . "\n\n";
            $message .= trans('actions.tarifs.feature_unlimited', [], $userLang) . "\n";
            $message .= trans('actions.tarifs.feature_voice', [], $userLang) . "\n";
            $message .= trans('actions.tarifs.feature_analytics', [], $userLang) . "\n";
            $message .= trans('actions.tarifs.feature_reminders', [], $userLang) . "\n";
            $message .= trans('actions.tarifs.feature_export', [], $userLang) . "\n\n";
            $message .= trans('actions.tarifs.payment_prompt', [], $userLang);

            InlineButton::link(trans('actions.tarifs.pay_button', [], $userLang), $invoiceUrl);
            Telegram::inlineButtons($this->chat_id, $message, InlineButton::$buttons)->send();
        } else {
            Telegram::message($this->chat_id, trans('actions.tarifs.invoice_failed', [], $userLang))->send();
        }
    }
}
