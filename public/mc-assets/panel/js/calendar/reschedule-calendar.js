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
    // حذف تکراری‌ها با استفاده از Set
    const selectedIds = [
        ...new Set(
            Array.from(selectedCheckboxes).map((cb) => parseInt(cb.value))
        ),
    ];
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
    console.log(
        "Initializing reschedule calendar with appointmentId:",
        appointmentId
    );

    const modalScope = document.querySelector("#reschedule-modal") || document;
    console.log("Modal scope found:", !!modalScope);

    const calendarBody = ensureRescheduleElementExists(
        "#reschedule-calendar-body",
        "Calendar body not found",
        modalScope
    );

    if (!calendarBody) {
        console.error("Calendar body not found, cannot initialize calendar");
        return;
    }

    console.log("Calendar body found:", !!calendarBody);

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
        console.log("Loading overlay shown");
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
    console.log("Clinic ID:", clinicId);

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
            return calendarDataCache.get(cacheKey);
        }

        try {
            // بررسی وجود متغیرهای سراسری
            if (!window.appointmentsCountUrl) {
                console.error("appointmentsCountUrl is not defined");
                return { holidays: [], appointments: [] };
            }

            // تنظیم پارامترهای مناسب برای هر گارد
            let requestData = {};
            const guardType = window.guardType || "doctor";

            if (guardType === "medical_center") {
                // برای مراکز درمانی، نیازی به selectedClinicId نیست
                requestData = {
                    guard_type: "medical_center",
                };
            } else {
                // برای پزشک و منشی، از selectedClinicId استفاده کن
                requestData = {
                    selectedClinicId:
                        localStorage.getItem("selectedClinicId") || "default",
                    guard_type: guardType,
                };
            }

            console.log("Fetching calendar data with:", {
                url: window.appointmentsCountUrl,
                data: requestData,
                guardType: guardType,
            });

            // Get appointments data from the server
            const response = await $.ajax({
                url: window.appointmentsCountUrl,
                method: "GET",
                data: requestData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
            });

            console.log("Calendar data response:", response);

            if (response.status) {
                const appointments = response.data || [];
                
                // استفاده از تعطیلات از پاسخ API
                let holidays = [];
                if (response.holidays && Array.isArray(response.holidays)) {
                    holidays = response.holidays;
                } else if (window.holidaysData && window.holidaysData.status) {
                    holidays = window.holidaysData.holidays;
                }

                const data = { holidays, appointments };
                calendarDataCache.set(cacheKey, data);
                return data;
            }
            return { holidays: [], appointments: [] };
        } catch (error) {
            console.error("Error in fetchCalendarData:", error);
            return { holidays: [], appointments: [] };
        }
    }

    async function generateCalendar(year, month) {
        console.log("Generating calendar for:", year, month);

        if (
            !calendarBody ||
            isRendering ||
            (lastRender.year === year && lastRender.month === month)
        ) {
            console.log("Calendar generation skipped:", {
                hasCalendarBody: !!calendarBody,
                isRendering: isRendering,
                lastRender: lastRender,
            });
            return;
        }

        isRendering = true;
        lastRender = { year, month };

        try {
            const fragment = document.createDocumentFragment();
            const targetYear = parseInt(year);
            const targetMonth = parseInt(month);

            const firstDayOfMonth = moment(
                `${targetYear}/${targetMonth}/01`,
                "jYYYY/jMM/jDD"
            ).locale("fa");
            if (!firstDayOfMonth.isValid()) {
                console.error(
                    "Invalid first day of month:",
                    `${targetYear}/${targetMonth}/01`
                );
                return;
            }

            const daysInMonth = firstDayOfMonth.jDaysInMonth();
            let firstDayWeekday = firstDayOfMonth.weekday();
            const today = moment().locale("fa");

            console.log("Calendar parameters:", {
                targetYear,
                targetMonth,
                daysInMonth,
                firstDayWeekday,
                today: today.format("jYYYY/jMM/jDD"),
            });

            const { holidays, appointments } = await fetchCalendarData(
                targetYear,
                targetMonth
            );

            console.log("Calendar data received:", {
                holidaysCount: holidays.length,
                appointmentsCount: appointments.length,
            });

            // Create empty days for first week
            for (let i = 0; i < firstDayWeekday; i++) {
                const emptyDay = document.createElement("div");
                emptyDay.classList.add("calendar-day", "empty");
                fragment.appendChild(emptyDay);
            }

            // Create a map of appointments for faster lookup
            const appointmentMap = new Map(
                appointments.map((appt) => [
                    moment(appt.appointment_date, [
                        "YYYY-MM-DD",
                        "YYYY-MM-DD HH:mm:ss",
                    ]).format("YYYY-MM-DD"),
                    appt,
                ])
            );

            // Create days
            for (let day = 1; day <= daysInMonth; day++) {
                const currentDay = firstDayOfMonth.clone().add(day - 1, "days");
                const jalaliDate = currentDay.format("jYYYY/jMM/jDD");
                const gregorianString = moment(currentDay.toDate()).format(
                    "YYYY-MM-DD"
                );

                const currentJalaliYear = parseInt(currentDay.format("jYYYY"));
                const currentJalaliMonth = parseInt(currentDay.format("jMM"));
                if (
                    currentJalaliYear !== targetYear ||
                    currentJalaliMonth !== targetMonth
                ) {
                    continue;
                }

                const dayElement = document.createElement("div");
                dayElement.classList.add("calendar-day");
                dayElement.setAttribute("data-date", jalaliDate);
                dayElement.setAttribute("data-gregorian", gregorianString);

                // Add holiday class if needed
                if (holidays.includes(gregorianString)) {
                    dayElement.classList.add("holiday");
                }

                // Get appointment data from map
                const appointment = appointmentMap.get(gregorianString);
                const appointmentCount =
                    appointment &&
                    !currentDay.isBefore(today, "day") &&
                    appointment.appointment_count > 0
                        ? appointment.appointment_count
                        : 0;

                // Add appointment count if needed
                if (
                    !currentDay.isBefore(today, "day") &&
                    appointmentCount > 0
                ) {
                    dayElement.classList.add("has-appointment");
                    const countElement = document.createElement("span");
                    countElement.classList.add("appointment-count");
                    countElement.textContent = appointmentCount;
                    dayElement.appendChild(countElement);
                }

                // Add day number
                const dayNumberElement = document.createElement("span");
                dayNumberElement.classList.add("day-number");
                dayNumberElement.textContent = currentDay.format("jD");
                dayElement.appendChild(dayNumberElement);

                // Add today label if needed
                if (currentDay.isSame(today, "day")) {
                    dayElement.classList.add("today");
                    const todayLabel = document.createElement("span");
                    todayLabel.classList.add("today-label");
                    todayLabel.textContent = "امروز";
                    dayElement.appendChild(todayLabel);
                }

                // Add friday class if needed
                if (currentDay.day() === 5) {
                    dayElement.classList.add("friday");
                }

                fragment.appendChild(dayElement);
            }

            // Update calendar body
            calendarBody.innerHTML = "";
            calendarBody.appendChild(fragment);

            // Add click handlers
            const days = calendarBody.querySelectorAll(
                ".calendar-day:not(.empty)"
            );
            days.forEach((day) => {
                day.onclick = () => handleDayClick(day);
            });

            console.log(
                "Calendar generated successfully with",
                days.length,
                "days"
            );
        } catch (error) {
            console.error("Error generating calendar:", error);
        } finally {
            isRendering = false;
            if (loadingOverlay) {
                loadingOverlay.style.display = "none";
                console.log("Loading overlay hidden");
            }
            if (calendarBody) {
                calendarBody.style.display = "grid";
                console.log("Calendar body displayed");
            }
        }
    }

    // Optimize debounce function
    const debouncedGenerateCalendar = debounce(generateCalendar, 300);

    // Optimize select box population
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

        // Create year options
        const yearFragment = document.createDocumentFragment();
        for (let year = currentYear - 10; year <= currentYear + 10; year++) {
            const option = document.createElement("option");
            option.value = year;
            option.textContent = year;
            yearFragment.appendChild(option);
        }
        yearSelect.innerHTML = "";
        yearSelect.appendChild(yearFragment);

        // Create month options
        const monthFragment = document.createDocumentFragment();
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
        persianMonths.forEach((month, index) => {
            const option = document.createElement("option");
            option.value = index + 1;
            option.textContent = month;
            monthFragment.appendChild(option);
        });
        monthSelect.innerHTML = "";
        monthSelect.appendChild(monthFragment);

        // Set default values
        yearSelect.value = currentYear;
        monthSelect.value = currentMonth;

        // Add event listener
        let isProcessing = false;
        const handleSelectChange = debounce((event) => {
            if (isProcessing) return;
            isProcessing = true;

            if (loadingOverlay) loadingOverlay.style.display = "flex";
            if (calendarBody) calendarBody.style.display = "none";

            debouncedGenerateCalendar(
                parseInt(yearSelect.value),
                parseInt(monthSelect.value)
            );

            setTimeout(() => (isProcessing = false), 300);
        }, 300);

        modalScope.removeEventListener("change", handleSelectChange);
        modalScope.addEventListener("change", handleSelectChange);
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
                let handler; // Declare handler variable first
                const timeoutId = setTimeout(() => {
                    window.removeEventListener(
                        "appointment-details-received",
                        handler
                    );
                    reject(
                        new Error("Timeout waiting for appointment details")
                    );
                }, 10000);

                handler = (event) => {
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
                    "mc.panel.turn.schedule.appointments-list",
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
                let handler; // Declare handler variable first
                const timeoutId = setTimeout(() => {
                    window.removeEventListener(
                        "available-times-updated",
                        handler
                    );
                    reject(new Error("Timeout waiting for available times"));
                }, 10000);

                handler = (event) => {
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
                    "mc.panel.turn.schedule.appointments-list",
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
                            console.log("Rescheduling appointment with data:", {
                                appointmentIds: selectedIds,
                                newDate: date,
                                selectedTime: time,
                                originalTime: originalTime,
                                originalDate: originalDate,
                            });

                            // اضافه کردن لاگ برای بررسی داده‌های ارسالی
                            console.log("Dispatching reschedule event with:", {
                                appointmentIds: selectedIds,
                                newDate: date,
                                selectedTime: time,
                            });

                            Livewire.dispatchTo(
                                "mc.panel.turn.schedule.appointments-list",
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
        try {
            // Get original appointment details for multiple appointments
            const appointments = await new Promise((resolve, reject) => {
                let handler; // Declare handler variable first
                const timeoutId = setTimeout(() => {
                    window.removeEventListener(
                        "appointment-details-received",
                        handler
                    );
                    reject(
                        new Error("Timeout waiting for appointment details")
                    );
                }, 10000);

                handler = (event) => {
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
                    "mc.panel.turn.schedule.appointments-list",
                    "getAppointmentDetails",
                    {
                        appointmentIds: selectedIds,
                    }
                );
            });

            // Get available times for the selected date
            const availableTimes = await new Promise((resolve, reject) => {
                let handler; // Declare handler variable first
                const timeoutId = setTimeout(() => {
                    window.removeEventListener(
                        "available-times-updated",
                        handler
                    );
                    reject(new Error("Timeout waiting for available times"));
                }, 10000);

                handler = (event) => {
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
                    "mc.panel.turn.schedule.appointments-list",
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

            // Check if we have enough time slots
            if (times.length < selectedIds.length) {
                Swal.fire({
                    title: "هشدار",
                    text: `تعداد زمان‌های خالی (${times.length}) کمتر از تعداد نوبت‌های انتخاب شده (${selectedIds.length}) است.`,
                    icon: "warning",
                    confirmButtonText: "باشه",
                });
                return;
            }

            // Sort times based on work hours
            const sortedTimes = [...times].sort((a, b) => {
                const [aHour, aMin] = a.split(":").map(Number);
                const [bHour, bMin] = b.split(":").map(Number);
                return aHour * 60 + aMin - (bHour * 60 + bMin);
            });

            // Create time mapping for confirmation message
            const timeMapping = selectedIds
                .map((id, index) => {
                    const appointment = appointments.find(
                        (app) => app.id === id
                    );
                    if (!appointment) return null;

                    return {
                        from: {
                            date: moment(appointment.appointment_date)
                                .locale("fa")
                                .format("jYYYY/jMM/jDD"),
                            time: appointment.appointment_time,
                        },
                        to: {
                            date: jalaliDate,
                            time: sortedTimes[index], // Use sorted times
                        },
                    };
                })
                .filter(Boolean);

            const result = await Swal.fire({
                title: "تایید جابجایی",
                html: `آیا از جابجایی ${
                    selectedIds.length
                } نوبت به تاریخ ${jalaliDate} اطمینان دارید؟<br><br>
                      <small>نوبت‌های فعلی و زمان‌های جدید:<br>${timeMapping
                          .map(
                              (m) =>
                                  `${m.from.time} تاریخ ${m.from.date} → ${m.to.time} تاریخ ${m.to.date}`
                          )
                          .join("<br>")}</small>`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "بله",
                cancelButtonText: "خیر",
            });

            if (result.isConfirmed) {
                // Create a map of appointment IDs to their new times
                const appointmentTimeMap = selectedIds.reduce(
                    (map, id, index) => {
                        map[id] = sortedTimes[index];
                        return map;
                    },
                    {}
                );

                console.log(
                    "Sending appointment time map:",
                    appointmentTimeMap
                );

                // Send reschedule request with mapped times
                Livewire.dispatchTo(
                    "mc.panel.turn.schedule.appointments-list",
                    "rescheduleAppointment",
                    {
                        appointmentIds: selectedIds,
                        newDate: date,
                        selectedTime: sortedTimes[0], // Assuming the first selected time is the new one for multiple
                        isMultiple: true, // Add flag to indicate multiple appointments
                    }
                );

                // Close the modal after sending the request
                window.dispatchEvent(
                    new CustomEvent("close-modal", {
                        detail: {
                            name: "reschedule-modal",
                        },
                    })
                );
            }
        } catch (error) {
            console.error("Error in handleDayClick:", error);
            Swal.fire({
                title: "خطا",
                text: "خطایی در دریافت اطلاعات رخ داد. لطفاً دوباره تلاش کنید.",
                icon: "error",
                confirmButtonText: "باشه",
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

// Event listeners for modal management (outside of livewire:initialized)
window.addEventListener("open-modal", (event) => {
    const modalId = event.detail.name;
    const appointmentId = event.detail.appointmentId || null;

    console.log("Open modal event received:", { modalId, appointmentId });

    if (modalId === "reschedule-modal") {
        console.debug(
            "Opening reschedule-modal with appointmentId:",
            appointmentId
        );
        const clinicId = localStorage.getItem("selectedClinicId") || "default";
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
