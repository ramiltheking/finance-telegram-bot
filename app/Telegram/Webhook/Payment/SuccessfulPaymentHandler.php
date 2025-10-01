<?php

namespace App\Telegram\Webhook\Payment;

use App\Facades\Telegram;
use App\Services\TelegramPaymentService;
use App\Telegram\Webhook\Webhook;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class SuccessfulPaymentHandler extends Webhook
{
    public function run()
    {
        $message = $this->request->input('message');
        $successfulPayment = $message['successful_payment'];
        $from = $message['from'];

        $user = User::where('telegram_id', $from['id'])->first();
        $userLang = $user?->settings?->language ?? 'ru';

        Log::info(trans('payment.successful.payment_received', [], $userLang), [
            'user_id' => $from['id'],
            'telegram_payment_charge_id' => $successfulPayment['telegram_payment_charge_id'],
            'total_amount' => $successfulPayment['total_amount'],
            'currency' => $successfulPayment['currency']
        ]);

        try {
            $payment = TelegramPaymentService::handleSuccessfulPayment([
                'user_id' => $from['id'],
                'telegram_payment_charge_id' => $successfulPayment['telegram_payment_charge_id'],
                'provider_payment_charge_id' => $successfulPayment['provider_payment_charge_id'],
                'total_amount' => $successfulPayment['total_amount'],
                'currency' => $successfulPayment['currency'],
                'invoice_payload' => $successfulPayment['invoice_payload'],
            ]);

            $this->sendSuccessMessage($payment, $userLang);

        } catch (\Exception $e) {
            Log::error(trans('payment.successful.log.exception', [], $userLang), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $from['id']
            ]);

            Telegram::message(
                $this->chat_id,
                trans('payment.successful.payment_error', [], $userLang)
            )->send();
        }
    }

    private function sendSuccessMessage($payment, $userLang)
    {
        if (!$payment) {
            Log::error(trans('payment.successful.log.payment_null', [], $userLang), [
                'user_id' => $this->chat_id
            ]);
            Telegram::message(
                $this->chat_id,
                trans('payment.successful.payment_issue', [], $userLang)
            )->send();
            return;
        }

        $payment->load('user');

        if ($payment->user && $payment->user->subscription_ends_at) {
            $dateFormat = $userLang === 'ru' ? 'd.m.Y' : 'Y-m-d';
            $endDate = $payment->user->subscription_ends_at->format($dateFormat);

            $message = trans('payment.successful.payment_completed', ['date' => $endDate], $userLang) . "\n\n";
            $message .= trans('payment.successful.features_unlocked', [], $userLang) . "\n";
            $message .= "✅ " . trans('payment.successful.feature_unlimited', [], $userLang) . "\n";
            $message .= "✅ " . trans('payment.successful.feature_voice', [], $userLang) . "\n";
            $message .= "✅ " . trans('payment.successful.feature_analytics', [], $userLang) . "\n\n";
            $message .= trans('payment.successful.thank_you', [], $userLang);

            Telegram::message($this->chat_id, $message)->send();
        } else {
            Log::warning(trans('payment.successful.log.user_not_found', [], $userLang), [
                'payment_id' => $payment->id,
                'user_exists' => !is_null($payment->user),
                'subscription_ends_at' => $payment->user->subscription_ends_at ?? 'null'
            ]);
            Telegram::message(
                $this->chat_id,
                trans('payment.successful.payment_processing', [], $userLang)
            )->send();
        }
    }
}
