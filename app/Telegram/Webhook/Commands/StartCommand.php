<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Services\TelegramPaymentService;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class StartCommand extends Webhook
{
    private $userLang = 'ru';

    public function run()
    {
        $this->detectUserLanguage();

        $messageText = $this->request->input('message.text');
        $params = $this->parseStartParams($messageText);

        $action = $params['start'] ?? null;

        switch ($action) {
            case 'subscribe':
                return $this->handleSubscribeStart();

            case 'help':
                return $this->handleHelpStart();

            default:
                return $this->handleRegularStart();
        }
    }

    private function detectUserLanguage()
    {
        $user = User::where('telegram_id', $this->chat_id)->first();
        $this->userLang = $user?->settings?->language ?? 'ru';
    }

    private function parseStartParams(string $messageText): array
    {
        $params = [];
        $parts = explode(' ', $messageText);

        if (count($parts) > 1) {
            $startParam = $parts[1];

            if (str_contains($startParam, '=')) {
                parse_str($startParam, $params);
            } else {
                $params['start'] = $startParam;
            }
        }

        return $params;
    }

    private function handleSubscribeStart()
    {
        $payload = TelegramPaymentService::createSubscriptionPayload($this->chat_id, 'monthly');

        $response = Telegram::createInvoice(
            $this->chat_id,
            trans('commands.start.subscribe.invoice_title', [], $this->userLang),
            trans('commands.start.subscribe.invoice_description', [], $this->userLang),
            $payload,
            [
                [
                    'label' => trans('commands.start.subscribe.invoice_label', [], $this->userLang),
                    'amount' => 250
                ]
            ],
            'XTR'
        )->send();

        if ($response['ok']) {
            Log::info(trans('commands.start.subscribe.success_log', [], $this->userLang), [
                'chat_id' => $this->chat_id
            ]);
        } else {
            Log::error(trans('commands.start.subscribe.error_log', [], $this->userLang), [
                'chat_id' => $this->chat_id,
                'response' => $response
            ]);
            Telegram::message($this->chat_id, trans('commands.start.subscribe.invoice_failed', [], $this->userLang))->send();
        }
    }

    private function handleHelpStart() {
        return;
    }

    private function handleRegularStart()
    {
        $first_name = $this->request->input('message')['from']['first_name'];
        $miniapp_url = env('APP_URL') . '/miniapp';

        $buttons = InlineButton::create()->add(trans('commands.start.buttons.work_info', [], $this->userLang), 'WorkInfo', [], 1)
                   ->add(trans('commands.start.buttons.tarifs', [], $this->userLang), 'Tarifs', [], 2)
                   ->web_app(trans('commands.start.buttons.statistics', [], $this->userLang), $miniapp_url, 3)
                   ->get();

        return Telegram::inlineButtons($this->chat_id, trans('messages.welcome', ['name' => $first_name], $this->userLang), $buttons)->send();
    }
}
