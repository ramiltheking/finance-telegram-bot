<?php

namespace App\Services;

use App\Models\User;
use App\Models\TelegramPayment;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TelegramPaymentService
{
    public static function createSubscriptionPayload($userId, $planType = 'monthly')
    {
        return "{$userId}:sub:{$planType}:" . now()->timestamp;
    }

    public static function parseInvoicePayload($payload)
    {
        $parts = explode(':', $payload);
        return [
            'user_id' => $parts[0] ?? null,
            'type' => $parts[1] ?? null,
            'plan' => $parts[2] ?? null,
            'timestamp' => $parts[3] ?? null
        ];
    }

    public static function handlePreCheckout(array $preCheckoutData)
    {
        $userId = $preCheckoutData['user_id'];
        $invoicePayload = $preCheckoutData['invoice_payload'];

        Log::info('Handling PreCheckout', [
            'user_id' => $userId,
            'invoice_payload' => $invoicePayload
        ]);

        $payloadData = self::parseInvoicePayload($invoicePayload);

        if (!$payloadData['user_id'] || $payloadData['user_id'] != $userId) {
            Log::error('Invalid payload user ID', [
                'payload_user_id' => $payloadData['user_id'],
                'actual_user_id' => $userId
            ]);
            return false;
        }

        $user = User::where('telegram_id', $userId)->first();
        if (!$user) {
            Log::error('User not found', ['user_id' => $userId]);
            return false;
        }

        Log::info('PreCheckout validation passed', ['user_id' => $userId]);
        return true;
    }

    public static function handleSuccessfulPayment(array $paymentData)
    {
        Log::info('Процесс успешной оплаты', $paymentData);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($paymentData) {
            $userId = $paymentData['user_id'];
            $invoicePayload = $paymentData['invoice_payload'];

            $user = User::where('telegram_id', $userId)->first();
            if (!$user) {
                Log::error('User not found for successful payment', ['telegram_id' => $userId]);
                return null;
            }

            $currentSubscriptionEndsAt = $user->subscription_ends_at;

            if ($currentSubscriptionEndsAt && $currentSubscriptionEndsAt->isFuture()) {
                $subscriptionEndsAt = $currentSubscriptionEndsAt->addMonth();
            } else {
                $subscriptionEndsAt = now()->addMonth();
            }

            $user->update([
                'subscription_status' => 'active',
                'subscription_started_at' => now(),
                'subscription_ends_at' => $subscriptionEndsAt,
            ]);

            $payment = TelegramPayment::create([
                'user_id' => $userId,
                'telegram_payment_charge_id' => $paymentData['telegram_payment_charge_id'],
                'provider_payment_charge_id' => $paymentData['provider_payment_charge_id'],
                'amount' => $paymentData['total_amount'],
                'currency' => $paymentData['currency'],
                'invoice_payload' => $invoicePayload,
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            return $payment;
        });
    }
}
