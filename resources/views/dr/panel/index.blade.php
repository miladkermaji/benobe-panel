@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/dashboard.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'لیست بیماران')
<div class="d-flex flex-column justify-content-center p-3 top-panel-bg">
  <div class="top-details-sicks-cards">
    <div class="top-s-a-wrapper">
      <div onclick="location.href='{{ route('dr-appointments') }}'" class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('dr-assets/icons/count.svg') }}" alt="تعداد بیماران امروز">
        </div>
        <div class="stat-info">
          <div class="stat-label">تعداد بیماران امروز</div>
          <div class="stat-value">{{ $totalPatientsToday }} بیمار</div>
        </div>
      </div>
      <div onclick="location.href='{{ route('dr-appointments') }}'" class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('dr-assets/icons/dashboard-tick.svg') }}" alt="بیماران ویزیت شده">
        </div>
        <div class="stat-info">
          <div class="stat-label">بیماران ویزیت شده</div>
          <div class="stat-value">{{ $visitedPatients }} بیمار</div>
        </div>
      </div>
      <div onclick="location.href='{{ route('dr-appointments') }}'" class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('dr-assets/icons/dashboard-timer.svg') }}" alt="بیماران باقی مانده">
        </div>
        <div class="stat-info">
          <div class="stat-label">بیماران باقی مانده</div>
          <div class="stat-value">{{ $remainingPatients }} بیمار</div>
        </div>
      </div>
      <div onclick="location.href='{{ route('dr.panel.financial-reports.index') }}'" class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('dr-assets/icons/money.svg') }}" alt="درآمد این هفته">
        </div>
        <div class="stat-info">
          <div class="stat-label">درآمد این هفته</div>
          <div class="stat-value">{{ number_format($weeklyIncome) }} تومان</div>
        </div>
      </div>
      <div onclick="location.href='{{ route('dr.panel.financial-reports.index') }}'" class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('dr-assets/icons/money.svg') }}" alt="درآمد این ماه">
        </div>
        <div class="stat-info">
          <div class="stat-label">درآمد این ماه</div>
          <div class="stat-value">{{ number_format($monthlyIncome) }} تومان</div>
        </div>
      </div>
      <div onclick="location.href='{{ route('dr.panel.financial-reports.index') }}'" class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('dr-assets/icons/money.svg') }}" alt="درآمد کلی">
        </div>
        <div class="stat-info">
          <div class="stat-label">درآمد کلی</div>
          <div class="stat-value">{{ number_format($totalIncome) }} تومان</div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="chart-content">
  <div class="chart-grid">
    <!-- نمودار ۱: تعداد ویزیت‌ها -->
    <div class="chart-container">
      <div class="chart-title">📊 تعداد ویزیت‌ها</div>
      <div class="chart-wrapper">
        <canvas id="doctor-performance-chart"></canvas>
      </div>
    </div>
    <!-- نمودار ۲: درآمد ماهانه -->
    <div class="chart-container">
      <div class="chart-title">💰 درآمد ماهانه</div>
      <div class="chart-wrapper">
        <canvas id="doctor-income-chart"></canvas>
      </div>
    </div>
    <!-- نمودار ۳: تعداد بیماران جدید -->
    <div class="chart-container">
      <div class="chart-title">👨‍⚕️ بیماران جدید</div>
      <div class="chart-wrapper">
        <canvas id="doctor-patient-chart"></canvas>
      </div>
    </div>
    <!-- نمودار ۴: وضعیت نوبت‌ها -->
    <div class="chart-container">
      <div class="chart-title">📈 انواع نوبت‌ها</div>
      <div class="chart-wrapper">
        <canvas id="doctor-status-chart"></canvas>
      </div>
    </div>
    <!-- نمودار ۵: درصد نوبت‌ها -->
    <div class="chart-container">
      <div class="chart-title">🥧 درصد نوبت‌ها</div>
      <div class="chart-wrapper">
        <canvas id="doctor-status-pie-chart"></canvas>
      </div>
    </div>
    <!-- نمودار ۶: روند بیماران -->
    <div class="chart-container">
      <div class="chart-title">📉 روند بیماران</div>
      <div class="chart-wrapper">
        <canvas id="doctor-patient-trend-chart"></canvas>
      </div>
    </div>
    <!-- نمودار ۷: نوبت‌های مشاوره -->
    <div class="chart-container">
      <div class="chart-title">🗣️ نوبت‌های مشاوره</div>
      <div class="chart-wrapper">
        <canvas id="doctor-counseling-chart"></canvas>
      </div>
    </div>
    <!-- نمودار ۸: نوبت‌های دستی -->
    <div class="chart-container">
      <div class="chart-title">✍️ نوبت‌های دستی</div>
      <div class="chart-wrapper">
        <canvas id="doctor-manual-chart"></canvas>
      </div>
    </div>
    <!-- نمودار ۹: درآمد کلی -->
    <div class="chart-container">
      <div class="chart-title">💸 درآمد کلی</div>
      <div class="chart-wrapper">
        <canvas id="doctor-total-income-chart"></canvas>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dashboard/dashboard.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var chartUrl = "{{ route('dr-my-performance-chart-data') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
</script>
@endsection
