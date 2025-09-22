<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Оплата тарифа</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/css/tarifs.css">
</head>

<body>
    <div class="card">
        <h2>💼 Тариф: {{ $tariffName }}</h2>
        <p>Стоимость: <b>{{ $price }} ₸</b> в месяц</p>
        <p>После оплаты будет активирована подписка.</p>
        <a href="{{ $url }}" class="pay-btn">💰 Оплатить</a>
    </div>
</body>

</html>
