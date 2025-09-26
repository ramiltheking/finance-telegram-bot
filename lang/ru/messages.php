<?php

return [
    'welcome' => '👋 Добро пожаловать, :name!',
    'reminder' => '🔔 Не забудьте записать операции за сегодняшний день.',

    // используется в файлах Text.php & VoiceMessage.php
    'audio_message_exceeds' => '❗ Длительность аудиосообщения превышает 20 секунд. Пожалуйста, отправьте более короткое сообщение.',
    'audio_message_failed' => '❗ Не удалось распознать голосовое сообщение',
    'operation_parse_failed' => '❗ Не удалось распознать операцию',
    'user_not_found' => '❗ Пользователь не найден.',
    'income_text' => '✅ Добавить запись: Получил(-a) :amount :currency — :title',
    'expense_text' => '✅ Добавить запись: Потратил(-a) :amount :currency — :title',
    'confirm' => '✅ Подтвердить',
    'decline' => '❌ Отклонить',

    'record_not_found' => '❌ Запись не найдена',
    'record_added' => '✅ Запись добавлена:',
    'record_rejected' => '❌ Запись отклонена:',
    'income_label' => '➕ Доход',
    'expense_label' => '➖ Расход',
    'category_label' => '📂 Категория: :category',
    'description_label' => '📝 :description',
    'date_label' => '📅 Дата: :date',
    'amount_label' => '💰 Сумма: :amount :currency',
];
