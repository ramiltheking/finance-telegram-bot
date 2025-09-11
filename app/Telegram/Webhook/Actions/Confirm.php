<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Telegram\Webhook\Webhook;
use Illuminate\Support\Facades\DB;

class Confirm extends Webhook
{
    public function run() {
        $callback = $this->request->input('callback_query');
        $data = json_decode($callback['data'], true);

        $operationId = $data['operation_id'];
        $messageId = $callback['message']['message_id'];

        DB::table('operations')
            ->where('id', $operationId)
            ->where('user_id', $this->chat_id)
            ->update([
                'status' => 'confirmed',
                'updated_at' => now(),
            ]);

        Telegram::editButtons($this->chat_id, "✅ Запись добавлена", null, $messageId)->send();
    }
}
