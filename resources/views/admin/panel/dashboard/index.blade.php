@extends('admin.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('admin-assets/panel/css/dashboard.css') }}" rel="stylesheet" />
  <style>
    .chart-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
      padding: 1.5rem;
    }

    .chart-container {
      background: white;
      border-radius: 1rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      padding: 1.5rem;
      height: 400px;
    }

    .chart-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #2d3748;
      margin-bottom: 1rem;
      text-align: right;
    }

    .chart-wrapper {
      height: calc(100% - 3rem);
    }
  </style>
@endsection
@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection
@section('content')
@section('bread-crumb-title', 'داشبورد')

<div class="d-flex flex-column justify-content-center p-3 top-panel-bg">
  <div class="top-details-sicks-cards">
    <div class="top-s-a-wrapper">
      <div class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('admin-assets/icons/doctor.svg') }}" alt="تعداد پزشکان">
        </div>
        <div class="stat-info">
          <div class="stat-label">تعداد پزشکان</div>
          <div class="stat-value">{{ $totalDoctors }} پزشک</div>
        </div>
      </div>
      <div class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('admin-assets/icons/patient.svg') }}" alt="تعداد بیماران">
        </div>
        <div class="stat-info">
          <div class="stat-label">تعداد بیماران</div>
          <div class="stat-value">{{ $totalPatients }} بیمار</div>
        </div>
      </div>
      <div class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('admin-assets/icons/secretary.svg') }}" alt="تعداد منشی‌ها">
        </div>
        <div class="stat-info">
          <div class="stat-label">تعداد منشی‌ها</div>
          <div class="stat-value">{{ $totalSecretaries }} منشی</div>
        </div>
      </div>
      <div class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('admin-assets/icons/manager.svg') }}" alt="تعداد مدیران">
        </div>
        <div class="stat-info">
          <div class="stat-label">تعداد مدیران</div>
          <div class="stat-value">{{ $totalManagers }} مدیر</div>
        </div>
      </div>
      <div class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('admin-assets/icons/clinic.svg') }}" alt="تعداد کلینیک‌ها">
        </div>
        <div class="stat-info">
          <div class="stat-label">تعداد کلینیک‌ها</div>
          <div class="stat-value">{{ $totalClinics }} کلینیک</div>
        </div>
      </div>
      <div class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('admin-assets/icons/appointment.svg') }}" alt="تعداد نوبت‌ها">
        </div>
        <div class="stat-info">
          <div class="stat-label">تعداد نوبت‌ها</div>
          <div class="stat-value">{{ $totalAppointments }} نوبت</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="chart-content">
  <div class="chart-grid">
    <!-- نمودار ۱: نوبت‌ها در هر ماه -->
    <div class="chart-container">
      <div class="chart-title">📊 نوبت‌ها در هر ماه</div>
      <div class="chart-wrapper">
        <canvas id="appointmentsByMonthChart"></canvas>
      </div>
    </div>

    <!-- نمودار ۲: وضعیت نوبت‌ها -->
    <div class="chart-container">
      <div class="chart-title">📈 وضعیت نوبت‌ها</div>
      <div class="chart-wrapper">
        <canvas id="appointmentStatusesChart"></canvas>
      </div>
    </div>

    <!-- نمودار ۳: نوبت‌ها در روزهای هفته -->
    <div class="chart-container">
      <div class="chart-title">📅 نوبت‌ها در روزهای هفته</div>
      <div class="chart-wrapper">
        <canvas id="appointmentsByDayOfWeekChart"></canvas>
      </div>
    </div>

    <!-- نمودار ۴: فعالیت کلینیک‌ها -->
    <div class="chart-container">
      <div class="chart-title">🏥 فعالیت کلینیک‌ها</div>
      <div class="chart-wrapper">
        <canvas id="clinicActivityChart"></canvas>
      </div>
    </div>

    <!-- نمودار ۵: توزیع تخصص‌های پزشکان -->
    <div class="chart-container">
      <div class="chart-title">👨‍⚕️ توزیع تخصص‌های پزشکان</div>
      <div class="chart-wrapper">
        <canvas id="doctorSpecialtiesChart"></canvas>
      </div>
    </div>

    <!-- نمودار ۶: روند نوبت‌ها -->
    <div class="chart-container">
      <div class="chart-title">📈 روند نوبت‌ها</div>
      <div class="chart-wrapper">
        <canvas id="appointmentsTrendChart"></canvas>
      </div>
    </div>

    <!-- نمودار ۷: مقایسه کلینیک‌ها -->
    <div class="chart-container">
      <div class="chart-title">⚖️ مقایسه کلینیک‌ها</div>
      <div class="chart-wrapper">
        <canvas id="clinicComparisonChart"></canvas>
      </div>
    </div>

    <!-- نمودار ۸: وضعیت پرداخت‌ها -->
    <div class="chart-container">
      <div class="chart-title">💰 وضعیت پرداخت‌ها</div>
      <div class="chart-wrapper">
        <canvas id="paymentStatusChart"></canvas>
      </div>
    </div>

    <!-- نمودار ۹: آمار بازدید -->
    <div class="chart-container">
      <div class="chart-title">👥 آمار بازدید</div>
      <div class="chart-wrapper">
        <canvas id="visitorStatsChart"></canvas>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const persianMonths = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    const persianDays = ['دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه', 'شنبه', 'یک‌شنبه'];

    const commonOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            font: {
              family: 'IRANSans',
              size: 14,
              weight: '500'
            },
            padding: 20,
            color: '#2d3748'
          }
        },
        tooltip: {
          enabled: true,
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleFont: {
            family: 'IRANSans',
            size: 14
          },
          bodyFont: {
            family: 'IRANSans',
            size: 12
          },
          padding: 10,
          cornerRadius: 8
        }
      },
      animation: {
        duration: 1200,
        easing: 'easeOutQuart'
      }
    };

    // نمودار ۱: نوبت‌ها در هر ماه (Bar Chart)
    const appointmentsByMonthCtx = document.getElementById('appointmentsByMonthChart').getContext('2d');
    new Chart(appointmentsByMonthCtx, {
      type: 'bar',
      data: {
        labels: persianMonths,
        datasets: [{
          label: 'تعداد نوبت‌ها',
          data: @json(array_values($appointmentsByMonth)),
          backgroundColor: '#60a5fa',
          borderColor: '#3b82f6',
          borderWidth: 1,
          borderRadius: 12,
          barThickness: 20
        }]
      },
      options: {
        ...commonOptions,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        },
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });

    // نمودار ۲: وضعیت نوبت‌ها (Doughnut Chart)
    const appointmentStatusesCtx = document.getElementById('appointmentStatusesChart').getContext('2d');
    new Chart(appointmentStatusesCtx, {
      type: 'doughnut',
      data: {
        labels: @json(array_keys($appointmentStatuses)),
        datasets: [{
          data: @json(array_values($appointmentStatuses)),
          backgroundColor: ['#60a5fa', '#f87171', '#34d399', '#fbbf24', '#a78bfa'],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        ...commonOptions,
        cutout: '60%'
      }
    });

    // نمودار ۳: نوبت‌ها در روزهای هفته (Line Chart)
    const appointmentsByDayOfWeekCtx = document.getElementById('appointmentsByDayOfWeekChart').getContext('2d');
    new Chart(appointmentsByDayOfWeekCtx, {
      type: 'line',
      data: {
        labels: persianDays,
        datasets: [{
          label: 'تعداد نوبت‌ها',
          data: @json(array_values($appointmentsByDayOfWeek)),
          borderColor: '#34d399',
          backgroundColor: 'rgba(52, 211, 153, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        ...commonOptions,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });

    // نمودار ۴: فعالیت کلینیک‌ها (Horizontal Bar Chart)
    const clinicActivityCtx = document.getElementById('clinicActivityChart').getContext('2d');
    new Chart(clinicActivityCtx, {
      type: 'bar',
      data: {
        labels: @json(array_keys($clinicActivity)),
        datasets: [{
          label: 'تعداد نوبت‌ها',
          data: @json(array_values($clinicActivity)),
          backgroundColor: ['#60a5fa', '#f87171', '#34d399', '#fbbf24', '#a78bfa', '#f59e0b'],
          borderColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#d97706'],
          borderWidth: 1,
          borderRadius: 12
        }]
      },
      options: {
        ...commonOptions,
        indexAxis: 'y',
        scales: {
          x: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          y: {
            grid: {
              display: false
            }
          }
        }
      }
    });

    // نمودار ۵: توزیع تخصص‌های پزشکان (Pie Chart)
    const doctorSpecialtiesCtx = document.getElementById('doctorSpecialtiesChart').getContext('2d');
    new Chart(doctorSpecialtiesCtx, {
      type: 'pie',
      data: {
        labels: @json(array_keys($doctorSpecialties)),
        datasets: [{
          data: @json(array_values($doctorSpecialties)),
          backgroundColor: ['#60a5fa', '#f87171', '#34d399', '#fbbf24', '#a78bfa', '#f59e0b'],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        ...commonOptions,
        plugins: {
          legend: {
            position: 'right'
          }
        }
      }
    });

    // نمودار ۶: روند نوبت‌ها (Area Chart)
    const appointmentsTrendCtx = document.getElementById('appointmentsTrendChart').getContext('2d');
    new Chart(appointmentsTrendCtx, {
      type: 'line',
      data: {
        labels: @json(array_keys($appointmentsTrend)),
        datasets: [{
          label: 'نوبت‌های جدید',
          data: @json(array_values($appointmentsTrend)),
          borderColor: '#8b5cf6',
          backgroundColor: 'rgba(139, 92, 246, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        ...commonOptions,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });

    // نمودار ۷: مقایسه کلینیک‌ها (Stacked Bar Chart)
    const clinicComparisonCtx = document.getElementById('clinicComparisonChart').getContext('2d');
    const clinicComparisonData = @json($clinicComparison);
    new Chart(clinicComparisonCtx, {
      type: 'bar',
      data: {
        labels: Object.keys(clinicComparisonData),
        datasets: [
          {
            label: 'حاضر شده',
            data: Object.values(clinicComparisonData).map(item => item['حاضر شده']),
            backgroundColor: '#34d399',
            borderColor: '#10b981',
            borderWidth: 1
          },
          {
            label: 'لغو شده',
            data: Object.values(clinicComparisonData).map(item => item['لغو شده']),
            backgroundColor: '#f87171',
            borderColor: '#ef4444',
            borderWidth: 1
          },
          {
            label: 'غایب',
            data: Object.values(clinicComparisonData).map(item => item['غایب']),
            backgroundColor: '#fbbf24',
            borderColor: '#f59e0b',
            borderWidth: 1
          }
        ]
      },
      options: {
        ...commonOptions,
        scales: {
          x: {
            stacked: true,
            grid: {
              display: false
            }
          },
          y: {
            stacked: true,
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          }
        }
      }
    });

    // نمودار ۸: وضعیت پرداخت‌ها (Polar Area Chart)
    const paymentStatusCtx = document.getElementById('paymentStatusChart').getContext('2d');
    new Chart(paymentStatusCtx, {
      type: 'polarArea',
      data: {
        labels: @json(array_keys($paymentStatus)),
        datasets: [{
          data: @json(array_values($paymentStatus)),
          backgroundColor: ['#34d399', '#fbbf24', '#f87171'],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        ...commonOptions,
        scales: {
          r: {
            ticks: {
              display: false
            }
          }
        }
      }
    });

    // نمودار ۹: آمار بازدید (Bar Chart)
    const visitorStatsCtx = document.getElementById('visitorStatsChart').getContext('2d');
    new Chart(visitorStatsCtx, {
      type: 'bar',
      data: {
        labels: ['امروز', 'دیروز', 'این هفته', 'هفته گذشته', 'این ماه'],
        datasets: [{
          label: 'تعداد بازدید',
          data: [
            @json($visitorStats['today']),
            @json($visitorStats['yesterday']),
            @json($visitorStats['this_week']),
            @json($visitorStats['last_week']),
            @json($visitorStats['this_month'])
          ],
          backgroundColor: '#60a5fa',
          borderColor: '#3b82f6',
          borderWidth: 1,
          borderRadius: 12
        }]
      },
      options: {
        ...commonOptions,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        },
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  });
</script>
@endsection
