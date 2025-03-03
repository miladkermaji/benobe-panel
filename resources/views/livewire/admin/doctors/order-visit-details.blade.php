<div>
    <div class="wrapper-md">
        <div class="panel panel-default shadow-sm">
            <div class="panel-heading py-2">مشاهده جزئیات</div>
            <div class="panel-body p-3">
                <table class="table">
                    <tbody>
                        <tr>
                            <td width="200">بیمار:</td>
                            <td>{{ $orderVisit->user->first_name . ' ' . $orderVisit->user->last_name }}</td>
                        </tr>
                        <tr>
                            <td>شماره موبایل بیمار:</td>
                            <td>{{ $orderVisit->mobile }}</td>
                        </tr>
                        <tr>
                            <td>کلینیک:</td>
                            <td>{{ $orderVisit->clinic ? $orderVisit->clinic->name : '-' }}</td>
                        </tr>
                        <tr>
                            <td>زمان پرداخت:</td>
                            <td>{{ $orderVisit->payment_date ? \App\Helpers\JalaliHelper::toJalaliDateTime($orderVisit->payment_date) : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td>شماره پیگیری نوبت:</td>
                            <td>{{ $orderVisit->tracking_code }}</td>
                        </tr>
                        <tr>
                            <td>روش پرداخت:</td>
                            <td>{{ $orderVisit->payment_method === 'online' ? 'پرداخت آنلاین' : ($orderVisit->payment_method === 'manual' ? 'ثبت دستی' : 'رایگان') }}
                            </td>
                        </tr>
                        <tr>
                            <td>شماره پیگیری بانک:</td>
                            <td>{{ $orderVisit->bank_ref_id ?? '0' }}</td>
                        </tr>
                        <tr>
                            <td>زمان ثبت نوبت:</td>
                            <td>{{ $orderVisit->created_at ? \App\Helpers\JalaliHelper::toJalaliDateTime($orderVisit->created_at) : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td>تاریخ مراجعه:</td>
                            <td>{{ $orderVisit->appointment_date ? \App\Helpers\JalaliHelper::toJalaliDate($orderVisit->appointment_date) : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td>ساعت مراجعه:</td>
                            <td>{{ $orderVisit->appointment_time ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>مرکز درمان / بیمارستان:</td>
                            <td>{{ $orderVisit->center_name ?? $orderVisit->doctor->full_name }}</td>
                        </tr>
                        <tr>
                            <td>پزشک:</td>
                            <td>{{ $orderVisit->doctor->full_name }}</td>
                        </tr>
                        <tr>
                            <td>هزینه ویزیت آزاد پزشک:</td>
                            <td>{{ $orderVisit->visit_cost > 0 ? number_format($orderVisit->visit_cost) . ' تومان' : 'رایگان' }}
                            </td>
                        </tr>
                        <tr>
                            <td>هزینه خدمات سایت:</td>
                            <td>{{ $orderVisit->service_cost > 0 ? number_format($orderVisit->service_cost) . ' تومان' : 'رایگان' }}
                            </td>
                        </tr>
                        <tr>
                            <td>مبلغ پرداخت‌شده:</td>
                            <td>{{ $orderVisit->amount > 0 ? number_format($orderVisit->amount) . ' تومان' : 'رایگان' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <style>
        .glass-header {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.9), rgba(124, 58, 237, 0.7));
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }
    
        .glass-header:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
    
        .panel-default {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
    
        .panel-heading {
            background: linear-gradient(135deg, #f9fafb, #e5e7eb);
            padding: 10px;
            font-weight: bold;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 8px 8px 0 0;
            color: #4b5563;
        }
    </style>
</div>