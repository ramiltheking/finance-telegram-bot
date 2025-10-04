<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Models\TelegramPayment;
use App\Models\User;
use App\Telegram\Webhook\Webhook;
use Illuminate\Support\Facades\Log;

class RefundCommand extends Webhook
{
    public function run()
    {
        $messageFromDev = $this->request->input('message.from.id') == env('TELEGRAM_DEV_CHAT');

        if (!$messageFromDev) {
            $message = trans('commands.refund.only_for_support');
            Telegram::message($this->chat_id, $message)->send();
            return;
        }

        $messageText = $this->request->input('message.text');

        $params = $this->parseSimpleFormat($messageText);

        if (!$params) {
            Telegram::message($this->chat_id, $this->getUsageHelp())->send();
            return;
        }

        try {
            $payment = TelegramPayment::where('telegram_payment_charge_id', $params['charge_id'])
                ->whereHas('user', function ($query) use ($params) {
                    $query->where('telegram_id', $params['user_id']);
                })
                ->first();

            if (!$payment) {
                $message = trans('commands.refund.payment_not_found', [
                    'user_id' => $params['user_id'],
                    'charge_id' => $params['charge_id']
                ]);
                Telegram::message($this->chat_id, $message)->send();
                return;
            }

            if ($payment->status === 'refunded') {
                Telegram::message($this->chat_id, trans('commands.refund.already_refunded'))->send();
                return;
            }

            $response = Telegram::refundPaymentByModel($payment);

            if ($response['ok']) {
                $payment->update(['status' => 'refunded']);

                $user = $payment->user;
                $user->update([
                    'subscription_status' => 'expired',
                    'subscription_ends_at' => now(),
                ]);

                Log::info('Payment refunded successfully', [
                    'payment_id' => $payment->id,
                    'user_id' => $user->telegram_id,
                    'charge_id' => $params['charge_id']
                ]);

                $message = trans('commands.refund.success_message') . "\n\n";
                $message .= trans('commands.refund.amount', [
                    'amount' => $payment->amount,
                    'currency' => $payment->currency
                ]) . "\n";
                $message .= trans('commands.refund.user', [
                    'name' => $user->first_name,
                    'username' => $user->username ? " (@{$user->username})" : ""
                ]);
                $message .= "\n" . trans('commands.refund.charge_id', ['charge_id' => $params['charge_id']]);

                Telegram::message($this->chat_id, $message)->send();

                $userMessage = trans('commands.refund.user_notification');
                Telegram::message($user->telegram_id, $userMessage)->send();
            } else {
                $errorMessage = $response['description'] ?? trans('commands.refund.unknown_error');
                Log::error('Ошибка возврата платежа: ', [
                    'user_id' => $params['user_id'],
                    'charge_id' => $params['charge_id'],
                    'error' => $errorMessage
                ]);
                $message = trans('commands.refund.refund_error', ['error' => $errorMessage]);
                Telegram::message($this->chat_id, $message)->send();
            }
        } catch (\Exception $e) {
            Log::error('Исключение команды возврата: ', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $message = trans('commands.refund.exception_error', ['error' => $e->getMessage()]);
            Telegram::message($this->chat_id, $message)->send();
        }
    }

    private function parseSimpleFormat(string $messageText): ?array
    {
        $parts = explode(' ', $messageText);

        array_shift($parts);

        if (count($parts) < 2) {
            return null;
        }

        $user_id = trim($parts[0]);
        $charge_id = trim($parts[1]);

        if (!is_numeric($user_id) || empty($charge_id)) {
            return null;
        }

        return [
            'user_id' => (int)$user_id,
            'charge_id' => $charge_id
        ];
    }

    private function getUsageHelp(): string
    {
        return trans('commands.refund.usage_help');
    }
}
