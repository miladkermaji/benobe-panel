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
    color: var(--secondary-hover); /* سبز برای روز جاری */
  }

  .card-selected {
    border: 2px solid var(--primary);
    background: var(--background-light);
    box-shadow: 0 2px 8px rgba(46, 134, 193, 0.2);
  }

  .card-selected .day-name,
  .card-selected .date {
    color: var(--support-text); /* پریمری برای کارت‌های کلیک‌شده */
  }

  #calendar.d-flex.w-100 {
    overflow-x: hidden;
    overflow-y: hidden;
    white-space: nowrap;
    width: 100%;
    padding: 15px 0;
    display: flex;
    gap: 10px !important;
    transition: transform 0.3s ease-out;
    box-sizing: border-box;
    margin: 10px;
    cursor: grab;
    user-select: none;
  }

  #calendar.d-flex.w-100.grabbing {
    cursor: grabbing;
  }

  #calendar.d-flex.w-100::-webkit-scrollbar {
    display: none;
  }

  .btn-light {
    background: var(--background-card);
    border: 1px solid var(--border-neutral);
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

  .loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }

  .loading-spinner {
    border: 4px solid var(--border-neutral);
    border-top: 4px solid var(--primary);
    border-radius: 50%;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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

<div class="w-100 d-flex justify-content-around align-items-center" style="margin: 0; padding: 0; position: relative;">
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
    <div id="calendar" class="d-flex w-100" style="display: none;">
      <!-- تقویم اولیه با تاریخ کنونی پر می‌شود -->
    </div>
    <div class="loading-overlay" id="calendar-loading">
      <div class="loading-spinner"></div>
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

<script src="{{ asset('dr-assets/panel/js/jquery-easing/1.4.1/jquery.easing.min.js') }}"></script>
<script>
$(document).ready(function () {
    let currentDate = moment().locale('en').startOf('day');
    const calendar = $('#calendar');
    const loadingOverlay = $('#calendar-loading');
    let isAnimating = false;
    let appointmentsData = [];
    let workingDays = [];
    let calendarDays = 30; // پیش‌فرض
    let appointmentSettings = [];
    const today = moment().locale('en').startOf('day');
    let isDragging = false;
    let startX = 0;
    let scrollLeft = 0;
    let velocity = 0;
    let lastX = 0;
    let lastTime = 0;

    function fetchAppointmentsCount() {
        loadingOverlay.show();
        calendar.hide();
        $.ajax({
            url: "{{ route('appointments.count') }}",
            method: 'GET',
            data: {
                selectedClinicId: localStorage.getItem('selectedClinicId')
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.status) {
                    appointmentsData = response.data;
                    workingDays = response.working_days || [];
                    calendarDays = response.calendar_days || 30;
                    appointmentSettings = response.appointment_settings || [];
                    $('#calendar-error').hide();
                    loadCalendar();
                    loadingOverlay.hide();
                    calendar.show();
                } else {
                    console.error('Error fetching appointments:', response.message);
                    $('#calendar-error').show();
                    loadingOverlay.hide();
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', error);
                $('#calendar-error').show();
                loadingOverlay.hide();
            }
        });
    }

    function loadCalendar() {
        calendar.empty();
        let badgeCount = 0;
        let displayedDays = 0;
        let current = moment(currentDate).locale('en');
        let i = 0;

        // اضافه کردن تاریخ امروز به‌صورت اجباری
        const persianDateToday = today.locale('fa').format('dddd');
        const persianFormattedDateToday = today.locale('fa').format('D MMMM YYYY');
        const appointmentDateToday = today.locale('en').format('YYYY-MM-DD');
        const dayOfWeekToday = today.format('dddd').toLowerCase();

        const appointmentToday = appointmentsData.find(appt => {
            const apptDate = moment(appt.appointment_date).locale('en').format('YYYY-MM-DD');
            return apptDate === appointmentDateToday;
        });

        // تعداد نوبت‌ها برای امروز (اگر روز کاری باشد)
        let appointmentCountToday = 0;
        if (workingDays.includes(dayOfWeekToday) && appointmentToday) {
            // بررسی appointment_settings
            const settingsForDay = appointmentSettings.find(setting => setting.day === dayOfWeekToday);
            let isReservable = true; // پیش‌فرض: قابل رزرو
            if (settingsForDay && settingsForDay.settings.length > 0) {
                isReservable = false; // اگر تنظیمات وجود دارد، بررسی می‌کنیم
                settingsForDay.settings.forEach(setting => {
                    const settingDay = setting.selected_day;
                    const settingStart = moment(setting.start_time, 'HH:mm');
                    const settingEnd = moment(setting.end_time, 'HH:mm');
                    const currentTime = moment();

                    if (currentTime.format('dddd').toLowerCase() === settingDay &&
                        currentTime.isBetween(settingStart, settingEnd)) {
                        isReservable = true;
                    }
                });
            }
            if (isReservable) {
                appointmentCountToday = appointmentToday.appointment_count;
            }
        }

        const badgeHtmlToday = appointmentCountToday > 0 ? `<span class="appointment-badge">${appointmentCountToday}</span>` : '';
        if (appointmentCountToday > 0) badgeCount++;

        const cardToday = `
            <div class="calendar-card btn btn-light my-active" data-date="${appointmentDateToday}" style="--delay: ${displayedDays}">
                ${badgeHtmlToday}
                <div class="day-name">${persianDateToday}</div>
                <div class="date">${persianFormattedDateToday}</div>
                <div class="current-day-icon"></div>
            </div>`;
        calendar.append(cardToday);
        displayedDays++;

        // ادامه برای سایر روزها
        while (displayedDays < calendarDays && i < calendarDays * 2) {
            if (!current.isSame(today, 'day')) { // جلوگیری از تکرار امروز
                const dayOfWeek = current.format('dddd').toLowerCase();
                if (workingDays.includes(dayOfWeek)) {
                    const persianDate = current.locale('fa').format('dddd');
                    const persianFormattedDate = current.locale('fa').format('D MMMM YYYY');
                    const appointmentDate = current.locale('en').format('YYYY-MM-DD');

                    const appointment = appointmentsData.find(appt => {
                        const apptDate = moment(appt.appointment_date).locale('en').format('YYYY-MM-DD');
                        return apptDate === appointmentDate;
                    });

                    // تعداد نوبت‌ها برای روزهای کاری و امروز یا آینده
                    let appointmentCount = 0;
                    if (workingDays.includes(dayOfWeek) && current.isSameOrAfter(today, 'day') && appointment) {
                        const settingsForDay = appointmentSettings.find(setting => setting.day === dayOfWeek);
                        let isReservable = true; // پیش‌فرض: قابل رزرو
                        if (settingsForDay && settingsForDay.settings.length > 0) {
                            isReservable = false; // اگر تنظیمات وجود دارد، بررسی می‌کنیم
                            settingsForDay.settings.forEach(setting => {
                                const settingDay = setting.selected_day;
                                const settingStart = moment(setting.start_time, 'HH:mm');
                                const settingEnd = moment(setting.end_time, 'HH:mm');
                                const currentTime = moment();

                                if (currentTime.format('dddd').toLowerCase() === settingDay &&
                                    currentTime.isBetween(settingStart, settingEnd)) {
                                    isReservable = true;
                                }
                            });
                        }
                        if (isReservable) {
                            appointmentCount = appointment.appointment_count;
                        }
                    }

                    const badgeHtml = appointmentCount > 0 ? `<span class="appointment-badge">${appointmentCount}</span>` : '';
                    if (appointmentCount > 0) badgeCount++;

                    const card = `
                        <div class="calendar-card btn btn-light" data-date="${appointmentDate}" style="--delay: ${displayedDays}">
                            ${badgeHtml}
                            <div class="day-name">${persianDate}</div>
                            <div class="date">${persianFormattedDate}</div>
                        </div>`;
                    calendar.append(card);
                    displayedDays++;
                }
            }
            current.add(1, 'days');
            i++;
        }

        updateButtonState();
    }

    function updateButtonState() {
        const prevButton = $('#prevRow');
        const nextButton = $('#nextRow');
        const firstDate = moment(currentDate).locale('en');
        const lastDate = moment(currentDate).locale('en').add(calendarDays - 1, 'days');

        prevButton.prop('disabled', firstDate.isSameOrBefore(today, 'day'));
        nextButton.prop('disabled', lastDate.isSameOrAfter(moment().add(calendarDays, 'days'), 'day'));
    }

    function animateAndLoadCalendar(direction) {
        if (isAnimating) return;

        isAnimating = true;
        const offset = direction === 'nextRow' ? 7 : -7;
        const newDate = moment(currentDate).locale('en').add(offset, 'days').format('YYYY-MM-DD');

        calendar.css({
            transition: 'transform 0.4s ease-in-out, opacity 0.6s ease-in-out',
            transform: direction === 'nextRow' ? 'translateX(50px)' : 'translateX(-50px)',
            opacity: 0.3
        });

        setTimeout(() => {
            currentDate = newDate;
            loadCalendar();
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

    // قابلیت کشیدن (Drag) و انتخاب کارت
    calendar.on('mousedown touchstart', function (e) {
        isDragging = true;
        calendar.addClass('grabbing');
        startX = (e.type === 'touchstart' ? e.originalEvent.touches[0].pageX : e.pageX);
        scrollLeft = calendar.scrollLeft() || 0;
        velocity = 0;
        lastX = startX;
        lastTime = Date.now();
        e.preventDefault();
    });

    calendar.on('mousemove touchmove', function (e) {
        if (!isDragging) return;

        const x = (e.type === 'touchmove' ? e.originalEvent.touches[0].pageX : e.pageX);
        const deltaX = x - startX;
        calendar.scrollLeft(scrollLeft - deltaX);

        // محاسبه سرعت برای انیمیشن اینرسی
        const currentTime = Date.now();
        const timeDiff = currentTime - lastTime;
        if (timeDiff > 0) {
            velocity = (x - lastX) / timeDiff;
        }
        lastX = x;
        lastTime = currentTime;

        // انتخاب کارت هنگام حرکت ماوس روی آن
        const targetCard = $(e.target).closest('.calendar-card');
        if (targetCard.length) {
            $('.calendar-card').not('.my-active').removeClass('card-selected');
            targetCard.addClass('card-selected');
        }

        e.preventDefault();
    });

    calendar.on('mouseup touchend', function () {
        isDragging = false;
        calendar.removeClass('grabbing');

        // اعمال انیمیشن اینرسی
        const inertiaDuration = 500; // مدت زمان اینرسی (میلی‌ثانیه)
        const inertiaDistance = velocity * inertiaDuration * 0.5; // فاصله اینرسی
        const currentScroll = calendar.scrollLeft();
        const targetScroll = currentScroll - inertiaDistance;

        calendar.animate(
            { scrollLeft: targetScroll },
            {
                duration: inertiaDuration,
                easing: 'easeOutQuad' // استفاده از easing پلاگین
            }
        );
    });

    calendar.on('mouseleave', function () {
        if (isDragging) {
            isDragging = false;
            calendar.removeClass('grabbing');
        }
    });

    // مدیریت کلیک روی کارت‌ها
    calendar.on('click', '.calendar-card', function (e) {
        // جلوگیری از اعمال کلیک هنگام drag
        if (Math.abs(velocity) > 0.1) return; // اگر حرکت drag وجود داشت، کلیک نادیده گرفته شود
        $('.calendar-card').not('.my-active').removeClass('card-selected');
        $(this).addClass('card-selected');
    });

    $('#nextRow').click(function () {
        if (!$(this).prop('disabled')) {
            animateAndLoadCalendar('nextRow');
        }
    });

    $('#prevRow').click(function () {
        if (!$(this).prop('disabled')) {
            animateAndLoadCalendar('prevRow');
        }
    });

    fetchAppointmentsCount();
});
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">