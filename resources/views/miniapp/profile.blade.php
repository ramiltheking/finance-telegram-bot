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
    <a href="/miniapp" class="btn">Назад</a>

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
