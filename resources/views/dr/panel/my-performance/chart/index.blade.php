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
      <h4 class="section-title">📊 تعداد ویزیت‌ها به تفکیک وضعیت</h4>
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

    <!-- 🥧 نمودار ۵: درصد وضعیت نوبت‌ها (جدید - Pie Chart) -->
    <div class="chart-container">
      <h4 class="section-title">🥧 درصد وضعیت نوبت‌ها</h4>
      <canvas id="doctor-status-pie-chart"></canvas>
    </div>

    <!-- 📉 نمودار ۶: روند بیماران جدید (جدید - Line Chart) -->
    <div class="chart-container">
      <h4 class="section-title">📉 روند بیماران جدید</h4>
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
          alert('خطا در دریافت اطلاعات نمودارها');
        }
      });
    }

    // تنظیمات مشترک برای نمودارها
    const commonOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top'
        },
        tooltip: {
          enabled: true
        }
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
              backgroundColor: '#36a2eb'
            },
            {
              label: 'انجام‌شده',
              data: data.map(item => item.attended_count),
              backgroundColor: '#4bc0c0'
            },
            {
              label: 'غیبت',
              data: data.map(item => item.missed_count),
              backgroundColor: '#ff6384'
            },
            {
              label: 'لغو‌شده',
              data: data.map(item => item.cancelled_count),
              backgroundColor: '#ff9f40'
            }
          ]
        },
        options: commonOptions
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
              backgroundColor: '#4caf50'
            },
            {
              label: 'پرداخت‌نشده',
              data: data.map(item => item.total_unpaid_income),
              backgroundColor: '#f44336'
            }
          ]
        },
        options: commonOptions
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
            backgroundColor: '#ffce56'
          }]
        },
        options: commonOptions
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
              backgroundColor: '#42a5f5'
            },
            {
              label: 'انجام‌شده',
              data: data.map(item => item.attended_count),
              backgroundColor: '#66bb6a'
            },
            {
              label: 'غیبت',
              data: data.map(item => item.missed_count),
              backgroundColor: '#ef5350'
            },
            {
              label: 'لغو‌شده',
              data: data.map(item => item.cancelled_count),
              backgroundColor: '#ffb74d'
            }
          ]
        },
        options: commonOptions
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
            backgroundColor: ['#42a5f5', '#66bb6a', '#ef5350', '#ffb74d']
          }]
        },
        options: commonOptions
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
            borderColor: '#ff5722',
            backgroundColor: 'rgba(255, 87, 34, 0.2)',
            fill: true,
            tension: 0.4
          }]
        },
        options: commonOptions
      });
    }

    loadCharts();
  });
</script>
@endsection
