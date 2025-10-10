<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;
use App\Models\User;

class Possibilities extends Webhook
{
    private $userLang = 'ru';

    public function run()
    {
        $user = User::where('telegram_id', $this->chat_id)->first();
        $userLang = $user?->settings?->language ?? 'ru';

        $buttons = InlineButton::create()
            ->add("Учет финансов", "FinancialAccounting", [], 1)
            ->add("Персональные категории", "CustomCategory", [], 2)
            ->add("← Назад", "BackStart", [], 3)
            ->get();

        if ($this->request->input('callback_query.message.photo') || $this->request->input('callback_query.message.caption')) {
            Telegram::deleteMessage($this->chat_id, $this->message_id)->send();
            Telegram::inlineButtons($this->chat_id, "📋 Список моих возможностей:", $buttons)->send();
        } else {
            Telegram::editButtons($this->chat_id, "📋 Список моих возможностей:", $buttons, $this->message_id)->send();
        }
    }
}
