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

    // نمایش لودینگ هنگام باز شدن مودال
    const loadingOverlay = modalScope.querySelector("#loading-overlay");
    if (loadingOverlay) {
        loadingOverlay.style.display = "flex";
    }
    if (calendarBody) {
        calendarBody.style.display = "none"; // مخفی کردن تقویم تا لود کامل
    }

    // اطمینان از وجود متغیرهای سراسری
    window.selectedAppointmentDates = window.selectedAppointmentDates || [];
    window.selectedAppointmentIds = window.selectedAppointmentIds || [];
    window.selectedSingleAppointmentId =
        window.selectedSingleAppointmentId || null;

    // تابع جمع‌آوری اطلاعات نوبت‌های انتخاب‌شده
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
        console.debug("Collected appointments:", {
            ids: window.selectedAppointmentIds,
            dates: window.selectedAppointmentDates,
        });
    }

    async function fetchCalendarData(year, month) {
        try {
            console.log(
                "Fetching calendar data for year:",
                year,
                "month:",
                month
            );

            // دریافت داده‌های تعطیلات
            let holidays = window.holidays || [];
            if (
                typeof Livewire !== "undefined" &&
                typeof Livewire.dispatch === "function"
            ) {
                console.log("Updating Livewire properties...");
                await Promise.all([
                    Livewire.dispatch("set", {
                        component: "dr.panel.turn.schedule.appointments-list",
                        property: "calendarYear",
                        value: year,
                    }),
                    Livewire.dispatch("set", {
                        component: "dr.panel.turn.schedule.appointments-list",
                        property: "calendarMonth",
                        value: month,
                    }),
                ]);
                holidays = window.holidays || holidays;
            } else {
                console.warn("Livewire.dispatch is not available");
            }

            // دریافت داده‌های نوبت‌ها از AJAX
            if (!appointmentsCountUrl) {
                console.error("Appointments count URL is not defined");
                if (loadingOverlay) loadingOverlay.style.display = "none";
                if (calendarBody) calendarBody.style.display = "grid";
                return { holidays, appointments: [] };
            }

            const appointments = await new Promise((resolve) => {
                $.ajax({
                    url: appointmentsCountUrl,
                    method: "GET",
                    data: {
                        selectedClinicId:
                            localStorage.getItem("selectedClinicId"),
                        year: year,
                        month: month,
                    },
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    success: function (response) {
                        if (response.status) {
                            const formattedAppointments = (
                                response.data || []
                            ).map((item) => ({
                                date: item.appointment_date,
                                count: item.appointment_count,
                            }));
                            console.log(
                                "Appointments data fetched:",
                                formattedAppointments
                            );
                            resolve(formattedAppointments);
                        } else {
                            console.error(
                                "Error fetching appointments:",
                                response.message
                            );
                            resolve([]);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(
                            "AJAX Error:",
                            status,
                            error,
                            xhr.responseText
                        );
                        resolve([]);
                    },
                    complete: function () {
                        // لودینگ در generateCalendar مدیریت می‌شود
                    },
                });
            });

            console.log("Returning calendar data:", { holidays, appointments });
            return { holidays, appointments };
        } catch (error) {
            console.error("Error in fetchCalendarData:", error);
            if (loadingOverlay) loadingOverlay.style.display = "none";
            if (calendarBody) calendarBody.style.display = "grid";
            return { holidays: window.holidays || [], appointments: [] };
        }
    }

    // تابع تولید تقویم
    async function generateCalendar(year, month) {
        if (!calendarBody) {
            console.error("Cannot generate calendar: Calendar body is missing");
            if (loadingOverlay) loadingOverlay.style.display = "none";
            return;
        }

        calendarBody.innerHTML = "";
        const firstDayOfMonth = moment(
            `${year}/${month}/01`,
            "jYYYY/jMM/jDD"
        ).locale("fa");
        const daysInMonth = firstDayOfMonth.jDaysInMonth();
        let firstDayWeekday = firstDayOfMonth.weekday();
        const today = moment().locale("fa");

        // دریافت داده‌های تعطیلات و نوبت‌ها
        const { holidays, appointments } = await fetchCalendarData(year, month);

        // مخفی کردن لودینگ و نمایش تقویم پس از لود کامل داده‌ها
        if (loadingOverlay) {
            loadingOverlay.style.display = "none";
        }
        if (calendarBody) {
            calendarBody.style.display = "grid";
        }

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
            const formattedJalaliDate = currentDay.format("jD jMMMM jYYYY");
            const gregorianDate = currentDay.toDate();
            const gregorianString = moment(gregorianDate).format("Y-MM-DD");

            const dayElement = document.createElement("div");
            dayElement.classList.add("calendar-day");
            dayElement.setAttribute("data-date", jalaliDate);
            dayElement.setAttribute("data-gregorian", gregorianString);

            // بررسی تعطیلات
            const isHoliday = holidays.includes(gregorianString);
            if (isHoliday) {
                dayElement.classList.add("holiday");
            }

            // بررسی نوبت‌ها
            const appointmentData = appointments.find(
                (appt) => appt.date === gregorianString
            );
            const appointmentCount = appointmentData
                ? appointmentData.count
                : 0;
            if (appointmentCount > 0) {
                dayElement.classList.add("has-appointment");
            }

            // ایجاد محتوای روز با تولتیپ
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

            if (currentDay.day() === 5) {
                dayElement.classList.add("friday");
            }
            if (currentDay.isSame(today, "day")) {
                dayElement.classList.add("today");
            }

            dayElement.addEventListener("click", function () {
                document
                    .querySelectorAll(".calendar-day")
                    .forEach((el) => el.classList.remove("selected"));
                this.classList.add("selected");

                const selectedGregorianDate =
                    this.getAttribute("data-gregorian");
                const selectedJalaliDate = formattedJalaliDate;

                // جمع‌آوری اطلاعات نوبت‌ها
                collectSelectedAppointments();

                // بررسی وجود نوبت‌های انتخاب‌شده
                if (window.selectedAppointmentDates.length === 0) {
                    const errorAlert = document.getElementById(
                        "reschedule-error-alert"
                    );
                    if (errorAlert) {
                        console.debug(
                            "No appointments selected, showing error alert"
                        );
                        window.openXAlert("reschedule-error-alert");
                    } else {
                        console.error(
                            "Error alert element 'reschedule-error-alert' not found"
                        );
                    }
                    return;
                }

                // ساخت پیام تأیید با تاریخ قبلی
                const oldDate = window.selectedAppointmentDates[0];
                const formattedOldDate = moment(oldDate, "jYYYY/jMM/jDD")
                    .locale("fa")
                    .format("jD jMMMM jYYYY");
                const message = `آیا می‌خواهید نوبت‌ها از تاریخ ${formattedOldDate} به تاریخ ${selectedJalaliDate} منتقل شوند؟`;

                // به‌روزرسانی پیام در الرت
                const alertElement = document.getElementById(
                    "reschedule-confirm-alert"
                );
                if (alertElement) {
                    let messageElement =
                        alertElement.querySelector(".x-alert__message");
                    if (!messageElement) {
                        console.warn(
                            "Alert message element '.x-alert__message' not found, creating one"
                        );
                        messageElement = document.createElement("p");
                        messageElement.classList.add("x-alert__message");
                        const bodyElement =
                            alertElement.querySelector(".x-alert__body") ||
                            alertElement;
                        bodyElement.appendChild(messageElement);
                    }
                    messageElement.textContent = message;
                    console.debug("Alert message set to:", message);

                    // به‌روزرسانی عنوان الرت
                    const titleElement =
                        alertElement.querySelector(".x-alert__title");
                    if (titleElement) {
                        titleElement.textContent = "تأیید جابجایی نوبت";
                        console.debug("Alert title set to: تأیید جابجایی نوبت");
                    }

                    // پاک کردن محتوای اسلات اضافی
                    const slotContent = alertElement.querySelector(
                        ".x-alert__body > :not(.x-alert__title):not(.x-alert__message)"
                    );
                    if (slotContent) {
                        slotContent.remove();
                        console.debug("Removed extra slot content from alert");
                    }

                    // تنظیم عملکرد دکمه تأیید
                    const confirmButton = alertElement.querySelector(
                        ".x-alert__button--confirm"
                    );
                    if (confirmButton) {
                        const newConfirmButton = confirmButton.cloneNode(true);
                        confirmButton.parentNode.replaceChild(
                            newConfirmButton,
                            confirmButton
                        );

                        newConfirmButton.addEventListener("click", function () {
                            if (window.selectedAppointmentIds.length === 0) {
                                const errorAlert = document.getElementById(
                                    "reschedule-error-alert"
                                );
                                if (errorAlert) {
                                    window.openXAlert("reschedule-error-alert");
                                }
                                return;
                            }

                            console.debug(
                                "Calling Livewire updateAppointmentDate:",
                                {
                                    component:
                                        "dr.panel.turn.schedule.appointments-list",
                                    method: "updateAppointmentDate",
                                    params: [
                                        window.selectedAppointmentIds,
                                        selectedGregorianDate,
                                    ],
                                }
                            );

                            // فراخوانی مستقیم به کامپوننت
                            Livewire.dispatchTo(
                                "dr.panel.turn.schedule.appointments-list",
                                "call",
                                {
                                    method: "updateAppointmentDate",
                                    params: [
                                        window.selectedAppointmentIds,
                                        selectedGregorianDate,
                                    ],
                                }
                            );
                            // اضافه کردن رویداد rescheduleAppointment
                            Livewire.dispatch("rescheduleAppointment", [
                                window.selectedAppointmentIds,
                                selectedGregorianDate,
                            ]);

                            window.closeXAlert("reschedule-confirm-alert");
                            window.closeXModal("reschedule-modal");
                        });
                    } else {
                        console.error(
                            "Confirm button '.x-alert__button--confirm' not found"
                        );
                    }

                    window.openXAlert("reschedule-confirm-alert");
                } else {
                    console.error(
                        "Alert element 'reschedule-confirm-alert' not found"
                    );
                }
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
            if (loadingOverlay) loadingOverlay.style.display = "flex";
            if (calendarBody) calendarBody.style.display = "none";
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
            if (loadingOverlay) loadingOverlay.style.display = "flex";
            if (calendarBody) calendarBody.style.display = "none";
            generateCalendar(currentYear, currentMonth);
        });
    }

    // جمع‌آوری اولیه نوبت‌ها
    collectSelectedAppointments();

    populateSelectBoxes();

    // تولید تقویم با تأخیر کوچک برای اطمینان از آماده بودن DOM
    setTimeout(() => {
        generateCalendar(moment().jYear(), moment().jMonth() + 1);
    }, 0);
}

// اجرای تقویم وقتی مودال باز می‌شود
document.addEventListener("livewire:initialized", () => {
    window.addEventListener("openXModal", (event) => {
        const modalId = event.detail.id;
        const appointmentId = event.detail.appointmentId || null;

        if (modalId === "reschedule-modal") {
            window.selectedSingleAppointmentId = appointmentId;
            initializeRescheduleCalendar();
        }
    });

    window.addEventListener("showModal", (event) => {
        if (event.detail === "reschedule-modal") {
            window.selectedSingleAppointmentId = null;
            initializeRescheduleCalendar();
        }
    });
});
