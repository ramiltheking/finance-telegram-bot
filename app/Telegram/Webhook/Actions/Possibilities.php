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
        $this->detectUserLanguage();

        $buttons = InlineButton::create()
            ->add(__('buttons.financial_tracking'), "FinancialAccounting", [], 1)
            ->add(__('buttons.financial_analytics'), "FinancialAnalytics", [], 2)
            ->add(__('buttons.personal_categories'), "CustomCategory", [], 3)
            ->add(__('buttons.export_operations'), "ExportOperations", [], 4)
            ->add(__('buttons.back'), "BackStart", [], 5)
            ->get();

        $isCallbackQuery = $this->request->input('callback_query');

        if ($isCallbackQuery) {
            $hasPhoto = $this->request->input('callback_query.message.photo');
            $hasCaption = $this->request->input('callback_query.message.caption');

            if ($hasPhoto || $hasCaption) {
                Telegram::deleteMessage($this->chat_id, $this->message_id);
                Telegram::inlineButtons($this->chat_id, __('messages.capabilities_title'), $buttons)->send();
            } else {
                Telegram::editButtons($this->chat_id, __('messages.capabilities_title'), $buttons, $this->message_id)->send();
            }
        } else {
            Telegram::inlineButtons($this->chat_id, __('messages.capabilities_title'), $buttons)->send();
        }
    }

    private function detectUserLanguage()
    {
        $user = User::where('telegram_id', $this->chat_id)->first();
        $this->userLang = $user?->settings?->language ?? 'ru';
    }
}
