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

// تابع برای حذف رویدادهای قبلی
function removeEventListeners(element, eventType) {
    if (!element) return;
    const clone = element.cloneNode(true);
    element.parentNode.replaceChild(clone, element);
    return clone;
}

// تابع اصلی تقویم
function initializeRescheduleCalendar(appointmentId = null) {
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

    if (selectedDateSpan) {
        selectedDateSpan.textContent = today;
    }

    const loadingOverlay = modalScope.querySelector("#loading-overlay");
    if (loadingOverlay) {
        loadingOverlay.style.display = "flex";
    }
    if (calendarBody) {
        calendarBody.style.display = "none";
        calendarBody.innerHTML = "";
        console.debug("Initial calendarBody cleared");
    }

    // ریست متغیرهای جهانی
    window.selectedAppointmentDates = [];
    window.selectedAppointmentIds = appointmentId ? [appointmentId] : [];
    window.selectedSingleAppointmentId = appointmentId || null;
    window.holidaysData = window.holidaysData || {
        status: false,
        holidays: [],
    };
    window.appointmentsData = window.appointmentsData || {
        status: false,
        data: [],
    };

    const clinicId = localStorage.getItem("selectedClinicId") || "default";

    // متغیرهای قفل رندر
    let isRendering = false;
    let lastRender = { year: null, month: null };

    // تابع Debounce
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                timeout = null;
                func.apply(this, args);
            }, wait);
        };
    }

    function collectSelectedAppointments() {
        const selectedCheckboxes = document.querySelectorAll(
            ".appointment-checkbox:checked"
        );
        const selectedIds = Array.from(selectedCheckboxes).map((cb) =>
            parseInt(cb.value)
        );
        const selectedDates = Array.from(selectedCheckboxes).map((cb) => {
            const row = cb.closest("tr");
            const dateCell = row ? row.querySelector("td:nth-child(5)") : null;
            return dateCell ? dateCell.textContent.trim() : "";
        });

        if (selectedIds.length > 0) {
            window.selectedAppointmentIds = selectedIds;
            window.selectedAppointmentDates = selectedDates;
        } else if (window.selectedSingleAppointmentId) {
            const checkbox = document.querySelector(
                `.appointment-checkbox[value="${window.selectedSingleAppointmentId}"]`
            );
            if (checkbox) {
                const row = checkbox.closest("tr");
                const dateCell = row
                    ? row.querySelector("td:nth-child(5)")
                    : null;
                window.selectedAppointmentIds = [
                    window.selectedSingleAppointmentId,
                ];
                window.selectedAppointmentDates = [
                    dateCell ? dateCell.textContent.trim() : "",
                ];
            } else {
                window.selectedAppointmentIds = [];
                window.selectedAppointmentDates = [];
            }
        } else {
            window.selectedAppointmentIds = [];
            window.selectedAppointmentDates = [];
        }
    }

    async function fetchCalendarData(year, month) {
        try {
            if (
                typeof Livewire !== "undefined" &&
                typeof Livewire.dispatch === "function"
            ) {
                await new Promise((resolve) => {
                    Livewire.dispatchTo(
                        "dr.panel.turn.schedule.appointments-list",
                        "setCalendarDate",
                        { year, month }
                    );
                    setTimeout(resolve, 100);
                });
            } else {
                console.warn("Livewire.dispatch is not available");
            }

            const holidays = window.holidaysData.status
                ? window.holidaysData.holidays
                : [];
            const appointments = window.appointmentsData.status
                ? window.appointmentsData.data.map((item) => ({
                      date: item.appointment_date,
                      count: item.appointment_count,
                  }))
                : [];

            return { holidays, appointments };
        } catch (error) {
            console.error("Error in fetchCalendarData:", error);
            if (loadingOverlay) loadingOverlay.style.display = "none";
            if (calendarBody) calendarBody.style.display = "grid";
            return { holidays: [], appointments: [] };
        }
    }

    async function generateCalendar(year, month) {
        if (!calendarBody) {
            console.error("Cannot generate calendar: Calendar body is missing");
            if (loadingOverlay) loadingOverlay.style.display = "none";
            return;
        }

        if (isRendering) {
            console.warn(
                `Calendar is already rendering for ${year}/${month}, skipping...`
            );
            return;
        }
        if (lastRender.year === year && lastRender.month === month) {
            console.debug(`Skipping duplicate render for ${year}/${month}`);
            if (loadingOverlay) loadingOverlay.style.display = "none";
            if (calendarBody) calendarBody.style.display = "grid";
            return;
        }

        isRendering = true;
        lastRender = { year, month };

        try {
            calendarBody.innerHTML = "";
            console.debug(`Cleared calendarBody for ${year}/${month}`);

            const targetYear = parseInt(year);
            const targetMonth = parseInt(month);
            console.debug(
                `Generating calendar for year: ${targetYear}, month: ${targetMonth}`
            );

            const firstDayOfMonth = moment(
                `${targetYear}/${targetMonth}/01`,
                "jYYYY/jMM/jDD"
            ).locale("fa");
            if (!firstDayOfMonth.isValid()) {
                console.error(
                    `Invalid date for ${targetYear}/${targetMonth}/01`
                );
                return;
            }

            const daysInMonth = firstDayOfMonth.jDaysInMonth();
            let firstDayWeekday = firstDayOfMonth.weekday();
            console.debug(
                `First day weekday: ${firstDayWeekday}, Days in month: ${daysInMonth}`
            );
            const today = moment().locale("fa");

            const { holidays, appointments } = await fetchCalendarData(
                targetYear,
                targetMonth
            );

            if (loadingOverlay) loadingOverlay.style.display = "none";
            if (calendarBody) calendarBody.style.display = "grid";

            for (let i = 0; i < firstDayWeekday; i++) {
                const emptyDay = document.createElement("div");
                emptyDay.classList.add("calendar-day", "empty");
                calendarBody.appendChild(emptyDay);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const currentDay = firstDayOfMonth.clone().add(day - 1, "days");
                const jalaliDate = currentDay.format("jYYYY/jMM/jDD");
                const formattedJalaliDate = currentDay.format("jD jMMMM jYYYY");
                const gregorianDate = currentDay.toDate();
                const gregorianString = moment(gregorianDate).format("Y-MM-DD");

                const currentJalaliYear = parseInt(currentDay.format("jYYYY"));
                const currentJalaliMonth = parseInt(currentDay.format("jMM"));
                if (
                    currentJalaliYear !== targetYear ||
                    currentJalaliMonth !== targetMonth
                ) {
                    console.warn(
                        `Skipping invalid date ${jalaliDate} (expected ${targetYear}/${targetMonth})`
                    );
                    continue;
                }

                const dayElement = document.createElement("div");
                dayElement.classList.add("calendar-day");
                dayElement.setAttribute("data-date", jalaliDate);
                dayElement.setAttribute("data-gregorian", gregorianString);

                const isHoliday = holidays.includes(gregorianString);
                if (isHoliday) dayElement.classList.add("holiday");

                const appointmentData = appointments.find(
                    (appt) => appt.date === gregorianString
                );
                const appointmentCount = appointmentData
                    ? appointmentData.count
                    : 0;
                if (appointmentCount > 0)
                    dayElement.classList.add("has-appointment");

                const spanElement = document.createElement("span");
                spanElement.textContent = currentDay.format("jD");

                const tooltipContent = isHoliday
                    ? "این روز تعطیل است"
                    : appointmentCount > 0
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

                if (currentDay.day() === 5) dayElement.classList.add("friday");
                if (currentDay.isSame(today, "day"))
                    dayElement.classList.add("today");

                dayElement.addEventListener("click", function () {
                    document
                        .querySelectorAll(".calendar-day")
                        .forEach((el) => el.classList.remove("selected"));
                    this.classList.add("selected");

                    const selectedGregorianDate =
                        this.getAttribute("data-gregorian");
                    const selectedJalaliDate = formattedJalaliDate;

                    collectSelectedAppointments();

                    if (window.selectedAppointmentDates.length === 0) {
                        Swal.fire({
                            title: "خطا",
                            text: "هیچ نوبت انتخاب‌شده‌ای یافت نشد. لطفاً حداقل یک نوبت را انتخاب کنید.",
                            icon: "error",
                            confirmButtonText: "باشه",
                        });
                        return;
                    }

                    const oldDate = window.selectedAppointmentDates[0];
                    const formattedOldDate = moment(oldDate, "jYYYY/jMM/jDD")
                        .locale("fa")
                        .format("jD jMMMM jYYYY");
                    const message = `آیا می‌خواهید نوبت‌ها از تاریخ ${formattedOldDate} به تاریخ ${selectedJalaliDate} منتقل شوند؟`;

                    Swal.fire({
                        title: "تأیید جابجایی نوبت",
                        text: message,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "بله، منتقل کن",
                        cancelButtonText: "خیر",
                        reverseButtons: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (window.selectedAppointmentIds.length === 0) {
                                Swal.fire({
                                    title: "خطا",
                                    text: "هیچ نوبت انتخاب‌شده‌ای یافت نشد.",
                                    icon: "error",
                                    confirmButtonText: "باشه",
                                });
                                return;
                            }

                            Livewire.dispatchTo(
                                "dr.panel.turn.schedule.appointments-list",
                                "rescheduleAppointment",
                                [
                                    window.selectedAppointmentIds,
                                    selectedGregorianDate,
                                ]
                            );

                            window.dispatchEvent(
                                new CustomEvent("close-modal", {
                                    detail: { name: "reschedule-modal" },
                                })
                            );
                        }
                    });
                });

                calendarBody.appendChild(dayElement);
            }

            const renderedDays = calendarBody.querySelectorAll(
                ".calendar-day:not(.empty)"
            );
            console.debug(
                `Total rendered days: ${renderedDays.length} for ${targetYear}/${targetMonth}`
            );
            if (renderedDays.length > daysInMonth) {
                console.warn(
                    `Excess days rendered: ${renderedDays.length} instead of ${daysInMonth}`
                );
            }
        } finally {
            isRendering = false;
        }
    }

    const debouncedGenerateCalendar = debounce(generateCalendar, 600);

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
        console.debug(
            `Populating select boxes with year: ${currentYear}, month: ${currentMonth}`
        );

        // پاک‌سازی منوهای کشویی
        yearSelect.innerHTML = "";
        monthSelect.innerHTML = "";

        // پر کردن منوی سال
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

        // پر کردن منوی ماه
        for (let month = 1; month <= 12; month++) {
            const option = document.createElement("option");
            option.value = month;
            option.textContent = persianMonths[month - 1];
            monthSelect.appendChild(option);
        }

        // تنظیم مقادیر پیش‌فرض
        yearSelect.value = currentYear;
        monthSelect.value = currentMonth;
        console.debug(
            `Year select set to: ${yearSelect.value}, Month select set to: ${monthSelect.value}`
        );

        let isProcessing = false;
        const handleYearChange = () => {
            if (isProcessing) return;
            isProcessing = true;
            console.debug(`Year changed to: ${yearSelect.value}`);
            if (loadingOverlay) loadingOverlay.style.display = "flex";
            if (calendarBody) calendarBody.style.display = "none";
            debouncedGenerateCalendar(
                parseInt(yearSelect.value),
                parseInt(monthSelect.value)
            );
            setTimeout(() => (isProcessing = false), 600);
        };
        const handleMonthChange = () => {
            if (isProcessing) return;
            isProcessing = true;
            console.debug(`Month changed to: ${monthSelect.value}`);
            if (loadingOverlay) loadingOverlay.style.display = "flex";
            if (calendarBody) calendarBody.style.display = "none";
            debouncedGenerateCalendar(
                parseInt(yearSelect.value),
                parseInt(monthSelect.value)
            );
            setTimeout(() => (isProcessing = false), 600);
        };

        // حذف رویدادهای قبلی
        yearSelect.replaceWith(yearSelect.cloneNode(true));
        monthSelect.replaceWith(monthSelect.cloneNode(true));
        const newYearSelect = modalScope.querySelector("#reschedule-year");
        const newMonthSelect = modalScope.querySelector("#reschedule-month");

        // تنظیم دوباره مقادیر پس از کلون کردن
        newYearSelect.value = currentYear;
        newMonthSelect.value = currentMonth;

        newYearSelect.addEventListener("change", handleYearChange);
        newMonthSelect.addEventListener("change", handleMonthChange);
    }

    let prevMonthBtn = ensureRescheduleElementExists(
        "#reschedule-prev-month",
        "Prev month button not found",
        modalScope
    );
    let nextMonthBtn = ensureRescheduleElementExists(
        "#reschedule-next-month",
        "Next month button not found",
        modalScope
    );

    if (prevMonthBtn && nextMonthBtn) {
        let isProcessing = false;
        const handlePrevMonth = () => {
            if (isProcessing) return;
            isProcessing = true;
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
            console.debug(
                `Navigated to previous month: ${currentYear}/${currentMonth}`
            );
            if (loadingOverlay) loadingOverlay.style.display = "flex";
            if (calendarBody) calendarBody.style.display = "none";
            debouncedGenerateCalendar(currentYear, currentMonth);
            setTimeout(() => (isProcessing = false), 600);
        };

        const handleNextMonth = () => {
            if (isProcessing) return;
            isProcessing = true;
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
            console.debug(
                `Navigated to next month: ${currentYear}/${currentMonth}`
            );
            if (loadingOverlay) loadingOverlay.style.display = "flex";
            if (calendarBody) calendarBody.style.display = "none";
            debouncedGenerateCalendar(currentYear, currentMonth);
            setTimeout(() => (isProcessing = false), 600);
        };

        // حذف رویدادهای قبلی
        prevMonthBtn = removeEventListeners(prevMonthBtn, "click");
        nextMonthBtn = removeEventListeners(nextMonthBtn, "click");

        prevMonthBtn.addEventListener("click", handlePrevMonth);
        nextMonthBtn.addEventListener("click", handleNextMonth);
    }

    collectSelectedAppointments();
    populateSelectBoxes();

    setTimeout(() => {
        const currentYear = moment().jYear();
        const currentMonth = moment().jMonth() + 1;
        console.debug(
            `Initial calendar render for ${currentYear}/${currentMonth}`
        );
        debouncedGenerateCalendar(currentYear, currentMonth);
    }, 0);
}

// مدیریت رویدادهای باز و بسته شدن مودال
document.addEventListener("livewire:initialized", () => {
    window.holidaysData = window.holidaysData || {
        status: false,
        holidays: [],
    };
    window.appointmentsData = window.appointmentsData || {
        status: false,
        data: [],
    };

    const clinicId = localStorage.getItem("selectedClinicId") || "default";
    if (clinicId !== "default") {
        Livewire.dispatch("setSelectedClinicId", { clinicId });
    }

    window.addEventListener("open-modal", (event) => {
        const modalId = event.detail.name;
        const appointmentId = event.detail.appointmentId || null;

        if (modalId === "reschedule-modal") {
            console.debug(
                "Opening reschedule-modal with appointmentId:",
                appointmentId
            );
            const clinicId =
                localStorage.getItem("selectedClinicId") || "default";
            if (clinicId !== "default") {
                Livewire.dispatch("setSelectedClinicId", { clinicId });
            }
            // ریست متغیرها و DOM
            const calendarBody = document.querySelector(
                "#reschedule-calendar-body"
            );
            if (calendarBody) {
                calendarBody.innerHTML = "";
                console.debug("Calendar body reset on modal open");
            }
            window.selectedAppointmentIds = [];
            window.selectedAppointmentDates = [];
            window.selectedSingleAppointmentId = null;

            initializeRescheduleCalendar(appointmentId);
        }
    });

    window.addEventListener("close-modal", (event) => {
        if (event.detail.name === "reschedule-modal") {
            console.debug("Closing reschedule-modal, cleaning up...");
            const calendarBody = document.querySelector(
                "#reschedule-calendar-body"
            );
            if (calendarBody) {
                calendarBody.innerHTML = "";
            }
            window.selectedAppointmentIds = [];
            window.selectedAppointmentDates = [];
            window.selectedSingleAppointmentId = null;
        }
    });
});
