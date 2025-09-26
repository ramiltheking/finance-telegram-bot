<?php

return [
    'welcome' => '👋 Welcome, :name!',
    'reminder' => '🔔 Don’t forget to log your operations for today.',

    // используется в файлах Text.php & VoiceMessage.php
    'audio_message_exceeds' => '❗ The audio message exceeds 20 seconds. Please send a shorter message.',
    'audio_message_failed' => '❗ Could not recognize the voice message',
    'operation_parse_failed' => '❗ Could not recognize the voice message',
    'user_not_found' => '❗ User not found.',
    'income_text' => '✅ Add record: Received :amount :currency — :title',
    'expense_text' => '✅ Add record: Spent :amount :currency — :title',
    'confirm' => '✅ Confirm',
    'decline' => '❌ Decline',

    'record_not_found' => '❌ Record not found',
    'record_added' => '✅ Record added:',
    'record_rejected' => '❌ Record rejected:',
    'income_label' => '➕ Income',
    'expense_label' => '➖ Expense',
    'category_label' => '📂 Category: :category',
    'description_label' => '📝 :description',
    'date_label' => '📅 Date: :date',
    'amount_label' => '💰 Amount: :amount :currency',
];
