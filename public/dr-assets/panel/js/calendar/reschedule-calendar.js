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

// کش محلی برای داده‌های تقویم
const calendarDataCache = new Map();

// تابع جمع‌آوری نوبت‌های انتخاب شده
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
            const dateCell = row ? row.querySelector("td:nth-child(5)") : null;
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

    async function fetchCalendarData(year, month) {
        const cacheKey = `${year}-${month}`;
        if (calendarDataCache.has(cacheKey)) {
            console.debug(`Using cached data for ${cacheKey}`);
            return calendarDataCache.get(cacheKey);
        }

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

            const data = { holidays, appointments };
            calendarDataCache.set(cacheKey, data);
            return data;
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
            const fragment = document.createDocumentFragment();
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
                fragment.appendChild(emptyDay);
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

                // فقط اگر تاریخ امروز یا آینده باشد، تعداد نوبت‌ها را نمایش بده
                if (
                    appointmentCount > 0 &&
                    !currentDay.isBefore(today, "day")
                ) {
                    dayElement.classList.add("has-appointment");
                    const countElement = document.createElement("span");
                    countElement.classList.add("appointment-count");
                    countElement.textContent = appointmentCount;
                    dayElement.appendChild(countElement);
                }

                const dayNumberElement = document.createElement("span");
                dayNumberElement.classList.add("day-number");
                dayNumberElement.textContent = currentDay.format("jD");
                dayElement.appendChild(dayNumberElement);

                if (currentDay.isSame(today, "day")) {
                    dayElement.classList.add("today");
                    const todayLabel = document.createElement("span");
                    todayLabel.classList.add("today-label");
                    todayLabel.textContent = "امروز";
                    dayElement.appendChild(todayLabel);
                }

                if (currentDay.day() === 5) dayElement.classList.add("friday");

                fragment.appendChild(dayElement);
            }

            calendarBody.innerHTML = "";
            calendarBody.appendChild(fragment);

            // Add click handlers to days
            const days = calendarBody.querySelectorAll(
                ".calendar-day:not(.empty)"
            );
            days.forEach((day) => {
                day.onclick = () => handleDayClick(day);
            });

            const renderedDays = calendarBody.querySelectorAll(
                ".calendar-day:not(.empty)"
            );
            console.debug(
                `Total rendered days: ${renderedDays.length} for ${targetYear}/${targetMonth}`
            );
        } finally {
            isRendering = false;
        }
    }

    const debouncedGenerateCalendar = debounce(generateCalendar, 300);

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

        // اصلاح: استفاده از Event Delegation برای مدیریت رویدادهای سلکت‌باکس
        let isProcessing = false;
        const handleSelectChange = (event) => {
            if (isProcessing) return;
            isProcessing = true;
            const target = event.target;
            if (target.matches("#reschedule-year")) {
                console.debug(`Year changed to: ${target.value}`);
            } else if (target.matches("#reschedule-month")) {
                console.debug(`Month changed to: ${target.value}`);
            }
            if (loadingOverlay) loadingOverlay.style.display = "flex";
            if (calendarBody) calendarBody.style.display = "none";
            debouncedGenerateCalendar(
                parseInt(yearSelect.value),
                parseInt(monthSelect.value)
            );
            setTimeout(() => (isProcessing = false), 300);
        };

        // اصلاح: حذف رویدادهای قبلی و بایندینگ با Event Delegation
        modalScope.removeEventListener("change", handleSelectChange);
        modalScope.addEventListener("change", handleSelectChange);

        // اصلاح: اطمینان از تنظیم مقادیر پس از ریست
        yearSelect.value = currentYear;
        monthSelect.value = currentMonth;
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
            setTimeout(() => (isProcessing = false), 300);
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
            setTimeout(() => (isProcessing = false), 300);
        };

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

async function handleDayClick(dayElement) {
    // Get the date from data-date attribute which is in Jalali format
    const jalaliDate = dayElement.getAttribute("data-date");
    const [year, month, day] = jalaliDate.split("-").map(Number);

    // Convert Jalali date to Gregorian
    const gregorianDate = moment(jalaliDate, "jYYYY-jMM-jDD");
    const today = moment().startOf("day");

    // Check if the selected date is in the past
    if (gregorianDate.isBefore(today)) {
        Swal.fire({
            title: "توجه",
            text: "نوبت دهی این روز به اتمام رسیده است",
            icon: "warning",
            confirmButtonText: "باشه",
        });
        return;
    }

    collectSelectedAppointments();
    const selectedIds = window.selectedAppointmentIds || [];
    console.log("Selected IDs:", selectedIds);

    if (selectedIds.length === 0) {
        Swal.fire({
            title: "هشدار",
            text: "لطفاً حداقل یک نوبت را انتخاب کنید.",
            icon: "warning",
            confirmButtonText: "باشه",
        });
        return;
    }

    const date = dayElement.getAttribute("data-gregorian");
    console.log("Selected date:", date, "Jalali:", jalaliDate);

    const selectedDateObj = moment(date, "jYYYY-jMM-jDD");

    // Check if selected date is in the past
    if (selectedDateObj.isBefore(today)) {
        Swal.fire({
            title: "خطا",
            text: "امکان جابجایی نوبت به تاریخ‌های گذشته وجود ندارد",
            icon: "error",
            confirmButtonText: "باشه",
        });
        return;
    }

    if (selectedIds.length === 1) {
        try {
            console.log("Fetching available times for date:", date);

            // Get the original appointment details using dispatchTo
            const appointmentDetails = await new Promise((resolve, reject) => {
                const timeoutId = setTimeout(() => {
                    window.removeEventListener(
                        "appointment-details-received",
                        handler
                    );
                    reject(
                        new Error("Timeout waiting for appointment details")
                    );
                }, 10000);

                const handler = (event) => {
                    console.log("Received appointment details:", event.detail);
                    clearTimeout(timeoutId);
                    window.removeEventListener(
                        "appointment-details-received",
                        handler
                    );
                    resolve(event.detail[0]);
                };

                window.addEventListener(
                    "appointment-details-received",
                    handler
                );
                Livewire.dispatchTo(
                    "dr.panel.turn.schedule.appointments-list",
                    "getAppointmentDetails",
                    {
                        appointmentId: selectedIds[0],
                    }
                );
            });

            if (!appointmentDetails || !appointmentDetails[0]) {
                throw new Error("No appointment details received");
            }

            const appointment = appointmentDetails[0];
            const originalDate = appointment.appointment_date;
            const originalTime = appointment.appointment_time;
            const originalJalaliDate = moment(originalDate)
                .locale("fa")
                .format("jYYYY/jMM/jDD");

            // Get available times
            const availableTimes = await new Promise((resolve, reject) => {
                const timeoutId = setTimeout(() => {
                    window.removeEventListener(
                        "available-times-updated",
                        handler
                    );
                    reject(new Error("Timeout waiting for available times"));
                }, 10000);

                const handler = (event) => {
                    console.log("Received times update:", event.detail);
                    clearTimeout(timeoutId);
                    window.removeEventListener(
                        "available-times-updated",
                        handler
                    );
                    resolve(event.detail[0]);
                };

                window.addEventListener("available-times-updated", handler);
                Livewire.dispatchTo(
                    "dr.panel.turn.schedule.appointments-list",
                    "getAvailableTimesForDate",
                    { date }
                );
            });

            const times = availableTimes?.times || [];

            if (!times || times.length === 0) {
                Swal.fire({
                    title: "هشدار",
                    text: "هیچ زمان کاری خالی برای این تاریخ وجود ندارد.",
                    icon: "warning",
                    confirmButtonText: "باشه",
                });
                return;
            }

            // نمایش مودال زمان‌ها به جای SweetAlert
            window.dispatchEvent(
                new CustomEvent("open-modal", {
                    detail: {
                        name: "reschedule-time-modal",
                    },
                })
            );

            // پاک کردن زمان‌های قبلی و اضافه کردن زمان‌های جدید
            const container = document.getElementById(
                "reschedule-available-times"
            );
            if (container) {
                container.innerHTML = "";
                times.forEach((time) => {
                    const button = document.createElement("button");
                    button.type = "button";
                    button.className =
                        "btn btn-outline-primary m-1 reschedule-time-btn";
                    button.setAttribute("data-time", time);
                    button.textContent = time;
                    button.onclick = async () => {
                        // بستن مودال زمان‌ها
                        window.dispatchEvent(
                            new CustomEvent("close-modal", {
                                detail: {
                                    name: "reschedule-time-modal",
                                },
                            })
                        );

                        // نمایش تأییدیه با SweetAlert
                        const result = await Swal.fire({
                            title: "تأیید جابجایی نوبت",
                            html: `آیا مایلید نوبت ${originalTime} تاریخ ${originalJalaliDate} را به ساعت ${time} تاریخ ${jalaliDate} منتقل کنید؟`,
                            icon: "question",
                            showCancelButton: true,
                            confirmButtonText: "بله، منتقل کن",
                            cancelButtonText: "خیر",
                            reverseButtons: true,
                        });

                        if (result.isConfirmed) {
                            console.log("Rescheduling appointment:", {
                                appointmentIds: selectedIds,
                                newDate: date,
                                selectedTime: time,
                            });
                            Livewire.dispatchTo(
                                "dr.panel.turn.schedule.appointments-list",
                                "rescheduleAppointment",
                                {
                                    appointmentIds: selectedIds,
                                    newDate: date,
                                    selectedTime: time,
                                }
                            );
                        }
                    };
                    container.appendChild(button);
                });
            }
        } catch (error) {
            console.error("Error in handleDayClick:", error);
            Swal.fire({
                title: "خطا",
                text: "خطایی در دریافت زمان‌های موجود رخ داد. لطفاً دوباره تلاش کنید.",
                icon: "error",
                confirmButtonText: "باشه",
            });
        }
    } else {
        // Get original appointment details for multiple appointments
        const appointments = await Livewire.dispatch("getAppointmentDetails", {
            appointmentIds: selectedIds,
        });
        const originalDates =
            appointments[0]?.map((app) => ({
                date: moment(app.appointment_date)
                    .locale("fa")
                    .format("jYYYY/jMM/jDD"),
                time: app.appointment_time,
            })) || [];

        const result = await Swal.fire({
            title: "تایید جابجایی",
            html: `آیا از جابجایی ${
                selectedIds.length
            } نوبت به تاریخ ${jalaliDate} اطمینان دارید؟<br><br>
                  <small>نوبت‌های فعلی:<br>${originalDates
                      .map((d) => `${d.time} تاریخ ${d.date}`)
                      .join("<br>")}</small>`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "بله",
            cancelButtonText: "خیر",
        });

        if (result.isConfirmed) {
            Livewire.dispatch("rescheduleAppointment", {
                appointmentIds: selectedIds,
                newDate: date,
            });
        }
    }
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

    // اضافه کردن لیسنر برای موفقیت جابجایی نوبت
    window.addEventListener("appointment-rescheduled", (event) => {
        console.log("Appointment rescheduled successfully:", event.detail);

        // نمایش توستر موفقیت
        if (typeof Toastify === "function") {
            Toastify({
                text: event.detail.message,
                duration: 3000,
                gravity: "top",
                position: "right",
                style: {
                    background: "#4CAF50",
                },
            }).showToast();
        }

        // بروزرسانی تعداد نوبت‌ها
        if (typeof fetchAppointmentsCount === "function") {
            fetchAppointmentsCount();
        }

        // بروزرسانی تقویم
        if (typeof loadCalendar === "function") {
            loadCalendar();
        }
    });

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
            // اصلاح: پاک‌سازی کامل کش و متغیرها
            calendarDataCache.clear();
            const calendarBody = document.querySelector(
                "#reschedule-calendar-body"
            );
            if (calendarBody) {
                calendarBody.innerHTML = "";
                calendarBody.onclick = null; // حذف رویدادهای قبلی
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
            calendarDataCache.clear();
            const calendarBody = document.querySelector(
                "#reschedule-calendar-body"
            );
            if (calendarBody) {
                calendarBody.innerHTML = "";
                calendarBody.onclick = null; // حذف رویدادهای قبلی
            }
            window.selectedAppointmentIds = [];
            window.selectedAppointmentDates = [];
            window.selectedSingleAppointmentId = null;
        }
    });

    // Add Livewire event listeners
    Livewire.on("available-times-updated", (event) => {
        const times = event.times || [];
        updateAvailableTimes(times);
    });

    Livewire.on("appointment-details-received", (event) => {
        console.log("Received appointment details:", event);
        const details = event.details || [];
        if (details.length > 0) {
            selectedAppointmentIds = details.map((d) => d.id);
            console.log("Selected IDs:", selectedAppointmentIds);
        }
    });
});

function handleDateSelection(date) {
    const selectedDate = moment(date).format("YYYY-MM-DD");
    console.log(
        "Selected date:",
        selectedDate,
        "Jalali:",
        moment(date).locale("fa").format("YYYY/MM/DD")
    );

    // Fetch available times for the selected date
    fetchAvailableTimes(selectedDate);
}

function fetchAvailableTimes(date) {
    console.log("Fetching available times for date:", date);
    Livewire.dispatch("getAvailableTimesForDate", { date: date });
}

function updateAvailableTimes(times) {
    console.log("Received times update:", times);
    const timeContainer = document.getElementById("reschedule-available-times");
    if (!timeContainer) return;

    timeContainer.innerHTML = "";

    if (!times || times.length === 0) {
        timeContainer.innerHTML =
            '<div class="alert alert-info w-100 text-center">هیچ ساعت خالی برای این تاریخ یافت نشد</div>';
        return;
    }

    times.forEach((time) => {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "btn btn-outline-primary m-1 reschedule-time-btn";
        button.dataset.time = time;
        button.textContent = time;

        button.addEventListener("click", () => {
            // Remove selection from other buttons
            document.querySelectorAll(".reschedule-time-btn").forEach((btn) => {
                btn.classList.remove("btn-primary");
                btn.classList.add("btn-outline-primary");
            });

            // Select this button
            button.classList.remove("btn-outline-primary");
            button.classList.add("btn-primary");

            // Store selected time
            selectedTime = time;
        });

        timeContainer.appendChild(button);
    });
}

function rescheduleAppointment() {
    if (!selectedDate) {
        Swal.fire({
            title: "خطا",
            text: "لطفاً یک تاریخ را انتخاب کنید",
            icon: "error",
            confirmButtonText: "باشه",
        });
        return;
    }

    const appointmentIds = selectedAppointmentIds;
    console.log("Rescheduling appointment:", {
        appointmentIds,
        newDate: selectedDate,
        selectedTime: selectedTime,
    });

    if (typeof Livewire !== "undefined") {
        Livewire.dispatch("rescheduleAppointment", [
            appointmentIds,
            selectedDate,
            selectedTime,
        ]);
    } else {
        console.error("Livewire is not available");
    }

    // Close modal and cleanup
    closeModal();
}
