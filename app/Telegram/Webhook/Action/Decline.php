<?php

namespace App\Telegram\Webhook\Action;

use App\Facades\Telegram;
use App\Telegram\Webhook\Webhook;

class Decline extends Webhook
{
    public function run() {
        $messageId = $this->request->input('callback_query')['message']['message_id'];
        Telegram::editButtons($this->chat_id, "❌ Запись отклонена", null, $messageId)->send();
    }
}
