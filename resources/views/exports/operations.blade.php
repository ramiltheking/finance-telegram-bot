<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ __('operation_export.history_title') }}</title>
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
    <h1>{{ __('operation_export.history_title') }}</h1>
    <p>{{ __('operation_export.period') }}: {{ now()->subDays(30)->format('d.m.Y') }} - {{ now()->format('d.m.Y') }}</p>

    <h2>{{ __('operation_export.income') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('operation_export.date') }}</th>
                <th>{{ __('operation_export.category') }}</th>
                <th>{{ __('operation_export.amount') }}</th>
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
                <td colspan="2">{{ __('operation_export.total_income') }}</td>
                <td>{{ number_format($operations->where('type', 'income')->sum('amount'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <h2>{{ __('operation_export.expense') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('operation_export.date') }}</th>
                <th>{{ __('operation_export.category') }}</th>
                <th>{{ __('operation_export.amount') }}</th>
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
                <td colspan="2">{{ __('operation_export.total_expense') }}</td>
                <td>{{ number_format($operations->where('type', 'expense')->sum('amount'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
