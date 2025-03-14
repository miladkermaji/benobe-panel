<div class="container-fluid py-4">
 <!-- Statistics Cards -->
 <div class="row mb-5 justify-content-center">
  <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
   <div class="card shadow-lg border-0 h-100 bg-dark-gradient text-center">
    <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
     <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#4dabf7" stroke-width="2"
      stroke-linecap="round" stroke-linejoin="round" class="mb-3">
      <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
      <circle cx="8.5" cy="7" r="4"></circle>
      <path d="M20 8v6m3-3h-6"></path>
     </svg>
     <h6 class="text-dark mb-2">پزشکان</h6>
     <span class="h2 mb-0 font-weight-bold text-primary-light">{{ $totalDoctors }}</span>
    </div>
   </div>
  </div>
  <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
   <div class="card shadow-lg border-0 h-100 bg-dark-gradient text-center">
    <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
     <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#63d475" stroke-width="2"
      stroke-linecap="round" stroke-linejoin="round" class="mb-3">
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
      <circle cx="9" cy="7" r="4"></circle>
      <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
      <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
     </svg>
     <h6 class="text-dark mb-2">بیماران</h6>
     <span class="h2 mb-0 font-weight-bold text-success-light">{{ $totalPatients }}</span>
    </div>
   </div>
  </div>
  <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
   <div class="card shadow-lg border-0 h-100 bg-dark-gradient text-center">
    <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
     <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#4bc9d9" stroke-width="2"
      stroke-linecap="round" stroke-linejoin="round" class="mb-3">
      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
      <circle cx="12" cy="7" r="4"></circle>
      <path d="M5 15l-2 4h4"></path>
     </svg>
     <h6 class="text-dark mb-2">منشی‌ها</h6>
     <span class="h2 mb-0 font-weight-bold text-info-light">{{ $totalSecretaries }}</span>
    </div>
   </div>
  </div>
  <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
   <div class="card shadow-lg border-0 h-100 bg-dark-gradient text-center">
    <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
     <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#ffd966" stroke-width="2"
      stroke-linecap="round" stroke-linejoin="round" class="mb-3">
      <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
      <path d="M2 17l10 5 10-5"></path>
      <path d="M2 12l10 5 10-5"></path>
     </svg>
     <h6 class="text-dark mb-2">مدیران</h6>
     <span class="h2 mb-0 font-weight-bold text-warning-light">{{ $totalManagers }}</span>
    </div>
   </div>
  </div>
  <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
   <div class="card shadow-lg border-0 h-100 bg-dark-gradient text-center">
    <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
     <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#ff6b7b" stroke-width="2"
      stroke-linecap="round" stroke-linejoin="round" class="mb-3">
      <path d="M3 21v-6a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v6"></path>
      <path d="M7 11V7a4 4 0 0 1 8 0v4"></path>
     </svg>
     <h6 class="text-dark mb-2">کلینیک‌ها</h6>
     <span class="h2 mb-0 font-weight-bold text-danger-light">{{ $totalClinics }}</span>
    </div>
   </div>
  </div>
  <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
   <div class="card shadow-lg border-0 h-100 bg-dark-gradient text-center">
    <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
     <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#9f7aec" stroke-width="2"
      stroke-linecap="round" stroke-linejoin="round" class="mb-3">
      <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
      <line x1="16" y1="2" x2="16" y2="6"></line>
      <line x1="8" y1="2" x2="8" y2="6"></line>
      <line x1="3" y1="10" x2="21" y2="10"></line>
     </svg>
     <h6 class="text-dark mb-2">نوبت‌ها</h6>
     <span class="h2 mb-0 font-weight-bold text-purple-light">{{ $totalAppointments }}</span>
    </div>
   </div>
  </div>
 </div>
 <!-- Charts (Unchanged) -->
 <div class="row">
  <div class="col-lg-6 col-xl-4 mb-4">
   <div class="card shadow-sm border-0 bg-light">
    <div class="card-header bg-transparent border-0 text-left">
     <h5 class="card-title mb-0 text-dark">نوبت‌ها در هر ماه</h5>
    </div>
    <div class="card-body">
     <canvas id="appointmentsByMonthChart" height="300"></canvas>
    </div>
   </div>
  </div>
  <div class="col-lg-6 col-xl-4 mb-4">
   <div class="card shadow-sm border-0 bg-light">
    <div class="card-header bg-transparent border-0 text-left">
     <h5 class="card-title mb-0 text-dark">وضعیت نوبت‌ها</h5>
    </div>
    <div class="card-body">
     <canvas id="appointmentStatusesChart" height="300"></canvas>
    </div>
   </div>
  </div>
  <div class="col-lg-6 col-xl-4 mb-4">
   <div class="card shadow-sm border-0 bg-light">
    <div class="card-header bg-transparent border-0 text-left">
     <h5 class="card-title mb-0 text-dark">نوبت‌ها در روزهای هفته</h5>
    </div>
    <div class="card-body">
     <canvas id="appointmentsByDayOfWeekChart" height="300"></canvas>
    </div>
   </div>
  </div>
  <div class="col-lg-6 col-xl-6 mb-4">
   <div class="card shadow-sm border-0 bg-light">
    <div class="card-header bg-transparent border-0 text-left">
     <h5 class="card-title mb-0 text-dark">فعالیت کلینیک‌ها</h5>
    </div>
    <div class="card-body">
     <canvas id="clinicActivityChart" height="300"></canvas>
    </div>
   </div>
  </div>
 </div>
</div>
@section('styles')
 <style>
  /* Dark Gradient Background for Cards */
  .bg-dark-gradient {
   background: linear-gradient(135deg, #2a2a2a 0%, #3f3f3f 100%);
   position: relative;
   overflow: hidden;
  }
  .bg-dark-gradient::before {
   content: '';
   position: absolute;
   top: -50%;
   left: -50%;
   width: 200%;
   height: 200%;
   background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
   transform: rotate(30deg);
   pointer-events: none;
  }
  /* Custom Text Colors */
  .text-primary-light {
   color: #4dabf7 !important;
  }
  .text-success-light {
   color: #63d475 !important;
  }
  .text-info-light {
   color: #4bc9d9 !important;
  }
  .text-warning-light {
   color: #ffd966 !important;
  }
  .text-danger-light {
   color: #ff6b7b !important;
  }
  .text-purple-light {
   color: #9f7aec !important;
  }
  .text-dark {
   color: #d1d5db !important;
  }
  /* Softer gray for labels */
  /* Card Styling */
  .card {
   border-radius: 25px;
   overflow: hidden;
   transition: transform 0.4s ease, box-shadow 0.4s ease;
   backdrop-filter: blur(5px);
   /* Glassmorphism effect */
  }
  .card:hover {
   transform: translateY(-12px) scale(1.02);
   box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  }
  .card-body {
   padding: 2rem;
   position: relative;
   z-index: 1;
  }
  svg {
   transition: transform 0.3s ease, filter 0.3s ease;
  }
  .card:hover svg {
   transform: scale(1.15);
   filter: brightness(1.2);
  }
  /* Chart Cards (Unchanged) */
  .bg-light {
   background: #f8f9fa;
  }
  .card-header {
   padding-bottom: 0;
  }
  .text-left {
   text-align: right !important;
  }
 </style>
@endsection
@section('scripts')
 <script>
  document.addEventListener('DOMContentLoaded', function() {
   // Persian month names
   const persianMonths = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن',
    'اسفند'
   ];
   // Persian day names (adjusted for WEEKDAY starting from Monday = 0)
   const persianDays = ['دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه', 'شنبه', 'یک‌شنبه'];
   // Appointments by Month Chart
   const appointmentsByMonthCtx = document.getElementById('appointmentsByMonthChart').getContext('2d');
   new Chart(appointmentsByMonthCtx, {
    type: 'bar',
    data: {
     labels: persianMonths,
     datasets: [{
      label: 'تعداد نوبت‌ها',
      data: @json(array_values($appointmentsByMonth)),
      backgroundColor: 'rgba(54, 162, 235, 0.7)',
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 1,
      borderRadius: 10,
     }]
    },
    options: {
     responsive: true,
     maintainAspectRatio: false,
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
   // Appointment Statuses Chart
   const appointmentStatusesCtx = document.getElementById('appointmentStatusesChart').getContext('2d');
   new Chart(appointmentStatusesCtx, {
    type: 'doughnut',
    data: {
     labels: @json(array_keys($appointmentStatuses)),
     datasets: [{
      data: @json(array_values($appointmentStatuses)),
      backgroundColor: [
       'rgba(54, 162, 235, 0.7)',
       'rgba(255, 99, 132, 0.7)',
       'rgba(75, 192, 192, 0.7)',
       'rgba(255, 206, 86, 0.7)'
      ],
      borderWidth: 2,
      borderColor: '#fff'
     }]
    },
    options: {
     responsive: true,
     maintainAspectRatio: false,
     plugins: {
      legend: {
       position: 'bottom',
       labels: {
        padding: 20
       }
      }
     }
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
      backgroundColor: 'rgba(75, 192, 192, 0.7)',
      borderColor: 'rgba(75, 192, 192, 1)',
      borderWidth: 1,
      borderRadius: 10,
     }]
    },
    options: {
     responsive: true,
     maintainAspectRatio: false,
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
   // Clinic Activity Chart
   const clinicActivityCtx = document.getElementById('clinicActivityChart').getContext('2d');
   new Chart(clinicActivityCtx, {
    type: 'bar',
    data: {
     labels: @json($clinicActivityLabels),
     datasets: [{
      label: 'تعداد نوبت‌ها',
      data: @json(array_values($clinicActivity)),
      backgroundColor: [
       'rgba(54, 162, 235, 0.7)',
       'rgba(255, 99, 132, 0.7)',
       'rgba(75, 192, 192, 0.7)',
       'rgba(255, 206, 86, 0.7)',
       'rgba(153, 102, 255, 0.7)',
       'rgba(255, 159, 64, 0.7)'
      ],
      borderColor: [
       'rgba(54, 162, 235, 1)',
       'rgba(255, 99, 132, 1)',
       'rgba(75, 192, 192, 1)',
       'rgba(255, 206, 86, 1)',
       'rgba(153, 102, 255, 1)',
       'rgba(255, 159, 64, 1)'
      ],
      borderWidth: 1,
      borderRadius: 12,
      barThickness: 20,
     }]
    },
    options: {
     responsive: true,
     maintainAspectRatio: false,
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
