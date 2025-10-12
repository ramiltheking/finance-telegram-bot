<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Webhook;
use App\Models\User;

class FinancialAnalytics extends Webhook
{
    private $userLang = 'ru';

    public function run()
    {
        $this->detectUserLanguage();

        $miniapp_url = env('APP_URL') . '/miniapp';

        $buttons = InlineButton::create()
            ->add("🟩❇️⬜", "Possibilities", [], 1)
            ->add(__('buttons.weekly_report'), "RedirectReportCommand", [], 2)
            ->add(__('buttons.full_report'), "RedirectFullReportCommand", [], 2)
            ->web_app(__('buttons.statistics'), $miniapp_url, 3)
            ->add(__('buttons.back'), "FinancialAccounting", [], 4)
            ->add(__('buttons.next'), "CustomCategory", [], 4)
            ->get();

        $photoId = null;

        Telegram::editButtons(
            $this->chat_id,
            // $photoId,
            // 'photo',
            __('messages.financial_tracking_title') .
            __('messages.financial_analytics_weekly') .
            __('messages.financial_analytics_full') .
            __('messages.financial_analytics_cta'),
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
