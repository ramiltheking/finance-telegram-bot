<?php

return [
    'welcome' => "👋 Welcome, :name!\n\n" .
    "<b>🤖 I'm your personal finance assistant - Finly</b>\n\n" .
    "<b>✨ My key Features:</b>\n" .
    "📝 Smart transaction tracking via text or voice\n" .
    "📊 Detailed analytics and visualization\n" .
    "🔔 Custom categories and reminders\n" .
    "💡 AI-powered financial advice\n\n" .
    "<b>🚀 Quick Start:</b>\n" .
    "1️⃣ Type <b>\"Spent 5000 on groceries\"</b>\n" .
    "2️⃣ Or send a voice message\n" .
    "3️⃣ I'll automatically recognize and record everything\n\n" .
    "🎯 <b>Start right now!</b> Just tell me about any financial transaction, and I'll help you track it.\n\n" .
    "💡 <b>Tip: Try sending a voice message - it works just as easily!</b>",
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
