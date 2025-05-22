@extends('admin.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('admin-assets/panel/css/dashboard.css') }}" rel="stylesheet" />
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
  </div>
</div>
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const persianMonths = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی',
      'بهمن', 'اسفند'
    ];
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

    // Appointments by Month Chart
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

    // Appointment Statuses Chart
    const appointmentStatusesCtx = document.getElementById('appointmentStatusesChart').getContext('2d');
    new Chart(appointmentStatusesCtx, {
      type: 'doughnut',
      data: {
        labels: @json(array_keys($appointmentStatuses)),
        datasets: [{
          data: @json(array_values($appointmentStatuses)),
          backgroundColor: ['#60a5fa', '#f87171', '#34d399', '#fbbf24'],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        ...commonOptions,
        cutout: '60%'
      }
    });

    // Appointments by Day of Week Chart
    const appointmentsByDayOfWeekCtx = document.getElementById('appointmentsByDayOfWeekChart').getContext('2d');
    new Chart(appointmentsByDayOfWeekCtx, {
      type: 'bar',
      data: {
        labels: persianDays,
        datasets: [{
          label: 'تعداد نوبت‌ها',
          data: @json(array_values($appointmentsByDayOfWeek)),
          backgroundColor: '#34d399',
          borderColor: '#10b981',
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

    // Clinic Activity Chart
    const clinicActivityCtx = document.getElementById('clinicActivityChart').getContext('2d');
    new Chart(clinicActivityCtx, {
      type: 'bar',
      data: {
        labels: @json($clinicActivityLabels),
        datasets: [{
          label: 'تعداد نوبت‌ها',
          data: @json(array_values($clinicActivity)),
          backgroundColor: ['#60a5fa', '#f87171', '#34d399', '#fbbf24', '#a78bfa', '#f59e0b'],
          borderColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#d97706'],
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
  });
</script>
@endsection
