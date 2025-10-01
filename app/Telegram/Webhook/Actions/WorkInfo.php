<?php

namespace App\Telegram\Webhook\Actions;

use App\Facades\Telegram;
use App\Telegram\Webhook\Webhook;
use App\Models\User;

class WorkInfo extends Webhook
{
    public function run()
    {
        $user = User::where('telegram_id', $this->chat_id)->first();
        $userLang = $user?->settings?->language ?? 'ru';

        $text = $this->generateWorkInfoText($userLang);
        Telegram::message($this->chat_id, $text)->send();
    }

    private function generateWorkInfoText($lang = 'ru')
    {
        return
            trans('actions.work_info.title', [], $lang) . "\n\n" .
            trans('actions.work_info.how_it_works', [], $lang) . "\n" .
            trans('actions.work_info.step_1', [], $lang) . "\n" .
            trans('actions.work_info.step_2', [], $lang) . "\n" .
            trans('actions.work_info.step_3', [], $lang) . "\n" .
            trans('actions.work_info.step_4', [], $lang) . "\n" .
            trans('actions.work_info.step_5', [], $lang) . "\n\n" .
            trans('actions.work_info.available_commands', [], $lang) . "\n" .
            trans('actions.work_info.command_remind', [], $lang) . "\n" .
            trans('actions.work_info.command_report', [], $lang) . "\n" .
            trans('actions.work_info.command_balance', [], $lang) . "\n" .
            trans('actions.work_info.command_delete_last', [], $lang) . "\n" .
            trans('actions.work_info.command_list', [], $lang) . "\n" .
            trans('actions.work_info.command_edit', [], $lang) . "\n" .
            trans('actions.work_info.command_fullreport', [], $lang) . "\n\n" .
            trans('actions.work_info.ai_description', [], $lang) . "\n\n" .
            trans('actions.work_info.final_note', [], $lang);
    }
}
