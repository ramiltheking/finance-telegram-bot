<?php

namespace App\Telegram\Webhook;

use App\Facades\Telegram;
use Illuminate\Http\Request;

class Webhook {
    protected Request $request;
    protected $chat_id;
    protected $message_id;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->getUserMessage();
    }

    public function run() {
        return Telegram::message(env("TELEGRAM_DEV_CHAT"), "Не удалось обработать сообщение! Подробности:\n<code>{$this->request}</code>")->send();
    }

    final public function getUserMessage()
    {
        if($this->request->input('callback_query'))
        {
            $this->chat_id = $this->request->input('callback_query')['from']['id'];
            $this->message_id = $this->request->input('callback_query')['message']['message_id'];
        }
        elseif($this->request->input('message'))
        {
            $this->chat_id = $this->request->input('message')['from']['id'];
            $this->message_id = $this->request->input('message')['message_id'];
        }
    }
}
