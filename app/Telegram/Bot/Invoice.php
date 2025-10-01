<?php

namespace App\Telegram\Bot;

use App\Models\TelegramPayment;

class Invoice extends Bot
{
    protected $data;
    protected $method;

    public function createInvoiceLink(string $title, string $description, string $payload, array $prices, string $period = "2592000")
    {
        $this->method = 'createInvoiceLink';
        $this->data = [
            'title' => $title,
            'description' => $description,
            'payload' => $payload,
            'provider_token' => env('TELEGRAM_PAYMENT_PROVIDER_TOKEN', ''),
            'currency' => 'XTR',
            'prices' => json_encode($prices),
            'subscription_period' => $period,
        ];

        return $this;
    }

    public function createInvoice($chatId, string $title, string $description, string $payload, array $prices, string $currency = 'XTR')
    {
        $this->method = 'sendInvoice';
        $this->data = [
            'chat_id' => $chatId,
            'title' => $title,
            'description' => $description,
            'payload' => $payload,
            'provider_token' => env('TELEGRAM_PAYMENT_PROVIDER_TOKEN', ''),
            'currency' => $currency,
            'prices' => json_encode($prices),
        ];

        return $this;
    }

    public function refundPaymentByModel(TelegramPayment $payment)
    {
        $this->method = 'refundStarPayment';
        $this->data = [
            'user_id' => $payment->user->telegram_id,
            'telegram_payment_charge_id' => $payment->telegram_payment_charge_id,
            'currency' => $payment->currency,
            'total_amount' => $payment->amount,
            'invoice_payload' => $payment->invoice_payload,
        ];

        if ($payment->provider_payment_charge_id) {
            $this->data['provider_payment_charge_id'] = $payment->provider_payment_charge_id;
        }

        return $this->send();
    }

    public function addPrice(string $label, int $amount)
    {
        if (!isset($this->data['prices'])) {
            $this->data['prices'] = [];
        }

        $this->data['prices'][] = [
            'label' => $label,
            'amount' => $amount
        ];

        return $this;
    }

    public function send()
    {
        if ($this->method === 'sendInvoice' && isset($this->data['prices']) && is_array($this->data['prices'])) {
            $this->data['prices'] = json_encode($this->data['prices']);
        }

        return parent::send();
    }

    public function setProviderToken(string $token)
    {
        $this->data['provider_token'] = $token;
        return $this;
    }

    public function setCurrency(string $currency)
    {
        $this->data['currency'] = $currency;
        return $this;
    }

    public function setProviderData(string $providerData)
    {
        $this->data['provider_data'] = $providerData;
        return $this;
    }

    public function setPhotoUrl(string $photoUrl)
    {
        $this->data['photo_url'] = $photoUrl;
        return $this;
    }

    public function setPhotoSize(int $width, int $height)
    {
        $this->data['photo_width'] = $width;
        $this->data['photo_height'] = $height;
        return $this;
    }

    public function setNeedName(bool $need = true)
    {
        $this->data['need_name'] = $need;
        return $this;
    }

    public function setNeedEmail(bool $need = true)
    {
        $this->data['need_email'] = $need;
        return $this;
    }

    public function setNeedPhone(bool $need = true)
    {
        $this->data['need_phone_number'] = $need;
        return $this;
    }

    public function setNeedAddress(bool $need = true)
    {
        $this->data['need_shipping_address'] = $need;
        return $this;
    }

    public function setSendPhoneNumberToProvider(bool $send = true)
    {
        $this->data['send_phone_number_to_provider'] = $send;
        return $this;
    }

    public function setSendEmailToProvider(bool $send = true)
    {
        $this->data['send_email_to_provider'] = $send;
        return $this;
    }

    public function setIsFlexible(bool $flexible = true)
    {
        $this->data['is_flexible'] = $flexible;
        return $this;
    }

    public function setStartParameter(string $startParameter)
    {
        $this->data['start_parameter'] = $startParameter;
        return $this;
    }

    public function clear()
    {
        $this->data = [];
        $this->method = null;
        return $this;
    }
}
