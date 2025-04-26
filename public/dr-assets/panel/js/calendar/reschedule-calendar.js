// تابع برای بررسی وجود المنت
function ensureRescheduleElementExists(
    selector,
    errorMessage,
    scope = document
) {
    const element = scope.querySelector(selector);
    if (!element) {
        console.error(errorMessage);
        return null;
    }
    return element;
}

// تابع اصلی تقویم جابجایی
function initializeRescheduleCalendar(modalId = "rescheduleModal") {
    const modalScope = document.querySelector(`#${modalId}`) || document;

    const calendarBody = ensureRescheduleElementExists(
        "#reschedule-calendar-body",
        "Reschedule calendar body not found",
        modalScope
    );
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

    if (!calendarBody || !yearSelect || !monthSelect) return;

    // تابع بررسی سال کبیسه
    function isLeapYear(jYear) {
        const cyclePosition = jYear % 33;
        return [1, 5, 9, 13, 17, 22, 26, 30].includes(cyclePosition);
    }

    // تابع دریافت تعطیلات و نوبت‌ها
    async function fetchCalendarData(date) {
        try {
            const holidays = await Livewire.dispatch("getHolidays")
                .to(
                    "App\\Livewire\\Dr\\Panel\\Turn\\Schedule\\AppointmentsList"
                )
                .promise();
            const appointments = await Livewire.dispatch(
                "getAppointmentsByDateSpecial",
                { date }
            )
                .to(
                    "App\\Livewire\\Dr\\Panel\\Turn\\Schedule\\AppointmentsList"
                )
                .promise();
            return {
                holidays: holidays || [],
                appointments: appointments || [],
            };
        } catch (error) {
            console.error("Error fetching calendar data:", error);
            return { holidays: [], appointments: [] };
        }
    }

    // تابع تولید تقویم
    async function generateRescheduleCalendar(year, month) {
        calendarBody.innerHTML = "";
        const firstDayOfMonth = moment(
            `${year}/${month}/01`,
            "jYYYY/jMM/jDD"
        ).locale("fa");
        const daysInMonth = firstDayOfMonth.jDaysInMonth();
        let firstDayWeekday = firstDayOfMonth.weekday();
        const today = moment().locale("fa");

        // دریافت تعطیلات و نوبت‌ها
        const { holidays, appointments } = await fetchCalendarData(
            firstDayOfMonth.format("YYYY-MM-DD")
        );

        // افزودن روزهای خالی
        for (let i = 0; i < firstDayWeekday; i++) {
            const emptyDay = document.createElement("div");
            emptyDay.classList.add("calendar-day", "empty");
            calendarBody.appendChild(emptyDay);
        }

        // افزودن روزهای ماه
        for (let day = 1; day <= daysInMonth; day++) {
            const currentDay = firstDayOfMonth.clone().add(day - 1, "days");
            const jalaliDate = currentDay.format("jYYYY/jMM/jDD");
            const gregorianDate = moment(currentDay.toDate()).format(
                "YYYY-MM-DD"
            );

            const dayElement = document.createElement("div");
            dayElement.classList.add("calendar-day");
            dayElement.setAttribute("data-date", jalaliDate);
            dayElement.setAttribute("data-gregorian", gregorianDate);

            if (currentDay.day() === 5) {
                dayElement.classList.add("friday");
            }
            if (currentDay.isSame(today, "day")) {
                dayElement.classList.add("today");
            }
            if (holidays.includes(gregorianDate)) {
                dayElement.classList.add("holiday");
            }
            if (
                appointments.some((apt) =>
                    moment(apt.appointment_date).isSame(gregorianDate, "day")
                )
            ) {
                dayElement.classList.add("has-appointment");
            }

            dayElement.innerHTML = `<span>${currentDay.format("jD")}</span>`;

            dayElement.addEventListener("click", function () {
                document
                    .querySelectorAll(".calendar-day")
                    .forEach((el) => el.classList.remove("selected"));
                this.classList.add("selected");
                const selectedGregorianDate =
                    this.getAttribute("data-gregorian");
                Livewire.dispatch("set", {
                    key: "rescheduleNewDate",
                    value: selectedGregorianDate,
                }).to("App\\Livewire\\RescheduleModal");
            });

            calendarBody.appendChild(dayElement);
        }
    }

    // تابع پر کردن سلکت  سلکت‌باکس‌ها
    function populateRescheduleSelectBoxes() {
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
            generateRescheduleCalendar(
                parseInt(yearSelect.value),
                parseInt(monthSelect.value)
            );
        });

        monthSelect.addEventListener("change", () => {
            generateRescheduleCalendar(
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
            generateRescheduleCalendar(currentYear, currentMonth);
        });

        nextMonthBtn.addEventListener("click", () => {
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
            generateRescheduleCalendar(currentYear, currentMonth);
        });
    }

    // اجرای اولیه
    populateRescheduleSelectBoxes();
    generateRescheduleCalendar(moment().jYear(), moment().jMonth() + 1);
}

// اجرای تقویم فقط وقتی مودال RescheduleModal باز می‌شود
Livewire.on("showModal", (event) => {
    if (event.data && event.data.alias === "reschedule-modal") {
        setTimeout(() => {
            initializeRescheduleCalendar("rescheduleModal");
        }, 500);
    }
});

// مدیریت به‌روزرسانی‌های Livewire
Livewire.hook("morph.updated", () => {
    setTimeout(() => {
        if (document.querySelector("#rescheduleModal.show")) {
            initializeRescheduleCalendar("rescheduleModal");
        }
    }, 300);
});
