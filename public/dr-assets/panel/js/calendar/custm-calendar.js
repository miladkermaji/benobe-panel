document.addEventListener("DOMContentLoaded", function () {
    // تابع برای بررسی وجود المنت
    function ensureElementExists(selector, errorMessage) {
        const element = document.querySelector(selector);
        if (!element) {
            console.warn(errorMessage);
            return null;
        }
        return element;
    }

    // انتخاب المنت‌ها
    const calendarBody = ensureElementExists(
        "#calendar-body",
        "Calendar body not found"
    );
    const selectedDateSpan = ensureElementExists(
        ".turning_selectDate__MLRSb span:first-child",
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
                const selectedDate = this.getAttribute("data-date");
                let gregorianDate;

                try {
                    const jalaliMoment = moment(selectedDate, "jYYYY/jMM/jDD");
                    gregorianDate = jalaliMoment.toDate();
                    gregorianDate = moment(gregorianDate).format("YYYY-MM-DD");
                } catch (error) {
                    console.error(
                        "Error converting Jalali to Gregorian:",
                        error
                    );
                    return;
                }

                // آپدیت span
                if (selectedDateSpan) {
                    selectedDateSpan.textContent = selectedDate;
                }

                // بستن مودال
                const modal = ensureElementExists(
                    "#miniCalendarModal",
                    "MiniCalendarModal not found"
                );
                if (modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }

                // حذف backdrop
                const existingBackdrops =
                    document.querySelectorAll(".modal-backdrop");
                existingBackdrops.forEach((backdrop) => backdrop.remove());
                document.body.classList.remove("modal-open");
                document.body.style.overflow = "";
                document.body.style.paddingRight = "";

                // ارسال رویداد به Livewire
                if (typeof Livewire !== "undefined") {
                    Livewire.dispatch("updateSelectedDate", {
                        date: gregorianDate,
                    });
                    // ارسال رویداد برای آپدیت تقویم ردیفی
                    const event = new CustomEvent("updateCalendarRow", {
                        detail: gregorianDate,
                    });
                    document.dispatchEvent(event);
                } else {
                    console.warn("Livewire is not defined");
                }
            });

            calendarBody.appendChild(dayElement);
        }
    }

    // تابع پر کردن سلکت‌باکس‌ها
    function populateSelectBoxes() {
        const yearSelect = ensureElementExists(
            "#year",
            "Year select not found"
        );
        const monthSelect = ensureElementExists(
            "#month",
            "Month select not found"
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
    const prevMonthBtn = ensureElementExists(
        "#prev-month",
        "Prev month button not found"
    );
    const nextMonthBtn = ensureElementExists(
        "#next-month",
        "Next month button not found"
    );
    if (prevMonthBtn && nextMonthBtn) {
        prevMonthBtn.addEventListener("click", () => {
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

        nextMonthBtn.addEventListener("click", () => {
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
        });
    }

    // اجرای اولیه
    populateSelectBoxes();
    generateCalendar(moment().jYear(), moment().jMonth() + 1);

    // مدیریت به‌روزرسانی‌های Livewire
    if (typeof Livewire !== "undefined") {
        Livewire.hook("morph.updated", () => {
            setTimeout(() => {
                populateSelectBoxes();
                generateCalendar(moment().jYear(), moment().jMonth() + 1);
            }, 150);
        });
    }
});
