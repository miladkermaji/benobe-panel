<div class="container-fluid py-4">
 <!-- Statistics Cards -->
 <div class="row mb-5 justify-content-center">
  <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
   <div class="card shadow-lg border-0 h-100 bg-dark-gradient text-center">
    <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
     <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-3">
      <path d="M12 20v-6m0-8a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v8h22V6a4 4 0 0 0-4-4h-3a4 4 0 0 0-4 4z"></path>
      <circle cx="12" cy="10" r="3"></circle>
     </svg>
     <h6 class="text-dark mb-2">پزشکان</h6>
     <span class="h2 mb-0 font-weight-bold text-primary">{{ $totalDoctors }}</span>
    </div>
   </div>
  </div>
  <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
   <div class="card shadow-lg border-0 h-100 bg-dark-gradient text-center">
    <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
     <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-3">
      <path d="M12 22s-8-4-8-10a8 8 0 1 1 16 0c0 6-8 10-8 10z"></path>
      <path d="M12 6a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"></path>
     </svg>
     <h6 class="text-dark mb-2">بیماران</h6>
     <span class="h2 mb-0 font-weight-bold text-success">{{ $totalPatients }}</span>
    </div>
   </div>
  </div>
  <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
   <div class="card shadow-lg border-0 h-100 bg-dark-gradient text-center">
    <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
     <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#22d3ee" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-3">
      <path d="M21 16V8a2 2 0 0 0-2-2h-6l-3-3H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2z"></path>
      <path d="M9 10h6"></path>
     </svg>
     <h6 class="text-dark mb-2">منشی‌ها</h6>
     <span class="h2 mb-0 font-weight-bold text-info">{{ $totalSecretaries }}</span>
    </div>
   </div>
  </div>
  <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
   <div class="card shadow-lg border-0 h-100 bg-dark-gradient text-center">
    <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
     <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-3">
      <path d="M12 2l9 4.5L12 11 3 6.5 12 2z"></path>
      <path d="M3 17l9 4.5 9-4.5"></path>
      <path d="M3 11.5l9 4.5 9-4.5"></path>
     </svg>
     <h6 class="text-dark mb-2">مدیران</h6>
     <span class="h2 mb-0 font-weight-bold text-warning">{{ $totalManagers }}</span>
    </div>
   </div>
  </div>
  <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
   <div class="card shadow-lg border-0 h-100 bg-dark-gradient text-center">
    <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
     <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-3">
      <path d="M3 21h18v-8a5 5 0 0 0-5-5H8a5 5 0 0 0-5 5v8z"></path>
      <path d="M12 8V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v4"></path>
     </svg>
     <h6 class="text-dark mb-2">کلینیک‌ها</h6>
     <span class="h2 mb-0 font-weight-bold text-danger">{{ $totalClinics }}</span>
    </div>
   </div>
  </div>
  <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
   <div class="card shadow-lg border-0 h-100 bg-dark-gradient text-center">
    <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
     <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-3">
      <rect x="4" y="4" width="16" height="16" rx="2"></rect>
      <path d="M9 12h6"></path>
      <path d="M12 9v6"></path>
     </svg>
     <h6 class="text-dark mb-2">نوبت‌ها</h6>
     <span class="h2 mb-0 font-weight-bold text-purple">{{ $totalAppointments }}</span>
    </div>
   </div>
  </div>
 </div>
 <!-- Charts (Unchanged) -->
 <div class="row">
  <div class="col-lg-12 col-xl-12 mb-4">
   <div class="card shadow-lg border-0 bg-light">
    <div class="card-header bg-transparent border-0 text-left">
     <h5 class="card-title mb-0 text-dark">نوبت‌ها در هر ماه</h5>
    </div>
    <div class="card-body">
     <canvas id="appointmentsByMonthChart" height="300"></canvas>
    </div>
   </div>
  </div>
  <div class="col-lg-4 col-xl-4 mb-4">
   <div class="card shadow-lg border-0 bg-light">
    <div class="card-header bg-transparent border-0 text-left">
     <h5 class="card-title mb-0 text-dark">وضعیت نوبت‌ها</h5>
    </div>
    <div class="card-body">
     <canvas id="appointmentStatusesChart" height="300"></canvas>
    </div>
   </div>
  </div>
  <div class="col-lg-4 col-xl-4 mb-4">
   <div class="card shadow-lg border-0 bg-light">
    <div class="card-header bg-transparent border-0 text-left">
     <h5 class="card-title mb-0 text-dark">نوبت‌ها در روزهای هفته</h5>
    </div>
    <div class="card-body">
     <canvas id="appointmentsByDayOfWeekChart" height="300"></canvas>
    </div>
   </div>
  </div>
  <div class="col-lg-4 col-xl-4 mb-4">
   <div class="card shadow-lg border-0 bg-light">
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
  /* Modern Card Background */
  .bg-dark-gradient {
   background: linear-gradient(135deg, #2d3748 0%, #4b5563 100%);
   position: relative;
   overflow: hidden;
   border-radius: 16px;
   border: 1px solid rgba(255, 255, 255, 0.1);
  }
  .bg-dark-gradient::after {
   content: '';
   position: absolute;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   background: rgba(255, 255, 255, 0.03);
   pointer-events: none;
   transition: opacity 0.3s ease;
   opacity: 0;
  }
  .card:hover .bg-dark-gradient::after {
   opacity: 1;
  }
  /* Darker Text Colors for Better Visibility */
  .text-primary {
   color: #60a5fa !important;
  }
  .text-success {
   color: #4ade80 !important;
  }
  .text-info {
   color: #22d3ee !important;
  }
  .text-warning {
   color: #fbbf24 !important;
  }
  .text-danger {
   color: #f87171 !important;
  }
  .text-purple {
   color: #a78bfa !important;
  }
  .text-dark {
   color: #d1d5db !important;
  }
  /* Card Styling */
  .card {
   border-radius: 16px;
   overflow: hidden;
   transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .card:hover {
   transform: translateY(-8px);
   box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  }
  .card-body {
   padding: 2rem;
   position: relative;
   z-index: 1;
  }
  svg {
   transition: transform 0.3s ease, stroke 0.3s ease;
  }
  .card:hover svg {
   transform: scale(1.1);
   stroke: #e5e7eb;
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
   const persianMonths = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
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