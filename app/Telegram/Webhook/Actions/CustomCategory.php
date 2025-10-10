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
        $user = User::where('telegram_id', $this->chat_id)->first();
        $userLang = $user?->settings?->language ?? 'ru';

        $buttons = InlineButton::create()
            ->add("🟩🟩", "Possibilities", [], 1)
            ->add("← Назад", "FinancialAccounting", [], 2)
            ->add("⌂ Меню", "Possibilities", [], 2)
            ->get();

        $photoId = "AgACAgIAAxkBAAII0WjnlESlgr5f4WPFz4WfYnzObB8fAAKhAzIbuZg4S_V-7vCRfLtEAQADAgADeQADNgQ";

        Telegram::editMessageMedia(
            $this->chat_id,
            $photoId,
            'photo',
            "🏷️ <b>Персональные категории</b>\n\n" .
            "✨ <b>Зачем нужны:</b>\n" .
            "• Точный учет по вашим привычкам\n" .
            "• Детальная аналитика расходов\n" .
            "• Удобная группировка операций\n\n" .
            "📱 <b>Как добавить:</b>\n" .
            "1. Откройте Mini App бота\n" .
            "2. Перейдите в «Настройки»\n" .
            "3. Выберите «Мои категории»\n" .
            "4. Нажмите «+ Добавить категорию»\n\n" .
            "💡 <b>Советы:</b>\n" .
            "• Делайте названия простыми, чтобы Вам было удобнее воспользоваться ими;\n" .
            "• Корректно указывайте тип добавляемой Вами категории, чтобы не допустить ошибки в подсчетах;\n" .
            "• Персональные категории дают Вам больше возможностей, чем базовые категории – воспользуйте ими, чтобы лучше анализировать свои финансы.",
            $buttons,
            $this->message_id,
        )->send();
    }
}
