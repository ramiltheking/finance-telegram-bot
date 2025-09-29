<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Оплата тарифа</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/tarifs.css">
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #1cc88a;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #343a40;
            --text: #444;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--light);
            color: var(--text);
            margin: 0;
            padding: 0;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin: 20px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.4s ease;
        }

        h2 {
            color: var(--dark);
            margin-bottom: 15px;
        }

        p {
            font-size: 15px;
            color: var(--text);
            margin: 10px 0;
        }

        b {
            color: var(--primary);
        }

        .pay-btn {
            display: inline-block;
            padding: 14px 28px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            border: none;
            border-radius: 12px;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-top: 20px;
        }

        .pay-btn:hover {
            background: linear-gradient(135deg, #43A047, #1B5E20);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        .pay-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .card {
                padding: 20px;
            }

            h2 {
                font-size: 18px;
            }

            p {
                font-size: 14px;
            }

            .pay-btn {
                width: 90%;
                padding: 12px;
                font-size: 15px;
            }
        }
    </style>
    <script src="/js/checkFromTelegram.js"></script>
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
