function ensureElementExists(selector, errorMessage, scope = document) {
    const element = scope.querySelector(selector);
    if (!element) {
        console.warn(errorMessage);
        return null;
    }
    return element;
}

function initializeSpecialDaysCalendar({ initialYear, initialMonth } = {}) {
    // اطمینان از وجود فقط یک نمونه تقویم
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

    let isRendering = false; // فلگ برای جلوگیری از رندر همزمان

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

        // پاک‌سازی کامل محتوای قبلی
        calendarBody.innerHTML = "";
        calendarBody.style.display = "none";

        // اعتبارسنجی year و month
        if (!year || !month || isNaN(year) || isNaN(month)) {
            console.warn(
                "Invalid year or month, falling back to initial values"
            );
            year = initialYear || moment().jYear();
            month = initialMonth || moment().jMonth() + 1;
        }

        // بقیه کد بدون تغییر
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

        // به‌روزرسانی داده‌ها قبل از رندر
        const { holidays, appointments } = await fetchCalendarData(year, month);

        // رندر روزهای خالی
        for (let i = 0; i < firstDayWeekday; i++) {
            const emptyDay = document.createElement("div");
            emptyDay.classList.add("calendar-day", "empty");
            calendarBody.appendChild(emptyDay);
        }

        // رندر روزهای ماه
        // رندر روزهای ماه
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

            // فقط برای روزهای غیر گذشته استایل‌ها را اعمال کن
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

            // تولتیپ فقط برای روزهای غیر گذشته
            const tooltipContent =
                !isPastDate && isHoliday
                    ? "این روز تعطیل است"
                    : !isPastDate && appointmentCount > 0
                    ? `تعداد نوبت‌ها: ${appointmentCount}`
                    : "";
            if (tooltipContent) {
                const tooltipWrapper = document.createElement("div");
                tooltipWrapper.setAttribute("x-tooltip", "");
                tooltipWrapper.setAttribute(
                    "id",
                    `tooltip-day-${gregorianString}`
                );
                tooltipWrapper.setAttribute("data-trigger", "hover");
                tooltipWrapper.setAttribute("data-placement", "top");
                tooltipWrapper.classList.add("x-tooltip");

                const triggerDiv = document.createElement("div");
                triggerDiv.classList.add("x-tooltip__trigger");
                triggerDiv.appendChild(spanElement);

                const contentDiv = document.createElement("div");
                contentDiv.classList.add("x-tooltip__content");
                contentDiv.textContent = tooltipContent;

                tooltipWrapper.appendChild(triggerDiv);
                tooltipWrapper.appendChild(contentDiv);

                dayElement.appendChild(tooltipWrapper);
            } else {
                dayElement.appendChild(spanElement);
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
                    // اجازه باز شدن مودال برای روزهای گذشته
                    Livewire.dispatch("openHolidayModal", {
                        modalId: "holiday-modal",
                        gregorianDate: gregorianDate,
                    });
                } else if (hasAppointment) {
                    // باز کردن مودال جابجایی برای روزهای دارای نوبت
                    Livewire.dispatch("openTransferModal", {
                        modalId: "transfer-modal",
                        gregorianDate: gregorianDate,
                    });
                } else {
                    // باز کردن مودال تعطیلی برای روزهای بدون نوبت
                    Livewire.dispatch("openHolidayModal", {
                        modalId: "holiday-modal",
                        gregorianDate: gregorianDate,
                    });
                }
            });

            calendarBody.appendChild(dayElement);
        }

        // نمایش تقویم
        if (loadingOverlay) {
            loadingOverlay.style.display = "none";
        }
        if (calendarBody) {
            calendarBody.style.display = "grid";
        }

        // به‌روزرسانی سلکت‌باکس‌ها
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

        // استفاده از مقادیر اولیه یا جاری
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

    Livewire.on("calendarDataUpdated", () => {
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

        generateCalendar(year, month);
    });

    Livewire.on("holidayUpdated", (event) => {
        const { date, isHoliday } = event;

        // آپدیت window.holidaysData
        if (isHoliday) {
            if (!window.holidaysData.holidays.includes(date)) {
                window.holidaysData.holidays.push(date);
            }
        } else {
            window.holidaysData.holidays = window.holidaysData.holidays.filter(
                (holiday) => holiday !== date
            );
        }

        const dayElement = document.querySelector(
            `.calendar-day[data-gregorian="${date}"]`
        );

        if (dayElement) {
            // به‌روزرسانی کلاس‌های روز
            if (isHoliday) {
                dayElement.classList.add("holiday");
            } else {
                dayElement.classList.remove("holiday");
            }

            // به‌روزرسانی تولتیپ
            const appointmentData = window.appointmentsData.data.find(
                (appt) => appt.date === date
            );
            const appointmentCount = appointmentData
                ? appointmentData.count
                : 0;

            const tooltipContent = isHoliday
                ? "این روز تعطیل است"
                : appointmentCount > 0
                ? `تعداد نوبت‌ها: ${appointmentCount}`
                : "";

            const span = dayElement.querySelector("span");
            dayElement.innerHTML = ""; // پاک کردن محتوای قبلی

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

                // انتقال contentDiv به body برای سازگاری با CustomTooltip
                document.body.appendChild(contentDiv);
            } else {
                dayElement.appendChild(span);
            }

            // اجبار به رندر دوباره
            dayElement.style.display = "none";
            void dayElement.offsetHeight;
            dayElement.style.display = "flex";
        } else {
            console.warn(
                `Day element for date ${date} not found, re-rendering calendar`
            );
            // رندر دوباره تقویم
            const yearSelect = document.querySelector("#special-days-year");
            const monthSelect = document.querySelector("#special-days-month");
            let year = parseInt(yearSelect?.value);
            let month = parseInt(monthSelect?.value);

            if (isNaN(year) || isNaN(month)) {
                console.warn("Invalid year or month, using initial values");
                year = initialYear || moment().jYear();
                month = initialMonth || moment().jMonth() + 1;
            }

            generateCalendar(year, month);
        }

        // بستن مودال
        window.closeXModal("holiday-modal");
    });

    // پر کردن اولیه سلکت‌باکس‌ها
    populateSelectBoxes(initialYear, initialMonth);

    // رندر اولیه تقویم
    setTimeout(() => {
        generateCalendar(
            initialYear || moment().jYear(),
            initialMonth || moment().jMonth() + 1
        );
    }, 0);
}

