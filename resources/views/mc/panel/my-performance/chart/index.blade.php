@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/my-performance/chart/chart.css') }}" rel="stylesheet" />
@endsection
@section('content')
@section('bread-crumb-title', 'آمار و نمودار')
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
<script>
  var chartUrl = "{{ route('dr-my-performance-chart-data') }}";
</script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dashboard/dashboard.js') }}"></script>
@endsection
