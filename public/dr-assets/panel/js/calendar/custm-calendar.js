    document.addEventListener("DOMContentLoaded", function() {
      const calendarBody = document.getElementById("calendar-body");
      const today = moment().startOf("day").locale("fa").format("jYYYY/jMM/jDD");
      const selectedDateSpan = document.querySelector(
        ".turning_selectDate__MLRSb span:first-child"
      );
      const calendarButton = document.querySelector(
        ".selectDate_datepicker__xkZeS"
      );
      const calendarModal = document.getElementById("miniCalendarModal");
      calendarButton.onclick = null;
      let modalInstance = null;
      calendarButton.removeEventListener("click", handleCalendarButtonClick);

      function handleCalendarButtonClick(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!modalInstance) {
          modalInstance = new bootstrap.Modal(calendarModal, {
            backdrop: "static",
            keyboard: false,
          });
        }

        const existingBackdrops = document.querySelectorAll(".modal-backdrop");
        existingBackdrops.forEach((backdrop) => backdrop.remove());
        document.body.classList.remove("modal-open");

        modalInstance.show();
      }

      calendarButton.addEventListener("click", handleCalendarButtonClick);
      selectedDateSpan.textContent = today;

      function generateCalendar(year, month) {
        calendarBody.innerHTML = "";

        const firstDayOfMonth = moment(
          `${year}/${month}/01`,
          "jYYYY/jMM/jDD"
        ).locale("fa");
        const daysInMonth = firstDayOfMonth.jDaysInMonth();
        let firstDayWeekday = firstDayOfMonth.weekday();
        const today = moment().locale("fa");

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
            dayElement.classList.add("friday");
          }
          if (currentDay.isSame(today, "day")) {
            dayElement.classList.add("active");
          }

          dayElement.innerHTML = `<span>${currentDay.format("jD")}</span>`;

          dayElement.addEventListener("click", function() {
            const selectedDate = this.getAttribute("data-date");
            if (modalInstance) {
              modalInstance.hide();
            }
            $("#miniCalendarModal").modal("hide");

            selectedDateSpan.textContent = selectedDate;

            setTimeout(() => {
              const existingBackdrops =
                document.querySelectorAll(".modal-backdrop");
              existingBackdrops.forEach((backdrop) => backdrop.remove());
              document.body.classList.remove("modal-open");
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

        yearSelect.addEventListener("change", function() {
          generateCalendar(
            parseInt(yearSelect.value),
            parseInt(monthSelect.value)
          );
        });

        monthSelect.addEventListener("change", function() {
          generateCalendar(
            parseInt(yearSelect.value),
            parseInt(monthSelect.value)
          );
        });
      }


      document
        .getElementById("prev-month")
        .addEventListener("click", function() {
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

      // دکمه ماه بعد
      document
        .getElementById("next-month")
        .addEventListener("click", function() {
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

      // اجرای اولیه
      populateSelectBoxes();
      generateCalendar(moment().jYear(), moment().jMonth() + 1);
    });