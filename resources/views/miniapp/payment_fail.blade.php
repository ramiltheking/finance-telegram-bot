<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Оплата не прошла</title>
</head>

<body>
    <h2>❌ Оплата не прошла</h2>
    @if ($payment)
        <p>Номер заказа: {{ $payment->inv_id }}</p>
        <p>Сумма: {{ $payment->amount }} ₸</p>
        <p>Статус: {{ $payment->status }}</p>
    @else
        <p>Платёж не найден.</p>
    @endif
</body>

</html>
