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

            console.log("window.holidaysData:", window.holidaysData);
            console.log("window.appointmentsData:", window.appointmentsData);

            const holidays = window.holidaysData.status
                ? window.holidaysData.holidays || []
                : [];
            const appointments = window.appointmentsData.status
                ? window.appointmentsData.data.map((item) => ({
                      date: item.date,
                      count: parseInt(item.count) || 0,
                  })) || []
                : [];

            console.log("Processed appointments:", appointments);

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
            console.log("Calendar is already rendering, skipping...");
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
        for (let day = 1; day <= daysInMonth; day++) {
            const currentDay = firstDayOfMonth.clone().add(day - 1, "days");
            const jalaliDate = currentDay.format("jYYYY/jMM/jDD");
            const gregorianDate = currentDay.toDate();
            const gregorianString = moment(gregorianDate).format("Y-MM-DD");

            const dayElement = document.createElement("div");
            dayElement.classList.add("calendar-day");
            dayElement.setAttribute("data-date", jalaliDate);
            dayElement.setAttribute("data-gregorian", gregorianString);

            const isHoliday = holidays.includes(gregorianString);
            if (isHoliday) {
                dayElement.classList.add("holiday");
            }

            const appointmentData = appointments.find(
                (appt) => appt.date === gregorianString
            );
            const appointmentCount = appointmentData
                ? appointmentData.count
                : 0;
            if (appointmentCount > 0) {
                dayElement.classList.add("has-appointment");
            }

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
                const gregorianDate = this.getAttribute("data-gregorian");
                const isPastDate = moment(gregorianDate).isBefore(
                    moment(),
                    "day"
                );
                const hasAppointment = appointmentCount > 0;

                if (isPastDate) {
                    // نمایش پیام برای روزهای گذشته
                    Livewire.dispatch("show-toastr", {
                        type: "warning",
                        message: "نمی‌توانید روزهای گذشته را تعطیل کنید.",
                    });
                    return;
                }

                if (hasAppointment) {
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
        console.log("calendarDataUpdated event received");
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
        console.log("holidayUpdated event received:", event);
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

function initializeTimepicker() {
    $(".timepicker-ui").each(function () {
        if (!$(this).data("timepicker-initialized")) {
            try {
                const options = {
                    clockType: "24h",
                    theme: "basic",
                    mobile: true,
                    enableScrollbar: true,
                    disableTimeRangeValidation: false,
                    autoClose: true,
                };
                const timepicker = new window.tui.TimepickerUI(this, options);
                timepicker.create();
                $(this).data("timepicker-initialized", true);
            } catch (e) {
                console.error("Error initializing timepicker:", e);
            }
        }
    });
}
initializeTimepicker();

// فراخوانی تایم‌پیکر بعد از باز شدن مودال
document.addEventListener("openXModal", (event) => {
    console.log("openXModal event received:", event.detail);
    const modalId = event.detail.id;
    if (modalId === "holiday-modal" || modalId === "calculator-modal") {
        setTimeout(() => {
            initializeTimepicker();
        }, 100);
    }
});

// اطمینان از اجرای تایم‌پیکر بعد از رندر اولیه
document.addEventListener("livewire:initialized", () => {
    // مدیریت باز شدن مودال‌ها
    Livewire.on("openXModal", (event) => {
        console.log("openXModal event received:", event);
        const modalId = event.id;
        const day = event.day;
        const index = event.index;

        if (modalId === "holiday-modal") {
            setTimeout(() => {
                initializeTimepicker();
            }, 100);
        } else if (
            modalId === "calculator-modal" &&
            day &&
            index !== undefined
        ) {
            console.log(
                "Opening calculator-modal with day:",
                day,
                "index:",
                index
            );
            // تنظیم داده‌های محاسبه‌گر
            Livewire.dispatchTo(
                "dr.panel.turn.schedule.special-days-appointment",
                "setCalculatorData",
                { day, index }
            );

            // دریافت زمان شروع و پایان
            const startTime = document
                .querySelector(
                    `input[data-day="${day}"][data-index="${index}"]`
                )
                ?.closest(".form-row")
                ?.querySelector(".start-time")?.value;
            const endTime = document
                .querySelector(
                    `input[data-day="${day}"][data-index="${index}"]`
                )
                ?.closest(".form-row")
                ?.querySelector(".end-time")?.value;

            if (!startTime || !endTime) {
                Livewire.dispatch("show-toastr", {
                    type: "error",
                    message: "لطفاً ابتدا زمان شروع و پایان را وارد کنید",
                });
                return;
            }

            Livewire.dispatchTo(
                "dr.panel.turn.schedule.special-days-appointment",
                "setCalculatorTimes",
                { startTime, endTime }
            );

            // باز کردن مودال
            window.openXModal(modalId);

            setTimeout(() => {
                initializeTimepicker();

                const $appointmentCount = $("#appointment-count");
                const $timeCount = $("#time-count");
                const $countRadio = $("#count-radio");
                const $timeRadio = $("#time-radio");

                const timeToMinutes = (time) => {
                    const [hours, minutes] = time.split(":").map(Number);
                    return hours * 60 + minutes;
                };
                const totalMinutes =
                    timeToMinutes(endTime) - timeToMinutes(startTime);

                if (totalMinutes <= 0) {
                    Livewire.dispatch("show-toastr", {
                        type: "error",
                        message: "زمان پایان باید بعد از زمان شروع باشد",
                    });
                    window.closeXModal(modalId);
                    return;
                }

                // دریافت مقادیر فعلی از Livewire
                Livewire.dispatchTo(
                    "dr.panel.turn.schedule.special-days-appointment",
                    "getCalculatorData",
                    {},
                    (response) => {
                        const currentCount = response.appointment_count;
                        const currentTime = response.time_per_appointment;

                        if (currentCount) {
                            $appointmentCount.val(currentCount);
                            $timeCount.val(
                                Math.round(totalMinutes / currentCount)
                            );
                        } else if (currentTime) {
                            $timeCount.val(currentTime);
                            $appointmentCount.val(
                                Math.round(totalMinutes / currentTime)
                            );
                        } else {
                            $appointmentCount.val("");
                            $timeCount.val("");
                        }
                    }
                );

                $appointmentCount.on("focus", function () {
                    $countRadio.prop("checked", true).trigger("change");
                    $timeRadio.prop("checked", false);
                    $appointmentCount.prop("disabled", false);
                    $timeCount.prop("disabled", true);
                    Livewire.dispatchTo(
                        "dr.panel.turn.schedule.special-days-appointment",
                        "setCalculationMode",
                        { mode: "count" }
                    );
                });

                $timeCount.on("focus", function () {
                    $timeRadio.prop("checked", true).trigger("change");
                    $countRadio.prop("checked", false);
                    $timeCount.prop("disabled", false);
                    $appointmentCount.prop("disabled", true);
                    Livewire.dispatchTo(
                        "dr.panel.turn.schedule.special-days-appointment",
                        "setCalculationMode",
                        { mode: "time" }
                    );
                });

                $appointmentCount.on("input", function () {
                    const count = parseInt($(this).val());
                    if (count && !isNaN(count) && count > 0) {
                        const timePerAppointment = Math.round(
                            totalMinutes / count
                        );
                        $timeCount.val(timePerAppointment);
                        Livewire.dispatchTo(
                            "dr.panel.turn.schedule.special-days-appointment",
                            "setCalculatorValues",
                            {
                                appointment_count: count,
                                time_per_appointment: timePerAppointment,
                            }
                        );
                    } else {
                        $timeCount.val("");
                        Livewire.dispatchTo(
                            "dr.panel.turn.schedule.special-days-appointment",
                            "setCalculatorValues",
                            {
                                appointment_count: null,
                                time_per_appointment: null,
                            }
                        );
                    }
                });

                $timeCount.on("input", function () {
                    const time = parseInt($(this).val());
                    if (time && !isNaN(time) && time > 0) {
                        const appointmentCount = Math.round(
                            totalMinutes / time
                        );
                        $appointmentCount.val(appointmentCount);
                        Livewire.dispatchTo(
                            "dr.panel.turn.schedule.special-days-appointment",
                            "setCalculatorValues",
                            {
                                time_per_appointment: time,
                                appointment_count: appointmentCount,
                            }
                        );
                    } else {
                        $appointmentCount.val("");
                        Livewire.dispatchTo(
                            "dr.panel.turn.schedule.special-days-appointment",
                            "setCalculatorValues",
                            {
                                appointment_count: null,
                                time_per_appointment: null,
                            }
                        );
                    }
                });

                $countRadio.on("change", function () {
                    if ($(this).is(":checked")) {
                        $appointmentCount.prop("disabled", false);
                        $timeCount.prop("disabled", true);
                        Livewire.dispatchTo(
                            "dr.panel.turn.schedule.special-days-appointment",
                            "setCalculationMode",
                            { mode: "count" }
                        );
                    }
                });

                $timeRadio.on("change", function () {
                    if ($(this).is(":checked")) {
                        $timeCount.prop("disabled", false);
                        $appointmentCount.prop("disabled", true);
                        Livewire.dispatchTo(
                            "dr.panel.turn.schedule.special-days-appointment",
                            "setCalculationMode",
                            { mode: "time" }
                        );
                    }
                });
            }, 100);
        } else {
            console.warn("Invalid parameters for openXModal:", event);
        }
    });

    // مدیریت بستن مودال
    Livewire.on("closeXModal", (event) => {
        console.log("closeXModal event received:", event);
        const modalId = event.id;
        window.closeXModal(modalId);
        if (modalId === "calculator-modal") {
            // پاک‌سازی رویدادهای ورودی
            $("#appointment-count").off("input focus");
            $("#time-count").off("input focus");
            $("#count-radio").off("change");
            $("#time-radio").off("change");
        }
    });

    // مدیریت رویدادهای Livewire برای محاسبه‌گر
    Livewire.on("close-calculator-modal", () => {
        console.log("close-calculator-modal event received");
        window.closeXModal("calculator-modal");
        const $button = $("#saveSelectionCalculator");
        if (
            $button.find(".loader").length &&
            $button.find(".button_text").length
        ) {
            toggleButtonLoading($button, false);
        }
    });
});

// تابع مدیریت لودینگ دکمه
function toggleButtonLoading($button, isLoading) {
    const $loader = $button.find(".loader");
    const $text = $button.find(".button_text");

    if (isLoading) {
        $loader.show();
        $text.hide();
        $button.prop("disabled", true);
    } else {
        $loader.hide();
        $text.show();
        $button.prop("disabled", false);
    }
}
