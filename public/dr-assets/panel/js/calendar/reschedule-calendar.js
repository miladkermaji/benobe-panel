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

    // تابع تولید تقویم
    function generateCalendar(year, month) {
        if (!calendarBody) {
            console.error("Cannot generate calendar: Calendar body is missing");
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
            const jalaliDate = currentDay.format("jYYYY/jMM/jDD");
            const formattedJalaliDate = currentDay.format("jD jMMMM jYYYY"); // مثلاً: 10 اردیبهشت 1404
            const gregorianDate = currentDay.toDate();
            const gregorianString = moment(gregorianDate).format("YYYY-MM-DD");
            dayElement.setAttribute("data-date", jalaliDate);
            dayElement.setAttribute("data-gregorian", gregorianString);

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
                const oldDate = window.selectedAppointmentDates[0]; // فرض می‌کنیم حداقل یه تاریخ داریم
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
                        // حذف شنونده‌های قبلی
                        const newConfirmButton = confirmButton.cloneNode(true);
                        confirmButton.parentNode.replaceChild(
                            newConfirmButton,
                            confirmButton
                        );

                        // داخل تابع کلیک روز تقویم (خط ~230)
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
                                "Dispatching Livewire call to AppointmentsList.updateAppointmentDate:",
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

                            // فراخوانی مستقیم به کامپوننت AppointmentsList
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

    // جمع‌آوری اولیه نوبت‌ها
    collectSelectedAppointments();

    populateSelectBoxes();
    generateCalendar(moment().jYear(), moment().jMonth() + 1);
}

// اجرای تقویم وقتی مودال باز می‌شه
document.addEventListener("livewire:initialized", () => {
    window.addEventListener("openXModal", (event) => {
        const modalId = event.detail.id;
        const appointmentId = event.detail.appointmentId || null;

        if (modalId === "reschedule-modal") {
            window.selectedSingleAppointmentId = appointmentId;
            setTimeout(() => {
                initializeRescheduleCalendar();
            }, 100);
        }
    });

    Livewire.on("showModal", (modalId) => {
        if (modalId === "reschedule-modal") {
            window.selectedSingleAppointmentId = null;
            setTimeout(() => {
                initializeRescheduleCalendar();
            }, 100);
        }
    });
});
