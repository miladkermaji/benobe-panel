// تابع برای بررسی وجود المنت
function ensureRescheduleElementExists(selector, errorMessage, scope = document) {
    const element = scope.querySelector(selector);
    if (!element) {
        console.warn(errorMessage);
        return null;
    }
    return element;
}

// تابع اصلی تقویم
function initializeRescheduleCalendar() {
    // پیدا کردن محدوده مودال
    const modalScope = document.querySelector("#reschedule-modal") || document;

    const calendarBody = ensureRescheduleElementExists(
        "#reschedule-calendar-body",
        "Calendar body not found",
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

    if (!calendarBody || !yearSelect || !monthSelect || !prevMonthBtn || !nextMonthBtn) return;

    // تابع برای دریافت تعطیلات و نوبت‌ها از سرور
    async function fetchCalendarData(year, month) {
        const jalaliDate = `${year}/${String(month).padStart(2, '0')}/01`;
        const gregorianDate = moment(jalaliDate, 'jYYYY/jMM/jDD').locale('fa').format('YYYY-MM-DD');
        
        // فراخوانی متدهای Livewire برای دریافت تعطیلات و نوبت‌ها
        const holidays = await Livewire.dispatch('call', ['getHolidays']).then(response => response[0] || []);
        const appointments = await Livewire.dispatch('call', ['getAppointmentsByDateSpecial', gregorianDate]).then(response => response[0] || []);

        return { holidays, appointments };
    }

    // تابع برای بررسی سال کبیسه
    function isLeapYear(jYear) {
        const cyclePosition = jYear % 33;
        return [1, 5, 9, 13, 17, 22, 26, 30].includes(cyclePosition);
    }

    // تابع تولید تقویم
    async function generateCalendar(year, month) {
        if (!calendarBody) return;

        calendarBody.innerHTML = "";
        const firstDayOfMonth = moment(`${year}/${month}/01`, "jYYYY/jMM/jDD").locale("fa");
        const daysInMonth = firstDayOfMonth.jDaysInMonth();
        let firstDayWeekday = firstDayOfMonth.weekday();
        const today = moment().locale("fa");

        // دریافت تعطیلات و نوبت‌ها
        const { holidays, appointments } = await fetchCalendarData(year, month);

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
            const gregorianDate = currentDay.format("YYYY-MM-DD");
            const dayElement = document.createElement("div");
            dayElement.classList.add("calendar-day");
            dayElement.setAttribute("data-date", jalaliDate);
            dayElement.setAttribute("data-gregorian", gregorianDate);

            // بررسی وضعیت روز
            if (currentDay.day() === 5) {
                dayElement.classList.add("friday");
            }
            if (currentDay.isSame(today, "day")) {
                dayElement.classList.add("today");
            }
            if (holidays.includes(gregorianDate)) {
                dayElement.classList.add("holiday");
            }
            if (appointments.some(appt => appt.appointment_date === gregorianDate)) {
                dayElement.classList.add("has-appointment");
            }

            dayElement.innerHTML = `<span>${currentDay.format("jD")}</span>`;

            // افزودن رویداد کلیک برای نمایش CustomAlert
            dayElement.addEventListener("click", async function () {
                if (this.classList.contains("empty") || this.classList.contains("holiday")) return;

                // علامت‌گذاری روز انتخاب‌شده
                document.querySelectorAll(".calendar-day").forEach(el => el.classList.remove("selected"));
                this.classList.add("selected");

                const selectedGregorianDate = this.getAttribute("data-gregorian");
                const selectedJalaliDate = this.getAttribute("data-date");

                // دریافت نوبت‌های انتخاب‌شده
                const selectedAppointmentIds = @this.rescheduleAppointmentIds.length > 0 
                    ? @this.rescheduleAppointmentIds 
                    : [@this.rescheduleAppointmentId];

                if (!selectedAppointmentIds || selectedAppointmentIds.length === 0) {
                    Swal.fire({
                        title: 'خطا',
                        text: 'هیچ نوبت انتخاب‌شده‌ای یافت نشد.',
                        icon: 'error',
                        confirmButtonText: 'باشه'
                    });
                    return;
                }

                // دریافت تاریخ‌های قدیمی از نوبت‌ها
                const appointments = @this.appointments;
                const oldDates = selectedAppointmentIds
                    .map(id => {
                        const appt = appointments.find(a => a.id === id);
                        return appt ? moment(appt.appointment_date).locale('fa').format('jYYYY/jMM/jDD') : null;
                    })
                    .filter(date => date)
                    .join('، ');

                // نمایش CustomAlert برای تأیید جابجایی
                const alertId = 'reschedule-confirm-alert';
                const alertHtml = `
                    <x-custom-alert 
                        id="${alertId}" 
                        type="warning" 
                        title="تأیید جابجایی نوبت" 
                        message="آیا می‌خواهید ${selectedAppointmentIds.length} نوبت از تاریخ ${oldDates} به تاریخ ${selectedJalaliDate} جابجا شوند؟" 
                        size="md" 
                        :show="true">
                        <div class="x-alert__footer">
                            <button type="button" class="x-alert__button x-alert__button--confirm" onclick="confirmReschedule('${selectedGregorianDate}', ${JSON.stringify(selectedAppointmentIds)})">
                                تأیید
                            </button>
                            <button type="button" class="x-alert__button x-alert__button--cancel" data-x-alert-close>
                                لغو
                            </button>
                        </div>
                    </x-custom-alert>
                `;

                // افزودن CustomAlert به DOM
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = alertHtml;
                document.body.appendChild(tempContainer);

                // باز کردن CustomAlert
                window.openXAlert(alertId);
            });

            calendarBody.appendChild(dayElement);
        }
    }

    // تابع پر کردن سلکت‌باکس‌ها
    function populateSelectBoxes() {
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
            "فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور",
            "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"
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
            generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
        });

        monthSelect.addEventListener("change", () => {
            generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
        });
    }

    // مدیریت دکمه‌های قبلی و بعدی
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
        generateCalendar(currentYear, currentMonth);
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
        generateCalendar(currentYear, currentMonth);
    });

    // تابع تأیید جابجایی
    window.confirmReschedule = function(selectedDate, appointmentIds) {
        // بستن CustomAlert
        window.closeXAlert('reschedule-confirm-alert');

        // تنظیم تاریخ جدید و فراخوانی متد جابجایی
        @this.set('rescheduleNewDate', selectedDate);
        @this.call('updateAppointmentDate', appointmentIds);

        // حذف المنت CustomAlert از DOM
        const alertElement = document.getElementById('reschedule-confirm-alert');
        if (alertElement) {
            alertElement.remove();
        }
    };

    // اجرای اولیه
    populateSelectBoxes();
    generateCalendar(moment().jYear(), moment().jMonth() + 1);
}

// اجرای تقویم وقتی مودال باز می‌شود
document.addEventListener("livewire:initialized", () => {
    window.addEventListener("openXModal", (event) => {
        const modalId = event.detail.id;
        if (modalId === "reschedule-modal") {
            setTimeout(() => {
                initializeRescheduleCalendar();
            }, 100);
        }
    });

    Livewire.on("showModal", (modalId) => {
        if (modalId === "reschedule-modal") {
            setTimeout(() => {
                initializeRescheduleCalendar();
            }, 100);
        }
    });
});