<div class="container-fluid py-4" dir="rtl">
  <!-- Statistics Cards -->
  <div class="row mb-6 justify-content-center">
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
      <div
        class="card shadow-xl border-0 h-100 bg-dark-gradient text-center transition-all duration-300 hover:shadow-2xl">
        <div class="card-body d-flex flex-column justify-content-center align-items-center p-5">
          <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round" class="mb-3 transition-transform duration-300">
            <path d="M12 20v-6m0-8a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v8h22V6a4 4 0 0 0-4-4h-3a4 4 0 0 0-4 4z"></path>
            <circle cx="12" cy="10" r="3"></circle>
          </svg>
          <h6 class="text-gray-200 mb-2 font-medium">پزشکان</h6>
          <span class="h2 mb-0 font-bold text-primary">{{ $totalDoctors }}</span>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
      <div
        class="card shadow-xl border-0 h-100 bg-dark-gradient text-center transition-all duration-300 hover:shadow-2xl">
        <div class="card-body d-flex flex-column justify-content-center align-items-center p-5">
          <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round" class="mb-3 transition-transform duration-300">
            <path d="M12 22s-8-4-8-10a8 8 0 1 1 16 0c0 6-8 10-8 10z"></path>
            <path d="M12 6a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"></path>
          </svg>
          <h6 class="text-gray-200 mb-2 font-medium">بیماران</h6>
          <span class="h2 mb-0 font-bold text-success">{{ $totalPatients }}</span>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
      <div
        class="card shadow-xl border-0 h-100 bg-dark-gradient text-center transition-all duration-300 hover:shadow-2xl">
        <div class="card-body d-flex flex-column justify-content-center align-items-center p-5">
          <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#22d3ee" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round" class="mb-3 transition-transform duration-300">
            <path d="M21 16V8a2 2 0 0 0-2-2h-6l-3-3H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2z"></path>
            <path d="M9 10h6"></path>
          </svg>
          <h6 class="text-gray-200 mb-2 font-medium">منشی‌ها</h6>
          <span class="h2 mb-0 font-bold text-info">{{ $totalSecretaries }}</span>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
      <div
        class="card shadow-xl border-0 h-100 bg-dark-gradient text-center transition-all duration-300 hover:shadow-2xl">
        <div class="card-body d-flex flex-column justify-content-center align-items-center p-5">
          <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round" class="mb-3 transition-transform duration-300">
            <path d="M12 2l9 4.5L12 11 3 6.5 12 2z"></path>
            <path d="M3 17l9 4.5 9-4.5"></path>
            <path d="M3 11.5l9 4.5 9-4.5"></path>
          </svg>
          <h6 class="text-gray-200 mb-2 font-medium">مدیران</h6>
          <span class="h2 mb-0 font-bold text-warning">{{ $totalManagers }}</span>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
      <div
        class="card shadow-xl border-0 h-100 bg-dark-gradient text-center transition-all duration-300 hover:shadow-2xl">
        <div class="card-body d-flex flex-column justify-content-center align-items-center p-5">
          <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round" class="mb-3 transition-transform duration-300">
            <path d="M3 21h18v-8a5 5 0 0 0-5-5H8a5 5 0 0 0-5 5v8z"></path>
            <path d="M12 8V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v4"></path>
          </svg>
          <h6 class="text-gray-200 mb-2 font-medium">کلینیک‌ها</h6>
          <span class="h2 mb-0 font-bold text-danger">{{ $totalClinics }}</span>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
      <div
        class="card shadow-xl border-0 h-100 bg-dark-gradient text-center transition-all duration-300 hover:shadow-2xl">
        <div class="card-body d-flex flex-column justify-content-center align-items-center p-5">
          <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round" class="mb-3 transition-transform duration-300">
            <rect x="4" y="4" width="16" height="16" rx="2"></rect>
            <path d="M9 12h6"></path>
            <path d="M12 9v6"></path>
          </svg>
          <h6 class="text-gray-200 mb-2 font-medium">نوبت‌ها</h6>
          <span class="h2 mb-0 font-bold text-purple">{{ $totalAppointments }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="row">
    <div class="col-lg-12 col-xl-12 mb-4">
      <div class="card shadow-xl border-0 bg-white transition-all duration-300 hover:shadow-2xl">
        <div class="card-header bg-transparent border-0 text-right px-5 pt-4">
          <h5 class="card-title mb-0 text-gray-800 font-semibold">نوبت‌ها در هر ماه</h5>
        </div>
        <div class="card-body p-5">
          <canvas id="appointmentsByMonthChart" height="350"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-xl-4 mb-4">
      <div class="card shadow-xl border-0 bg-white transition-all duration-300 hover:shadow-2xl">
        <div class="card-header bg-transparent border-0 text-right px-5 pt-4">
          <h5 class="card-title mb-0 text-gray-800 font-semibold">وضعیت نوبت‌ها</h5>
        </div>
        <div class="card-body p-5">
          <canvas id="appointmentStatusesChart" height="350"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-xl-4 mb-4">
      <div class="card shadow-xl border-0 bg-white transition-all duration-300 hover:shadow-2xl">
        <div class="card-header bg-transparent border-0 text-right px-5 pt-4">
          <h5 class="card-title mb-0 text-gray-800 font-semibold">نوبت‌ها در روزهای هفته</h5>
        </div>
        <div class="card-body p-5">
          <canvas id="appointmentsByDayOfWeekChart" height="350"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-xl-4 mb-4">
      <div class="card shadow-xl border-0 bg-white transition-all duration-300 hover:shadow-2xl">
        <div class="card-header bg-transparent border-0 text-right px-5 pt-4">
          <h5 class="card-title mb-0 text-gray-800 font-semibold">فعالیت کلینیک‌ها</h5>
        </div>
        <div class="card-body p-5">
          <canvas id="clinicActivityChart" height="350"></canvas>
        </div>
      </div>
    </div>
  </div>




  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const persianMonths = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی',
        'بهمن', 'اسفند'
      ];
      const persianDays = ['دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه', 'شنبه', 'یک‌شنبه'];

      const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              font: {
                family: 'IRANSans',
                size: 14,
                weight: '500'
              },
              padding: 20,
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

      // Appointments by Month Chart
      const appointmentsByMonthCtx = document.getElementById('appointmentsByMonthChart').getContext('2d');
      new Chart(appointmentsByMonthCtx, {
        type: 'bar',
        data: {
          labels: persianMonths,
          datasets: [{
            label: 'تعداد نوبت‌ها',
            data: @json(array_values($appointmentsByMonth)),
            backgroundColor: '#60a5fa',
            borderColor: '#3b82f6',
            borderWidth: 1,
            borderRadius: 12,
            barThickness: 20
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
          },
          plugins: {
            legend: {
              display: false
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
            backgroundColor: ['#60a5fa', '#f87171', '#34d399', '#fbbf24'],
            borderWidth: 2,
            borderColor: '#fff'
          }]
        },
        options: {
          ...commonOptions,
          cutout: '60%'
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
            backgroundColor: '#34d399',
            borderColor: '#10b981',
            borderWidth: 1,
            borderRadius: 12,
            barThickness: 20
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
          },
          plugins: {
            legend: {
              display: false
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
            backgroundColor: ['#60a5fa', '#f87171', '#34d399', '#fbbf24', '#a78bfa', '#f59e0b'],
            borderColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#d97706'],
            borderWidth: 1,
            borderRadius: 12,
            barThickness: 20
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
</div>
