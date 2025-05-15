@php
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Carbon;

@endphp

<div class="container" dir="rtl" wire:init="loadReports">
    <!-- Top Section -->
    <div class="top-section mb-2 mt-4">
        <div class="top-section-content">
            <h1 class="top-section-title">گزارش مالی</h1>
            <div class="top-section-actions">
                <div class="search-box">
                    <input type="text" class="search-input" wire:model.live.debounce.500ms="search" placeholder="جستجو...">
                    <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2">
                        <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                    </svg>
                </div>
                <button wire:click="exportExcel" class="btn btn-export">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16v16H4zM8 2v2m8-2v2m-4 14v-6m-3 3l3 3 3-3" />
                    </svg>
                    اکسل
                </button>
                <button wire:click="exportPdf" class="btn btn-export">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16v16H4zM8 2v2m8-2v2m-4 14v-6m-3 3l3 3 3-3" />
                    </svg>
                    PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-container mb-2">
        <div class="summary-card">
            <span class="summary-label">روزانه</span>
            <span class="summary-value">{{ number_format($summary['daily']) }} <small>تومان</small></span>
        </div>
        <div class="summary-card">
            <span class="summary-label">هفتگی</span>
            <span class="summary-value">{{ number_format($summary['weekly']) }} <small>تومان</small></span>
        </div>
        <div class="summary-card">
            <span class="summary-label">ماهانه</span>
            <span class="summary-value">{{ number_format($summary['monthly']) }} <small>تومان</small></span>
        </div>
        <div class="summary-card">
            <span class="summary-label">سالانه</span>
            <span class="summary-value">{{ number_format($summary['yearly']) }} <small>تومان</small></span>
        </div>
        <div class="summary-card">
            <span class="summary-label">کل</span>
            <span class="summary-value">{{ number_format($summary['total']) }} <small>تومان</small></span>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card mb-2">
        <div class="filters-header">
            <button class="filters-toggle" onclick="toggleFilters()">نمایش/مخفی</button>
        </div>
        <div class="filters-body" id="filters-body">
            <div class="filter-group">
                <label>بازه زمانی</label>
                <select class="filter-select" wire:model.live.debounce.500ms="dateFilter">
                    <option value="daily">روزانه</option>
                    <option value="weekly">هفتگی</option>
                    <option value="monthly">ماهانه</option>
                    <option value="yearly">سالانه</option>
                    <option value="custom">دلخواه</option>
                    <option value="all">همه</option>
                </select>
            </div>
            @if ($dateFilter === 'custom')
                <div class="filter-group">
                    <label>از تاریخ</label>
                    <input data-jdp type="text" class="filter-input jalali-datepicker" wire:model.live.debounce.500ms="startDate" placeholder="انتخاب تاریخ">
                </div>
                <div class="filter-group">
                    <label>تا تاریخ</label>
                    <input data-jdp type="text" class="filter-input jalali-datepicker" wire:model.live.debounce.500ms="endDate" placeholder="انتخاب تاریخ">
                </div>
            @endif
            <div class="filter-group">
                <label>نوع تراکنش</label>
                <select class="filter-select" wire:model.live.debounce.500ms="transactionType">
                    <option value="">همه</option>
                    <option value="wallet_charge">شارژ کیف پول</option>
                    <option value="profile_upgrade">ارتقای حساب</option>
                    <option value="appointment">نوبت‌دهی</option>
                    <option value="online">آنلاین</option>
                    <option value="in_person">حضوری</option>
                    <option value="charge">شارژ</option>
                    <option value="phone">تلفنی</option>
                    <option value="video">تصویری</option>
                    <option value="text">متنی</option>
                    <option value="manual">دستی</option>
                </select>
            </div>
            <div class="filter-group">
                <label>وضعیت تراکنش</label>
                <select class="filter-select" wire:model.live.debounce.500ms="transactionStatus">
                    <option value="">همه</option>
                    <option value="pending">در انتظار</option>
                    <option value="paid">پرداخت‌شده</option>
                    <option value="failed">ناموفق</option>
                    <option value="available">موجود</option>
                    <option value="requested">درخواست‌شده</option>
                    <option value="unpaid">پرداخت‌نشده</option>
                    <option value="scheduled">برنامه‌ریزی‌شده</option>
                    <option value="cancelled">لغو شده</option>
                </select>
            </div>
            <div class="filter-group">
                <label>کلینیک</label>
                <select class="filter-select" wire:model.live.debounce.500ms="clinicId">
                    <option value="">همه</option>
                    @foreach ($clinics as $clinic)
                        <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>روش پرداخت</label>
                <select class="filter-select" wire:model.live.debounce.500ms="paymentMethod">
                    <option value="">همه</option>
                    <option value="online">آنلاین</option>
                    <option value="cash">نقدی</option>
                    <option value="card_to_card">کارت به کارت</option>
                    <option value="pos">POS</option>
                    <option value="card">کارت</option>
                    <option value="insurance">بیمه</option>
                </select>
            </div>
            <div class="filter-group">
                <label>بیمه</label>
                <select class="filter-select" wire:model.live.debounce.500ms="insuranceId">
                    <option value="">همه</option>
                    @foreach ($insurances as $insurance)
                        <option value="{{ $insurance->id }}">{{ $insurance->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>حداقل مبلغ (تومان)</label>
                <input type="number" class="filter-input" wire:model.live.debounce.500ms="minAmount" placeholder="0">
            </div>
            <div class="filter-group">
                <label>حداکثر مبلغ (تومان)</label>
                <input type="number" class="filter-input" wire:model.live.debounce.500ms="maxAmount" placeholder="0">
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="chart-card mb-2">
        <h4 class="section-title">📊 نمودار تراکنش‌ها</h4>
        <div class="chart-container" wire:ignore>
            <canvas id="financialChart"></canvas>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>ردیف</th>
                        <th>تاریخ</th>
                        <th>کلینیک</th>
                        <th>نوع</th>
                        <th>وضعیت</th>
                        <th>مبلغ (تومان)</th>
                        <th class="hidden-mobile">روش پرداخت</th>
                        <th class="hidden-mobile">بیمه</th>
                        <th class="hidden-mobile">توضیحات</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($readyToLoad)
                        @forelse ($transactions as $index => $transaction)
                            <tr>
                                <td>{{ $transactions->firstItem() + $index }}</td>
                                <td>{{ Jalalian::fromCarbon(Carbon::parse($transaction['date']))->format('Y/m/d H:i') }}</td>
                                <td>
                                    @if ($transaction['clinic_id'])
                                        {{ \App\Models\Clinic::find($transaction['clinic_id']) ? \App\Models\Clinic::find($transaction['clinic_id'])->name : 'بدون کلینیک' }}
                                    @else
                                        بدون کلینیک
                                    @endif
                                </td>
                                <td>{{ $this->formatTransactionType($transaction['transaction_type']) }}</td>
                                <td>{{ $this->formatStatus($transaction['status']) }}</td>
                                <td>{{ number_format($transaction['amount']) }}</td>
                                <td class="hidden-mobile">{{ $this->formatPaymentMethod($transaction['payment_method']) }}</td>
                                <td class="hidden-mobile">
                                    @if ($transaction['insurance_id'])
                                        {{ \App\Models\Insurance::find($transaction['insurance_id']) ? \App\Models\Insurance::find($transaction['insurance_id'])->name : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="hidden-mobile">{{ $transaction['description'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="no-data">هیچ تراکنشی یافت نشد.</td>
                            </tr>
                        @endforelse
                    @else
                        <tr>
                            <td colspan="9" class="no-data">در حال بارگذاری...</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            @if ($readyToLoad)
                @if ($transactions->total() > 0)
                    <span>نمایش {{ $transactions->firstItem() }} تا {{ $transactions->lastItem() }} از {{ $transactions->total() }} ردیف</span>
                @else
                    <span>هیچ تراکنشی یافت نشد.</span>
                @endif
                @if ($transactions->hasPages())
                    {{ $transactions->links('livewire::bootstrap') }}
                @endif
            @else
                <span>در حال بارگذاری...</span>
            @endif
        </div>
    </div>

    <!-- Total Summary -->
    <div class="total-card mt-2">
        <h4>جمع‌بندی</h4>
        <p>مجموع تراکنش‌ها: <strong>{{ number_format($totalAmount) }}</strong> تومان</p>
    </div>

    <!-- JavaScript Scripts -->
    <script>
        document.addEventListener('livewire:initialized', function() {
            window.financialChart = null;

            function destroyChart() {
                if (window.financialChart) {
                    window.financialChart.destroy();
                    window.financialChart = null;
                }
            }

            Livewire.on('component.dehydrated', () => {
                destroyChart();
            });

            Livewire.on('updateChart', (chartData) => {
                const renderChart = () => {
                    const canvas = document.getElementById('financialChart');
                    if (!canvas) {
                        console.error('Canvas element not found');
                        return;
                    }
                    const ctx = canvas.getContext('2d');
                    if (!ctx) {
                        console.error('Failed to get canvas context');
                        return;
                    }

                    destroyChart();

                    if (!chartData.labels || !chartData.values || chartData.labels.length === 0) {
                        canvas.parentNode.innerHTML = '<p>داده‌ای برای نمایش وجود ندارد</p>';
                        return;
                    }

                    window.financialChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                label: 'مبلغ تراکنش‌ها (تومان)',
                                data: chartData.values,
                                borderColor: 'var(--primary)',
                                backgroundColor: 'var(--primary-light)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: 'var(--primary)'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: {
                                            family: 'Vazir',
                                            size: 12,
                                            weight: '500'
                                        },
                                        padding: 12,
                                        color: 'var(--text-primary)',
                                        boxWidth: 12,
                                        usePointStyle: true
                                    }
                                },
                                tooltip: {
                                    enabled: true,
                                    backgroundColor: 'rgba(30, 41, 59, 0.9)',
                                    titleFont: {
                                        family: 'Vazir',
                                        size: 13
                                    },
                                    bodyFont: {
                                        family: 'Vazir',
                                        size: 11
                                    },
                                    padding: 10,
                                    cornerRadius: 8,
                                    borderColor: 'rgba(255, 255, 255, 0.2)',
                                    borderWidth: 1,
                                    callbacks: {
                                        label: function(context) {
                                            return new Intl.NumberFormat('fa-IR').format(context.parsed.y) + ' تومان';
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            family: 'Vazir',
                                            size: 10
                                        },
                                        color: 'var(--text-secondary)',
                                        maxRotation: 45,
                                        minRotation: 45
                                    },
                                    title: {
                                        display: true,
                                        text: 'تاریخ',
                                        color: 'var(--text-primary)',
                                        font: {
                                            family: 'Vazir',
                                            size: 12
                                        }
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'var(--border-neutral)'
                                    },
                                    ticks: {
                                        font: {
                                            family: 'Vazir',
                                            size: 10
                                        },
                                        color: 'var(--text-secondary)',
                                        callback: function(value) {
                                            return new Intl.NumberFormat('fa-IR').format(value);
                                        }
                                    },
                                    title: {
                                        display: true,
                                        text: 'مبلغ (تومان)',
                                        color: 'var(--text-primary)',
                                        font: {
                                            family: 'Vazir',
                                            size: 12
                                        }
                                    }
                                }
                            },
                            animation: {
                                duration: 1200,
                                easing: 'easeOutQuart',
                                delay: 200
                            },
                            hover: {
                                mode: 'nearest',
                                intersect: true,
                                animationDuration: 400
                            }
                        }
                    });
                };

                const targetNode = document.querySelector('.chart-container');
                if (!targetNode) {
                    console.error('Chart container not found');
                    return;
                }

                const observer = new MutationObserver((mutations, obs) => {
                    const canvas = document.getElementById('financialChart');
                    if (canvas) {
                        renderChart();
                        obs.disconnect();
                    }
                });

                observer.observe(targetNode, {
                    childList: true,
                    subtree: true
                });

                // Fallback: Try rendering immediately
                renderChart();
            });

            function toggleFilters() {
                const filtersBody = document.getElementById('filters-body');
                filtersBody.classList.toggle('active');
            }
        });
    </script>
</div>