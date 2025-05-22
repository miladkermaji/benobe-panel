<script>
  $(document).ready(function() {
    let dropdownOpen = false;
    let selectedClinic = localStorage.getItem('selectedClinic');
    let selectedClinicId = localStorage.getItem('selectedClinicId');

    // تنظیم مقدار اولیه برای منوی کشویی
    if (selectedClinic && selectedClinicId) {
      $('.dropdown-label').text(selectedClinic);
      $('.option-card').each(function() {
        if ($(this).attr('data-id') === selectedClinicId) {
          $('.option-card').removeClass('card-active');
          $(this).addClass('card-active');
        }
      });
    } else {
      localStorage.setItem('selectedClinic', 'مشاوره آنلاین به نوبه');
      localStorage.setItem('selectedClinicId', 'default');
      $('.dropdown-label').text('مشاوره آنلاین به نوبه');
    }

    // بررسی کلینیک‌های غیرفعال برای نمایش هشدار
    function checkInactiveClinics() {
      var hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
      if (hasInactiveClinics) {
        $('.dropdown-trigger').addClass('warning');
      } else {
        $('.dropdown-trigger').removeClass('warning');
      }
    }
    checkInactiveClinics();

    // مدیریت کلیک روی دکمه منوی کشویی
    $('.dropdown-trigger').on('click', function(event) {
      event.stopPropagation();
      dropdownOpen = !dropdownOpen;
      $(this).toggleClass('border border-primary');
      $('.my-dropdown-menu').toggleClass('d-none');
      setTimeout(() => {
        dropdownOpen = $('.my-dropdown-menu').is(':visible');
      }, 100);
    });

    // بستن منوی کشویی با کلیک خارج از آن
    $(document).on('click', function() {
      if (dropdownOpen) {
        $('.dropdown-trigger').removeClass('border border-primary');
        $('.my-dropdown-menu').addClass('d-none');
        dropdownOpen = false;
      }
    });

    // جلوگیری از بسته شدن منو با کلیک داخل آن
    $('.my-dropdown-menu').on('click', function(event) {
      event.stopPropagation();
    });

    // مدیریت انتخاب گزینه کلینیک
    $('.option-card').on('click', function() {
      let currentDate = moment().format('YYYY-MM-DD');
      let persianDate = moment(currentDate, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');
      var selectedText = $(this).find('.fw-bold.d-block.fs-15').text().trim();
      var selectedId = $(this).attr('data-id');
      $('.option-card').removeClass('card-active');
      $(this).addClass('card-active');
      $('.dropdown-label').text(selectedText);
      localStorage.setItem('selectedClinic', selectedText);
      localStorage.setItem('selectedClinicId', selectedId);
      selectedClinicId = selectedId;
      window.location.reload();

      $('.dropdown-trigger').removeClass('border border-primary');
      $('.my-dropdown-menu').addClass('d-none');
      dropdownOpen = false;
    });
  });

  // متغیر جهانی برای clinic_id
  let selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';

  // تابع بارگذاری نمودارها
  function loadCharts() {
    console.log('Loading charts with clinic_id:', selectedClinicId);
    $('#chart-container').html('<div class="loader">در حال بارگذاری...</div>');
    $.ajax({
      url: "{{ route('dr-my-performance-chart-data') }}",
      method: 'GET',
      data: {
        clinic_id: selectedClinicId,
        _t: new Date().getTime()
      },
      success: function(response) {
        console.log('AJAX response:', response);
        $('#chart-container .loader').remove();

        // تبدیل ماه‌ها به فارسی
        const persianMonths = response.appointments?.map(item => {
          const [year, month] = item.month.split('-');
          return moment(`${year}-${month}-01`).locale('fa').format('jYYYY/jMM');
        }) || [];

        // داده‌های پیش‌فرض
        const defaultData = [{
          month: persianMonths[0] || 'ماه قبل',
          scheduled: 0,
          attended: 0,
          missed: 0,
          cancelled: 0,
          paid: 0,
          unpaid: 0,
          count: 0,
          total: 0
        }];

        // تبدیل داده‌ها به فرمت مناسب
        const appointments = response.appointments?.map(item => ({
          month: moment(item.month + '-01').locale('fa').format('jYYYY/jMM'),
          scheduled: item.scheduled || 0,
          attended: item.attended || 0,
          missed: item.missed || 0,
          cancelled: item.cancelled || 0
        })) || defaultData;

        const monthlyIncome = response.monthlyIncome?.map(item => ({
          month: moment(item.month + '-01').locale('fa').format('jYYYY/jMM'),
          paid: item.paid || 0,
          unpaid: item.unpaid || 0
        })) || defaultData;

        const newPatients = response.newPatients?.map(item => ({
          month: moment(item.month + '-01').locale('fa').format('jYYYY/jMM'),
          count: item.count || 0
        })) || defaultData;

        const counselingAppointments = response.counselingAppointments?.map(item => ({
          month: moment(item.month + '-01').locale('fa').format('jYYYY/jMM'),
          scheduled: item.scheduled || 0,
          attended: item.attended || 0,
          missed: item.missed || 0,
          cancelled: item.cancelled || 0
        })) || defaultData;

        const manualAppointments = response.manualAppointments?.map(item => ({
          month: moment(item.month + '-01').locale('fa').format('jYYYY/jMM'),
          scheduled: item.scheduled || 0,
          confirmed: item.confirmed || 0
        })) || defaultData;

        const totalIncome = response.totalIncome?.map(item => ({
          month: moment(item.month + '-01').locale('fa').format('jYYYY/jMM'),
          total: item.total || 0
        })) || defaultData;

        // رندر نمودارها
        renderPerformanceChart(appointments);
        renderIncomeChart(monthlyIncome);
        renderPatientChart(newPatients);
        renderStatusChart(appointments);
        renderStatusPieChart(appointments);
        renderPatientTrendChart(newPatients);
        renderCounselingChart(counselingAppointments);
        renderManualChart(manualAppointments);
        renderTotalIncomeChart(totalIncome);

        $('#chart-container').hide().show();
      },
      error: function(xhr, status, error) {
        console.error('AJAX error:', status, error);
        $('#chart-container .loader').remove();
        $('#chart-container').html('<p>خطا در دریافت اطلاعات</p>');
        toastr.error('خطا در دریافت اطلاعات نمودارها');
      }
    });
  }

  // 📊 نمودار تعداد ویزیت‌ها - نمودار میله‌ای برای مقایسه بهتر
  function renderPerformanceChart(data) {
    let ctx = document.getElementById('doctor-performance-chart').getContext('2d');
    if (window.performanceChart) {
      window.performanceChart.destroy();
    }
    if (!data || data.length === 0) {
      ctx.canvas.parentNode.innerHTML = '<p>داده‌ای برای نمایش وجود ندارد</p>';
      return;
    }
    let labels = data.map(item => item.month);
    window.performanceChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
            label: 'ویزیت شده',
            data: data.map(item => item.scheduled || 0),
            backgroundColor: '#2e86c1',
            borderColor: '#2e86c1',
            borderWidth: 1
          },
          {
            label: 'انجام‌شده',
            data: data.map(item => item.attended || 0),
            backgroundColor: '#34d399',
            borderColor: '#34d399',
            borderWidth: 1
          },
          {
            label: 'غیبت',
            data: data.map(item => item.missed || 0),
            backgroundColor: '#f87171',
            borderColor: '#f87171',
            borderWidth: 1
          },
          {
            label: 'لغو‌شده',
            data: data.map(item => item.cancelled || 0),
            backgroundColor: '#fbbf24',
            borderColor: '#fbbf24',
            borderWidth: 1
          }
        ]
      },
      options: {
        ...commonOptions,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
              font: {
                size: 10
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 10
              },
              maxRotation: 0,
              minRotation: 0
            }
          }
        }
      }
    });
  }

  // 💰 نمودار درآمد ماهانه - نمودار خطی برای نمایش روند
  function renderIncomeChart(data) {
    let ctx = document.getElementById('doctor-income-chart').getContext('2d');
    if (window.incomeChart) {
      window.incomeChart.destroy();
    }
    if (!data || data.length === 0) {
      ctx.canvas.parentNode.innerHTML = '<p>داده‌ای برای نمایش وجود ندارد</p>';
      return;
    }
    let labels = data.map(item => item.month);
    window.incomeChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
            label: 'پرداخت‌شده',
            data: data.map(item => item.paid || 0),
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.2)',
            fill: true,
            tension: 0.4
          },
          {
            label: 'پرداخت‌نشده',
            data: data.map(item => item.unpaid || 0),
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.2)',
            fill: true,
            tension: 0.4
          }
        ]
      },
      options: {
        ...commonOptions,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
              font: {
                size: 10
              },
              callback: function(value) {
                return value.toLocaleString() + ' تومان';
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 10
              },
              maxRotation: 0,
              minRotation: 0
            }
          }
        }
      }
    });
  }

  // 👨‍⚕️ نمودار تعداد بیماران جدید - نمودار خطی برای نمایش روند
  function renderPatientChart(data) {
    let ctx = document.getElementById('doctor-patient-chart').getContext('2d');
    if (window.patientChart) {
      window.patientChart.destroy();
    }
    if (!data || data.length === 0) {
      ctx.canvas.parentNode.innerHTML = '<p>داده‌ای برای نمایش وجود ندارد</p>';
      return;
    }
    let labels = data.map(item => item.month);
    window.patientChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'بیماران جدید',
          data: data.map(item => item.count || 0),
          borderColor: '#f59e0b',
          backgroundColor: 'rgba(245, 158, 11, 0.2)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        ...commonOptions,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
              font: {
                size: 10
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 10
              },
              maxRotation: 0,
              minRotation: 0
            }
          }
        }
      }
    });
  }

  // 📈 نمودار وضعیت نوبت‌ها - نمودار میله‌ای گروهی
  function renderStatusChart(data) {
    let ctx = document.getElementById('doctor-status-chart').getContext('2d');
    if (window.statusChart) {
      window.statusChart.destroy();
    }
    if (!data || data.length === 0) {
      ctx.canvas.parentNode.innerHTML = '<p>داده‌ای برای نمایش وجود ندارد</p>';
      return;
    }
    let labels = data.map(item => item.month);
    window.statusChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
            label: 'ویزیت شده',
            data: data.map(item => item.scheduled || 0),
            backgroundColor: '#2e86c1',
            borderColor: '#2e86c1',
            borderWidth: 1
          },
          {
            label: 'انجام‌شده',
            data: data.map(item => item.attended || 0),
            backgroundColor: '#34d399',
            borderColor: '#34d399',
            borderWidth: 1
          },
          {
            label: 'غیبت',
            data: data.map(item => item.missed || 0),
            backgroundColor: '#f87171',
            borderColor: '#f87171',
            borderWidth: 1
          },
          {
            label: 'لغو‌شده',
            data: data.map(item => item.cancelled || 0),
            backgroundColor: '#fbbf24',
            borderColor: '#fbbf24',
            borderWidth: 1
          }
        ]
      },
      options: {
        ...commonOptions,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
              font: {
                size: 10
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 10
              },
              maxRotation: 0,
              minRotation: 0
            }
          }
        }
      }
    });
  }

  // 🥧 نمودار درصد نوبت‌ها - نمودار دایره‌ای
  function renderStatusPieChart(data) {
    let ctx = document.getElementById('doctor-status-pie-chart').getContext('2d');
    if (window.statusPieChart) {
      window.statusPieChart.destroy();
    }
    if (!data || data.length === 0) {
      ctx.canvas.parentNode.innerHTML = '<p>داده‌ای برای نمایش وجود ندارد</p>';
      return;
    }

    const lastMonth = data[data.length - 1];
    const total = (lastMonth.scheduled || 0) + (lastMonth.attended || 0) +
      (lastMonth.missed || 0) + (lastMonth.cancelled || 0);

    window.statusPieChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ['ویزیت شده', 'انجام‌شده', 'غیبت', 'لغو‌شده'],
        datasets: [{
          data: [
            lastMonth.scheduled || 0,
            lastMonth.attended || 0,
            lastMonth.missed || 0,
            lastMonth.cancelled || 0
          ],
          backgroundColor: [
            '#2e86c1',
            '#34d399',
            '#f87171',
            '#fbbf24'
          ],
          borderColor: '#ffffff',
          borderWidth: 2
        }]
      },
      options: {
        ...commonOptions,
        plugins: {
          ...commonOptions.plugins,
          tooltip: {
            ...commonOptions.plugins.tooltip,
            callbacks: {
              label: function(context) {
                const value = context.raw;
                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                return `${context.label}: ${value} (${percentage}%)`;
              }
            }
          }
        }
      }
    });
  }

  // 📉 نمودار روند بیماران - نمودار خطی با ناحیه
  function renderPatientTrendChart(data) {
    let ctx = document.getElementById('doctor-patient-trend-chart').getContext('2d');
    if (window.patientTrendChart) {
      window.patientTrendChart.destroy();
    }
    if (!data || data.length === 0) {
      ctx.canvas.parentNode.innerHTML = '<p>داده‌ای برای نمایش وجود ندارد</p>';
      return;
    }
    let labels = data.map(item => item.month);
    window.patientTrendChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'بیماران جدید',
          data: data.map(item => item.count || 0),
          borderColor: '#f97316',
          backgroundColor: 'rgba(249, 115, 22, 0.2)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        ...commonOptions,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
              font: {
                size: 10
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 10
              },
              maxRotation: 0,
              minRotation: 0
            }
          }
        }
      }
    });
  }

  // 🗣️ نمودار نوبت‌های مشاوره - نمودار میله‌ای گروهی
  function renderCounselingChart(data) {
    let ctx = document.getElementById('doctor-counseling-chart').getContext('2d');
    if (window.counselingChart) {
      window.counselingChart.destroy();
    }
    if (!data || data.length === 0) {
      ctx.canvas.parentNode.innerHTML = '<p>داده‌ای برای نمایش وجود ندارد</p>';
      return;
    }
    let labels = data.map(item => item.month);
    window.counselingChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
            label: 'ویزیت شده',
            data: data.map(item => item.scheduled || 0),
            backgroundColor: '#8b5cf6',
            borderColor: '#8b5cf6',
            borderWidth: 1
          },
          {
            label: 'انجام‌شده',
            data: data.map(item => item.attended || 0),
            backgroundColor: '#3b82f6',
            borderColor: '#3b82f6',
            borderWidth: 1
          },
          {
            label: 'غیبت',
            data: data.map(item => item.missed || 0),
            backgroundColor: '#ec4899',
            borderColor: '#ec4899',
            borderWidth: 1
          },
          {
            label: 'لغو‌شده',
            data: data.map(item => item.cancelled || 0),
            backgroundColor: '#facc15',
            borderColor: '#facc15',
            borderWidth: 1
          }
        ]
      },
      options: {
        ...commonOptions,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
              font: {
                size: 10
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 10
              },
              maxRotation: 0,
              minRotation: 0
            }
          }
        }
      }
    });
  }

  // ✍️ نمودار نوبت‌های دستی - نمودار میله‌ای گروهی
  function renderManualChart(data) {
    let ctx = document.getElementById('doctor-manual-chart').getContext('2d');
    if (window.manualChart) {
      window.manualChart.destroy();
    }
    if (!data || data.length === 0) {
      ctx.canvas.parentNode.innerHTML = '<p>داده‌ای برای نمایش وجود ندارد</p>';
      return;
    }
    let labels = data.map(item => item.month);
    window.manualChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
            label: 'ویزیت شده',
            data: data.map(item => item.scheduled || 0),
            backgroundColor: '#14b8a6',
            borderColor: '#14b8a6',
            borderWidth: 1
          },
          {
            label: 'تأیید‌شده',
            data: data.map(item => item.confirmed || 0),
            backgroundColor: '#6366f1',
            borderColor: '#6366f1',
            borderWidth: 1
          }
        ]
      },
      options: {
        ...commonOptions,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
              font: {
                size: 10
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 10
              },
              maxRotation: 0,
              minRotation: 0
            }
          }
        }
      }
    });
  }

  // 💸 نمودار درآمد کلی - نمودار خطی با ناحیه
  function renderTotalIncomeChart(data) {
    let ctx = document.getElementById('doctor-total-income-chart').getContext('2d');
    if (window.totalIncomeChart) {
      window.totalIncomeChart.destroy();
    }
    if (!data || data.length === 0) {
      ctx.canvas.parentNode.innerHTML = '<p>داده‌ای برای نمایش وجود ندارد</p>';
      return;
    }
    let labels = data.map(item => item.month);
    window.totalIncomeChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'درآمد کلی',
          data: data.map(item => item.total || 0),
          borderColor: '#d946ef',
          backgroundColor: 'rgba(217, 70, 239, 0.2)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        ...commonOptions,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
              font: {
                size: 10
              },
              callback: function(value) {
                return value.toLocaleString() + ' تومان';
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 10
              },
              maxRotation: 0,
              minRotation: 0
            }
          }
        }
      }
    });
  }

  // تنظیمات مشترک برای نمودارها
  const commonOptions = {
    responsive: true,
    maintainAspectRatio: true,
    aspectRatio: 2.5, // افزایش نسبت عرض به ارتفاع برای نمودارهای کوتاه‌تر
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          font: {
            family: 'IRANSans',
            size: 12,
            weight: '500'
          },
          padding: 12,
          color: '#1e293b',
          boxWidth: 12,
          usePointStyle: true
        }
      },
      tooltip: {
        enabled: true,
        backgroundColor: 'rgba(30, 41, 59, 0.9)',
        titleFont: {
          family: 'IRANSans',
          size: 13
        },
        bodyFont: {
          family: 'IRANSans',
          size: 11
        },
        padding: 10,
        cornerRadius: 8,
        borderColor: 'rgba(255, 255, 255, 0.2)',
        borderWidth: 1
      }
    },
    animation: {
      duration: 1200,
      easing: 'easeOutQuart',
      delay: 200
    },
    hover: {
      mode: 'nearest',
      intersect: true,
      animationDuration: 400
    },
    layout: {
      padding: {
        top: 20,
        right: 20,
        bottom: 20,
        left: 20
      }
    }
  };

  // بارگذاری اولیه نمودارها
  document.addEventListener("DOMContentLoaded", function() {
    loadCharts();
  });
</script>
