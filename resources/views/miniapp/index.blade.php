<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Finance MiniApp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--light);
            color: var(--text);
            margin: 0;
            padding: 20px;
        }

        h2,
        h3 {
            margin-top: 5px;
            margin-bottom: 25px;
            color: var(--dark);
        }

        a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        a:hover {
            text-decoration: underline;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 20px;
        }

        canvas {
            max-width: 100%;
            height: auto;
        }

        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .legend div {
            background: #f1f1f1;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 14px;
        }

        .list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .operation {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            padding: 12px 15px;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            font-size: 15px;
        }

        .op-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }

        .op-income {
            background: #e6f9ed;
            color: #1cc88a;
        }

        .op-expense {
            background: #fdeaea;
            color: #e74a3b;
        }

        .op-text {
            flex: 1;
            color: #444;
        }

        .op-amount {
            font-weight: 600;
            color: var(--primary);
        }

        .list-item {
            background: #fff;
            padding: 12px 15px;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            font-size: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .list-item span {
            font-weight: 600;
            color: var(--primary);
        }

        .export-links a {
            margin-right: 12px;
            padding: 8px 14px;
            border: 1px solid var(--primary);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .export-links a:hover {
            background: var(--primary);
            color: #fff;
        }

        .subscribe a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 16px;
            background: var(--secondary);
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.2s;
        }

        .subscribe a:hover {
            background: #17a673;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            .list-item {
                font-size: 14px;
                padding: 10px 12px;
            }

            h2,
            h3 {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>
    <div class="card">
        <h2>📊 Статистика</h2>
        <canvas id="chart"></canvas>
        <div id="legend" class="legend"></div>
    </div>

    <div class="card">
        <h3>📋 Последние операции</h3>
        <div class="list operations" id="operations"></div>
    </div>

    <div class="card">
        <h3>💳 История оплат</h3>
        <div class="list payments" id="payments"></div>
    </div>

    <div class="card">
        <h3>📂 Экспорт</h3>
        <div class="export-links">
            <a href="/miniapp/export/excel">Excel</a>
            <a href="/miniapp/export/pdf">PDF</a>
            <a href="/miniapp/export/word">Word</a>
        </div>
    </div>

    <div class="card subscribe">
        <h3>💰 Подписка</h3>
        <a href="{{ route('tarifs') }}">Оплатить через Robokassa</a>
    </div>

    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script>
        const tg = window.Telegram.WebApp;
        tg.expand();

        const userId = tg.initDataUnsafe?.user?.id;

        fetch(`/miniapp/data?telegram_id=${userId}`)
            .then(res => res.json())
            .then(data => {
                const ctx = document.getElementById('chart').getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(data.categories),
                        datasets: [{
                            data: Object.values(data.categories),
                            backgroundColor: ['#f66', '#6f6', '#66f', '#fc6', '#6cf']
                        }]
                    }
                });

                document.getElementById('legend').innerHTML =
                    Object.keys(data.categories)
                    .map(cat => `<div>${cat}</div>`)
                    .join('');

                document.getElementById('operations').innerHTML =
                    data.operations.map(op => {
                        const isIncome = op.type === 'income';
                        return `
                            <div class="operation">
                            <div class="op-icon ${isIncome ? 'op-income' : 'op-expense'}">
                                ${isIncome ? '+' : '−'}
                            </div>
                            <div class="op-text">${op.category}</div>
                            <div class="op-amount">${op.amount} ${op.currency}</div>
                            </div>
                        `;
                    }).join('');

                document.getElementById('payments').innerHTML =
                    data.payments
                    .map(p => `
                            <div class="list-item">
                                ID: ${p.inv_id} <span>${p.amount} (${p.status})</span>
                            </div>`
                    ).join('');
            });
    </script>
</body>

</html>
