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
    let charts = {};

    // تنظیمات پایه برای همه نمودارها
    const baseOptions = {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
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
            color: '#2d3748',
            usePointStyle: true,
            pointStyle: 'rect'
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
          cornerRadius: 8,
          animation: false
        }
      },
      elements: {
        line: {
          tension: 0,
          borderWidth: 2,
          fill: false
        },
        point: {
          radius: 0,
          hitRadius: 10,
          hoverRadius: 4
        },
        bar: {
          borderWidth: 2,
          borderRadius: 4
        }
      },
      interaction: {
        mode: 'index',
        intersect: false
      },
      layout: {
        padding: {
          top: 10,
          right: 10,
          bottom: 10,
          left: 10
        }
      },
      onResize: function(chart, size) {
        chart.canvas.style.width = '100%';
        chart.canvas.style.height = '100%';
      }
    };

    // تابع برای ایجاد نمودار
    function createChart(ctx, type, data, options = {}) {
      if (charts[ctx.canvas.id]) {
        charts[ctx.canvas.id].destroy();
      }

      // تنظیم ابعاد ثابت برای canvas
      ctx.canvas.style.width = '100%';
      ctx.canvas.style.height = '100%';

      charts[ctx.canvas.id] = new Chart(ctx, {
        type: type,
        data: data,
        options: {
          ...baseOptions,
          ...options
        }
      });
      return charts[ctx.canvas.id];
    }

    // تابع برای بارگذاری داده‌ها
    function loadCharts() {
      $.ajax({
        url: "{{ route('dr-my-performance-chart-data') }}",
        method: 'GET',
        data: {
          clinic_id: selectedClinicId
        },
        success: function(response) {
          // نمودار تعداد ویزیت‌ها
          createChart(
            document.getElementById('doctor-performance-chart').getContext('2d'),
            'line', {
              labels: response.appointments.map(item => item.month),
              datasets: [{
                label: 'برنامه‌ریزی‌شده',
                data: response.appointments.map(item => item.scheduled_count),
                borderColor: '#60a5fa',
                backgroundColor: '#60a5fa',
                tension: 0,
                fill: false
              }, {
                label: 'انجام‌شده',
                data: response.appointments.map(item => item.attended_count),
                borderColor: '#34d399',
                backgroundColor: '#34d399',
                tension: 0,
                fill: false
              }, {
                label: 'غیبت',
                data: response.appointments.map(item => item.missed_count),
                borderColor: '#f87171',
                backgroundColor: '#f87171',
                tension: 0,
                fill: false
              }, {
                label: 'لغو‌شده',
                data: response.appointments.map(item => item.cancelled_count),
                borderColor: '#fbbf24',
                backgroundColor: '#fbbf24',
                tension: 0,
                fill: false
              }]
            }
          );

          // نمودار درآمد ماهانه
          createChart(
            document.getElementById('doctor-income-chart').getContext('2d'),
            'line', {
              labels: response.monthlyIncome.map(item => item.month),
              datasets: [{
                label: 'پرداخت‌شده',
                data: response.monthlyIncome.map(item => item.total_paid_income),
                borderColor: '#10b981',
                backgroundColor: '#10b981',
                tension: 0,
                fill: false
              }, {
                label: 'پرداخت‌نشده',
                data: response.monthlyIncome.map(item => item.total_unpaid_income),
                borderColor: '#ef4444',
                backgroundColor: '#ef4444',
                tension: 0,
                fill: false
              }]
            }
          );

          // نمودار بیماران جدید
          createChart(
            document.getElementById('doctor-patient-chart').getContext('2d'),
            'line', {
              labels: response.newPatients.map(item => item.month),
              datasets: [{
                label: 'بیماران جدید',
                data: response.newPatients.map(item => item.total_patients),
                borderColor: '#f59e0b',
                backgroundColor: '#f59e0b',
                tension: 0,
                fill: false
              }]
            }
          );

          // نمودار وضعیت نوبت‌ها
          createChart(
            document.getElementById('doctor-status-chart').getContext('2d'),
            'line', {
              labels: response.appointmentStatusByMonth.map(item => item.month),
              datasets: [{
                label: 'برنامه‌ریزی‌شده',
                data: response.appointmentStatusByMonth.map(item => item.scheduled_count),
                borderColor: '#60a5fa',
                backgroundColor: '#60a5fa',
                tension: 0,
                fill: false
              }, {
                label: 'انجام‌شده',
                data: response.appointmentStatusByMonth.map(item => item.attended_count),
                borderColor: '#34d399',
                backgroundColor: '#34d399',
                tension: 0,
                fill: false
              }, {
                label: 'غیبت',
                data: response.appointmentStatusByMonth.map(item => item.missed_count),
                borderColor: '#f87171',
                backgroundColor: '#f87171',
                tension: 0,
                fill: false
              }, {
                label: 'لغو‌شده',
                data: response.appointmentStatusByMonth.map(item => item.cancelled_count),
                borderColor: '#fbbf24',
                backgroundColor: '#fbbf24',
                tension: 0,
                fill: false
              }]
            }
          );

          // نمودار درصد وضعیت نوبت‌ها
          let totalScheduled = response.appointmentStatusByMonth.reduce((sum, item) => sum + item
            .scheduled_count, 0);
          let totalAttended = response.appointmentStatusByMonth.reduce((sum, item) => sum + item
            .attended_count, 0);
          let totalMissed = response.appointmentStatusByMonth.reduce((sum, item) => sum + item.missed_count,
            0);
          let totalCancelled = response.appointmentStatusByMonth.reduce((sum, item) => sum + item
            .cancelled_count, 0);

          createChart(
            document.getElementById('doctor-status-pie-chart').getContext('2d'),
            'pie', {
              labels: ['برنامه‌ریزی‌شده', 'انجام‌شده', 'غیبت', 'لغو‌شده'],
              datasets: [{
                data: [totalScheduled, totalAttended, totalMissed, totalCancelled],
                backgroundColor: ['#60a5fa', '#34d399', '#f87171', '#fbbf24'],
                borderWidth: 2,
                borderColor: '#ffffff'
              }]
            }, {
              plugins: {
                legend: {
                  position: 'bottom'
                }
              }
            }
          );

          // نمودار روند بیماران
          createChart(
            document.getElementById('doctor-patient-trend-chart').getContext('2d'),
            'line', {
              labels: response.newPatients.map(item => item.month),
              datasets: [{
                label: 'بیماران جدید',
                data: response.newPatients.map(item => item.total_patients),
                borderColor: '#f97316',
                backgroundColor: '#f97316',
                tension: 0,
                fill: false
              }]
            }
          );
        },
        error: function() {
          toastr.error('خطا در دریافت اطلاعات نمودارها');
        }
      });
    }

    // بارگذاری اولیه نمودارها
    loadCharts();
  });
</script>
@endsection
