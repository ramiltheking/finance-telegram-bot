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
        <div class="card-header error">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor"
                class="bi bi-exclamation-circle" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7
              0 0 1 0 14m0 1A8 8 0 1 0 8 0a8
              8 0 0 0 0 16" />
                <path d="M7.002 11a1 1 0 1
              1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905
              0 1 1 1.8 0l-.35 3.507a.552.552
              0 0 1-1.1 0z" />
            </svg>
            Оплата не прошла
        </div>
        <div class="card-body">
            @if ($payment)
                <p><strong>Номер заказа:</strong> {{ $payment->inv_id }}</p>
                <p><strong>Сумма:</strong> {{ $payment->amount }} ₸</p>
                <p><strong>Статус:</strong> {{ $payment->status }}</p>
            @else
                <p>Платёж не найден.</p>
            @endif
            <a href="{{ route('miniapp.index') }}" class="back-link">← Вернуться на главную</a>
        </div>
    </div>

</body>

</html>
