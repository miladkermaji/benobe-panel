<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>گزارش مالی</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #2E86C1;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: right;
        }
        th {
            background-color: #2E86C1;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total {
            font-weight: bold;
            margin-top: 20px;
            text-align: left;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <h1>گزارش مالی</h1>
    <table>
        <thead>
            <tr>
                <th>ردیف</th>
                <th>تاریخ</th>
                <th>کلینیک</th>
                <th>نوع تراکنش</th>
                <th>وضعیت</th>
                <th>مبلغ (ریال)</th>
                <th>روش پرداخت</th>
                <th>بیمه</th>
                <th>توضیحات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $index => $transaction)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($transaction->registered_at)->format('Y-m-d H:i') }}</td>
                <td>{{ $transaction->clinic ? $transaction->clinic->name : 'بدون کلینیک' }}</td>
                <td>
                    @switch($transaction->type)
                        @case('online') آنلاین @break
                        @case('in_person') حضوری @break
                        @case('charge') شارژ @break
                    @endswitch
                </td>
                <td>
                    @switch($transaction->status)
                        @case('pending') در انتظار @break
                        @case('available') موجود @break
                        @case('requested') درخواست‌شده @break
                        @case('paid') پرداخت‌شده @break
                    @endswitch
                </td>
                <td>{{ number_format($transaction->amount) }}</td>
                <td>{{ $transaction->appointment ? $this->formatPaymentMethod($transaction->appointment->payment_method) : '-' }}</td>
                <td>{{ $transaction->appointment && $transaction->appointment->insurance ? $transaction->appointment->insurance->name : '-' }}</td>
                <td>{{ $transaction->description ?? 'بدون توضیح' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p class="total">مجموع تراکنش‌ها: {{ number_format($transactions->sum('amount')) }} ریال</p>
    <div class="footer">
        <p>تولیدشده توسط سیستم مدیریت پزشکی | تاریخ: {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</p>
    </div>
</body>
</html>