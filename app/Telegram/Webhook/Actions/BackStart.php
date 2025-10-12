<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;
use App\Models\User;
use App\Telegram\Helpers\KeyboardButton;

class BackStart extends Webhook
{
    private $userLang = 'ru';

    public function run()
    {
        $this->detectUserLanguage();

        $miniapp_url = env('APP_URL') . '/miniapp';

        $buttons = InlineButton::create()->add(trans('commands.start.buttons.work_info', [], $this->userLang), 'WorkInfo', [], 1)
                   ->add(trans('commands.start.buttons.bot_capabilities', [], $this->userLang), 'Possibilities', [], 2)
                   ->add(trans('commands.start.buttons.tarifs', [], $this->userLang), 'Tarifs', [], 3)
                   ->web_app(trans('commands.start.buttons.statistics', [], $this->userLang), $miniapp_url, 4)
                   ->get();

        if ($this->request->input('callback_query.message.photo') || $this->request->input('callback_query.message.caption')) {
            Telegram::deleteMessage($this->chat_id, $this->message_id)->send();
            Telegram::inlineButtons($this->chat_id, trans('messages.welcome_introduction', [], $this->userLang), $buttons)->send();
        } else {
            return Telegram::editButtons($this->chat_id, trans('messages.welcome_introduction', [], $this->userLang), $buttons, $this->message_id)->send();
        }
    }

    private function detectUserLanguage()
    {
        $user = User::where('telegram_id', $this->chat_id)->first();
        $this->userLang = $user?->settings?->language ?? 'ru';
    }
}
