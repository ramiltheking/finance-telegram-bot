<?php

namespace App\Telegram\Webhook\Payment;

use App\Facades\Telegram;
use App\Services\TelegramPaymentService;
use App\Telegram\Webhook\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PreCheckoutHandler extends Webhook
{
    public function run()
    {
        $preCheckoutQuery = $this->request->input('pre_checkout_query');
        $preCheckoutQueryId = $preCheckoutQuery['id'];
        $from = $preCheckoutQuery['from'];
        $invoicePayload = $preCheckoutQuery['invoice_payload'];

        Log::info('PreCheckout Query Received', [
            'pre_checkout_query_id' => $preCheckoutQueryId,
            'user_id' => $from['id'],
            'invoice_payload' => $invoicePayload,
            'currency' => $preCheckoutQuery['currency'],
            'total_amount' => $preCheckoutQuery['total_amount']
        ]);

        $ok = TelegramPaymentService::handlePreCheckout([
            'user_id' => $from['id'],
            'invoice_payload' => $invoicePayload,
            'currency' => $preCheckoutQuery['currency'],
            'total_amount' => $preCheckoutQuery['total_amount']
        ]);

        if ($ok) {
            Log::info('PreCheckout подтвержден', ['pre_checkout_query_id' => $preCheckoutQueryId]);
            Telegram::answerPreCheckoutQuery($preCheckoutQueryId, true);
        } else {
            Log::warning('PreCheckout отклонен', ['pre_checkout_query_id' => $preCheckoutQueryId]);
            Telegram::answerPreCheckoutQuery($preCheckoutQueryId, false, "Платеж не может быть обработан");
        }
    }
}
