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
      setTimeout(() => {
        // اگر داده‌ها کم باشن، داده‌های پیش‌فرض اضافه می‌کنیم
        const defaultData = [
          { month: 'ماه قبل', scheduled_count: 0, attended_count: 0, missed_count: 0, cancelled_count: 0, total_paid_income: 0, total_unpaid_income: 0, total_patients: 0 },
          { month: 'این ماه', scheduled_count: 0, attended_count: 0, missed_count: 0, cancelled_count: 0, total_paid_income: 0, total_unpaid_income: 0, total_patients: 0 }
        ];
        const appointments = response.appointments?.length > 1 ? response.appointments : defaultData;
        const monthlyIncome = response.monthlyIncome?.length > 1 ? response.monthlyIncome : defaultData;
        const newPatients = response.newPatients?.length > 1 ? response.newPatients : defaultData;
        const appointmentStatusByMonth = response.appointmentStatusByMonth?.length > 1 ? response.appointmentStatusByMonth : defaultData;

        renderPerformanceChart(appointments);
        renderIncomeChart(monthlyIncome);
        renderPatientChart(newPatients);
        renderStatusChart(appointmentStatusByMonth);
        renderStatusPieChart(appointmentStatusByMonth);
        renderPatientTrendChart(newPatients);
        $('#chart-container').hide().show();
      }, 0);
    },
    error: function(xhr, status, error) {
      console.error('AJAX error:', status, error);
      $('#chart-container .loader').remove();
      $('#chart-container').html('<p>خطا در دریافت اطلاعات</p>');
      toastr.error('خطا در دریافت اطلاعات نمودارها');
    }
  });
}

// 📊 نمودار تعداد ویزیت‌ها
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
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'ویزیت شده',
          data: data.map(item => item.scheduled_count || 0),
          borderColor: '#60a5fa',
          backgroundColor: 'rgba(96, 165, 250, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        },
        {
          label: 'انجام‌شده',
          data: data.map(item => item.attended_count || 0),
          borderColor: '#34d399',
          backgroundColor: 'rgba(52, 211, 153, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        },
        {
          label: 'غیبت',
          data: data.map(item => item.missed_count || 0),
          borderColor: '#f87171',
          backgroundColor: 'rgba(248, 113, 113, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        },
        {
          label: 'لغو‌شده',
          data: data.map(item => item.cancelled_count || 0),
          borderColor: '#fbbf24',
          backgroundColor: 'rgba(251, 191, 36, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        }
      ]
    },
    options: {
      ...commonOptions,
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' },
          ticks: { font: { size: 10 } }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 10 }, maxRotation: 0, minRotation: 0 },
          type: 'category', // اطمینان از نوع محور
          labels: labels.length === 1 ? [labels[0], ''] : labels // افزودن برچسب خالی برای داده‌های تکی
        }
      }
    }
  });
}

// 💰 نمودار درآمد ماهانه
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
      datasets: [
        {
          label: 'پرداخت‌شده',
          data: data.map(item => item.total_paid_income || 0),
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        },
        {
          label: 'پرداخت‌نشده',
          data: data.map(item => item.total_unpaid_income || 0),
          borderColor: '#ef4444',
          backgroundColor: 'rgba(239, 68, 68, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        }
      ]
    },
    options: {
      ...commonOptions,
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' },
          ticks: { font: { size: 10 } }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 10 }, maxRotation: 0, minRotation: 0 },
          type: 'category',
          labels: labels.length === 1 ? [labels[0], ''] : labels
        }
      }
    }
  });
}

// 👨‍⚕️ نمودار تعداد بیماران جدید
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
      datasets: [
        {
          label: 'بیماران جدید',
          data: data.map(item => item.total_patients || 0),
          borderColor: '#f59e0b',
          backgroundColor: 'rgba(245, 158, 11, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        }
      ]
    },
    options: {
      ...commonOptions,
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' },
          ticks: { font: { size: 10 } }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 10 }, maxRotation: 0, minRotation: 0 },
          type: 'category',
          labels: labels.length === 1 ? [labels[0], ''] : labels
        }
      }
    }
  });
}

// 📈 نمودار وضعیت نوبت‌ها
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
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'ویزیت شده',
          data: data.map(item => item.scheduled_count || 0),
          borderColor: '#60a5fa',
          backgroundColor: 'rgba(96, 165, 250, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        },
        {
          label: 'انجام‌شده',
          data: data.map(item => item.attended_count || 0),
          borderColor: '#34d399',
          backgroundColor: 'rgba(52, 211, 153, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        },
        {
          label: 'غیبت',
          data: data.map(item => item.missed_count || 0),
          borderColor: '#f87171',
          backgroundColor: 'rgba(248, 113, 113, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        },
        {
          label: 'لغو‌شده',
          data: data.map(item => item.cancelled_count || 0),
          borderColor: '#fbbf24',
          backgroundColor: 'rgba(251, 191, 36, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        }
      ]
    },
    options: {
      ...commonOptions,
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' },
          ticks: { font: { size: 10 } }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 10 }, maxRotation: 0, minRotation: 0 },
          type: 'category',
          labels: labels.length === 1 ? [labels[0], ''] : labels
        }
      }
    }
  });
}

// 🥧 نمودار درصد نوبت‌ها (اکنون خطی)
function renderStatusPieChart(data) {
  let ctx = document.getElementById('doctor-status-pie-chart').getContext('2d');
  if (window.statusPieChart) {
    window.statusPieChart.destroy();
  }
  if (!data || data.length === 0) {
    ctx.canvas.parentNode.innerHTML = '<p>داده‌ای برای نمایش وجود ندارد</p>';
    return;
  }
  let labels = data.map(item => item.month);
  window.statusPieChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'ویزیت شده',
          data: data.map(item => item.scheduled_count || 0),
          borderColor: '#60a5fa',
          backgroundColor: 'rgba(96, 165, 250, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        },
        {
          label: 'انجام‌شده',
          data: data.map(item => item.attended_count || 0),
          borderColor: '#34d399',
          backgroundColor: 'rgba(52, 211, 153, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        },
        {
          label: 'غیبت',
          data: data.map(item => item.missed_count || 0),
          borderColor: '#f87171',
          backgroundColor: 'rgba(248, 113, 113, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        },
        {
          label: 'لغو‌شده',
          data: data.map(item => item.cancelled_count || 0),
          borderColor: '#fbbf24',
          backgroundColor: 'rgba(251, 191, 36, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        }
      ]
    },
    options: {
      ...commonOptions,
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' },
          ticks: { font: { size: 10 } }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 10 }, maxRotation: 0, minRotation: 0 },
          type: 'category',
          labels: labels.length === 1 ? [labels[0], ''] : labels
        }
      }
    }
  });
}

// 📉 نمودار روند بیماران جدید
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
      datasets: [
        {
          label: 'بیماران جدید',
          data: data.map(item => item.total_patients || 0),
          borderColor: '#f97316',
          backgroundColor: 'rgba(249, 115, 22, 0.2)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        }
      ]
    },
    options: {
      ...commonOptions,
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' },
          ticks: { font: { size: 10 } }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 10 }, maxRotation: 0, minRotation: 0 },
          type: 'category',
          labels: labels.length === 1 ? [labels[0], ''] : labels
        }
      }
    }
  });
}

// تنظیمات مشترک برای نمودارها
const commonOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: {
        font: { family: 'IRANSans', size: 11, weight: '500' },
        padding: 10,
        color: '#2d3748'
      }
    },
    tooltip: {
      enabled: true,
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleFont: { family: 'IRANSans', size: 12 },
      bodyFont: { family: 'IRANSans', size: 10 },
      padding: 8,
      cornerRadius: 6
    }
  },
  animation: {
    duration: 1000,
    easing: 'easeOutQuart'
  }
};

// بارگذاری اولیه نمودارها
document.addEventListener("DOMContentLoaded", function() {
  loadCharts();
});
</script>
