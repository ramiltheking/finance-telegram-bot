<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Настройки</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/css/settings.css">
</head>

<body>
    <header class="header">
        <a href="{{ route('miniapp.profile') }}" class="prev-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                class="bi bi-arrow-left-circle" viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                    d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z" />
            </svg>
        </a>
        <div class="header-title">
            <strong>{{ __('settings.title') }}</strong>
        </div>
    </header>

    <main class="main">
        <div class="card subscription-card">
            <h3>🤖 Управление подпиской</h3>

            <div class="subscription-info">
                <div class="info-row">
                    <span class="info-label">Статус:</span>
                    <span class="info-value" id="subscriptionStatus">Загрузка...</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Подписка до:</span>
                    <span class="info-value" id="subscriptionEnds">Загрузка...</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Следующий платеж:</span>
                    <span class="info-value" id="nextPayment">Загрузка...</span>
                </div>
            </div>

            <div class="toggle-wrapper recurring-toggle">
                <input type="checkbox" id="recurringToggle" class="toggle-input">
                <label for="recurringToggle" class="toggle-label"></label>
                <span class="toggle-text">Автопродление подписки</span>
            </div>

            <div id="recurringInfo" class="recurring-info hidden">
                <p class="info-note">✅ Автопродление включено. Следующий платеж произойдет автоматически.</p>
                <button id="manageSubscription" class="manage-btn">Управление подпиской</button>
            </div>

            <div id="recurringDisabled" class="recurring-info">
                <p class="info-note">🔒 Совершите первый платеж для включения автопродления</p>
                <a href="{{ route('miniapp.tarifs') }}" class="subscribe-btn">Оформить подписку</a>
            </div>
        </div>

        <div class="card currency-card">
            <h3>{{ __('settings.currency') }}</h3>
            <div class="buttons__group">
                <button data-currency="KZT">KZT</button>
                <button data-currency="RUB">RUB</button>
                <button data-currency="USD">USD</button>
                <button data-currency="EUR">EUR</button>
            </div>
        </div>

        <div class="card language-card">
            <h3>{{ __('settings.language') }}</h3>
            <div class="buttons__group">
                <button data-lang="ru">Русский</button>
                <button data-lang="en">English</button>
                {{-- <button data-lang="kz">Қазақша</button> --}}
            </div>
        </div>

        <div class="card timezone-card">
            <h3>{{ __('settings.timezone') }}</h3>
            <strong id="userTimezone"></strong><br><br>
            <button id="detectTimezone">{{ __('settings.detect_timezone') }}</button>
        </div>

        <div class="card reminders-card">
            <h3>{{ __('settings.reminders') }}</h3>
            <div class="toggle-wrapper">
                <input type="checkbox" id="reminderToggle" class="toggle-input">
                <label for="reminderToggle" class="toggle-label"></label>
                <span class="toggle-text">{{ __('settings.enable_reminders') }}</span>
            </div>
            <div id="reminderTime" class="time-settings hidden">
                <label>
                    <select id="reminderHour"></select>
                </label>

                <label>
                    <select id="reminderMinute"></select>
                </label>
            </div>
        </div>
    </main>

    <script>
        window.userSettings = @json($settings);
        window.subscriptionInfo = @json($subscriptionInfo ?? []);
    </script>
    <script src="/js/settings.js"></script>

</body>

</html>
