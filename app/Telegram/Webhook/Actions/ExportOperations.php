<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;
use App\Models\User;

class ExportOperations extends Webhook
{
    private $userLang = 'ru';

    public function run()
    {
        $this->detectUserLanguage();

        $miniapp_url = env('APP_URL') . '/miniapp';

        $buttons = InlineButton::create()
            ->add("🟩🟩🟩❇️", "Possibilities", [], 1)
            ->web_app(__('buttons.export'), $miniapp_url, 2)
            ->add(__('buttons.back'), "CustomCategory", [], 3)
            ->add(__('buttons.menu'), "Possibilities", [], 3)
            ->get();

        $photoId = null;

        Telegram::editButtons(
            $this->chat_id,
            // $photoId,
            // 'photo',
            __('messages.export_operations'),
            $buttons,
            $this->message_id,
        )->send();
    }

    private function detectUserLanguage()
    {
        $user = User::where('telegram_id', $this->chat_id)->first();
        $this->userLang = $user?->settings?->language ?? 'ru';
    }
}
