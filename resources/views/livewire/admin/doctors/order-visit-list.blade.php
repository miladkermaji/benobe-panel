<div>
    <div class="wrapper-md">
        <div class="panel panel-default shadow-sm mb-4">
            <div class="panel-heading py-2">جستجو و گزارش‌گیری</div>
            <div class="panel-body p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control input-shiny border-0 shadow-none"
                                wire:model.live="search"
                                placeholder="جستجو بر اساس نام کاربر / شماره همراه / کد پیگیری / کلینیک ...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="card shadow-sm">
            <div class="card-body p-3">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نام و نام خانوادگی</th>
                                <th>شماره موبایل</th>
                                <th>کلینیک</th>
                                <th>تاریخ پرداخت</th>
                                <th>شماره پیگیری بانک</th>
                                <th>شماره پیگیری نوبت</th>
                                <th>از</th>
                                <th>مبلغ</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orderVisits as $index => $orderVisit)
                                <tr>
                                    <td>{{ $orderVisits->firstItem() + $index }}</td>
                                    <td>{{ $orderVisit->user->first_name . ' ' . $orderVisit->user->last_name }}</td>
                                    <td>{{ $orderVisit->mobile }}</td>
                                    <td>{{ $orderVisit->clinic ? $orderVisit->clinic->name : '-' }}</td>
                                    <td>{{ $orderVisit->payment_date ? \App\Helpers\JalaliHelper::toJalaliDateTime($orderVisit->payment_date) : '-' }}
                                    </td>
                                    <td>
                                        @if ($orderVisit->payment_method === 'manual')
                                            ثبت دستی
                                        @elseif ($orderVisit->payment_method === 'free')
                                            رایگان
                                        @else
                                            {{ $orderVisit->bank_ref_id ?? '-' }}
                                        @endif
                                    </td>
                                    <td>{{ $orderVisit->tracking_code }}</td>
                                    <td>
                                        @if ($orderVisit->payment_method === 'online')
                                            پرداخت آنلاین
                                        @elseif ($orderVisit->payment_method === 'manual')
                                            ثبت دستی
                                        @else
                                            رایگان
                                        @endif
                                    </td>
                                    <td>{{ $orderVisit->amount > 0 ? number_format($orderVisit->amount) . ' تومان' : 'رایگان' }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.doctors.order-visit.show', $orderVisit->id) }}"
                                            class="btn btn-sm btn-gradient-primary">مشاهده جزئیات</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <i class="fas fa-money-check-alt fs-1 text-muted mb-3"></i>
                                        <p class="text-muted fw-medium">هیچ پرداختی یافت نشد.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted fs-6">نمایش {{ $orderVisits->firstItem() }} تا {{ $orderVisits->lastItem() }} از
                        {{ $orderVisits->total() }} ردیف
                    </div>
                    <div class="mt-3">{{ $orderVisits->links('vendor.pagination.bootstrap-4') }}</div>
                </div>
                <div class="panel mt-3" style="color: green; padding: 10px; font-size: 13pt;">
                    جمع کل: {{ number_format($totalAmount) }} تومان
                </div>
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
    
        .input-shiny {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fff;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
        }
    
        .input-shiny:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }
    
        .btn-gradient-primary {
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            border: none;
            color: white;
        }
    
        .btn-gradient-primary:hover {
            background: linear-gradient(90deg, #4338ca, #6d28d9);
            transform: translateY(-1px);
        }
    
        .table-sm td,
        .table-sm th {
            padding: 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</div>