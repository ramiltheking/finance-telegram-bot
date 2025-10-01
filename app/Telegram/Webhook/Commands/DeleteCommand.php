<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Models\User;
use App\Models\Operation;
use App\Telegram\Webhook\Webhook;

class DeleteCommand extends Webhook
{
    public function run()
    {
        $userId = $this->chat_id;
        $text = $this->request->input('message.text');
        $args = explode(' ', $text);

        if (count($args) < 2) {
            Telegram::message($userId, trans('commands.delete.invalid_format'), $this->message_id)->send();
            return;
        }

        $index = (int)$args[1];
        if ($index <= 0) {
            Telegram::message($userId, trans('commands.delete.invalid_number'), $this->message_id)->send();
            return;
        }

        $user = User::where('telegram_id', $userId)->first();
        if (!$user) {
            Telegram::message($userId, trans('commands.delete.user_not_found'), $this->message_id)->send();
            return;
        }

        $operations = Operation::where('user_id', $user->telegram_id)
            ->where('occurred_at', '>=', now()->subDays(7))
            ->where('status', 'confirmed')
            ->orderByDesc('occurred_at')
            ->get();

        if (!isset($operations[$index - 1])) {
            Telegram::message($userId, trans('commands.delete.operation_not_found', ['index' => $index]), $this->message_id)->send();
            return;
        }

        $operation = $operations[$index - 1];
        $operation->delete();

        Telegram::message($userId, trans('commands.delete.success', ['index' => $index]))->send();
    }
}
