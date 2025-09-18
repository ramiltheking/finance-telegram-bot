<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Finance MiniApp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/css/index.css">
</head>

<body>
    <header class="header">
        <div class="subscription-info" id="subStatus">

        </div>

        <a href="/miniapp/profile" class="profile" id="userProfile">
            <img src="" alt="avatar" id="userPhoto" class="avatar">
            <span id="username" class="username"></span>
        </a>
    </header>

    <main class="main">
        <div class="card">
            <h3>📊 Статистика</h3>
            <canvas id="chart"></canvas>
            <div id="legend" class="legend"></div>
        </div>

        <div class="card">
            <h3>📋 Последние операции</h3>
            <div class="list operations" id="operations"></div>
        </div>

        <div class="card">
            <h3>📂 Экспорт</h3>
            <div class="export-links">
                <a href="/miniapp/export/excel">Excel</a>
                <a href="/miniapp/export/pdf">PDF</a>
                <a href="/miniapp/export/word">Word</a>
            </div>
        </div>
    </main>

    <script src="/js/index.js"></script>
</body>

</html>
