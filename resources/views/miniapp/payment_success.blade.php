<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Оплата успешна</title>
</head>

<body>
    <h2>✅ Оплата прошла успешно!</h2>
    <p>Номер заказа: {{ $payment->inv_id }}</p>
    <p>Сумма: {{ $payment->amount }} ₸</p>
    <p>Статус: {{ $payment->status }}</p>
</body>

</html>
