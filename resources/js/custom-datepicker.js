document.addEventListener("DOMContentLoaded", () => {
 const persianMonths = [
  "فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور",
  "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"
 ];

 function gregorianToJalali(gy, gm, gd) {
  const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
  let jy = (gy <= 1600) ? 0 : 979;
  gy -= (gy <= 1600) ? 621 : 1600;
  const gy2 = (gm > 2) ? (gy + 1) : gy;
  let days = (365 * gy) + Math.floor(gy / 4) - Math.floor(gy / 100) + Math.floor(gy2 / 400) + g_d_m[gm - 1] + gd - 1;
  jy += 33 * Math.floor(days / 12053);
  days %= 12053;
  jy += 4 * Math.floor(days / 1461);
  days %= 1461;
  if (days > 365) {
   jy += Math.floor((days - 1) / 365);
   days = (days - 1) % 365;
  }
  const jm = (days < 186) ? 1 + Math.floor(days / 31) : 7 + Math.floor((days - 186) / 30);
  const jd = 1 + ((days < 186) ? (days % 31) : ((days - 186) % 30));
  return [jy, jm, jd];
 }

 function isLeapYear(year) {
  return ((year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0));
 }

 function initializeDatepicker(input) {
  let mode = "days";
  const today = new Date();
  const jalaliToday = gregorianToJalali(today.getFullYear(), today.getMonth() + 1, today.getDate());
  let currentMonthIndex = jalaliToday[1] - 1;
  let currentYear = jalaliToday[0];
  const todayDay = jalaliToday[2];

  const container = document.createElement("div");
  container.className = "datepicker-container";
  input.parentNode.insertBefore(container, input);
  container.appendChild(input);

  const calendar = document.createElement("div");
  calendar.className = "calendar";
  calendar.id = `calendar-${input.id}`;
  container.appendChild(calendar);

  const header = document.createElement("div");
  header.className = "header";
  calendar.appendChild(header);

  const prev = document.createElement("button");
  prev.id = `prev-${input.id}`;
  prev.textContent = "‹";
  header.appendChild(prev);

  const monthYear = document.createElement("span");
  monthYear.id = `month-year-${input.id}`;
  header.appendChild(monthYear);

  const next = document.createElement("button");
  next.id = `next-${input.id}`;
  next.textContent = "›";
  header.appendChild(next);

  const daysContainer = document.createElement("div");
  daysContainer.className = "days";
  daysContainer.id = `days-${input.id}`;
  calendar.appendChild(daysContainer);

  const monthsContainer = document.createElement("div");
  monthsContainer.className = "months hidden";
  monthsContainer.id = `months-${input.id}`;
  calendar.appendChild(monthsContainer);

  const yearsContainer = document.createElement("div");
  yearsContainer.className = "years hidden";
  yearsContainer.id = `years-${input.id}`;
  calendar.appendChild(yearsContainer);

  input.addEventListener("click", (e) => {
   e.stopPropagation();
   calendar.classList.toggle("active");
   if (calendar.classList.contains("active")) {
    renderDays();
   }
  });

  document.addEventListener("click", (e) => {
   if (!calendar.contains(e.target) && e.target !== input) {
    calendar.classList.remove("active");
   }
  });

  function renderDays() {
   mode = "days";
   daysContainer.classList.remove("hidden");
   monthsContainer.classList.add("hidden");
   yearsContainer.classList.add("hidden");
   daysContainer.innerHTML = "";

   const daysInMonth = currentMonthIndex < 6 ? 31 : (currentMonthIndex === 11 ? (isLeapYear(currentYear) ? 30 : 29) : 30);
   const dayNames = ["ش", "ی", "د", "س", "چ", "پ", "ج"];
   dayNames.forEach((name) => {
    const dayName = document.createElement("div");
    dayName.className = "day-name";
    dayName.textContent = name;
    daysContainer.appendChild(dayName);
   });

   for (let day = 1; day <= daysInMonth; day++) {
    const dayElement = document.createElement("div");
    dayElement.className = `day ${day === todayDay && currentMonthIndex === jalaliToday[1] - 1 && currentYear === jalaliToday[0] ? "today" : ""}`;
    dayElement.textContent = day;
    dayElement.addEventListener("click", (e) => {
     e.stopPropagation();
     document.querySelectorAll(`#${daysContainer.id} .day`).forEach(d => d.classList.remove("selected"));
     dayElement.classList.add("selected");
     const persianDate = `${day} ${persianMonths[currentMonthIndex]} ${currentYear}`;
     input.value = persianDate;
     calendar.classList.remove("active");
    });
    daysContainer.appendChild(dayElement);
   }
   monthYear.textContent = `${persianMonths[currentMonthIndex]} ${currentYear}`;
  }

  function renderMonths() {
   mode = "months";
   daysContainer.classList.add("hidden");
   monthsContainer.classList.remove("hidden");
   yearsContainer.classList.add("hidden");
   monthsContainer.innerHTML = "";

   persianMonths.forEach((month, index) => {
    const monthElement = document.createElement("div");
    monthElement.className = "month";
    monthElement.textContent = month;
    monthElement.addEventListener("click", (e) => {
     e.stopPropagation();
     currentMonthIndex = index;
     document.querySelectorAll(`#${monthsContainer.id} .month`).forEach(m => m.classList.remove("selected"));
     monthElement.classList.add("selected");
     renderDays();
    });
    monthsContainer.appendChild(monthElement);
   });
   monthYear.textContent = `${currentYear}`;
  }

  function renderYears() {
   mode = "years";
   daysContainer.classList.add("hidden");
   monthsContainer.classList.add("hidden");
   yearsContainer.classList.remove("hidden");
   yearsContainer.innerHTML = "";

   const startYear = currentYear - 6;
   for (let year = startYear; year < startYear + 14; year++) {
    const yearElement = document.createElement("div");
    yearElement.className = "year";
    yearElement.textContent = year;
    yearElement.addEventListener("click", (e) => {
     e.stopPropagation();
     currentYear = year;
     document.querySelectorAll(`#${yearsContainer.id} .year`).forEach(y => y.classList.remove("selected"));
     yearElement.classList.add("selected");
     renderMonths();
    });
    yearsContainer.appendChild(yearElement);
   }
   monthYear.textContent = "انتخاب سال";
  }

  prev.addEventListener("click", (e) => {
   e.stopPropagation();
   if (mode === "days") {
    currentMonthIndex--;
    if (currentMonthIndex < 0) {
     currentMonthIndex = 11;
     currentYear--;
    }
    renderDays();
   } else if (mode === "years") {
    currentYear -= 14;
    renderYears();
   }
  });

  next.addEventListener("click", (e) => {
   e.stopPropagation();
   if (mode === "days") {
    currentMonthIndex++;
    if (currentMonthIndex > 11) {
     currentMonthIndex = 0;
     currentYear++;
    }
    renderDays();
   } else if (mode === "years") {
    currentYear += 14;
    renderYears();
   }
  });

  monthYear.addEventListener("click", (e) => {
   e.stopPropagation();
   if (mode === "days") {
    renderMonths();
   } else if (mode === "months") {
    renderYears();
   }
  });

  renderDays();
 }

 document.querySelectorAll(".custom-datepicker").forEach(input => {
  initializeDatepicker(input);
 });
});