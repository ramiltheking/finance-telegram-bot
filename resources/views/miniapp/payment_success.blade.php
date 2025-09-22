<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Статус оплаты</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/payments.css">
</head>

<body>

    <div class="card">
        <div class="card-header success">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor"
                class="bi bi-check2-circle" viewBox="0 0 16 16">
                <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0
              0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0
              0 0-1 0 5.5 5.5 0 1 1-11 0" />
                <path d="M15.354 3.354a.5.5 0 0
              0-.708-.708L8 9.293 5.354 6.646a.5.5
              0 1 0-.708.708l3 3a.5.5 0 0
              0 .708 0z" />
            </svg>
            Оплата прошла успешно!
        </div>
        <div class="card-body">
            <p><strong>Номер заказа:</strong> {{ $payment->inv_id }}</p>
            <p><strong>Сумма:</strong> {{ $payment->amount }} ₸</p>
            <p><strong>Статус:</strong> {{ $payment->status }}</p>
            <a href="{{ route('miniapp.index') }}" class="back-link">← Вернуться на главную</a>
        </div>
    </div>

</body>

</html>
