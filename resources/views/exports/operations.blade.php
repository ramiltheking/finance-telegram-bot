<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>История операций</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; }
        h2 { margin-top: 40px; }
    </style>
</head>
<body>
    <h1>История операций</h1>
    <p>Период: {{ now()->subDays(30)->format('d.m.Y') }} - {{ now()->format('d.m.Y') }}</p>

    <h2>Доходы</h2>
    <table>
        <thead>
            <tr>
                <th>Дата</th>
                <th>Категория</th>
                <th>Сумма</th>
            </tr>
        </thead>
        <tbody>
            @foreach($operations->where('type', 'income') as $op)
                <tr>
                    <td>{{ $op->occurred_at->format('d.m.Y H:i') }}</td>
                    <td>{{ $op->category_name }}</td>
                    <td>{{ number_format($op->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="2">Итого доходов:</td>
                <td>{{ number_format($operations->where('type', 'income')->sum('amount'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <h2>Расходы</h2>
    <table>
        <thead>
            <tr>
                <th>Дата</th>
                <th>Категория</th>
                <th>Сумма</th>
            </tr>
        </thead>
        <tbody>
            @foreach($operations->where('type', 'expense') as $op)
                <tr>
                    <td>{{ $op->occurred_at->format('d.m.Y H:i') }}</td>
                    <td>{{ $op->category_name }}</td>
                    <td>{{ number_format($op->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="2">Итого расходов:</td>
                <td>{{ number_format($operations->where('type', 'expense')->sum('amount'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
