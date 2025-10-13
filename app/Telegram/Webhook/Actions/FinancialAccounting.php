<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;
use App\Models\User;

class FinancialAccounting extends Webhook
{
    private $userLang = 'ru';

    public function run()
    {
        $this->detectUserLanguage();

        $buttons = InlineButton::create()
            ->add("❇️⬜⬜⬜", "Possibilities", [], 1)
            ->add(__('buttons.menu'), "Possibilities", [], 2)
            ->add(__('buttons.next'), "FinancialAnalytics", [], 2)
            ->get();

        $photoId = null;

        Telegram::editButtons(
            $this->chat_id,
            // $photoId,
            // 'photo',
            __('messages.financial_tracking_title') .
            __('messages.financial_tracking_howto') .
            __('messages.financial_tracking_tips') .
            __('messages.financial_tracking_edit'),
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
