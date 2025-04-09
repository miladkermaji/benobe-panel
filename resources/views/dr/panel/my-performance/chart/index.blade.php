@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/my-performance/chart/chart.css') }}" rel="stylesheet" />
  <style>
    :root {
      --chart-bg: #ffffff;
      --chart-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
      --chart-border-radius: 16px;
      --primary-color: #4a90e2;
      --gradient-bg: linear-gradient(145deg, #f8fafc, #ffffff);
      --text-color: #2d3748;
      --hover-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    }

    .chart-content {
      padding: 30px;
      max-width: 1440px;
      margin: 0 auto;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f0 100%);
      border-radius: 20px;
      box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .chart-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
      gap: 25px;
    }

    .chart-container {
      background: var(--gradient-bg);
      border-radius: var(--chart-border-radius);
      box-shadow: var(--chart-shadow);
      padding: 25px;
      height: 420px;
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: all 0.3s ease;
      border: 1px solid rgba(255, 255, 255, 0.5);
      position: relative;
      overflow: hidden;
    }

    .chart-container:hover {
      transform: translateY(-5px);
      box-shadow: var(--hover-shadow);
    }

    .chart-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-color), transparent);
      opacity: 0.8;
    }

    .section-title {
      font-size: 1.3rem;
      font-weight: 600;
      color: var(--text-color);
      margin-bottom: 20px;
      text-align: center;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: linear-gradient(90deg, var(--primary-color), #7fbfff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    canvas {
      width: 100% !important;
      height: 100% !important;
      font-family: IRANSans, sans-serif !important;
    }

    @media only screen and (max-width: 768px) {
      .chart-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .chart-container {
        height: 380px;
      }

      .section-title {
        font-size: 1.1rem;
      }
    }
  </style>
@endsection

@section('content')
@section('bread-crumb-title', 'آمار و نمودار')
<div class="chart-content">
  <div class="chart-grid">
    <!-- 📊 نمودار ۱: تعداد ویزیت‌ها به تفکیک وضعیت -->
    <div class="chart-container">
      <h4 class="section-title">📊 تعداد ویزیت‌ها</h4>
      <canvas id="doctor-performance-chart"></canvas>
    </div>

    <!-- 💰 نمودار ۲: درآمد ماهانه -->
    <div class="chart-container">
      <h4 class="section-title">💰 درآمد ماهانه</h4>
      <canvas id="doctor-income-chart"></canvas>
    </div>

    <!-- 👨‍⚕️ نمودار ۳: تعداد بیماران جدید -->
    <div class="chart-container">
      <h4 class="section-title">👨‍⚕️ بیماران جدید</h4>
      <canvas id="doctor-patient-chart"></canvas>
    </div>

    <!-- 📈 نمودار ۴: وضعیت نوبت‌ها -->
    <div class="chart-container">
      <h4 class="section-title">📈 وضعیت نوبت‌ها</h4>
      <canvas id="doctor-status-chart"></canvas>
    </div>

    <!-- 🥧 نمودار ۵: درصد وضعیت نوبت‌ها -->
    <div class="chart-container">
      <h4 class="section-title">🥧 درصد نوبت‌ها</h4>
      <canvas id="doctor-status-pie-chart"></canvas>
    </div>

    <!-- 📉 نمودار ۶: روند بیماران جدید -->
    <div class="chart-container">
      <h4 class="section-title">📉 روند بیماران</h4>
      <canvas id="doctor-patient-trend-chart"></canvas>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('dr-assets/panel/js/calendar/custm-calendar.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    let selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';

    function loadCharts() {
      $.ajax({
        url: "{{ route('dr-my-performance-chart-data') }}",
        method: 'GET',
        data: {
          clinic_id: selectedClinicId
        },
        success: function(response) {
          renderPerformanceChart(response.appointments);
          renderIncomeChart(response.monthlyIncome);
          renderPatientChart(response.newPatients);
          renderStatusChart(response.appointmentStatusByMonth);
          renderStatusPieChart(response.appointmentStatusByMonth);
          renderPatientTrendChart(response.newPatients);
        },
        error: function() {
          toastr.error('خطا در دریافت اطلاعات نمودارها');
        }
      });
    }

    const commonOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
          labels: {
            font: {
              family: 'IRANSans',
              size: 14,
              weight: '500'
            },
            padding: 15,
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

    // 📊 نمودار تعداد ویزیت‌ها
    function renderPerformanceChart(data) {
      let ctx = document.getElementById('doctor-performance-chart').getContext('2d');
      if (window.performanceChart) window.performanceChart.destroy();

      let labels = data.map(item => item.month);
      window.performanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
              label: 'برنامه‌ریزی‌شده',
              data: data.map(item => item.scheduled_count),
              backgroundColor: '#60a5fa',
              borderRadius: 6
            },
            {
              label: 'انجام‌شده',
              data: data.map(item => item.attended_count),
              backgroundColor: '#34d399',
              borderRadius: 6
            },
            {
              label: 'غیبت',
              data: data.map(item => item.missed_count),
              backgroundColor: '#f87171',
              borderRadius: 6
            },
            {
              label: 'لغو‌شده',
              data: data.map(item => item.cancelled_count),
              backgroundColor: '#fbbf24',
              borderRadius: 6
            }
          ]
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
    }

    // 💰 نمودار درآمد ماهانه
    function renderIncomeChart(data) {
      let ctx = document.getElementById('doctor-income-chart').getContext('2d');
      if (window.incomeChart) window.incomeChart.destroy();

      let labels = data.map(item => item.month);
      window.incomeChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
              label: 'پرداخت‌شده',
              data: data.map(item => item.total_paid_income),
              backgroundColor: '#10b981',
              borderRadius: 6
            },
            {
              label: 'پرداخت‌نشده',
              data: data.map(item => item.total_unpaid_income),
              backgroundColor: '#ef4444',
              borderRadius: 6
            }
          ]
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
    }

    // 👨‍⚕️ نمودار تعداد بیماران جدید
    function renderPatientChart(data) {
      let ctx = document.getElementById('doctor-patient-chart').getContext('2d');
      if (window.patientChart) window.patientChart.destroy();

      let labels = data.map(item => item.month);
      window.patientChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'بیماران جدید',
            data: data.map(item => item.total_patients),
            backgroundColor: '#f59e0b',
            borderRadius: 6
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
    }

    // 📈 نمودار وضعیت نوبت‌ها
    function renderStatusChart(data) {
      let ctx = document.getElementById('doctor-status-chart').getContext('2d');
      if (window.statusChart) window.statusChart.destroy();

      let labels = data.map(item => item.month);
      window.statusChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
              label: 'برنامه‌ریزی‌شده',
              data: data.map(item => item.scheduled_count),
              backgroundColor: '#60a5fa',
              borderRadius: 6
            },
            {
              label: 'انجام‌شده',
              data: data.map(item => item.attended_count),
              backgroundColor: '#34d399',
              borderRadius: 6
            },
            {
              label: 'غیبت',
              data: data.map(item => item.missed_count),
              backgroundColor: '#f87171',
              borderRadius: 6
            },
            {
              label: 'لغو‌شده',
              data: data.map(item => item.cancelled_count),
              backgroundColor: '#fbbf24',
              borderRadius: 6
            }
          ]
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
    }

    // 🥧 نمودار درصد وضعیت نوبت‌ها (Pie Chart)
    function renderStatusPieChart(data) {
      let ctx = document.getElementById('doctor-status-pie-chart').getContext('2d');
      if (window.statusPieChart) window.statusPieChart.destroy();

      let totalScheduled = data.reduce((sum, item) => sum + item.scheduled_count, 0);
      let totalAttended = data.reduce((sum, item) => sum + item.attended_count, 0);
      let totalMissed = data.reduce((sum, item) => sum + item.missed_count, 0);
      let totalCancelled = data.reduce((sum, item) => sum + item.cancelled_count, 0);

      window.statusPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: ['برنامه‌ریزی‌شده', 'انجام‌شده', 'غیبت', 'لغو‌شده'],
          datasets: [{
            data: [totalScheduled, totalAttended, totalMissed, totalCancelled],
            backgroundColor: ['#60a5fa', '#34d399', '#f87171', '#fbbf24'],
            borderWidth: 2,
            borderColor: '#ffffff'
          }]
        },
        options: {
          ...commonOptions,
          plugins: {
            ...commonOptions.plugins,
            legend: {
              position: 'bottom'
            }
          }
        }
      });
    }

    // 📉 نمودار روند بیماران جدید (Line Chart)
    function renderPatientTrendChart(data) {
      let ctx = document.getElementById('doctor-patient-trend-chart').getContext('2d');
      if (window.patientTrendChart) window.patientTrendChart.destroy();

      let labels = data.map(item => item.month);
      window.patientTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'بیماران جدید',
            data: data.map(item => item.total_patients),
            borderColor: '#f97316',
            backgroundColor: 'rgba(249, 115, 22, 0.2)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#f97316',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
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
    }

    loadCharts();
  });
</script>
@endsection
