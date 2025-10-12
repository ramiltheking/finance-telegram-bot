<?php

return [
    'welcome' => "👋 Welcome, :name!",
    'welcome_introduction' => "<b>🤖 I'm your personal finance assistant - Finly</b>\n\n" .
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

    'financial_analytics_title' => "📊 <b>Financial Analytics — Your Personal Financial Advisor!</b>\n\n",
    'financial_analytics_weekly' => "🔍 <b>Weekly Report:</b>\n📅 Financial overview for the last 7 days\n💰 Income and expenses with category breakdown\n📈 Top-10 categories with percentage ratios\n⚖️ Balance with color indicators\n💱 Automatic currency conversion\n\n",
    'financial_analytics_full' => "📈 <b>Full Report (all history):</b>\n📊 Monthly analytics and trends\n📉 Top-15 income and expense categories\n📅 Comparison of different periods\n🎯 Identifying financial trends and habits\n💡 Insights for budget optimization\n\n",
    'financial_analytics_benefits' => "✨ <b>What You'll Discover:</b>\n🎯 What you spend the most on\n📈 Whether your income is growing over time\n💡 Where you can save without sacrifice\n⚖️ How healthy your financial balance is\n📊 Effectiveness of your financial decisions\n\n",
    'financial_analytics_cta' => "🚀 <b>Start analyzing today!</b>\nChoose report type below 👇",

    'financial_tracking_title' => "💫 <b>Financial Tracking Made Simple!</b>\n\n",
    'financial_tracking_howto' => "📝 <b>How to Add an Operation:</b>\n– Send a message with any amount and category\n– Or send a voice message with financial operation\n– For example: `5000 groceries` or `150000 salary`\n\n",
    'financial_tracking_tips' => "✨ <b>What You Need to Know:</b>\n– ➕/➖ I set automatically\n– Amount only → record goes to «Other»\n– Default date is today\n– For another date — specify it below\n\n",
    'financial_tracking_edit' => "🔄 <b>Any record can be modified or deleted</b>\n\n📸 See examples above 👆",

    'capabilities_title' => '📋 List of My Capabilities:',

    'personal_categories_title' => "🏷️ <b>Personal Categories</b>\n\n",
    'personal_categories_description' => "Personal categories give you more capabilities than basic categories – use them to better analyze your finances.\n\n",
    'personal_categories_why' => "✨ <b>Why You Need Them:</b>\n– Accurate tracking according to your habits\n– Detailed expense analytics\n– Convenient grouping of operations\n\n",
    'personal_categories_how' => "📱 <b>How to Add:</b>\n1. Open the bot's Mini App\n2. Go to «Settings»\n3. Select «My Categories»\n4. Click «+ Add Category»\n\n",
    'personal_categories_tips' => "💡 <b>Tips:</b>\n\n",
    'personal_categories_grouping' => "📦 <b>Combine similar expenses into common categories:</b>\n✨ <i>Instead of many small categories — a few clear groups</i>\n  • \"Clothes\", \"Shoes\", \"Accessories\" = <b>Shopping</b>\n  • \"Taxi\", \"Subway\", \"Buses\" = <b>Transport</b>\n  • \"Cinema\", \"Concerts\", \"Entertainment\" = <b>Leisure</b>\n",
    'personal_categories_naming' => "🏷️ <b>Give categories simple and clear names</b>\n  • It will be easier to select them when adding operations\n  • Example: instead of «Grocery shopping at supermarket» — just «Groceries»\n",
    'personal_categories_types' => "✅ <b>Carefully choose the category type</b>\n  • «Income» — for money coming in (salary, gifts)\n  • «Expense» — for money going out (purchases, services)\n  • This is important for correct balance calculation",

    // используется в файлах Text.php & VoiceMessage.php
    'audio_message_exceeds' => '❗ The audio message exceeds 20 seconds. Please send a shorter message.',
    'audio_message_failed' => '❗ Could not recognize the voice message',
    'operation_parse_failed' => "❗ Could not recognize the operation.\n\n🤖 I only understand <b>financial transactions</b>, for example:\n• «Received salary of 150000 KZT»\n• «Spent 5000 on lunch»\n• «Transferred 20000 for rent»\n\n🎯 Use the buttons below for other actions:",
    'user_not_found' => '❗ User not found.',
    'income_text' => '✅ Add record: Received :amount :currency — :title',
    'expense_text' => '✅ Add record: Spent :amount :currency — :title',
    'confirm' => '✅ Confirm',
    'decline' => '❌ Decline',
    'balance_positive' => '📈 +:amount :currency',
    'balance_negative' => '📉 -:amount :currency',

    'unknown_request' => "🤔 I didn't understand your request. Try using the /help command or typing 'Help'.",

    'record_not_found' => '❌ Record not found',
    'record_added' => '✅ Record added:',
    'record_rejected' => '❌ Record rejected:',
    'income_label' => '➕ Income',
    'expense_label' => '➖ Expense',
    'category_label' => '📂 Category: :category',
    'description_label' => '📝 :description',
    'date_label' => '📅 Date: :date',
    'amount_label' => '💰 Amount: :amount :currency',

    'ai_actions' => [
        'processing_full_report' => "📊 Generating your complete financial report...",
        'processing_operations_list' => "📋 Getting your operations list...",
        'processing_balance' => "🪙 Calculating your current balance...",
        'processing_weekly_report' => "📅 Preparing weekly report...",
    ],
];
