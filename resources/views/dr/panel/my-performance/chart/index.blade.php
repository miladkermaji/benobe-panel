@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/my-performance/chart/chart.css') }}" rel="stylesheet" />
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
