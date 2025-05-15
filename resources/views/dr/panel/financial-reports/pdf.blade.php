<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8">
  <title>گزارش مالی</title>
  <style>
    @import url('{{ asset("dr-assets/fonts/vazir/font-face.css") }}');

    @font-face {
      font-family: Vazir;
      font-weight: normal;
      font-style: normal;
    }

    body {
      font-family: Vazir;
      font-size: 12px;
      margin: 20px;
      color: #333;
      direction: rtl;
      text-align: right;
      unicode-bidi: embed;
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

    th,
    td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: right;
      vertical-align: middle;
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
      text-align: right;
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
          <td>
            {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($transaction['date']))->format('Y/m/d H:i') }}
          </td>
          <td>
            @if ($transaction['clinic_id'])
              {{ \App\Models\Clinic::find($transaction['clinic_id']) ? \App\Models\Clinic::find($transaction['clinic_id'])->name : 'بدون کلینیک' }}
            @else
              بدون کلینیک
            @endif
          </td>
          <td>
            @switch($transaction['transaction_type'])
              @case('wallet_charge')
                شارژ کیف پول
              @break

              @case('profile_upgrade')
                ارتقای حساب
              @break

              @case('appointment')
                نوبت‌دهی
              @break

              @case('online')
                آنلاین
              @break

              @case('in_person')
                حضوری
              @break

              @case('charge')
                شارژ
              @break

              @case('phone')
                تلفنی
              @break

              @case('video')
                تصویری
              @break

              @case('text')
                متنی
              @break

              @case('manual')
                دستی
              @break

              @default
                {{ $transaction['transaction_type'] ?: 'نامشخص' }}
            @endswitch
          </td>
          <td>
            @switch($transaction['status'])
              @case('pending')
                در انتظار
              @break

              @case('paid')
                پرداخت‌شده
              @break

              @case('failed')
                ناموفق
              @break

              @case('available')
                موجود
              @break

              @case('requested')
                درخواست‌شده
              @break

              @case('unpaid')
                پرداخت‌نشده
              @break

              @case('scheduled')
                برنامه‌ریزی‌شده
              @break

              @case('cancelled')
                لغو شده
              @break

              @default
                {{ $transaction['status'] ?: 'نامشخص' }}
            @endswitch
          </td>
          <td>{{ number_format($transaction['amount']) }}</td>
          <td>
            @switch($transaction['payment_method'])
              @case('online')
                آنلاین
              @break

              @case('cash')
                نقدی
              @break

              @case('card_to_card')
                کارت به کارت
              @break

              @case('pos')
                POS
              @break

              @case('card')
                کارت
              @break

              @case('insurance')
                بیمه
              @break

              @default
                {{ $transaction['payment_method'] ?: '-' }}
            @endswitch
          </td>
          <td>
            @if ($transaction['insurance_id'])
              {{ \App\Models\Insurance::find($transaction['insurance_id']) ? \App\Models\Insurance::find($transaction['insurance_id'])->name : '-' }}
            @else
              -
            @endif
          </td>
          <td>{{ $transaction['description'] }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
  <p class="total">مجموع تراکنش‌ها: {{ number_format(array_sum(array_column($transactions, 'amount'))) }} ریال</p>
  <div class="footer">
    <p>تولیدشده توسط سیستم مدیریت پزشکی | تاریخ:
      {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::now())->format('Y/m/d H:i') }}</p>
  </div>
</body>

</html>
