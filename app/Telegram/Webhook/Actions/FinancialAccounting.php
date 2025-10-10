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
        $user = User::where('telegram_id', $this->chat_id)->first();
        $userLang = $user?->settings?->language ?? 'ru';

        $buttons = InlineButton::create()
            ->add("🟩⬜", "Possibilities", [], 1)
            ->add("← Назад", "Possibilities", [], 2)
            ->add("Далее →", "CustomCategory", [], 2)
            ->get();

        $photoId = "AgACAgIAAxkBAAIImmjnhmKKmRQzNoQJ-2ASG6EZsf4ZAAIaAzIbuZg4S3BK8n0UMRSMAQADAgADeQADNgQ";

        Telegram::editMessageMedia(
            $this->chat_id,
            $photoId,
            'photo',
            "💫 <b>Учет финансов — это просто!</b>\n\n" .
            "📝 <b>Как добавить операцию:</b>\n" .
            "• Отправте сообщение с любои суммой и категорией\n" .
            "• Или голосовое сообщение с финансовой операцией\n" .
            "• Например: `5000 продукты` или `150000 зарплата`\n\n" .
            "✨ <b>Что нужно знать:</b>\n" .
            "• ➕/➖ ставлю автоматически\n" .
            "• Только сумма → запись в «Прочие»\n" .
            "• Дата по умолчанию — сегодня\n" .
            "• Для другой даты — укажите её ниже\n\n" .
            "🔄 <b>Любую запись можно изменить или удалить</b>\n\n" .
            "📸 Смотрите примеры выше 👆",
            $buttons,
            $this->message_id,
        )->send();
    }
}
