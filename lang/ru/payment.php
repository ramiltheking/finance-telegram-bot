<?php

return [
    'successful' => [
        'payment_received' => 'Успешное полученние платежа',
        'payment_completed' => '✅ Платеж успешно завершен! Ваша подписка активирована до :date',
        'payment_processing' => '✅ Платеж завершен! Подписка будет активирована в ближайшее время.',
        'payment_issue' => '✅ Платеж завершен, но возникла проблема с активацией подписки. Свяжитесь с поддержкой.',
        'payment_error' => '❌ Ошибка обработки платежа. Обратитесь в поддержку.',

        'features_unlocked' => 'Теперь вам доступны все функции:',
        'features_analytics' => '📊 Расширенной аналитике своих финансов',
        'features_voice' => '🎤 Обработке голосовых сообщений',
        'features_reminders' => '🔔 Умным напоминаниям',
        'features_export' => '📤 Экспортом данных в Excel/PDF/Word',
        'features_unlimited_operations' => '💰 Неограниченным количеством операций',
        'features_personal_categories' => '🗂️ Учет финансов по персональным категориям',
        'thank_you' => 'Спасибо за покупку! 🎉',

        'log' => [
            'user_not_found' => 'Пользователь или дата подписки не найдены после оплаты',
            'payment_null' => 'Обработка платежа вернула null',
            'exception' => 'Исключение в SuccessfulPaymentHandler',
        ],
    ],
];
