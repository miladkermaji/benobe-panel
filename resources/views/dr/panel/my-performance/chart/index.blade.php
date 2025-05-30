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
<script src="{{ asset('dr-assets/panel/js/calendar/custm-calendar.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    let selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';
    let charts = {};

    // تنظیمات پایه برای همه نمودارها
    const baseOptions = {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      plugins: {
        legend: {
          position: 'top',
          labels: {
            font: {
              family: 'IRANSans',
              size: 14,
              weight: '500'
            },
            padding: 15,
            color: '#2d3748',
            usePointStyle: true,
            pointStyle: 'rect'
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
          cornerRadius: 8,
          animation: false,
          callbacks: {
            label: function(context) {
              let label = context.dataset.label || '';
              if (label) {
                label += ': ';
              }
              if (context.parsed.y !== null) {
                label += new Intl.NumberFormat('fa-IR').format(context.parsed.y);
              }
              return label;
            }
          }
        }
      },
      elements: {
        line: {
          tension: 0,
          borderWidth: 2,
          fill: false
        },
        point: {
          radius: 0,
          hitRadius: 10,
          hoverRadius: 4
        },
        bar: {
          borderWidth: 2,
          borderRadius: 4
        }
      },
      interaction: {
        mode: 'index',
        intersect: false
      },
      layout: {
        padding: {
          top: 10,
          right: 10,
          bottom: 10,
          left: 10
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            maxRotation: 45,
            minRotation: 45,
            font: {
              family: 'IRANSans',
              size: 12
            },
            callback: function(value, index, values) {
              const label = this.getLabelForValue(value);
              if (label.length > 10) {
                return label.substring(0, 10) + '...';
              }
              return label;
            }
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0, 0, 0, 0.05)'
          },
          ticks: {
            font: {
              family: 'IRANSans',
              size: 12
            },
            callback: function(value) {
              return new Intl.NumberFormat('fa-IR').format(value);
            }
          }
        }
      }
    };

    // تابع برای ایجاد نمودار
    function createChart(ctx, type, data, options = {}) {
      if (charts[ctx.canvas.id]) {
        charts[ctx.canvas.id].destroy();
      }

      // تنظیم ابعاد ثابت برای canvas
      ctx.canvas.style.width = '100%';
      ctx.canvas.style.height = '100%';

      charts[ctx.canvas.id] = new Chart(ctx, {
        type: type,
        data: data,
        options: {
          ...baseOptions,
          ...options
        }
      });
      return charts[ctx.canvas.id];
    }

    // تابع برای بارگذاری داده‌ها
    function loadCharts() {
      $.ajax({
        url: "{{ route('dr-my-performance-chart-data') }}",
        method: 'GET',
        data: {
          clinic_id: selectedClinicId
        },
        success: function(response) {
          // نمودار تعداد ویزیت‌ها - تغییر به نمودار ستونی گروهی
          createChart(
            document.getElementById('doctor-performance-chart').getContext('2d'),
            'bar', {
              labels: response.appointments.map(item => item.month),
              datasets: [{
                label: 'برنامه‌ریزی‌شده',
                data: response.appointments.map(item => item.scheduled_count),
                backgroundColor: '#60a5fa',
                borderColor: '#60a5fa',
                borderWidth: 1
              }, {
                label: 'انجام‌شده',
                data: response.appointments.map(item => item.attended_count),
                backgroundColor: '#34d399',
                borderColor: '#34d399',
                borderWidth: 1
              }, {
                label: 'غیبت',
                data: response.appointments.map(item => item.missed_count),
                backgroundColor: '#f87171',
                borderColor: '#f87171',
                borderWidth: 1
              }, {
                label: 'لغو‌شده',
                data: response.appointments.map(item => item.cancelled_count),
                backgroundColor: '#fbbf24',
                borderColor: '#fbbf24',
                borderWidth: 1
              }]
            }, {
              scales: {
                x: {
                  stacked: false,
                  ticks: {
                    maxTicksLimit: 12
                  }
                },
                y: {
                  stacked: false,
                  title: {
                    display: true,
                    text: 'تعداد ویزیت'
                  }
                }
              }
            }
          );

          // نمودار درآمد ماهانه - تغییر به نمودار خطی با ناحیه پر شده
          createChart(
            document.getElementById('doctor-income-chart').getContext('2d'),
            'line', {
              labels: response.monthlyIncome.map(item => item.month),
              datasets: [{
                label: 'پرداخت‌شده',
                data: response.monthlyIncome.map(item => item.total_paid_income),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4
              }, {
                label: 'پرداخت‌نشده',
                data: response.monthlyIncome.map(item => item.total_unpaid_income),
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true,
                tension: 0.4
              }]
            }, {
              scales: {
                x: {
                  ticks: {
                    maxTicksLimit: 12
                  }
                },
                y: {
                  title: {
                    display: true,
                    text: 'مبلغ (تومان)'
                  },
                  ticks: {
                    callback: function(value) {
                      return new Intl.NumberFormat('fa-IR').format(value) + ' تومان';
                    }
                  }
                }
              }
            }
          );

          // نمودار بیماران جدید - تغییر به نمودار خطی با نقاط
          createChart(
            document.getElementById('doctor-patient-chart').getContext('2d'),
            'line', {
              labels: response.newPatients.map(item => item.month),
              datasets: [{
                label: 'بیماران جدید',
                data: response.newPatients.map(item => item.total_patients),
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6
              }]
            }, {
              scales: {
                x: {
                  ticks: {
                    maxTicksLimit: 12
                  }
                },
                y: {
                  title: {
                    display: true,
                    text: 'تعداد بیمار'
                  }
                }
              }
            }
          );

          // نمودار وضعیت نوبت‌ها - تغییر به نمودار ستونی انباشته
          createChart(
            document.getElementById('doctor-status-chart').getContext('2d'),
            'bar', {
              labels: response.appointmentStatusByMonth.map(item => item.month),
              datasets: [{
                label: 'برنامه‌ریزی‌شده',
                data: response.appointmentStatusByMonth.map(item => item.scheduled_count),
                backgroundColor: '#60a5fa',
                borderColor: '#60a5fa',
                borderWidth: 1
              }, {
                label: 'انجام‌شده',
                data: response.appointmentStatusByMonth.map(item => item.attended_count),
                backgroundColor: '#34d399',
                borderColor: '#34d399',
                borderWidth: 1
              }, {
                label: 'غیبت',
                data: response.appointmentStatusByMonth.map(item => item.missed_count),
                backgroundColor: '#f87171',
                borderColor: '#f87171',
                borderWidth: 1
              }, {
                label: 'لغو‌شده',
                data: response.appointmentStatusByMonth.map(item => item.cancelled_count),
                backgroundColor: '#fbbf24',
                borderColor: '#fbbf24',
                borderWidth: 1
              }]
            }, {
              scales: {
                x: {
                  stacked: true,
                  ticks: {
                    maxTicksLimit: 12
                  }
                },
                y: {
                  stacked: true,
                  title: {
                    display: true,
                    text: 'تعداد نوبت'
                  }
                }
              }
            }
          );

          // نمودار درصد وضعیت نوبت‌ها - بهبود نمودار دایره‌ای
          let totalScheduled = response.appointmentStatusByMonth.reduce((sum, item) => sum + item
            .scheduled_count, 0);
          let totalAttended = response.appointmentStatusByMonth.reduce((sum, item) => sum + item
            .attended_count, 0);
          let totalMissed = response.appointmentStatusByMonth.reduce((sum, item) => sum + item.missed_count,
            0);
          let totalCancelled = response.appointmentStatusByMonth.reduce((sum, item) => sum + item
            .cancelled_count, 0);

          createChart(
            document.getElementById('doctor-status-pie-chart').getContext('2d'),
            'doughnut', {
              labels: ['برنامه‌ریزی‌شده', 'انجام‌شده', 'غیبت', 'لغو‌شده'],
              datasets: [{
                data: [totalScheduled, totalAttended, totalMissed, totalCancelled],
                backgroundColor: ['#60a5fa', '#34d399', '#f87171', '#fbbf24'],
                borderWidth: 2,
                borderColor: '#ffffff'
              }]
            }, {
              plugins: {
                legend: {
                  position: 'bottom'
                },
                tooltip: {
                  callbacks: {
                    label: function(context) {
                      const label = context.label || '';
                      const value = context.raw;
                      const total = context.dataset.data.reduce((a, b) => a + b, 0);
                      const percentage = Math.round((value / total) * 100);
                      return `${label}: ${new Intl.NumberFormat('fa-IR').format(value)} (${percentage}%)`;
                    }
                  }
                }
              },
              cutout: '60%'
            }
          );

          // نمودار روند بیماران - تغییر به نمودار خطی با ناحیه پر شده
          createChart(
            document.getElementById('doctor-patient-trend-chart').getContext('2d'),
            'line', {
              labels: response.newPatients.map(item => item.month),
              datasets: [{
                label: 'بیماران جدید',
                data: response.newPatients.map(item => item.total_patients),
                borderColor: '#f97316',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6
              }]
            }, {
              scales: {
                x: {
                  ticks: {
                    maxTicksLimit: 12
                  }
                },
                y: {
                  title: {
                    display: true,
                    text: 'تعداد بیمار'
                  }
                }
              }
            }
          );
        },
        error: function() {
          toastr.error('خطا در دریافت اطلاعات نمودارها');
        }
      });
    }

    // بارگذاری اولیه نمودارها
    loadCharts();
  });
</script>
@endsection
