@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/dashboard.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'لیست بیماران')
<div class="d-flex flex-column justify-content-center p-3 top-panel-bg">
  <div class="top-details-sicks-cards">
    <div class="top-s-a-wrapper">
      <div onclick="location.href='{{ route('mc-appointments') }}'" class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('mc-assets/icons/count.svg') }}" alt="تعداد بیماران امروز">
        </div>
        <div class="stat-info">
          <div class="stat-label">تعداد بیماران امروز</div>
          <div class="stat-value">{{ $totalPatientsToday }} بیمار</div>
        </div>
      </div>
      <div onclick="location.href='{{ route('mc-appointments') }}'" class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('mc-assets/icons/dashboard-tick.svg') }}" alt="بیماران ویزیت شده">
        </div>
        <div class="stat-info">
          <div class="stat-label">بیماران ویزیت شده</div>
          <div class="stat-value">{{ $visitedPatients }} بیمار</div>
        </div>
      </div>
      <div onclick="location.href='{{ route('mc-appointments') }}'" class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('mc-assets/icons/dashboard-timer.svg') }}" alt="بیماران باقی مانده">
        </div>
        <div class="stat-info">
          <div class="stat-label">بیماران باقی مانده</div>
          <div class="stat-value">{{ $remainingPatients }} بیمار</div>
        </div>
      </div>
      <div onclick="location.href='{{ route('mc.panel.financial-reports.index') }}'" class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('mc-assets/icons/money.svg') }}" alt="درآمد این هفته">
        </div>
        <div class="stat-info">
          <div class="stat-label">درآمد این هفته</div>
          <div class="stat-value">{{ number_format($weeklyIncome) }} تومان</div>
        </div>
      </div>
      <div onclick="location.href='{{ route('mc.panel.financial-reports.index') }}'" class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('mc-assets/icons/money.svg') }}" alt="درآمد این ماه">
        </div>
        <div class="stat-info">
          <div class="stat-label">درآمد این ماه</div>
          <div class="stat-value">{{ number_format($monthlyIncome) }} تومان</div>
        </div>
      </div>
      <div onclick="location.href='{{ route('mc.panel.financial-reports.index') }}'" class="stat-card cursor-pointer">
        <div class="stat-icon">
          <img src="{{ asset('mc-assets/icons/money.svg') }}" alt="درآمد کلی">
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
  <div class="row">
    <!-- نمودار ۱: تعداد ویزیت‌ها -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">📊 تعداد ویزیت‌ها</h5>
          <div class="chart-container">
            <canvas id="doctor-performance-chart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار ۲: درآمد ماهانه -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">💰 درآمد ماهانه</h5>
          <div class="chart-container">
            <canvas id="doctor-income-chart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار ۳: تعداد بیماران جدید -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">👨‍⚕️ بیماران جدید</h5>
          <div class="chart-container">
            <canvas id="doctor-patient-chart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار ۴: وضعیت نوبت‌ها -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">📈 انواع نوبت‌ها</h5>
          <div class="chart-container">
            <canvas id="doctor-status-chart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار ۵: درصد نوبت‌ها -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">🥧 درصد نوبت‌ها</h5>
          <div class="chart-container">
            <canvas id="doctor-status-pie-chart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار ۶: روند بیماران -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">📉 روند بیماران</h5>
          <div class="chart-container">
            <canvas id="doctor-patient-trend-chart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار ۷: نوبت‌های مشاوره -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">🗣️ نوبت‌های مشاوره</h5>
          <div class="chart-container">
            <canvas id="doctor-counseling-chart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار ۸: نوبت‌های دستی -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">✍️ نوبت‌های دستی</h5>
          <div class="chart-container">
            <canvas id="doctor-manual-chart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار ۹: درآمد کلی -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">💸 درآمد کلی</h5>
          <div class="chart-container">
            <canvas id="doctor-total-income-chart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/dashboard/dashboard.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var chartUrl = "{{ route('mc-my-performance-chart-data') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
  window.selectedClinicId = @json($selectedClinicId ?? 'default');
</script>
<script>
  (function() {
    const slider = document.querySelector('.top-s-a-wrapper');
    if (!slider) return;
    let isDown = false;
    let startX, scrollLeft;

    slider.addEventListener('mousedown', (e) => {
      isDown = true;
      slider.classList.add('grabbing');
      startX = e.pageX - slider.offsetLeft;
      scrollLeft = slider.scrollLeft;
      e.preventDefault();
    });

    slider.addEventListener('mouseleave', () => {
      isDown = false;
      slider.classList.remove('grabbing');
    });

    slider.addEventListener('mouseup', () => {
      isDown = false;
      slider.classList.remove('grabbing');
    });

    slider.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      const x = e.pageX - slider.offsetLeft;
      const walk = x - startX;
      slider.scrollLeft = scrollLeft - walk;
      e.preventDefault();
    });

    // جلوگیری از اجرای لینک هنگام drag
    let dragMoved = false;
    slider.addEventListener('mousedown', () => {
      dragMoved = false;
    });
    slider.addEventListener('mousemove', () => {
      if (isDown) dragMoved = true;
    });
    slider.querySelectorAll('.stat-card').forEach(card => {
      card.addEventListener('click', function(e) {
        if (dragMoved) {
          e.preventDefault();
          e.stopImmediatePropagation();
        }
      }, true);
    });
  })();
</script>
@endsection
<style>
.top-s-a-wrapper.grabbing {
  cursor: grabbing !important;
  user-select: none;
}
</style>
