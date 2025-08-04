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
          <img src="{{ asset('admin-assets/icons/clinic.svg') }}" alt="تعداد مراکز درمانی">
        </div>
        <div class="stat-info">
          <div class="stat-label">تعداد مراکز درمانی</div>
          <div class="stat-value">{{ $totalMedicalCenters }} مرکز درمانی</div>
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
  <div class="row">
    <!-- نمودار نوبت‌ها در هر ماه -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">نوبت‌ها در هر ماه</h5>
          <div class="chart-container">
            <canvas id="appointmentsByMonthChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار وضعیت نوبت‌ها -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">وضعیت نوبت‌ها</h5>
          <div class="chart-container">
            <canvas id="appointmentStatusesChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار نوبت‌ها در روزهای هفته -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">نوبت‌ها در روزهای هفته</h5>
          <div class="chart-container">
            <canvas id="appointmentsByDayOfWeekChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار توزیع تخصص‌های پزشکان -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">توزیع تخصص‌های پزشکان</h5>
          <div class="chart-container">
            <canvas id="doctorSpecialtiesChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار روند نوبت‌ها -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">روند نوبت‌ها</h5>
          <div class="chart-container">
            <canvas id="appointmentsTrendChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار مقایسه کلینیک‌ها -->

    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">مقایسه مراکز درمانی</h5>
          <div class="chart-container">
            <canvas id="medicalCenterComparisonChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار وضعیت پرداخت‌ها -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">وضعیت پرداخت‌ها</h5>
          <div class="chart-container">
            <canvas id="paymentStatusChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار آمار بازدید -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">آمار بازدید</h5>
          <div class="chart-container">
            <canvas id="visitorStatsChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار درآمد ماهانه -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">درآمد ماهانه</h5>
          <div class="chart-container">
            <canvas id="monthlyRevenueChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار توزیع بیمه‌ها -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">توزیع بیمه‌ها</h5>
          <div class="chart-container">
            <canvas id="insuranceDistributionChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار روند رشد کاربران -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">روند رشد کاربران</h5>
          <div class="chart-container">
            <canvas id="userGrowthChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار توزیع جنسیت بیماران -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">توزیع جنسیت بیماران</h5>
          <div class="chart-container">
            <canvas id="patientGenderDistributionChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار توزیع سنی بیماران -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">توزیع سنی بیماران</h5>
          <div class="chart-container">
            <canvas id="patientAgeDistributionChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- نمودار توزیع جغرافیایی بیماران -->
    <div class="col-md-4">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title">توزیع جغرافیایی بیماران</h5>
          <div class="chart-container">
            <canvas id="patientGeographicDistributionChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
  const chartData = {
    appointmentsByMonth: @json($appointmentsByMonth),
    appointmentStatuses: @json($appointmentStatuses),
    appointmentsByDayOfWeek: @json($appointmentsByDayOfWeek),
    doctorSpecialties: @json($doctorSpecialties),
    appointmentsTrend: @json($appointmentsTrend),
    medicalCenterComparison: @json($medicalCenterComparison),
    clinicComparison: @json($medicalCenterComparison),
    paymentStatus: @json($paymentStatus),
    visitorStats: @json($visitorStats),
    monthlyRevenue: @json($monthlyRevenue),
    insuranceDistribution: @json($insuranceDistribution),
    userGrowth: @json($userGrowth),
    patientGenderDistribution: @json($patientGenderDistribution),
    patientAgeDistribution: @json($patientAgeDistribution),
    patientGeographicDistribution: @json($patientGeographicDistribution)
  };
</script>
<script src="{{ asset('admin-assets/panel/js/dashboard/dashboard.js') }}"></script>
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
<style>
  .top-s-a-wrapper.grabbing {
    cursor: grabbing !important;
    user-select: none;
  }
</style>
@endsection
