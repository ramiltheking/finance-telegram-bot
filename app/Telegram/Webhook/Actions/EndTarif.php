<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Services\TelegramPaymentService;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;
use App\Models\User;

class EndTarif extends Webhook
{
    public function run()
    {
        // $priceInKZT = '2500.00';
        // $tariffName = "Стандартный";
        // $url = env("APP_URL") . "/miniapp/tarifs";

        // InlineButton::web_app("💰 Оплатить {$tariffName} тариф", $url, 1);
        // Telegram::inlineButtons($this->chat_id, "⏳ У вас закончился пробный период или подписка.\n\nПожалуйста, оплатите для дальнейшего использования.\n\n📦 Тариф: <b>{$tariffName}</b> — {$priceInKZT} ₸ в месяц", InlineButton::$buttons)->send();

        $user = User::where('telegram_id', $this->chat_id)->first();
        $userLang = $user?->settings?->language ?? 'ru';

        $payload = TelegramPaymentService::createSubscriptionPayload($this->chat_id, 'monthly');

        $invoiceResponse = Telegram::createInvoiceLink(
            trans('actions.end_tarif.invoice_title', [], $userLang),
            trans('actions.end_tarif.invoice_description', [], $userLang),
            $payload,
            [
                [
                    'label' => trans('actions.end_tarif.invoice_label', [], $userLang),
                    'amount' => 250
                ]
            ],
            'XTR'
        )->send();

        if ($invoiceResponse['ok']) {
            $invoiceUrl = $invoiceResponse['result'];

            $message = trans('actions.end_tarif.expired_message', [], $userLang) . "\n";
            $message .= trans('actions.end_tarif.payment_request', [], $userLang) . "\n\n";
            $message .= trans('actions.tarifs.title', [], $userLang) . "\n\n";
            $message .= trans('actions.tarifs.feature_unlimited', [], $userLang) . "\n";
            $message .= trans('actions.tarifs.feature_voice', [], $userLang) . "\n";
            $message .= trans('actions.tarifs.feature_analytics', [], $userLang) . "\n";
            $message .= trans('actions.tarifs.feature_reminders', [], $userLang) . "\n";
            $message .= trans('actions.tarifs.feature_export', [], $userLang) . "\n\n";
            $message .= trans('actions.tarifs.payment_prompt', [], $userLang);

            InlineButton::link(trans('actions.end_tarif.pay_button', [], $userLang), $invoiceUrl);
            Telegram::inlineButtons($this->chat_id, $message, InlineButton::$buttons)->send();
        } else {
            $message = trans('actions.end_tarif.expired_message', [], $userLang);
            Telegram::message($this->chat_id, $message)->send();
        }
    }
}
