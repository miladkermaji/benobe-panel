function ensureElementExists(selector, errorMessage, scope = document) {
    const element = scope.querySelector(selector);
    if (!element) {
        console.warn(errorMessage);
        return null;
    }
    return element;
}

function initializeSpecialDaysCalendar({ initialYear, initialMonth } = {}) {
    const calendarContainer = document.querySelector(
        ".special-days-calendar-container"
    );
    if (!calendarContainer) {
        console.error("Calendar container not found");
        return;
    }

    const calendarBody = ensureElementExists(
        "#special-days-calendar-body",
        "Calendar body not found",
        calendarContainer
    );
    const loadingOverlay = ensureElementExists(
        "#loading-overlay",
        "Loading overlay not found",
        calendarContainer
    );

    if (loadingOverlay) {
        loadingOverlay.style.display = "flex";
    }
    if (calendarBody) {
        calendarBody.style.display = "none";
    }

    async function fetchCalendarData(year, month) {
        try {
            if (
                typeof Livewire !== "undefined" &&
                typeof Livewire.dispatch === "function"
            ) {
                await new Promise((resolve) => {
                    Livewire.dispatchTo(
                        "dr.panel.turn.schedule.special-days-appointment",
                        "setCalendarDate",
                        { year, month }
                    );
                    setTimeout(resolve, 100);
                });
            } else {
                console.warn("Livewire.dispatch is not available");
            }
            const holidays = window.holidaysData.status
                ? window.holidaysData.holidays || []
                : [];
            const appointments = window.appointmentsData.status
                ? window.appointmentsData.data.map((item) => ({
                      date: item.date,
                      count: parseInt(item.count) || 0,
                  })) || []
                : [];
            return { holidays, appointments };
        } catch (error) {
            console.error("Error in fetchCalendarData:", error);
            if (loadingOverlay) loadingOverlay.style.display = "none";
            if (calendarBody) calendarBody.style.display = "grid";
            return { holidays: [], appointments: [] };
        }
    }

    let isRendering = false;

    async function generateCalendar(year, month) {
        if (isRendering) {
            return;
        }
        isRendering = true;

        if (!calendarBody) {
            console.error("Cannot generate calendar: Calendar body is missing");
            if (loadingOverlay) loadingOverlay.style.display = "none";
            isRendering = false;
            return;
        }

        // پاک‌سازی کامل calendarBody
        calendarBody.innerHTML = "";
        calendarBody.style.display = "none";

        if (!year || !month || isNaN(year) || isNaN(month)) {
            console.warn(
                "Invalid year or month, falling back to initial values"
            );
            year = initialYear || moment().jYear();
            month = initialMonth || moment().jMonth() + 1;
        }

        const firstDayOfMonth = moment(
            `${year}/${month}/01`,
            "jYYYY/jMM/jDD"
        ).locale("fa");
        if (!firstDayOfMonth.isValid()) {
            console.error("Invalid date format for firstDayOfMonth");
            isRendering = false;
            return;
        }

        const daysInMonth = firstDayOfMonth.jDaysInMonth();
        let firstDayWeekday = firstDayOfMonth.weekday();
        const today = moment().locale("fa");

        const { holidays, appointments } = await fetchCalendarData(year, month);

        // اضافه کردن روزهای خالی
        for (let i = 0; i < firstDayWeekday; i++) {
            const emptyDay = document.createElement("div");
            emptyDay.classList.add("calendar-day", "empty");
            calendarBody.appendChild(emptyDay);
        }

        // اضافه کردن روزهای ماه
        for (let day = 1; day <= daysInMonth; day++) {
            const currentDay = firstDayOfMonth.clone().add(day - 1, "days");
            const jalaliDate = currentDay.format("jYYYY/jMM/jDD");
            const gregorianDate = currentDay.toDate();
            const gregorianString = moment(gregorianDate).format("Y-MM-DD");

            const dayElement = document.createElement("div");
            dayElement.classList.add("calendar-day");
            dayElement.setAttribute("data-date", jalaliDate);
            dayElement.setAttribute("data-gregorian", gregorianString);

            const isPastDate = moment(gregorianString).isBefore(
                moment(),
                "day"
            );
            const isHoliday = holidays.includes(gregorianString);
            const appointmentData = appointments.find(
                (appt) => appt.date === gregorianString
            );
            const appointmentCount = appointmentData
                ? appointmentData.count
                : 0;

            if (!isPastDate) {
                if (isHoliday) {
                    dayElement.classList.add("holiday");
                }
                if (appointmentCount > 0) {
                    dayElement.classList.add("has-appointment");
                }
            }

            const spanElement = document.createElement("span");
            spanElement.textContent = currentDay.format("jD");
            dayElement.appendChild(spanElement);

            // Add appointment count badge if there are appointments
            if (!isPastDate && appointmentCount > 0) {
                const countElement = document.createElement("span");
                countElement.classList.add("appointment-count");
                countElement.textContent = appointmentCount;
                dayElement.appendChild(countElement);
            }

            if (currentDay.day() === 5) {
                dayElement.classList.add("friday");
            }
            if (currentDay.isSame(today, "day")) {
                dayElement.classList.add("today");
            }

            dayElement.addEventListener("click", function () {
                const gregorianDate = this.getAttribute("data-gregorian");
                const isPastDate = moment(gregorianDate).isBefore(
                    moment(),
                    "day"
                );
                const hasAppointment = appointmentCount > 0;

                if (isPastDate) {
                    Livewire.dispatch("openHolidayModal", {
                        modalId: "holiday-modal",
                        gregorianDate: gregorianDate,
                    });
                } else if (hasAppointment) {
                    Livewire.dispatch("openTransferModal", {
                        modalId: "transfer-modal",
                        gregorianDate: gregorianDate,
                    });
                } else {
                    Livewire.dispatch("openHolidayModal", {
                        modalId: "holiday-modal",
                        gregorianDate: gregorianDate,
                    });
                }
            });

            calendarBody.appendChild(dayElement);
        }

        if (loadingOverlay) {
            loadingOverlay.style.display = "none";
        }
        if (calendarBody) {
            calendarBody.style.display = "grid";
        }

        populateSelectBoxes(year, month);
        isRendering = false;
    }

    function populateSelectBoxes(year, month) {
        const yearSelect = ensureElementExists(
            "#special-days-year",
            "Year select not found",
            calendarContainer
        );
        const monthSelect = ensureElementExists(
            "#special-days-month",
            "Month select not found",
            calendarContainer
        );
        if (!yearSelect || !monthSelect) return;

        const currentYear = year || initialYear || moment().jYear();
        const currentMonth = month || initialMonth || moment().jMonth() + 1;

        yearSelect.innerHTML = "";
        for (let y = currentYear - 10; y <= currentYear + 10; y++) {
            const option = document.createElement("option");
            option.value = y;
            option.textContent = y;
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
        for (let m = 1; m <= 12; m++) {
            const option = document.createElement("option");
            option.value = m;
            option.textContent = persianMonths[m - 1];
            monthSelect.appendChild(option);
        }

        yearSelect.value = currentYear;
        monthSelect.value = currentMonth;

        yearSelect.addEventListener("change", () => {
            if (loadingOverlay) loadingOverlay.style.display = "flex";
            if (calendarBody) calendarBody.style.display = "none";
            generateCalendar(
                parseInt(yearSelect.value),
                parseInt(monthSelect.value)
            );
        });

        monthSelect.addEventListener("change", () => {
            if (loadingOverlay) loadingOverlay.style.display = "flex";
            if (calendarBody) calendarBody.style.display = "none";
            generateCalendar(
                parseInt(yearSelect.value),
                parseInt(monthSelect.value)
            );
        });
    }

    const prevMonthBtn = ensureElementExists(
        "#special-days-prev-month",
        "Prev month button not found",
        calendarContainer
    );
    const nextMonthBtn = ensureElementExists(
        "#special-days-next-month",
        "Next month button not found",
        calendarContainer
    );

    if (prevMonthBtn && nextMonthBtn) {
        prevMonthBtn.addEventListener("click", () => {
            const yearSelect = document.querySelector("#special-days-year");
            const monthSelect = document.querySelector("#special-days-month");
            let currentMonth = parseInt(monthSelect.value);
            let currentYear = parseInt(yearSelect.value);

            if (isNaN(currentMonth) || isNaN(currentYear)) {
                currentYear = initialYear || moment().jYear();
                currentMonth = initialMonth || moment().jMonth() + 1;
            }

            if (currentMonth === 1) {
                currentYear -= 1;
                currentMonth = 12;
            } else {
                currentMonth -= 1;
            }

            yearSelect.value = currentYear;
            monthSelect.value = currentMonth;
            if (loadingOverlay) loadingOverlay.style.display = "flex";
            if (calendarBody) calendarBody.style.display = "none";
            generateCalendar(currentYear, currentMonth);
        });

        nextMonthBtn.addEventListener("click", () => {
            const yearSelect = document.querySelector("#special-days-year");
            const monthSelect = document.querySelector("#special-days-month");
            let currentMonth = parseInt(monthSelect.value);
            let currentYear = parseInt(yearSelect.value);

            if (isNaN(currentMonth) || isNaN(currentYear)) {
                currentYear = initialYear || moment().jYear();
                currentMonth = initialMonth || moment().jMonth() + 1;
            }

            if (currentMonth === 12) {
                currentYear += 1;
                currentMonth = 1;
            } else {
                currentMonth += 1;
            }

            yearSelect.value = currentYear;
            monthSelect.value = currentMonth;
            if (loadingOverlay) loadingOverlay.style.display = "flex";
            if (calendarBody) calendarBody.style.display = "none";
            generateCalendar(currentYear, currentMonth);
        });
    }

    Livewire.on("holidayUpdated", (event) => {
        if (
            !event ||
            typeof event !== "object" ||
            !event.date ||
            typeof event.isHoliday === "undefined"
        ) {
            console.error("Invalid holidayUpdated event data", event);
            return;
        }

        const { date, isHoliday } = event;

        // به‌روزرسانی holidaysData
        if (!window.holidaysData) {
            window.holidaysData = { holidays: [] };
        }
        if (isHoliday) {
            if (!window.holidaysData.holidays.includes(date)) {
                window.holidaysData.holidays.push(date);
            }
        } else {
            window.holidaysData.holidays = window.holidaysData.holidays.filter(
                (holiday) => holiday !== date
            );
        }

        // پیدا کردن المان روز
        const dayElement = document.querySelector(
            `.calendar-day[data-gregorian="${date}"]`
        );

        // بررسی سال و ماه فعلی
        const yearSelect = document.querySelector("#special-days-year");
        const monthSelect = document.querySelector("#special-days-month");
        let year = parseInt(yearSelect?.value);
        let month = parseInt(monthSelect?.value);

        if (isNaN(year) || isNaN(month)) {
            console.warn("Invalid year or month, using initial values");
            year = initialYear || moment().jYear();
            month = initialMonth || moment().jMonth() + 1;
        }

        // بررسی اینکه تاریخ در ماه فعلی است
        const eventDate = moment(date, "YYYY-MM-DD").locale("fa");
        const eventYear = eventDate.jYear();
        const eventMonth = eventDate.jMonth() + 1;

        if (dayElement) {
            // اطمینان از وجود window.appointmentsData
            const appointmentData =
                window.appointmentsData?.data?.find(
                    (appt) => appt.date === date
                ) || null;
            const appointmentCount = appointmentData
                ? parseInt(appointmentData.count) || 0
                : 0;

            // به‌روزرسانی استایل و کلاس‌ها
            dayElement.classList.remove("holiday", "has-appointment");
            if (isHoliday) {
                dayElement.classList.add("holiday");
            } else if (appointmentCount > 0) {
                dayElement.classList.add("has-appointment");
            }

            // تنظیم محتوای تولتیپ
            const tooltipContent = isHoliday
                ? "این روز تعطیل است"
                : appointmentCount > 0
                ? `تعداد نوبت‌ها: ${appointmentCount}`
                : "";

            const span = dayElement.querySelector("span");
            dayElement.innerHTML = "";

            if (tooltipContent) {
                const tooltipWrapper = document.createElement("div");
                tooltipWrapper.setAttribute("x-tooltip", "");
                tooltipWrapper.setAttribute("id", `tooltip-day-${date}`);
                tooltipWrapper.setAttribute("data-trigger", "hover");
                tooltipWrapper.setAttribute("data-placement", "top");
                tooltipWrapper.classList.add("x-tooltip");

                const triggerDiv = document.createElement("div");
                triggerDiv.classList.add("x-tooltip__trigger");
                triggerDiv.appendChild(span);

                const contentDiv = document.createElement("div");
                contentDiv.classList.add("x-tooltip__content");
                contentDiv.setAttribute(
                    "data-tooltip-id",
                    `tooltip-day-${date}`
                );
                contentDiv.textContent = tooltipContent;

                tooltipWrapper.appendChild(triggerDiv);
                tooltipWrapper.appendChild(contentDiv);
                dayElement.appendChild(tooltipWrapper);
            } else {
                dayElement.appendChild(span);
            }

            // اجبار به رفرش DOM
            dayElement.style.display = "none";
            void dayElement.offsetHeight;
            dayElement.style.display = "flex";
        } else if (eventYear === year && eventMonth === month) {
            // فقط اگر تاریخ در ماه فعلی بود و المان پیدا نشد، تقویم رو رندر کن
            console.warn(
                `Day element for date ${date} not found, re-rendering calendar`
            );
            if (!isRendering) {
                generateCalendar(year, month);
            }
        }

        // بستن مودال
        window.dispatchEvent(
            new CustomEvent("close-modal", {
                detail: { name: "holiday-modal" },
            })
        );
    });

    Livewire.on("calendarDataUpdated", () => {
        // فقط اگر تغییر از جای دیگه (مثل تغییر ماه/سال) بود، تقویم رو رندر کن
        const yearSelect = document.querySelector("#special-days-year");
        const monthSelect = document.querySelector("#special-days-month");
        let year = parseInt(yearSelect?.value);
        let month = parseInt(monthSelect?.value);

        if (isNaN(year) || isNaN(month)) {
            console.warn(
                "Invalid year or month from select boxes, using initial values"
            );
            year = initialYear || moment().jYear();
            month = initialMonth || moment().jMonth() + 1;
            if (yearSelect && monthSelect) {
                yearSelect.value = year;
                monthSelect.value = month;
            }
        }

        // فقط اگر رندر در جریان نباشه، تقویم رو رندر کن
        if (!isRendering) {
            generateCalendar(year, month);
        }
    });

    populateSelectBoxes(initialYear, initialMonth);

    setTimeout(() => {
        generateCalendar(
            initialYear || moment().jYear(),
            initialMonth || moment().jMonth() + 1
        );
    }, 0);
}
