// تابع برای بررسی وجود المنت
function ensureRescheduleElementExists(
    selector,
    errorMessage,
    scope = document
) {
    const element = scope.querySelector(selector);
    if (!element) {
        console.warn(errorMessage);
        return null;
    }
    return element;
}

// تابع اصلی تقویم
function initializeRescheduleCalendar() {
    // پیدا کردن محدوده مودال
    const modalScope = document.querySelector("#reschedule-modal") || document;

    const calendarBody = ensureRescheduleElementExists(
        "#reschedule-calendar-body",
        "Calendar body not found",
        modalScope
    );
    const selectedDateSpan = ensureRescheduleElementExists(
        ".selectDate_datepicker__xkZeS span:first-child",
        "Selected date span not found"
    );
    const today = moment().startOf("day").locale("fa").format("jYYYY/jMM/jDD");

    // تنظیم تاریخ اولیه
    if (selectedDateSpan) {
        selectedDateSpan.textContent = today;
    }

    // تابع بررسی سال کبیسه
    function isLeapYear(jYear) {
        const cyclePosition = jYear % 33;
        return [1, 5, 9, 13, 17, 22, 26, 30].includes(cyclePosition);
    }

    // تابع تولید تقویم
    function generateCalendar(year, month) {
        if (!calendarBody) return;

        calendarBody.innerHTML = "";
        const firstDayOfMonth = moment(
            `${year}/${month}/01`,
            "jYYYY/jMM/jDD"
        ).locale("fa");
        const daysInMonth = firstDayOfMonth.jDaysInMonth();
        let firstDayWeekday = firstDayOfMonth.weekday();
        const today = moment().locale("fa");

        // افزودن روزهای خالی
        for (let i = 0; i < firstDayWeekday; i++) {
            const emptyDay = document.createElement("div");
            emptyDay.classList.add("calendar-day", "empty");
            calendarBody.appendChild(emptyDay);
        }

        // افزودن روزهای ماه
        for (let day = 1; day <= daysInMonth; day++) {
            const currentDay = firstDayOfMonth.clone().add(day - 1, "days");
            const dayElement = document.createElement("div");
            dayElement.classList.add("calendar-day");
            dayElement.setAttribute(
                "data-date",
                currentDay.format("jYYYY/jMM/jDD")
            );

            if (currentDay.day() === 5) {
                dayElement.classList.add("friday");
            }
            if (currentDay.isSame(today, "day")) {
                dayElement.classList.add("today");
            }

            dayElement.innerHTML = `<span>${currentDay.format("jD")}</span>`;

            dayElement.addEventListener("click", function () {
                document
                    .querySelectorAll(".calendar-day")
                    .forEach((el) => el.classList.remove("selected"));
                this.classList.add("selected");
                const selectedGregorianDate =
                    this.getAttribute("data-gregorian");
                console.log("Selected date:", selectedGregorianDate);
            });

            calendarBody.appendChild(dayElement);
        }
    }

    // تابع پر کردن سلکت‌باکس‌ها
    function populateSelectBoxes() {
        const yearSelect = ensureRescheduleElementExists(
            "#reschedule-year",
            "Year select not found",
            modalScope
        );
        const monthSelect = ensureRescheduleElementExists(
            "#reschedule-month",
            "Month select not found",
            modalScope
        );
        if (!yearSelect || !monthSelect) return;

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

        yearSelect.addEventListener("change", () => {
            generateCalendar(
                parseInt(yearSelect.value),
                parseInt(monthSelect.value)
            );
        });

        monthSelect.addEventListener("change", () => {
            generateCalendar(
                parseInt(yearSelect.value),
                parseInt(monthSelect.value)
            );
        });
    }

    // مدیریت دکمه‌های قبلی و بعدی
    const prevMonthBtn = ensureRescheduleElementExists(
        "#reschedule-prev-month",
        "Prev month button not found",
        modalScope
    );
    const nextMonthBtn = ensureRescheduleElementExists(
        "#reschedule-next-month",
        "Next month button not found",
        modalScope
    );
    if (prevMonthBtn && nextMonthBtn) {
        prevMonthBtn.addEventListener("click", () => {
            const yearSelect = modalScope.querySelector("#reschedule-year");
            const monthSelect = modalScope.querySelector("#reschedule-month");
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

        nextMonthBtn.addEventListener("click", () => {
            const yearSelect = modalScope.querySelector("#reschedule-year");
            const monthSelect = modalScope.querySelector("#reschedule-month");
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
        });
    }

    // اجرای اولیه
    populateSelectBoxes();
    generateCalendar(moment().jYear(), moment().jMonth() + 1);
}

// اجرای تقویم وقتی مودال باز می‌شه
document.addEventListener("livewire:initialized", () => {
    // گوش دادن به رویداد openXModal
    window.addEventListener("openXModal", (event) => {
        const modalId = event.detail.id;

        if (modalId === "reschedule-modal") {
            setTimeout(() => {
                initializeRescheduleCalendar();
            }, 100); // تأخیر برای اطمینان از رندر DOM
        }
    });

    // نگه‌داری پشتیبانی از showModal برای مودال‌های دیگر
    Livewire.on("showModal", (modalId) => {
        if (modalId === "reschedule-modal") {
            setTimeout(() => {
                initializeRescheduleCalendar();
            }, 100);
        }
    });
});
