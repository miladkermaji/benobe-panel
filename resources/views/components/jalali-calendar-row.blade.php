<style>
  :root {
    --background-card: #ffffff;
    --border-neutral: #e5e7eb;
    --shadow: rgba(0, 0, 0, 0.1);
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --primary: #2e86c1;
    --primary-light: #84caf9;
    --secondary: #1deb3c;
    --secondary-hover: #16a34a;
    --background-light: #f9fafb;
    --support-text: #2e86c1;
    --radius-card: 0.5rem;
    --radius-button: 6px;
    --radius-circle: 50%;
  }

  .calendar-card {
    background: var(--background-card);
    padding: 10px;
    min-width: 90px;
    text-align: center;
    border: 1px solid var(--border-neutral);
    border-radius: var(--radius-card);
    box-shadow: 0 1px 4px var(--shadow);
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    cursor: pointer;
    position: relative;
    animation: slideUp 0.3s ease-out forwards;
    animation-delay: calc(var(--delay) * 0.05s);
    box-sizing: border-box;
    flex-shrink: 0;
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .calendar-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 8px var(--shadow);
    background: var(--background-light);
  }

  .calendar-card:active {
    transform: scale(0.98);
    box-shadow: 0 1px 4px var(--shadow);
  }

  .calendar-card .day-name {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.2px;
  }

  .calendar-card .date {
    font-size: 10px;
    font-weight: 500;
    color: var(--text-secondary);
  }

  .calendar-card .current-day-icon {
    position: absolute;
    bottom: -6px;
    left: 50%;
    transform: translateX(-50%);
    width: 8px;
    height: 8px;
    background: var(--secondary);
    border-radius: var(--radius-circle);
    box-shadow: 0 0 4px rgba(29, 235, 60, 0.5);
  }


  .calendar-card .appointment-badge {
    position: absolute;
    top: -8px;
    left: -8px;
    background: var(--primary);
    color: var(--background-card);
    font-size: 11px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: var(--radius-button);
    box-shadow: 0 1px 2px var(--shadow);
    transition: transform 0.15s ease, background 0.15s ease;
    animation: badgePulse 0.4s ease-out;
    z-index: 200;
    display: inline-block;
  }

  @keyframes badgePulse {
    0% {
      transform: scale(0.8);
      opacity: 0;
    }

    70% {
      transform: scale(1.1);
    }

    100% {
      transform: scale(1);
      opacity: 1;
    }
  }

  .calendar-card .appointment-badge:hover {
    transform: scale(1.05);
    background: var(--primary-light);
  }

  .my-active {
    border: 2px solid var(--secondary);
    background: var(--background-light);
    box-shadow: 0 2px 8px rgba(29, 235, 60, 0.2);
  }

  .my-active .day-name,
  .my-active .date {
    color: var(--secondary-hover);
  }

  .card-selected {
    border: 2px solid var(--primary);
    background: var(--background-light);
    box-shadow: 0 2px 8px rgba(46, 134, 193, 0.2);
  }

  .card-selected .day-name,
  .card-selected .date {
    color: var(--support-text);
  }

  #calendar.d-flex.w-100 {
    overflow-x: scroll;
    overflow-y: hidden;
    white-space: nowrap;
    width: 100%;
    padding: 15px 0;
    /* پدینگ کم برای بج‌ها */
    display: flex;
    gap: 10px !important;
    transition: transform 0.4s ease-in-out;
    box-sizing: border-box;
    margin: 10px;
    /* حذف حاشیه‌های احتمالی */
  }

  #calendar.d-flex.w-100::-webkit-scrollbar {
    display: none;
  }

  .btn-light {
    background: var(--background-card);
    border: 1px solid var(--border-neutral);
    /*  padding: 13px 0; */
    border-radius: var(--radius-button);
    transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
  }

  .btn-light:hover {
    background: var(--background-light);
    border-color: var(--primary-light);
    box-shadow: 0 1px 4px var(--shadow);
  }

  .btn-light:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .btn-light svg {
    vertical-align: middle;
    stroke: var(--text-primary);
    transition: stroke 0.15s ease;
  }

  .btn-light:hover:not(:disabled) svg {
    stroke: var(--primary);
  }

  .error-message {
    display: none;
    color: #e63946;
    font-size: 12px;
    text-align: center;
    margin-top: 8px;
  }

  @media (max-width: 576px) {
    .calendar-card {
      min-width: 80px;
      padding: 8px;
      margin-right: -3px;
    }

    .calendar-card .day-name {
      font-size: 11px;
    }

    .calendar-card .date {
      font-size: 9px;
    }

    .calendar-card .appointment-badge {
      font-size: 10px;
      padding: 2px 5px;
      top: -6px;
      left: -6px;
    }

  }
</style>

<div class="w-100 d-flex justify-content-around align-items-center" style="margin: 0; padding: 0;">
  <div class="w-100 d-flex align-items-center gap-2">
    <div>
      <button id="prevRow" class="btn btn-light">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <g id="Arrow / Chevron_Right_MD">
            <path id="Vector" d="M10 8L14 12L10 16" stroke="#000000" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </g>
        </svg>
      </button>
    </div>
    <div id="calendar" class="d-flex w-100">
      <!-- تقویم اولیه با تاریخ کنونی پر می‌شود -->
    </div>
    <div>
      <button id="nextRow" class="btn btn-light">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <g id="Arrow / Chevron_Left_MD">
            <path id="Vector" d="M14 16L10 12L14 8" stroke="#000000" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </g>
        </svg>
      </button>
    </div>
  </div>
  <div id="calendar-error" class="error-message">
    خطا در بارگذاری تعداد نوبت‌ها. لطفاً دوباره تلاش کنید.
  </div>
</div>
<script>
$(document).ready(function() {
    // تنظیم تاریخ شروع برای قرار گرفتن امروز در وسط
    let currentDate = moment().locale('en').subtract(8, 'days').format('YYYY-MM-DD'); // 8 روز قبل از امروز
    const days = 18; // بدون تغییر
    const calendar = $('#calendar');
    let isAnimating = false;
    const minDate = moment().locale('en').subtract(30, 'days').format('YYYY-MM-DD');
    const maxDate = moment().locale('en').add(30, 'days').format('YYYY-MM-DD');
    let appointmentsData = [];

    function fetchAppointmentsCount() {
      $.ajax({
        url: "{{ route('appointments.count') }}",
        method: 'GET',
        data: {
          selectedClinicId: localStorage.getItem('selectedClinicId')
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          if (response.status) {
            appointmentsData = response.data;
            $('#calendar-error').hide();
            loadCalendar(currentDate);
          } else {
            console.error('Error fetching appointments:', response.message);
            $('#calendar-error').show();
          }
        },
        error: function(xhr, status, error) {
          console.error('AJAX error:', error);
          $('#calendar-error').show();
        }
      });
    }

    function loadCalendar(date) {
      calendar.empty();
      let badgeCount = 0;

      for (let i = 0; i < days; i++) {
        const current = moment(date).locale('en').add(i, 'days');
        const persianDate = current.locale('fa').format('dddd');
        const persianFormattedDate = current.locale('fa').format('D MMMM YYYY');
        const isActive = current.isSame(moment().locale('en'), 'day') ? 'my-active' : '';
        const appointmentDate = current.locale('en').format('YYYY-MM-DD');

        const appointment = appointmentsData.find(appt => {
          const apptDate = moment(appt.appointment_date).locale('en').format('YYYY-MM-DD');
          return apptDate === appointmentDate;
        });

        const appointmentCount = appointment ? appointment.appointment_count : 0;
        const badgeHtml = appointmentCount > 0 ? `<span class="appointment-badge">${appointmentCount}</span>` : '';
        if (appointmentCount > 0) badgeCount++;

        const card = `
        <div class="calendar-card btn btn-light ${isActive}" data-date="${appointmentDate}" style="--delay: ${i}">
          ${badgeHtml}
          <div class="day-name">${persianDate}</div>
          <div class="date">${persianFormattedDate}</div>
          ${isActive ? '<div class="current-day-icon"></div>' : ''}
        </div>`;
        calendar.append(card);
      }

      updateButtonState();
    }

    function updateButtonState() {
      const prevButton = $('#prevRow');
      const nextButton = $('#nextRow');
      const firstDate = moment(currentDate).locale('en');
      const lastDate = moment(currentDate).locale('en').add(days - 1, 'days');

      prevButton.prop('disabled', firstDate.isSameOrBefore(minDate, 'day'));
      nextButton.prop('disabled', lastDate.isSameOrAfter(maxDate, 'day'));
    }

    function animateAndLoadCalendar(direction) {
      if (isAnimating) return;

      isAnimating = true;
      const newDate = direction === 'nextRow' ?
        moment(currentDate).locale('en').add(7, 'days').format('YYYY-MM-DD') : // تغییر به 7 روز
        moment(currentDate).locale('en').subtract(7, 'days').format('YYYY-MM-DD'); // تغییر به 7 روز

      calendar.css({
        transition: 'transform 0.4s ease-in-out, opacity 0.6s ease-in-out',
        transform: direction === 'nextRow' ? 'translateX(50px)' : 'translateX(-50px)',
        opacity: 0.3
      });

      setTimeout(() => {
        currentDate = newDate;
        loadCalendar(currentDate);
        calendar.css({
          transform: direction === 'nextRow' ? 'translateX(-50px)' : 'translateX(50px)',
          opacity: 0.3
        });
        setTimeout(() => {
          calendar.css({
            transform: 'translateX(0)',
            opacity: 1
          });
          setTimeout(() => {
            calendar.css('transition', '');
            isAnimating = false;
          }, 400);
        }, 50);
      }, 300);
    }

    calendar.on('click', '.calendar-card', function() {
      $('.calendar-card').removeClass('card-selected');
      $(this).addClass('card-selected');
    });

    $('#nextRow').click(function() {
      if (!$(this).prop('disabled')) {
        animateAndLoadCalendar('nextRow');
      }
    });

    $('#prevRow').click(function() {
      if (!$(this).prop('disabled')) {
        animateAndLoadCalendar('prevRow');
      }
    });

    fetchAppointmentsCount();
});
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
