<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Оплата тарифа</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
        }

        .card {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            margin: auto;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #2ea44f;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
        }

        .btn:hover {
            background: #22863a;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2>💼 Тариф: {{ $tariffName }}</h2>
        <p>Стоимость: <b>{{ $price }} ₸</b> в месяц</p>
        <p>После оплаты будет активирована подписка.</p>
        <a href="{{ $url }}" class="btn">💰 Оплатить</a>
    </div>
</body>

</html>
