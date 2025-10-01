<?php

namespace App\Telegram\Bot;

use Illuminate\Support\Facades\Http;

class Bot
{
    protected $data;
    protected $method;

    public function send()
    {
        return Http::post(
            "https://api.telegram.org/bot" . env("TELEGRAM_BOT_TOKEN") . "/" . $this->method,
            $this->data
        )->json();
    }

    public function answerPreCheckoutQuery($preCheckoutQueryId, $ok, $errorMessage = null)
    {
        $this->method = 'answerPreCheckoutQuery';
        $this->data = [
            'pre_checkout_query_id' => $preCheckoutQueryId,
            'ok' => $ok
        ];

        if (!$ok && $errorMessage) {
            $this->data['error_message'] = $errorMessage;
        }

        return $this->send();
    }

    public function answerShippingQuery($shippingQueryId, $ok, $shippingOptions = null, $errorMessage = null)
    {
        $this->method = 'answerShippingQuery';
        $this->data = [
            'shipping_query_id' => $shippingQueryId,
            'ok' => $ok
        ];

        if ($ok && $shippingOptions) {
            $this->data['shipping_options'] = $shippingOptions;
        } elseif (!$ok && $errorMessage) {
            $this->data['error_message'] = $errorMessage;
        }

        return $this->send();
    }

    public function sendRequest($method, $data)
    {
        $this->method = $method;
        $this->data = $data;
        return $this->send();
    }
}
