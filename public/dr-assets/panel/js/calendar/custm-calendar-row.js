$(document).ready(function () {
    moment.locale("en");
    let currentDate = moment().startOf("day").subtract(9, "days");
    const calendar = $("#calendar");
    const loadingOverlay = $("#calendar-loading");
    let isAnimating = false;
    let appointmentsData = [];
    let workingDays = [];
    let calendarDays = 60;
    let appointmentSettings = [];
    const today = moment().startOf("day");
    let isDragging = false;
    let startX = 0;
    let scrollLeft = 0;
    let velocity = 0;
    let lastX = 0;
    let lastTime = 0;
    const visibleDays = 18;
    let selectedDate = null;

    // چک کردن وجود المنت‌ها
    if (!calendar.length) {
        console.error("Calendar element (#calendar) not found in DOM");
    }
    if (!loadingOverlay.length) {
        console.error("Loading overlay (#calendar-loading) not found in DOM");
    }

    // رویداد Livewire برای آپدیت DOM
    document.addEventListener("livewire:updated", function () {
        setTimeout(() => {
            loadingOverlay.hide();
            calendar.show();
        }, 100);
    });

    // تایم‌اوت اضطراری برای لودینگ
    function ensureLoadingHidden() {
        setTimeout(() => {
            if (loadingOverlay.is(":visible")) {
                loadingOverlay.hide();
                calendar.show();
            }
        }, 5000);
    }

    // رویداد آپدیت تقویم ردیفی
    $(document).on("updateCalendarRow", function (e) {
        const selectedDate = e.originalEvent.detail;
        if (!moment(selectedDate, "YYYY-MM-DD", true).isValid()) {
            console.warn(
                "Invalid selectedDate, using currentDate:",
                currentDate.format("YYYY-MM-DD")
            );
            fetchAppointmentsCount();
            return;
        }
        currentDate = moment(selectedDate, "YYYY-MM-DD")
            .startOf("day")
            .subtract(9, "days");
        fetchAppointmentsCount();
    });

    // گوش دادن به رویداد لغو نوبت
    Livewire.on("appointments-cancelled", (event) => {
        fetchAppointmentsCount();
    });
    Livewire.on("appointments-rescheduled", (event) => {
        fetchAppointmentsCount();
    });
    Livewire.on("visited", (event) => {
        fetchAppointmentsCount();
    });

    function fetchAppointmentsCount() {
        if (!calendar.length) {
            console.error("Calendar element not found, aborting AJAX");
            return;
        }
        loadingOverlay.show();
        calendar.hide();
        ensureLoadingHidden();

        $.ajax({
            url: appointmentsCountUrl,
            method: "GET",
            data: {
                selectedClinicId: localStorage.getItem("selectedClinicId"),
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.status) {
                    appointmentsData = response.data || [];
                    workingDays = response.working_days || [];
                    calendarDays = response.calendar_days || 60;
                    appointmentSettings = response.appointment_settings || [];
                    $("#calendar-error").hide();
                    loadCalendar();
                } else {
                    console.error(
                        "Error fetching appointments:",
                        response.message
                    );
                    $("#calendar-error").show();
                    loadingOverlay.hide();
                    calendar.show();
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error, xhr.responseText);
                $("#calendar-error").show();
                loadingOverlay.hide();
                calendar.show();
            },
            complete: function () {
                loadingOverlay.hide();
                calendar.show();
            },
        });
    }

    function loadCalendar() {
        if (!calendar.length) {
            console.error("Calendar element not found in DOM");
            return;
        }
        if (!moment(currentDate).isValid()) {
            console.warn(
                "Invalid currentDate, resetting to today:",
                moment().format("YYYY-MM-DD")
            );
            currentDate = moment().startOf("day").subtract(9, "days");
        }

        calendar.empty();
        let badgeCount = 0;
        let displayedDays = 0;
        let current = moment(currentDate);
        let i = 0;

        // دریافت روزهای خاص از پاسخ AJAX
        const specialDays = window.appointmentsData.special_days || [];

        while (displayedDays < visibleDays && i < calendarDays * 2) {
            const dayOfWeek = current.format("dddd").toLowerCase();
            const appointmentDate = current.format("YYYY-MM-DD");
            const isToday = current.isSame(today, "day");
            const isPast = current.isBefore(today, "day");
            const isWorkingDay = workingDays.includes(dayOfWeek);
            const isSelected = appointmentDate === selectedDate;
            const isHoliday =
                window.holidaysData && window.holidaysData.holidays
                    ? window.holidaysData.holidays.includes(appointmentDate)
                    : false;
            // بررسی آیا این روز خاص است
            const isSpecialDay = specialDays.includes(appointmentDate);

            if (isWorkingDay || isToday || isSpecialDay) {
                const persianDate = moment(current).locale("fa").format("dddd");
                const persianFormattedDate = moment(current)
                    .locale("fa")
                    .format("D MMMM YYYY");
                const appointment = appointmentsData.find((appt) => {
                    const apptDate = moment(appt.appointment_date, [
                        "YYYY-MM-DD",
                        "YYYY-MM-DD HH:mm:ss",
                    ]).format("YYYY-MM-DD");
                    return apptDate === appointmentDate;
                });

                let appointmentCount =
                    appointment && !isPast && appointment.appointment_count > 0
                        ? appointment.appointment_count
                        : 0;

                const badgeHtml =
                    appointmentCount > 0
                        ? `<span class="appointment-badge">${appointmentCount}</span>`
                        : "";
                const cardClass = isToday
                    ? "my-active"
                    : isSelected
                    ? "card-selected"
                    : "";
                const holidayClass = isHoliday ? "holiday-card" : "";
                const specialClass = isSpecialDay ? "special-day" : ""; // کلاس برای روزهای خاص

                const cardContent = `
                <div class="calendar-card btn btn-light ${cardClass} ${holidayClass} ${specialClass}" data-date="${appointmentDate}" style="--delay: ${displayedDays}">
                    ${badgeHtml}
                    <div class="day-name">${persianDate}</div>
                    <div class="date">${persianFormattedDate}</div>
                    ${isToday ? '<div class="current-day-icon"></div>' : ""}
                    ${
                        isSpecialDay
                            ? '<div class="special-day-icon"></div>'
                            : ""
                    }
                </div>
            `;

                const tooltipContent = isHoliday
                    ? "این روز تعطیل است"
                    : isSpecialDay
                    ? "این روز برنامه خاص دارد"
                    : "";

                const card = tooltipContent
                    ? `
                    <div x-tooltip id="tooltip-day-${appointmentDate}" data-trigger="hover" data-placement="top" class="x-tooltip">
                        <div class="x-tooltip__trigger">
                            ${cardContent}
                        </div>
                        <div class="x-tooltip__content" data-tooltip-id="tooltip-day-${appointmentDate}">
                            ${tooltipContent}
                        </div>
                    </div>
                `
                    : cardContent;

                calendar.append(card);
                if (appointmentCount > 0) badgeCount++;
                displayedDays++;
            }

            current.add(1, "days");
            i++;
        }

        if (displayedDays === 0) {
            console.warn(
                "No days displayed. Check workingDays, specialDays or appointmentsData."
            );
        }

        updateButtonState();
    }

    function updateButtonState() {
        const prevButton = $("#prevRow");
        const nextButton = $("#nextRow");
        const lastDate = moment(currentDate).add(visibleDays - 1, "days");

        prevButton.prop("disabled", false);
        const isNextDisabled = lastDate.isSameOrAfter(
            moment().add(calendarDays, "days"),
            "day"
        );
        nextButton.prop("disabled", isNextDisabled);
    }

    function animateAndLoadCalendar(direction) {
        if (isAnimating) return;
        isAnimating = true;
        const offset = direction === "nextRow" ? 5 : -5;
        const newDate = moment(currentDate).add(offset, "days").startOf("day");

        calendar.css({
            transition: "transform 0.4s ease-in-out, opacity 0.6s ease-in-out",
            transform:
                direction === "nextRow"
                    ? "translateX(50px)"
                    : "translateX(-50px)",
            opacity: 0.3,
        });

        setTimeout(() => {
            currentDate = newDate;
            loadCalendar();
            calendar.css({
                transform:
                    direction === "nextRow"
                        ? "translateX(-50px)"
                        : "translateX(50px)",
                opacity: 0.3,
            });
            setTimeout(() => {
                calendar.css({
                    transform: "translateX(0)",
                    opacity: 1,
                });
                setTimeout(() => {
                    calendar.css("transition", "");
                    isAnimating = false;
                }, 400);
            }, 50);
        }, 300);
    }

    calendar.on("mousedown touchstart", function (e) {
        isDragging = true;
        calendar.addClass("grabbing");
        startX =
            e.type === "touchstart"
                ? e.originalEvent.touches[0].pageX
                : e.pageX;
        scrollLeft = calendar.scrollLeft() || 0;
        velocity = 0;
        lastX = startX;
        lastTime = Date.now();
        e.preventDefault();
    });

    calendar.on("mousemove touchmove", function (e) {
        if (!isDragging) return;
        const x =
            e.type === "touchmove" ? e.originalEvent.touches[0].pageX : e.pageX;
        const deltaX = x - startX;
        calendar.scrollLeft(scrollLeft - deltaX);
        const currentTime = Date.now();
        const timeDiff = currentTime - lastTime;
        if (timeDiff > 0) {
            velocity = (x - lastX) / timeDiff;
        }
        lastX = x;
        lastTime = currentTime;
        e.preventDefault();
    });

    calendar.on("mouseup touchend", function () {
        isDragging = false;
        calendar.removeClass("grabbing");
        const inertiaDuration = 500;
        const inertiaDistance = velocity * inertiaDuration * 0.5;
        const currentScroll = calendar.scrollLeft();
        const targetScroll = currentScroll - inertiaDistance;
        calendar.animate(
            { scrollLeft: targetScroll },
            { duration: inertiaDuration, easing: "easeOutQuad" }
        );
    });

    calendar.on("mouseleave", function () {
        if (isDragging) {
            isDragging = false;
            calendar.removeClass("grabbing");
        }
    });

    calendar.on("click", ".calendar-card", function (e) {
        if (Math.abs(velocity) > 0.1) return;
        $(".calendar-card").not(".my-active").removeClass("card-selected");
        $(this).addClass("card-selected");
        const date = $(this).data("date");
        if (typeof Livewire !== "undefined") {
            Livewire.dispatch("updateSelectedDate", { date: date });
            selectedDate = date;
            loadCalendar();
        } else {
            console.error("Livewire is not defined");
        }
    });

    $("#nextRow").click(function () {
        if (!$(this).prop("disabled")) {
            animateAndLoadCalendar("nextRow");
        }
    });

    $("#prevRow").click(function () {
        if (!$(this).prop("disabled")) {
            animateAndLoadCalendar("prevRow");
        }
    });

    // رویداد Livewire برای اطمینان از ریست تقویم
    document.addEventListener("livewire:init", function () {
        fetchAppointmentsCount();
    });

    fetchAppointmentsCount();
});
