<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Профиль</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <link rel="stylesheet" href="/css/profile.css">
</head>

<body>
    <header class="header">
        <a href="{{ route('miniapp.index') }}" class="prev-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-arrow-left-circle" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
            </svg>
        </a>
        <div class="header-title">
            <strong>Профиль</strong>
        </div>
    </header>

    <div class="card" style="text-align: center;">
        <img id="userPhoto" class="avatar" src="" alt="avatar">
        <div class="username" id="username"></div>
        <div id="fullname"></div>
        <div id="telegramId"></div>
    </div>

    <div class="card" id="subscription">

    </div>

    <div class="card">
        <h3>💳 История оплат</h3>
        <div class="list payments" id="payments"></div>
    </div>

    <script src="/js/profile.js"></script>
</body>

</html>
