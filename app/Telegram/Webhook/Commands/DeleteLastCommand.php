<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Models\Operation;
use App\Models\User;
use App\Telegram\Webhook\Webhook;

class DeleteLastCommand extends Webhook
{
    public function run()
    {
        $userId = $this->chat_id;

        $user = User::where('telegram_id', $userId)->first();
        if (!$user) {
            Telegram::message($userId, trans('commands.delete_last.user_not_found'), $this->message_id)->send();
            return;
        }

        $lastOperation = Operation::where('user_id', $userId)
            ->where('status', 'confirmed')
            ->orderByDesc('created_at')
            ->first();

        if (!$lastOperation) {
            Telegram::message($userId, trans('commands.delete_last.no_operations'), $this->message_id)->send();
            return;
        }

        $lastOperation->delete();

        Telegram::message($userId, trans('commands.delete_last.success'))->send();
    }
}
