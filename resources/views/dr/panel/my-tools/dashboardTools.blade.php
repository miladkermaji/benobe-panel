<script>
  /* drop select option */
  $(document).ready(function() {
    let dropdownOpen = false;
    let selectedClinic = localStorage.getItem('selectedClinic');
    let selectedClinicId = localStorage.getItem('selectedClinicId');
    if (selectedClinic && selectedClinicId) {
      $('.dropdown-label').text(selectedClinic);
      $('.option-card').each(function() {
        if ($(this).attr('data-id') === selectedClinicId) {
          $('.option-card').removeClass('card-active');
          $(this).addClass('card-active');
        }
      });
    } else {
      localStorage.setItem('selectedClinic', 'ویزیت آنلاین به نوبه');
      localStorage.setItem('selectedClinicId', 'default');
    }

    function checkInactiveClinics() {
      var hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
      if (hasInactiveClinics) {
        $('.dropdown-trigger').addClass('warning');
      } else {
        $('.dropdown-trigger').removeClass('warning');
      }
    }
    checkInactiveClinics();
    $('.dropdown-trigger').on('click', function(event) {
      event.stopPropagation();
      dropdownOpen = !dropdownOpen;
      $(this).toggleClass('border border-primary');
      $('.my-dropdown-menu').toggleClass('d-none');
      setTimeout(() => {
        dropdownOpen = $('.my-dropdown-menu').is(':visible');
      }, 100);
    });
    $(document).on('click', function() {
      if (dropdownOpen) {
        $('.dropdown-trigger').removeClass('border border-primary');
        $('.my-dropdown-menu').addClass('d-none');
        dropdownOpen = false;
      }
    });
    $('.my-dropdown-menu').on('click', function(event) {
      event.stopPropagation();
    });
    $('.option-card').on('click', function() {
      let currentDate = moment().format('YYYY-MM-DD');
      let persianDate = moment(currentDate, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');
      var selectedText = $(this).find('.font-weight-bold.d-block.fs-15').text().trim();
      var selectedId = $(this).attr('data-id');
      $('.option-card').removeClass('card-active');
      $(this).addClass('card-active');
      $('.dropdown-label').text(selectedText);
      localStorage.setItem('selectedClinic', selectedText);
      localStorage.setItem('selectedClinicId', selectedId);
      checkInactiveClinics();
      handleDateSelection(persianDate, selectedId);
      loadAppointments(persianDate, selectedId);
      fetchAppointmentsCount()
      $('.dropdown-trigger').removeClass('border border-primary');
      $('.my-dropdown-menu').addClass('d-none');
      dropdownOpen = false;
    });
  });
  $(document).ready(function() {
    let currentDate = moment().format('YYYY-MM-DD');
    let persianDate = moment(currentDate, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');
    let selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';
    $('.selectDate_datepicker__xkZeS span.mx-1').text(persianDate);
  });
  /* drop select option */
  const appointmentsTableBody = $('.table tbody');
  let loadingIndicator = `<tr id="loading-row" class="w-100">
    <td colspan="12" class="text-center py-4">
        <div class="spinner-custom" role="status">
            <span class="visually-hidden">در حال بارگذاری...</span>
        </div>
    </td>
</tr>`;
  document.addEventListener("DOMContentLoaded", function() {
    let selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';

    function loadCharts() {
      $.ajax({
        url: "{{ route('dr-my-performance-chart-data') }}",
        method: 'GET',
        data: {
          clinic_id: selectedClinicId
        },
        success: function(response) {
          renderPerformanceChart(response.appointments);
          renderIncomeChart(response.monthlyIncome);
          renderPatientChart(response.newPatients);
          renderStatusChart(response.appointmentStatusByMonth);
          renderStatusPieChart(response.appointmentStatusByMonth);
          renderPatientTrendChart(response.newPatients);
        },
        error: function() {
          toastr.error('خطا در دریافت اطلاعات نمودارها');
        }
      });
    }

    const commonOptions = {
      responsive: true,
      maintainAspectRatio: false,
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

    // 📊 نمودار تعداد ویزیت‌ها
    function renderPerformanceChart(data) {
      let ctx = document.getElementById('doctor-performance-chart').getContext('2d');
      if (window.performanceChart) window.performanceChart.destroy();

      let labels = data.map(item => item.month);
      window.performanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
              label: 'برنامه‌ریزی‌شده',
              data: data.map(item => item.scheduled_count),
              backgroundColor: '#60a5fa',
              borderRadius: 6
            },
            {
              label: 'انجام‌شده',
              data: data.map(item => item.attended_count),
              backgroundColor: '#34d399',
              borderRadius: 6
            },
            {
              label: 'غیبت',
              data: data.map(item => item.missed_count),
              backgroundColor: '#f87171',
              borderRadius: 6
            },
            {
              label: 'لغو‌شده',
              data: data.map(item => item.cancelled_count),
              backgroundColor: '#fbbf24',
              borderRadius: 6
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
    }

    // 💰 نمودار درآمد ماهانه
    function renderIncomeChart(data) {
      let ctx = document.getElementById('doctor-income-chart').getContext('2d');
      if (window.incomeChart) window.incomeChart.destroy();

      let labels = data.map(item => item.month);
      window.incomeChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
              label: 'پرداخت‌شده',
              data: data.map(item => item.total_paid_income),
              backgroundColor: '#10b981',
              borderRadius: 6
            },
            {
              label: 'پرداخت‌نشده',
              data: data.map(item => item.total_unpaid_income),
              backgroundColor: '#ef4444',
              borderRadius: 6
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
    }

    // 👨‍⚕️ نمودار تعداد بیماران جدید
    function renderPatientChart(data) {
      let ctx = document.getElementById('doctor-patient-chart').getContext('2d');
      if (window.patientChart) window.patientChart.destroy();

      let labels = data.map(item => item.month);
      window.patientChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'بیماران جدید',
            data: data.map(item => item.total_patients),
            backgroundColor: '#f59e0b',
            borderRadius: 6
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
          }
        }
      });
    }

    // 📈 نمودار وضعیت نوبت‌ها
    function renderStatusChart(data) {
      let ctx = document.getElementById('doctor-status-chart').getContext('2d');
      if (window.statusChart) window.statusChart.destroy();

      let labels = data.map(item => item.month);
      window.statusChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
              label: 'برنامه‌ریزی‌شده',
              data: data.map(item => item.scheduled_count),
              backgroundColor: '#60a5fa',
              borderRadius: 6
            },
            {
              label: 'انجام‌شده',
              data: data.map(item => item.attended_count),
              backgroundColor: '#34d399',
              borderRadius: 6
            },
            {
              label: 'غیبت',
              data: data.map(item => item.missed_count),
              backgroundColor: '#f87171',
              borderRadius: 6
            },
            {
              label: 'لغو‌شده',
              data: data.map(item => item.cancelled_count),
              backgroundColor: '#fbbf24',
              borderRadius: 6
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
    }

    // 🥧 نمودار درصد وضعیت نوبت‌ها (Pie Chart)
    function renderStatusPieChart(data) {
      let ctx = document.getElementById('doctor-status-pie-chart').getContext('2d');
      if (window.statusPieChart) window.statusPieChart.destroy();

      let totalScheduled = data.reduce((sum, item) => sum + item.scheduled_count, 0);
      let totalAttended = data.reduce((sum, item) => sum + item.attended_count, 0);
      let totalMissed = data.reduce((sum, item) => sum + item.missed_count, 0);
      let totalCancelled = data.reduce((sum, item) => sum + item.cancelled_count, 0);

      window.statusPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: ['برنامه‌ریزی‌شده', 'انجام‌شده', 'غیبت', 'لغو‌شده'],
          datasets: [{
            data: [totalScheduled, totalAttended, totalMissed, totalCancelled],
            backgroundColor: ['#60a5fa', '#34d399', '#f87171', '#fbbf24'],
            borderWidth: 2,
            borderColor: '#ffffff'
          }]
        },
        options: {
          ...commonOptions,
          plugins: {
            ...commonOptions.plugins,
            legend: {
              position: 'bottom'
            }
          }
        }
      });
    }

    // 📉 نمودار روند بیماران جدید (Line Chart)
    function renderPatientTrendChart(data) {
      let ctx = document.getElementById('doctor-patient-trend-chart').getContext('2d');
      if (window.patientTrendChart) window.patientTrendChart.destroy();

      let labels = data.map(item => item.month);
      window.patientTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'بیماران جدید',
            data: data.map(item => item.total_patients),
            borderColor: '#f97316',
            backgroundColor: 'rgba(249, 115, 22, 0.2)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#f97316',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
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
          }
        }
      });
    }

    loadCharts();
  });
</script>
