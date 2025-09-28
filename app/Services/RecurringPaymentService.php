<?php

namespace App\Services;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class RecurringPaymentService
{
    public function processDuePayments()
    {
        $users = User::where('recurring_enabled', true)
            ->where('subscription_status', 'active')
            ->whereNotNull('recurring_token')
            ->where('next_payment_date', '<=', now())
            ->get();

        $processed = 0;
        $successful = 0;

        foreach ($users as $user) {
            $processed++;

            try {
                $result = $this->processUserPayment($user);

                if ($result) {
                    $successful++;
                    Log::info("Recurring payment successful", ['user_id' => $user->telegram_id]);
                } else {
                    Log::error("Recurring payment failed", ['user_id' => $user->telegram_id]);
                    $this->handleFailedPayment($user);
                }

            } catch (\Exception $e) {
                Log::error("Recurring payment exception", [
                    'user_id' => $user->telegram_id,
                    'error' => $e->getMessage()
                ]);
                $this->handleFailedPayment($user);
            }
        }

        Log::info("Recurring payments processed", [
            'processed' => $processed,
            'successful' => $successful
        ]);

        return ['processed' => $processed, 'successful' => $successful];
    }

    private function processUserPayment(User $user): bool
    {
        $amount = 2500.00;
        $description = 'Автопродление подписки VoiceFinance';

        $payment = Payment::create([
            'user_id' => $user->telegram_id,
            'inv_id' => random_int(100000, 99999999),
            'amount' => $amount,
            'status' => 'processing',
            'is_recurring' => true,
            'recurring_token' => $user->recurring_token,
        ]);

        $result = PaymentService::initiateRecurringPayment(
            $user->recurring_token,
            $amount,
            $description
        );

        if ($result) {
            $payment->update(['status' => 'completed']);

            $user->update([
                'subscription_ends_at' => $user->subscription_ends_at->addDays(30),
                'next_payment_date' => $user->subscription_ends_at,
                'recurring_attempts' => 0,
            ]);

            return true;
        }

        $payment->update(['status' => 'failed']);
        return false;
    }

    private function handleFailedPayment(User $user)
    {
        $user->increment('recurring_attempts');

        if ($user->recurring_attempts >= 3) {
            $user->update(['recurring_enabled' => false]);
            Log::warning("Recurring disabled after 3 failures", ['user_id' => $user->telegram_id]);
        }
    }

    public function enableRecurringForUser(User $user, string $token): bool
    {
        try {
            $user->update([
                'recurring_enabled' => true,
                'recurring_token' => $token,
                'recurring_activated_at' => now(),
                'recurring_attempts' => 0,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to enable recurring", [
                'user_id' => $user->telegram_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
