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
            Telegram::message($this->chat_id, "Возврат средств можно запросить через поддержку бота. Напиши нам с прозьбой возврата и опишите причину с приложенным счетом оплаты транзакции.")->send();
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
                Telegram::message($this->chat_id, "❌ Платеж не найден.\nUser ID: {$params['user_id']}\nCharge ID: {$params['charge_id']}")->send();
                return;
            }

            if ($payment->status === 'refunded') {
                Telegram::message($this->chat_id, "⚠️ Этот платеж уже был возвращен ранее.")->send();
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

                $message = "✅ Платеж успешно возвращен!\n\n";
                $message .= "💳 Сумма: {$payment->amount} {$payment->currency}\n";
                $message .= "👤 Пользователь: {$user->first_name}";
                $message .= $user->username ? " (@{$user->username})" : "";
                $message .= "\n🆔 Charge ID: {$params['charge_id']}";

                Telegram::message($this->chat_id, $message)->send();

                Telegram::message($user->telegram_id, "💰 Ваш платеж был возвращен. Подписка деактивирована.")->send();
            } else {
                $errorMessage = $response['description'] ?? 'Неизвестная ошибка';
                Log::error('Ошибка возврата платежа: ', [
                    'user_id' => $params['user_id'],
                    'charge_id' => $params['charge_id'],
                    'error' => $errorMessage
                ]);
                Telegram::message($this->chat_id, "❌ Ошибка возврата: {$errorMessage}")->send();
            }
        } catch (\Exception $e) {
            Log::error('Исключение команды возврата: ', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Telegram::message($this->chat_id, "❌ Ошибка: " . $e->getMessage())->send();
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
        return "💰 Возврат платежа\n\n" .
            "📝 Использование:\n" .
            "/refund [user_id] [charge_id]\n\n" .
            "Где:\n" .
            "- user_id - ID пользователя (число)\n" .
            "- charge_id - ID платежа Stars\n\n" .
            "⚠️ Внимание: Эта операция необратима!";
    }
}
