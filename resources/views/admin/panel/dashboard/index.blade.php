@extends('admin.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('admin-assets/panel/css/dashboard.css') }}" rel="stylesheet" />

  <style>
    .chart-content {
      padding: 1.5rem;
    }

    .chart-card {
      background: white;
      border-radius: 1rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      margin-bottom: 1.5rem;
      height: 400px;
    }

    .chart-card .card-body {
      height: 100%;
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
    }

    .chart-card .card-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #2d3748;
      margin-bottom: 1rem;
      text-align: right;
      font-family: 'Vazir', sans-serif;
    }

    .chart-container {
      flex: 1;
      position: relative;
      min-height: 300px;
    }

    @media (max-width: 768px) {
      .chart-content {
        padding: 0.5rem;
      }

      .chart-card {
        margin-bottom: 1rem;
        height: 350px;
      }

      .chart-card .card-body {
        padding: 1rem;
      }

      .chart-card .card-title {
        font-size: 1rem;
        margin-bottom: 0.5rem;
      }

      .chart-container {
        min-height: 250px;
      }
    }
  </style>
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
          <img src="{{ asset('admin-assets/icons/clinic.svg') }}" alt="تعداد کلینیک‌ها">
        </div>
        <div class="stat-info">
          <div class="stat-label">تعداد کلینیک‌ها</div>
          <div class="stat-value">{{ $totalClinics }} کلینیک</div>
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
          <h5 class="card-title">مقایسه کلینیک‌ها</h5>
          <div class="chart-container">
            <canvas id="clinicComparisonChart"></canvas>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
          display: true,
          position: 'bottom',
          align: 'center',
          labels: {
            font: {
              family: 'Vazir',
              size: 14
            },
            padding: 20,
            usePointStyle: true,
            pointStyle: 'circle'
          }
        }
      }
    };

    const pieChartOptions = {
      ...commonOptions,
      plugins: {
        legend: {
          display: true,
          position: 'bottom',
          align: 'center',
          labels: {
            font: {
              family: 'Vazir',
              size: 14
            },
            padding: 20,
            usePointStyle: true,
            pointStyle: 'circle'
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return context.label + ': ' + context.raw;
            }
          }
        }
      },
      layout: {
        padding: {
          bottom: 40
        }
      },
      cutout: '50%'
    };

    // نمودار نوبت‌ها در هر ماه
    const appointmentsByMonthData = @json($appointmentsByMonth);
    const monthLabels = [];
    const monthData = [];
    for (let i = 1; i <= 12; i++) {
      if (appointmentsByMonthData[i] !== undefined) {
        monthLabels.push(persianMonths[i - 1]);
        monthData.push(appointmentsByMonthData[i]);
      }
    }
    new Chart(document.getElementById('appointmentsByMonthChart'), {
      type: 'bar',
      data: {
        labels: monthLabels,
        datasets: [{
          label: 'تعداد نوبت‌ها',
          data: monthData,
          backgroundColor: 'rgb(75, 192, 192)',
          borderWidth: 0
        }]
      },
      options: {
        ...commonOptions,
        plugins: {
          ...commonOptions.plugins,
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'تعداد نوبت‌ها: ' + context.raw;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });

    // نمودار وضعیت نوبت‌ها
    const appointmentStatusesData = @json($appointmentStatuses);
    new Chart(document.getElementById('appointmentStatusesChart'), {
      type: 'pie',
      data: {
        labels: Object.keys(appointmentStatusesData),
        datasets: [{
          data: Object.values(appointmentStatusesData),
          backgroundColor: [
            'rgb(75, 192, 192)',
            'rgb(255, 99, 132)',
            'rgb(255, 205, 86)',
            'rgb(54, 162, 235)'
          ]
        }]
      },
      options: pieChartOptions
    });

    // نمودار نوبت‌ها در روزهای هفته
    const appointmentsByDayOfWeekData = @json($appointmentsByDayOfWeek);
    new Chart(document.getElementById('appointmentsByDayOfWeekChart'), {
      type: 'bar',
      data: {
        labels: persianDays,
        datasets: [{
          label: 'تعداد نوبت‌ها',
          data: Object.values(appointmentsByDayOfWeekData),
          backgroundColor: 'rgb(153, 102, 255)'
        }]
      },
      options: commonOptions
    });

    // نمودار توزیع تخصص‌های پزشکان
    const doctorSpecialtiesData = @json($doctorSpecialties);
    const topSpecialties = Object.entries(doctorSpecialtiesData)
      .sort(([, a], [, b]) => b - a)
      .slice(0, 5)
      .reduce((r, [k, v]) => ({
        ...r,
        [k]: v
      }), {});

    new Chart(document.getElementById('doctorSpecialtiesChart'), {
      type: 'doughnut',
      data: {
        labels: Object.keys(topSpecialties),
        datasets: [{
          data: Object.values(topSpecialties),
          backgroundColor: [
            'rgb(255, 99, 132)',
            'rgb(54, 162, 235)',
            'rgb(255, 205, 86)',
            'rgb(75, 192, 192)',
            'rgb(153, 102, 255)'
          ]
        }]
      },
      options: pieChartOptions
    });

    // نمودار روند نوبت‌ها
    const appointmentsTrendData = @json($appointmentsTrend);
    const jalaliAppointmentsTrend = convertWeeksToJalali(appointmentsTrendData);

    new Chart(document.getElementById('appointmentsTrendChart'), {
      type: 'line',
      data: {
        labels: Object.keys(jalaliAppointmentsTrend),
        datasets: [{
          label: 'تعداد نوبت‌ها',
          data: Object.values(jalaliAppointmentsTrend),
          borderColor: 'rgb(153, 102, 255)',
          tension: 0.1
        }]
      },
      options: commonOptions
    });

    // نمودار مقایسه کلینیک‌ها
    const clinicComparisonData = @json($clinicComparison);
    new Chart(document.getElementById('clinicComparisonChart'), {
      type: 'bar',
      data: {
        labels: Object.keys(clinicComparisonData),
        datasets: [{
            label: 'حاضر شده',
            data: Object.values(clinicComparisonData).map(item => item['حاضر شده']),
            backgroundColor: 'rgb(75, 192, 192)'
          },
          {
            label: 'لغو شده',
            data: Object.values(clinicComparisonData).map(item => item['لغو شده']),
            backgroundColor: 'rgb(255, 99, 132)'
          },
          {
            label: 'غایب',
            data: Object.values(clinicComparisonData).map(item => item['غایب']),
            backgroundColor: 'rgb(255, 205, 86)'
          }
        ]
      },
      options: commonOptions
    });

    // نمودار وضعیت پرداخت‌ها
    const paymentStatusData = @json($paymentStatus);
    new Chart(document.getElementById('paymentStatusChart'), {
      type: 'pie',
      data: {
        labels: Object.keys(paymentStatusData),
        datasets: [{
          data: Object.values(paymentStatusData),
          backgroundColor: [
            'rgb(75, 192, 192)',
            'rgb(255, 99, 132)',
            'rgb(255, 205, 86)'
          ]
        }]
      },
      options: pieChartOptions
    });

    // نمودار آمار بازدید
    const visitorStatsData = @json($visitorStats);
    const persianVisitorLabels = {
      'today': 'امروز',
      'yesterday': 'دیروز',
      'this_week': 'این هفته',
      'last_week': 'هفته گذشته',
      'this_month': 'این ماه'
    };

    const persianVisitorData = {};
    Object.keys(visitorStatsData).forEach(key => {
      persianVisitorData[persianVisitorLabels[key]] = visitorStatsData[key];
    });

    new Chart(document.getElementById('visitorStatsChart'), {
      type: 'bar',
      data: {
        labels: Object.keys(persianVisitorData),
        datasets: [{
          label: 'تعداد بازدید',
          data: Object.values(persianVisitorData),
          backgroundColor: 'rgb(54, 162, 235)'
        }]
      },
      options: commonOptions
    });

    // نمودار درآمد ماهانه
    const monthlyRevenueData = @json($monthlyRevenue);
    const jalaliMonthlyRevenue = convertMonthsToJalali(monthlyRevenueData);

    new Chart(document.getElementById('monthlyRevenueChart'), {
      type: 'line',
      data: {
        labels: Object.keys(jalaliMonthlyRevenue),
        datasets: [{
          label: 'درآمد ماهانه',
          data: Object.values(jalaliMonthlyRevenue),
          borderColor: 'rgb(75, 192, 192)',
          tension: 0.1
        }]
      },
      options: {
        ...commonOptions,
        plugins: {
          ...commonOptions.plugins,
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'درآمد: ' + context.raw + ' تومان';
              }
            }
          }
        }
      }
    });

    // نمودار توزیع بیمه‌ها
    const insuranceDistributionData = @json($insuranceDistribution);
    const topInsurances = Object.entries(insuranceDistributionData)
      .sort(([, a], [, b]) => b - a)
      .slice(0, 5)
      .reduce((r, [k, v]) => ({
        ...r,
        [k]: v
      }), {});

    new Chart(document.getElementById('insuranceDistributionChart'), {
      type: 'doughnut',
      data: {
        labels: Object.keys(topInsurances),
        datasets: [{
          data: Object.values(topInsurances),
          backgroundColor: [
            'rgb(255, 99, 132)',
            'rgb(54, 162, 235)',
            'rgb(255, 205, 86)',
            'rgb(75, 192, 192)',
            'rgb(153, 102, 255)'
          ]
        }]
      },
      options: pieChartOptions
    });

    // نمودار روند رشد کاربران
    const userGrowthData = @json($userGrowth);
    const jalaliUserGrowth = convertMonthsToJalali(userGrowthData);

    new Chart(document.getElementById('userGrowthChart'), {
      type: 'line',
      data: {
        labels: Object.keys(jalaliUserGrowth),
        datasets: [{
          label: 'تعداد کاربران جدید',
          data: Object.values(jalaliUserGrowth),
          borderColor: 'rgb(255, 99, 132)',
          tension: 0.1
        }]
      },
      options: {
        ...commonOptions,
        plugins: {
          ...commonOptions.plugins,
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'تعداد کاربران: ' + context.raw;
              }
            }
          }
        }
      }
    });

    // نمودار توزیع جنسیت بیماران
    const patientGenderData = @json($patientGenderDistribution);
    new Chart(document.getElementById('patientGenderDistributionChart'), {
      type: 'pie',
      data: {
        labels: Object.keys(patientGenderData),
        datasets: [{
          data: Object.values(patientGenderData),
          backgroundColor: [
            'rgb(54, 162, 235)',
            'rgb(255, 99, 132)'
          ]
        }]
      },
      options: pieChartOptions
    });

    // نمودار توزیع سنی بیماران
    const patientAgeData = @json($patientAgeDistribution);
    new Chart(document.getElementById('patientAgeDistributionChart'), {
      type: 'bar',
      data: {
        labels: Object.keys(patientAgeData),
        datasets: [{
          label: 'تعداد بیماران',
          data: Object.values(patientAgeData),
          backgroundColor: 'rgb(75, 192, 192)'
        }]
      },
      options: commonOptions
    });

    // نمودار توزیع جغرافیایی بیماران
    const geographicData = @json($patientGeographicDistribution);
    const topGeographic = Object.entries(geographicData)
      .sort(([, a], [, b]) => b - a)
      .slice(0, 10)
      .reduce((r, [k, v]) => ({
        ...r,
        [k]: v
      }), {});
    new Chart(document.getElementById('patientGeographicDistributionChart'), {
      type: 'bar',
      data: {
        labels: Object.keys(topGeographic),
        datasets: [{
          label: 'تعداد بیماران',
          data: Object.values(topGeographic),
          backgroundColor: 'rgb(75, 192, 192)'
        }]
      },
      options: commonOptions
    });

    // تبدیل ماه‌های میلادی به شمسی برای نمودارها
    function convertMonthsToJalali(data) {
      const jalaliMonths = {
        '01': 'فروردین',
        '02': 'اردیبهشت',
        '03': 'خرداد',
        '04': 'تیر',
        '05': 'مرداد',
        '06': 'شهریور',
        '07': 'مهر',
        '08': 'آبان',
        '09': 'آذر',
        '10': 'دی',
        '11': 'بهمن',
        '12': 'اسفند'
      };

      const result = {};
      Object.keys(data).forEach(key => {
        if (key && data[key] !== undefined) {
          const [year, month] = key.split('-');
          if (month && jalaliMonths[month]) {
            const jalaliMonth = jalaliMonths[month];
            result[jalaliMonth] = data[key];
          }
        }
      });
      return result;
    }

    // تبدیل هفته‌های میلادی به شمسی برای نمودارها
    function convertWeeksToJalali(data) {
      const result = {};
      Object.keys(data).forEach(key => {
        const [year, week] = key.split('-');
        const date = moment().year(year).week(week);
        result[toJalaliWeek(date)] = data[key];
      });
      return result;
    }

    // تبدیل تاریخ میلادی به شمسی
    function toJalali(date) {
      return moment(date).format('jYYYY/jMM/jDD');
    }

    // تبدیل هفته میلادی به شمسی
    function toJalaliWeek(date) {
      return moment(date).format('jYYYY/jMM/jDD');
    }
  });
</script>
@endsection
