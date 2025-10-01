<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Services\TelegramPaymentService;
use App\Telegram\Webhook\Webhook;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class SubscribeCommand extends Webhook
{
    private $subscriptionPlans = [
        'monthly' => [
            'price' => 250,
            'days' => 30,
            'currency' => 'XTR'
        ],
        'yearly' => [
            'price' => 2500,
            'days' => 365,
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

        try {
            $response = Telegram::createInvoice(
                $userId,
                trans("commands.subscribe.{$planType}_title", [], $userLang),
                trans("commands.subscribe.{$planType}_description", ['days' => $plan['days']], $userLang),
                $payload,
                [
                    [
                        'label' => trans("commands.subscribe.{$planType}_label", [], $userLang),
                        'amount' => $plan['price']
                    ]
                ],
                $plan['currency']
            )->send();

            if (!$response['ok']) {
                $errorMessage = $response['description'] ?? 'Неизвестная ошибка';
                Log::error('Invoice creation failed', [
                    'error' => $errorMessage,
                    'error_code' => $response['error_code'] ?? 'unknown',
                    'plan_type' => $planType
                ]);

                Telegram::message($userId, trans('commands.subscribe.invoice_failed', [], $userLang))->send();
            }
        } catch (\Exception $e) {
            Log::error('SubscribeCommand exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'plan_type' => $planType
            ]);

            Telegram::message($userId, trans('commands.subscribe.invoice_error', [], $userLang))->send();
        }
    }
}
