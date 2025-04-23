document.addEventListener("DOMContentLoaded", function () {
    const calendarBody = document.getElementById("calendar-body");
    const today = moment().startOf("day").locale("fa").format("jYYYY/jMM/jDD");
    const selectedDateSpan = document.querySelector(
        ".turning_selectDate__MLRSb span:first-child"
    );
    const calendarButton = document.querySelector(
        ".selectDate_datepicker__xkZeS"
    );
    const calendarModal = document.getElementById("miniCalendarModal");
    let modalInstance = null;

    if (calendarButton) {
        calendarButton.onclick = null;
        calendarButton.removeEventListener("click", handleCalendarButtonClick);

        function handleCalendarButtonClick(e) {
            e.preventDefault();
            e.stopPropagation();

            console.log("Calendar button clicked"); // لاگ برای کلیک دکمه

            if (!modalInstance) {
                modalInstance = new bootstrap.Modal(calendarModal, {
                    backdrop: "static",
                    keyboard: false,
                });
            }

            const existingBackdrops =
                document.querySelectorAll(".modal-backdrop");
            existingBackdrops.forEach((backdrop) => backdrop.remove());
            document.body.classList.remove("modal-open");

            modalInstance.show();
        }

        calendarButton.addEventListener("click", handleCalendarButtonClick);
        if (selectedDateSpan) selectedDateSpan.textContent = today;
    }

    function isLeapYear(jYear) {
        const cyclePosition = jYear % 33;
        return [1, 5, 9, 13, 17, 22, 26, 30].includes(cyclePosition);
    }

    function generateCalendar(year, month) {
        calendarBody.innerHTML = "";
        const firstDayOfMonth = moment(
            `${year}/${month}/01`,
            "jYYYY/jMM/jDD"
        ).locale("fa");
        const daysInMonth = firstDayOfMonth.jDaysInMonth();
        let firstDayWeekday = firstDayOfMonth.weekday();
        const today = moment().locale("fa");

        console.log(
            `Generating calendar for ${year}/${month}, first weekday: ${firstDayWeekday}`
        );

        for (let i = 0; i < firstDayWeekday; i++) {
            const emptyDay = document.createElement("div");
            emptyDay.classList.add("calendar-day", "empty");
            calendarBody.appendChild(emptyDay);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const currentDay = firstDayOfMonth.clone().add(day - 1, "days");
            const dayElement = document.createElement("div");
            dayElement.classList.add("calendar-day");
            dayElement.setAttribute(
                "data-date",
                currentDay.format("jYYYY/jMM/jDD")
            );

            if (currentDay.day() === 5) {
                console.log(
                    `Friday detected: ${currentDay.format("jYYYY/jMM/jDD")}`
                );
                dayElement.classList.add("friday");
            }

            if (currentDay.isSame(today, "day")) {
                dayElement.classList.add("today");
            }

            dayElement.innerHTML = `<span>${currentDay.format("jD")}</span>`;

      dayElement.addEventListener("click", function () {
          const selectedDate = this.getAttribute("data-date");
          console.log("Selected date (Jalali):", selectedDate);

          // تبدیل تاریخ جلالی به میلادی
          let gregorianDate;
          try {
              const jalaliMoment = moment(selectedDate, "jYYYY/jMM/jDD");
              gregorianDate = jalaliMoment.toDate();
              gregorianDate = moment(gregorianDate).format("YYYY-MM-DD");
              console.log("Converted to Gregorian:", gregorianDate);
          } catch (error) {
              console.error("Error converting Jalali to Gregorian:", error);
              return;
          }

          // آپدیت span
          if (selectedDateSpan) {
              selectedDateSpan.textContent = selectedDate;
              console.log("Span updated with:", selectedDate);
          }

          // بستن مودال
          if (modalInstance) modalInstance.hide();
          $("#miniCalendarModal").modal("hide");
          console.log("Modal closed");

          // ارسال رویداد به Livewire
          if (typeof Livewire !== "undefined") {
              Livewire.dispatch("updateSelectedDate", { date: gregorianDate });
              console.log(
                  "Livewire event dispatched: updateSelectedDate",
                  gregorianDate
              );
              // ارسال رویداد برای آپدیت تقویم ردیفی
              $(document).trigger("updateCalendarRow", gregorianDate);
          } else {
              console.error("Livewire is not defined");
          }

          setTimeout(() => {
              const existingBackdrops =
                  document.querySelectorAll(".modal-backdrop");
              existingBackdrops.forEach((backdrop) => backdrop.remove());
              document.body.classList.remove("modal-open");
              console.log("Backdrops cleared");
          }, 300);
      });

            calendarBody.appendChild(dayElement);
        }
    }

    function populateSelectBoxes() {
        const yearSelect = document.getElementById("year");
        const monthSelect = document.getElementById("month");
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

        yearSelect.addEventListener("change", function () {
            generateCalendar(
                parseInt(yearSelect.value),
                parseInt(monthSelect.value)
            );
        });

        monthSelect.addEventListener("change", function () {
            generateCalendar(
                parseInt(yearSelect.value),
                parseInt(monthSelect.value)
            );
        });
    }

    document
        .getElementById("prev-month")
        .addEventListener("click", function () {
            const yearSelect = document.getElementById("year");
            const monthSelect = document.getElementById("month");
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

    document
        .getElementById("next-month")
        .addEventListener("click", function () {
            const yearSelect = document.getElementById("year");
            const monthSelect = document.getElementById("month");
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

    populateSelectBoxes();
    generateCalendar(moment().jYear(), moment().jMonth() + 1);
});
