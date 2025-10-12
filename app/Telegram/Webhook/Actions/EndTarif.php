<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Services\TelegramPaymentService;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;
use App\Models\User;

class EndTarif extends Webhook
{
    private $userLang = 'ru';

    public function run()
    {
        // $priceInKZT = '2500.00';
        // $tariffName = "Стандартный";
        // $url = env("APP_URL") . "/miniapp/tarifs";

        // InlineButton::web_app("💰 Оплатить {$tariffName} тариф", $url, 1);
        // Telegram::inlineButtons($this->chat_id, "⏳ У вас закончился пробный период или подписка.\n\nПожалуйста, оплатите для дальнейшего использования.\n\n📦 Тариф: <b>{$tariffName}</b> — {$priceInKZT} ₸ в месяц", InlineButton::$buttons)->send();

        $this->detectUserLanguage();

        $payload = TelegramPaymentService::createSubscriptionPayload($this->chat_id, 'monthly');

        $invoiceResponse = Telegram::createInvoiceLink(
            trans('actions.end_tarif.invoice_title', [], $this->userLang),
            trans('actions.end_tarif.invoice_description', [], $this->userLang),
            $payload,
            [
                [
                    'label' => trans('actions.end_tarif.invoice_label', [], $this->userLang),
                    'amount' => 250
                ]
            ],
            'XTR'
        )->send();

        if ($invoiceResponse['ok']) {
            $invoiceUrl = $invoiceResponse['result'];

            $message = trans('actions.end_tarif.expired_message', [], $this->userLang) . "\n";
            $message .= trans('actions.end_tarif.payment_request', [], $this->userLang) . "\n\n";
            $message .= trans('actions.tarifs.title', [], $this->userLang) . "\n\n";
            $message .= trans('actions.tarifs.feature_unlimited', [], $this->userLang) . "\n";
            $message .= trans('actions.tarifs.feature_voice', [], $this->userLang) . "\n";
            $message .= trans('actions.tarifs.feature_analytics', [], $this->userLang) . "\n";
            $message .= trans('actions.tarifs.feature_reminders', [], $this->userLang) . "\n";
            $message .= trans('actions.tarifs.feature_export', [], $this->userLang) . "\n\n";
            $message .= trans('actions.tarifs.payment_prompt', [], $this->userLang);

            $buttons = InlineButton::create()->link(trans('actions.end_tarif.pay_button', [], $this->userLang), $invoiceUrl)->get();
            Telegram::inlineButtons($this->chat_id, $message, $buttons)->send();
        } else {
            $message = trans('actions.end_tarif.expired_message', [], $this->userLang);
            Telegram::message($this->chat_id, $message)->send();
        }
    }

    private function detectUserLanguage()
    {
        $user = User::where('telegram_id', $this->chat_id)->first();
        $this->userLang = $user?->settings?->language ?? 'ru';
    }
}
