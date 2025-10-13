<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;
use App\Models\User;

class CustomCategory extends Webhook
{
    private $userLang = 'ru';

    public function run()
    {
        $this->detectUserLanguage();

        $miniapp_settings_url = env('APP_URL') . '/miniapp/settings';

        $buttons = InlineButton::create()
            ->add("🟩🟩❇️⬜", "Possibilities", [], 1)
            ->web_app(__('buttons.add_category'), $miniapp_settings_url, 2)
            ->add(__('buttons.back'), "FinancialAnalytics", [], 3)
            ->add(__('buttons.next'), "ExportOperations", [], 3)
            ->get();

        $photoId = null;

        Telegram::editButtons(
            $this->chat_id,
            // $photoId,
            // 'photo',
            __('messages.personal_categories_title') .
            __('messages.personal_categories_description') .
            __('messages.personal_categories_why') .
            __('messages.personal_categories_how') .
            __('messages.personal_categories_tips') .
            __('messages.personal_categories_grouping') .
            __('messages.personal_categories_types'),
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
