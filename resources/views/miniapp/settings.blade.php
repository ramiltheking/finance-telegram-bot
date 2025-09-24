<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Настройки</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            <strong>Настройки</strong>
        </div>
    </header>

    <main class="main">
        <div class="card">
            <h3>💱 Валюта</h3>
            <div class="buttons__group">
                <button>KZT</button>
                <button>RUB</button>
                <button>USD</button>
                <button>EUR</button>
            </div>
        </div>

        <div class="card">
            <h3>⏰ Часовой пояс</h3>
            <strong id="userTimezone"></strong><br><br>
            <button id="detectTimezone">Определить автоматически</button>
        </div>

        <div class="card">
            <h3>🔔 Напоминания</h3>
            <div class="toggle-wrapper">
                <input type="checkbox" id="reminderToggle" class="toggle-input">
                <label for="reminderToggle" class="toggle-label"></label>
                <span class="toggle-text">Включить ежедневные напоминания</span>
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

        <div class="card">
            <h3>🌐 Язык</h3>
            <div class="buttons__group">
                <button data-lang="ru">Русский</button>
                <button data-lang="en">English</button>
                <button data-lang="kz">Қазақша</button>
            </div>
        </div>
    </main>

    <script>
        window.userSettings = @json($settings);
    </script>
    <script src="/js/settings.js"></script>

</body>

</html>
