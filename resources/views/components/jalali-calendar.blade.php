<style>
  /* متغیرهای استایل (دقیقاً مشابه react-modern-calendar-datepicker) */
  :root {
    /* پالت رنگی اصلی */
    --primary: #2E86C1;
    /* آبی اصلی - استفاده در دکمه‌ها و لینک‌ها */
    --primary-light: #84CAF9;
    /* آبی روشن - در گرادیان‌ها */
    --secondary: #1DEB3C;
    /* سبز - برای دکمه‌های ثانویه */
    --secondary-hover: #15802A;
    /* سبز تیره‌تر برای هاور */
    --background-light: #F0F8FF;
    /* آبی بسیار روشن - پس‌زمینه بخش‌ها */
    --background-footer: #D4ECFD;
    /* آبی روشن‌تر - فوتر */
    --background-card: #FFFFFF;
    /* سفید - کارت‌ها */
    --text-primary: #000000;
    /* مشکی - متن اصلی */
    --text-secondary: #707070;
    /* خاکستری - متن ثانویه */
    --text-discount: #008000;
    /* سبز - قیمت با تخفیف */
    --text-original: #FF0000;
    /* قرمز - قیمت اولیه */
    --border-neutral: #E5E7EB;
    /* خاکستری روشن - حاشیه‌ها */
    --shadow: rgba(0, 0, 0, 0.1) 0px 0px 5px 0px, rgba(0, 0, 0, 0.1) 0px 0px 1px 0px !important;
    /* سایه‌ها */
    --gradient-instagram-from: #F92CA7;
    /* گرادیان اینستاگرام - شروع */
    --gradient-instagram-to: #6B1A93;
    /* گرادیان اینستاگرام - پایان */
    --button-mobile: #4F9ACD;
    /* آبی متوسط - دکمه‌های موبایل */
    --button-mobile-light: #A2CDEB;
    /* آبی روشن‌تر - دکمه‌های موبایل */
    --support-section: #2E86C1;
    /* آبی - بخش پشتیبانی */
    --support-text: #084D7C;
    /* آبی تیره - متن پشتیبانی */

    /* شعاع گوشه‌ها (border-radius) */
    --radius-button: 0.5rem;
    /* 8px - دکمه‌های کوچک */
    --radius-button-large: 1rem;
    /* 16px - دکمه‌های بزرگ */
    --radius-button-xl: 1.25rem;
    /* 20px - دکمه‌های خیلی بزرگ */
    --radius-card: 1.125rem;
    /* 18px - کارت‌ها */
    --radius-footer: 1.875rem;
    /* 30px - فوتر و برخی بخش‌ها */
    --radius-nav: 1.25rem;
    /* 20px - نوار ناوبری */
    --radius-circle: 9999px;
    /* دایره کامل - برای آیکون‌ها */
  }

  /* استایل‌های مودال */
  #miniCalendarModal .modal.custom-modal {
    display: none;
  }

  #miniCalendarModal .modal.custom-modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.5);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1050;
  }

  #miniCalendarModal .modal-dialog {
    max-width: 360px !important;
    margin: 1.75rem auto;
    transition: transform 0.3s ease, opacity 0.3s ease;
    transform: scale(0.7);
    opacity: 0;
  }

  #miniCalendarModal .modal.custom-modal.show .modal-dialog {
    transform: scale(1);
    opacity: 1;
  }

  #miniCalendarModal .modal-content {
    background: var(--background-card);
    border-radius: var(--radius-card);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    border: none;
    padding: 8px;
  }

  #miniCalendarModal .modal-header {
    border-bottom: none;
    padding: 8px 16px;
    position: relative;
  }

  #miniCalendarModal .calendar-day.friday {
    color: #ff4d4f !important;
  }

  #miniCalendarModal .modal-header .btn-close {
    background: none;
    border: none;
    font-size: 1.7rem;
    color: var(--text-secondary);
    opacity: 0.7;
    transition: opacity 0.2s ease;
    position: absolute;
    left: 16px;
    top: 12px;
    padding: 10px
  }

  #miniCalendarModal .modal-header .btn-close:hover {
    opacity: 1;
    color: var(--text-primary);
  }

  #miniCalendarModal .modal-body {
    padding: 0 16px 16px;
  }

  /* کانتینر تقویم */
  #miniCalendarModal .calendar {
    background: var(--background-card);
  }

  /* هدر تقویم */
  #miniCalendarModal .calendar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
  }

  #miniCalendarModal .select-group {
    display: flex;
    gap: 6px;
    flex: 1;
    justify-content: center;
  }

  #miniCalendarModal .calendar-select {
    background: var(--background-card);
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23666666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: left 8px center;
    padding-left: 24px;
  }

  #miniCalendarModal .calendar-select:focus {
    outline: none;
    background: var(--primary-light);
    color: var(--primary);
  }

  #miniCalendarModal .calendar-select:hover {
    background: var(--primary-light);
  }

  #miniCalendarModal .nav-btn {
    background: transparent;
    border: none;
    padding: 6px;
    cursor: pointer;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-circle);
    transition: background 0.2s ease, transform 0.2s ease;
  }

  #miniCalendarModal .nav-btn svg {
    stroke: var(--text-secondary);
    width: 14px;
    height: 14px;
  }

  #miniCalendarModal .nav-btn:hover {
    background: var(--primary-light);
    transform: scale(1.1);
  }

  #miniCalendarModal .nav-btn:hover svg {
    stroke: var(--primary);
  }

  /* بدنه تقویم */
  #miniCalendarModal .calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
  }

  /* نام روزهای هفته */
  #miniCalendarModal .calendar-day-name {
    text-align: center;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
    padding: 8px 0;
  }

  /* روزهای ماه */
  #miniCalendarModal .calendar-day {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    cursor: pointer;
    border-radius: var(--radius-circle);
    transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
    position: relative;
    margin: 0 auto;
  }

  #miniCalendarModal .calendar-day.empty {
    background: transparent;
    cursor: default;
  }

  #miniCalendarModal .calendar-day:hover:not(.empty) {
    background: var(--primary-light);
    transform: scale(1.15);
  }

  #miniCalendarModal .calendar-day.friday {
    color: #ff4d4f;
  }

  #miniCalendarModal .calendar-day.today {
    background: var(--primary-light);
    border: 1px solid var(--primary);
    color: var(--primary);
    font-weight: 600;
    border-radius: var(--radius-circle);
  }

  #miniCalendarModal .calendar-day.selected {
    background: var(--primary);
    color: var(--background-card);
    font-weight: 600;
    border-radius: var(--radius-circle);
    box-shadow: 0 2px 4px rgba(0, 147, 255, 0.2);
  }

  /* بج برای رویدادها */
  #miniCalendarModal .calendar-day.has-event::after {
    content: '';
    position: absolute;
    bottom: 4px;
    width: 5px;
    height: 5px;
    background: var(--secondary);
    border-radius: var(--radius-circle);
  }

  /* فوتر تقویم */
  #miniCalendarModal .calendar-footer {
    text-align: center;
    padding-top: 12px;
    font-size: 14px;
    color: var(--text-primary);
  }

  /* ریسپانسیو */
  @media (max-width: 576px) {
    #miniCalendarModal .modal-dialog {
      max-width: 90%;
      margin: 1rem auto;
    }

    #miniCalendarModal .calendar {
      padding: 12px;
    }

    #miniCalendarModal .calendar-header {
      gap: 6px;
    }

    #miniCalendarModal .select-group {
      gap: 4px;
    }

    #miniCalendarModal .calendar-select {
      font-size: 13px;
      padding: 3px 6px 3px 20px;
      background-position: left 6px center;
    }

    #miniCalendarModal .calendar-day {
      width: 32px;
      height: 32px;
      font-size: 13px;
    }

    #miniCalendarModal .calendar-day-name {
      font-size: 11px;
    }
  }

  @media (max-width: 425px) {
    #miniCalendarModal .calendar-day {
      width: 28px;
      height: 28px;
      font-size: 12px;
    }

    #miniCalendarModal .calendar-day-name {
      font-size: 10px;
    }
  }

  #miniCalendarModal .modal-content {
    height: 350px !important;
    border-radius: 10px !important
  }
</style>

<div class="container calendar">
  <div class="calendar-header w-100 d-flex justify-content-between align-items-center gap-4">
    <div>
      <button id="prev-month" class="btn btn-light nav-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <g id="Arrow / Chevron_Right_MD">
            <path id="Vector" d="M10 8L14 12L10 16" stroke="#000000" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </g>
        </svg>
      </button>
    </div>
    <div class="w-100 select-group">
      <select id="year" class="form-select w-100 bg-light border-0 calendar-select"></select>
    </div>
    <div class="w-100 select-group">
      <select id="month" class="form-select w-100 bg-light border-0 calendar-select"></select>
    </div>
    <div>
      <button id="next-month" class="btn btn-light nav-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <g id="Arrow / Chevron_Left_MD">
            <path id="Vector" d="M14 16L10 12L14 8" stroke="#000000" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </g>
        </svg>
      </button>
    </div>
  </div>
  <div class="calendar-body calendar-body-g-425">
    <!-- عناوین روزهای هفته -->
    <div class="calendar-day-name text-center">ش</div>
    <div class="calendar-day-name text-center">ی</div>
    <div class="calendar-day-name text-center">د</div>
    <div class="calendar-day-name text-center">س</div>
    <div class="calendar-day-name text-center">چ</div>
    <div class="calendar-day-name text-center">پ</div>
    <div class="calendar-day-name text-center">ج</div>
  </div>
  <div class="calendar-body" id="calendar-body">
    <!-- تقویم در اینجا بارگذاری می‌شود -->
  </div>
</div>

@push('scripts')
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      generateCalendar(moment().jYear(), moment().jMonth() + 1);
      const calendarBody = document.getElementById("calendar-body");
      const today = moment().startOf("day").locale("fa").format("jYYYY/jMM/jDD");
      const selectedDateSpan = document.querySelector(
        ".turning_selectDate__MLRSb span:first-child"
      );
      const calendarButton = document.querySelector(
        ".selectDate_datepicker__xkZeS"
      );
      const calendarModal = document.getElementById("miniCalendarModal");
      calendarButton.onclick = null;
      let modalInstance = null;
      calendarButton.removeEventListener("click", handleCalendarButtonClick);

      function handleCalendarButtonClick(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!modalInstance) {
          modalInstance = new bootstrap.Modal(calendarModal, {
            backdrop: "static",
            keyboard: false,
          });
        }

        const existingBackdrops = document.querySelectorAll(".modal-backdrop");
        existingBackdrops.forEach((backdrop) => backdrop.remove());
        document.body.classList.remove("modal-open");

        modalInstance.show();
      }

      calendarButton.addEventListener("click", handleCalendarButtonClick);
      selectedDateSpan.textContent = today;

    function generateCalendar(year, month) {
    calendarBody.innerHTML = "";

    const firstDayOfMonth = moment(
        `${year}/${month}/01`,
        "jYYYY/jMM/jDD"
    ).locale("fa");
    const daysInMonth = firstDayOfMonth.jDaysInMonth();
    let firstDayWeekday = firstDayOfMonth.weekday();
    const today = moment().locale("fa");

    // روزهای خالی قبل از شروع ماه
    for (let i = 0; i < firstDayWeekday; i++) {
        const emptyDay = document.createElement("div");
        emptyDay.classList.add("calendar-day", "empty");
        calendarBody.appendChild(emptyDay);
    }

    // تولید روزهای ماه
    for (let day = 1; day <= daysInMonth; day++) {
        const currentDay = firstDayOfMonth.clone().add(day - 1, "days");
        const dayElement = document.createElement("div");

        dayElement.classList.add("calendar-day");
        dayElement.setAttribute(
            "data-date",
            currentDay.format("jYYYY/jMM/jDD")
        );

        // بررسی روز جمعه
        if (currentDay.day() === 6) {
            console.log(`Friday detected: ${currentDay.format("jYYYY/jMM/jDD")}`);
            dayElement.classList.add("friday");
        }

        // بررسی روز جاری
        if (currentDay.isSame(today, "day")) {
            dayElement.classList.add("active");
        }

        dayElement.innerHTML = `<span>${currentDay.format("jD")}</span>`;

        dayElement.addEventListener("click", function() {
            const selectedDate = this.getAttribute("data-date");
            if (modalInstance) {
                modalInstance.hide();
            }
            $("#miniCalendarModal").modal("hide");

            selectedDateSpan.textContent = selectedDate;

            setTimeout(() => {
                const existingBackdrops =
                    document.querySelectorAll(".modal-backdrop");
                existingBackdrops.forEach((backdrop) => backdrop.remove());
                document.body.classList.remove("modal-open");
            }, 300);
        });

        calendarBody.appendChild(dayElement);
    }
}

      function populateSelectBoxes() {
        const yearSelect = document.getElementById("year");
        const monthSelect = document.getElementById("month");

        const currentYear = moment().jYear();
        const currentMonth = moment().jMonth() + 1;

        yearSelect.innerHTML = "";
        for (let year = currentYear - 10; year <= currentYear + 10; year++) {
          const option = document.createElement("option");
          option.value = year;
          option.textContent = year;
          yearSelect.appendChild(option);
        }

        const persianMonths = [
          "فروردین",
          "اردیبهشت",
          "خرداد",
          "تیر",
          "مرداد",
          "شهریور",
          "مهر",
          "آبان",
          "آذر",
          "دی",
          "بهمن",
          "اسفند",
        ];

        monthSelect.innerHTML = "";
        for (let month = 1; month <= 12; month++) {
          const option = document.createElement("option");
          option.value = month;
          option.textContent = persianMonths[month - 1];
          monthSelect.appendChild(option);
        }

        yearSelect.value = currentYear;
        monthSelect.value = currentMonth;

        yearSelect.addEventListener("change", function() {
          generateCalendar(
            parseInt(yearSelect.value),
            parseInt(monthSelect.value)
          );
        });

        monthSelect.addEventListener("change", function() {
          generateCalendar(
            parseInt(yearSelect.value),
            parseInt(monthSelect.value)
          );
        });
      }


      document
        .getElementById("prev-month")
        .addEventListener("click", function() {
          const yearSelect = document.getElementById("year");
          const monthSelect = document.getElementById("month");
          let currentMonth = parseInt(monthSelect.value);
          let currentYear = parseInt(yearSelect.value);

          if (currentMonth === 1) {
            currentYear -= 1;
            currentMonth = 12;
          } else {
            currentMonth -= 1;
          }

          yearSelect.value = currentYear;
          monthSelect.value = currentMonth;
          generateCalendar(currentYear, currentMonth);
        });

      // دکمه ماه بعد
      document
        .getElementById("next-month")
        .addEventListener("click", function() {
          const yearSelect = document.getElementById("year");
          const monthSelect = document.getElementById("month");
          let currentMonth = parseInt(monthSelect.value);
          let currentYear = parseInt(yearSelect.value);

          if (currentMonth === 12) {
            currentYear += 1;
            currentMonth = 1;
          } else {
            currentMonth += 1;
          }

          yearSelect.value = currentYear;
          monthSelect.value = currentMonth;
          generateCalendar(currentYear, currentMonth);
        });*/

      // اجرای اولیه
      populateSelectBoxes();
      generateCalendar(moment().jYear(), moment().jMonth() + 1);
    });
  </script>
@endpush
