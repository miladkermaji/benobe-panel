@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/dashboard.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/my-performance/chart/chart.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'لیست بیماران')
<div class="d-flex flex-column justify-content-center p-3 top-panel-bg">
  <div class="top-details-sicks-cards">
    <div class="top-s-a-wrapper">
      <div>
        <img src="{{ asset('dr-assets/icons/count.svg') }}" alt="تعداد بیماران امروز">
        <span class="fw-bold mr-2 ml-2 text-dark">تعداد بیماران امروز</span>
        <span class="font-medium">{{ $totalPatientsToday }} بیمار</span>
      </div>
      <div>
        <img src="{{ asset('dr-assets/icons/dashboard-tick.svg') }}" alt="بیماران ویزیت شده">
        <span class="fw-bold mr-2 ml-2 text-dark">بیماران ویزیت شده</span>
        <span class="font-medium">{{ $visitedPatients }} بیمار</span>
      </div>
      <div>
        <img src="{{ asset('dr-assets/icons/dashboard-timer.svg') }}" alt="بیماران باقی مانده">
        <span class="fw-bold mr-2 ml-2 text-dark">بیماران باقی مانده</span>
        <span class="font-medium">{{ $remainingPatients }} بیمار</span>
      </div>
      <div>
        <img src="{{ asset('dr-assets/icons/count.svg') }}" alt="تعداد منشی ها">
        <span class="fw-bold mr-2 ml-2 text-dark">تعداد منشی ها</span>
        <span class="font-medium">{{ $secretriesCount }} نفر</span>
      </div>
      <div>
        <img src="{{ asset('dr-assets/icons/count.svg') }}" alt="تعداد کلینیک">
        <span class="fw-bold mr-2 ml-2 text-dark">تعداد کلینیک</span>
        <span class="font-medium">{{ $clinicsCount }} کلینیک</span>
      </div>
    </div>
  </div>
</div>

<div class="chart-content">
  <div class="chart-grid">
    <!-- 📊 نمودار ۱: تعداد ویزیت‌ها (تمام صفحه) -->
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
@include('dr.panel.my-tools.dashboardTools')
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>

<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('showModal')) {
      $('#activation-modal').modal('show');
    }
  });
</script>
@endsection
