<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Services\TelegramPaymentService;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;
use App\Models\User;

class Tarifs extends Webhook
{
    private $userLang = 'ru';

    public function run()
    {
        // $priceInKZT = '2500.00';
        // $tariffName = "Стандартный";
        // $url = env("APP_URL") . "/miniapp/tarifs";

        // $message = "🎁 Стартовый период — 2 недели.\n\n📦 Далее взимается тарифная помесячная оплата:\n\n💼 <b>{$tariffName}</b> — {$priceInKZT} ₸ в месяц\n\nНажимая оплатить я даю согласие на регулярные списания, на обработку персональных данных и принимаю условия публичной оферты";
        // InlineButton::web_app("💰 Оплатить {$tariffName} тариф", $url, 1);

        $this->detectUserLanguage();

        $payload = TelegramPaymentService::createSubscriptionPayload($this->chat_id, 'monthly');

        $invoiceResponse = Telegram::createInvoiceLink(
            trans('actions.tarifs.invoice_title', [], $this->userLang),
            trans('actions.tarifs.invoice_description', [], $this->userLang),
            $payload,
            [
                [
                    'label' => trans('actions.tarifs.invoice_label', [], $this->userLang),
                    'amount' => 250
                ]
            ],
            'XTR'
        )->send();

        if ($invoiceResponse['ok']) {
            $invoiceUrl = $invoiceResponse['result'];

            $message = trans('actions.tarifs.title', [], $this->userLang) . "\n\n";
            $message .= trans('actions.tarifs.feature_unlimited', [], $this->userLang) . "\n";
            $message .= trans('actions.tarifs.feature_voice', [], $this->userLang) . "\n";
            $message .= trans('actions.tarifs.feature_analytics', [], $this->userLang) . "\n";
            $message .= trans('actions.tarifs.feature_reminders', [], $this->userLang) . "\n";
            $message .= trans('actions.tarifs.feature_export', [], $this->userLang) . "\n\n";
            $message .= trans('actions.tarifs.payment_prompt', [], $this->userLang);

            $buttons = InlineButton::create()->link(trans('actions.tarifs.pay_button', [], $this->userLang), $invoiceUrl, 1)->add(__('buttons.back'), "BackStart", [], 2)->get();

            $isCallbackQuery = $this->request->input('callback_query');

            if ($isCallbackQuery) {
                Telegram::editButtons($this->chat_id, $message, $buttons, $this->message_id)->send();
            } else {
                Telegram::inlineButtons($this->chat_id, $message, $buttons)->send();
            }
        } else {
            Telegram::message($this->chat_id, trans('actions.tarifs.invoice_failed', [], $this->userLang))->send();
        }
    }

    private function detectUserLanguage()
    {
        $user = User::where('telegram_id', $this->chat_id)->first();
        $this->userLang = $user?->settings?->language ?? 'ru';
    }
}
