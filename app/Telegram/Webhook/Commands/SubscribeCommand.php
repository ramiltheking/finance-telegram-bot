<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Services\TelegramPaymentService;
use App\Telegram\Webhook\Webhook;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Telegram\Helpers\InlineButton;

class SubscribeCommand extends Webhook
{
    private $subscriptionPlans = [
        'monthly' => [
            'price' => 250,
            'days' => 30,
            'period' => "2592000",
            'currency' => 'XTR'
        ],
        'yearly' => [
            'price' => 2500,
            'days' => 365,
            'period' => "31536000",
            'currency' => 'XTR'
        ]
    ];

    public function run()
    {
        $userId = $this->chat_id;
        $text = $this->request->input('message.text');
        $args = explode(' ', $text);

        $planType = $args[1] ?? 'monthly';
        $planType = in_array($planType, ['monthly', 'yearly']) ? $planType : 'monthly';

        Log::info('SubscribeCommand started', [
            'chat_id' => $userId,
            'plan_type' => $planType,
            'provider_token_set' => !empty(env('TELEGRAM_PAYMENT_PROVIDER_TOKEN')),
        ]);

        $user = User::where('telegram_id', $userId)->first();
        $userLang = $user?->settings?->language ?? 'ru';

        $plan = $this->subscriptionPlans[$planType];
        $payload = TelegramPaymentService::createSubscriptionPayload($userId, $planType);

        $invoiceResponse = Telegram::createInvoiceLink(
            trans("commands.subscribe.{$planType}_title", [], $userLang),
            trans("commands.subscribe.{$planType}_description", ['days' => $plan['days']], [], $userLang),
            $payload,
            [
                [
                    'label' => trans("commands.subscribe.{$planType}_label", [], $userLang),
                    'amount' => $plan['price']
                ]
            ],
            $plan['currency'],
            $plan['period']
        )->send();

        if ($invoiceResponse['ok']) {
            $invoiceUrl = $invoiceResponse['result'];

            $message = trans("commands.subscribe.{$planType}_title", [], $userLang) . "\n\n";
            $message .= trans("actions.tarifs.feature_unlimited", [], $userLang) . "\n";
            $message .= trans("actions.tarifs.feature_voice", [], $userLang) . "\n";
            $message .= trans("actions.tarifs.feature_analytics", [], $userLang) . "\n";
            $message .= trans("actions.tarifs.feature_reminders", [], $userLang) . "\n";
            $message .= trans("actions.tarifs.feature_export", [], $userLang) . "\n\n";
            $message .= trans("actions.tarifs.payment_prompt", [], $userLang);

            $buttons = InlineButton::create()->link(trans('actions.tarifs.pay_button', [], $userLang), $invoiceUrl)->get();
            Telegram::inlineButtons($this->chat_id, $message, $buttons)->send();
        } else {
            Telegram::message($this->chat_id, trans('commands.subscribe.invoice_failed', [], $userLang))->send();
        }
    }
}
