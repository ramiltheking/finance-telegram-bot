<?php

namespace App\Telegram\Webhook\Other;

use App\Telegram\Webhook\Webhook;
use Illuminate\Support\Facades\Log;

class Other extends Webhook
{
    public function run()
    {
        Log::info('Пропущен апдейт', ['update' => $this->request->all(),]);
        return response()->json(['ok' => true]);
    }
}
