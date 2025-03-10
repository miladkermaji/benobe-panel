const persianMonths = [
 "فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور",
 "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"
];

function gregorianToJalali(gy, gm, gd) {
 gy = parseInt(gy, 10);
 gm = parseInt(gm, 10);
 gd = parseInt(gd, 10);

 if (isNaN(gy) || isNaN(gm) || isNaN(gd)) {
  console.error('Invalid date input:', { gy, gm, gd });
  return [1403, 1, 1];
 }

 const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
 if (isLeapYear(gy) && gm > 2) g_d_m[2] = 60;

 let doy = g_d_m[gm - 1] + gd;
 let jy = gy - 621;

 if (doy <= 79) {
  doy = 286 + doy;
  jy--;
 } else {
  doy -= 79;
 }

 let jm, jd;
 if (doy <= 186) {
  jm = Math.ceil(doy / 31) || 1;
  jd = doy - (jm - 1) * 31;
 } else {
  doy -= 186;
  jm = Math.ceil(doy / 30) + 6 || 7;
  jd = doy - (jm - 7) * 30;
 }

 jd += 1; // تنظیم برای ایران

 return [jy, jm, jd];
}

function isLeapYear(year) {
 return ((year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0));
}

// تابع سراسری برای دسترسی توی هوک
window.initializeDatepicker = function (input) {
 if (input.dataset.datepickerInitialized) {
  return;
 }

 let mode = "days";
 const today = new Date();
 const iranOffset = 3.5 * 60 * 60 * 1000; // UTC+3:30
 const iranTime = new Date(today.getTime() + iranOffset);
 const year = iranTime.getUTCFullYear();
 const month = iranTime.getUTCMonth() + 1;
 const day = iranTime.getUTCDate();
 const jalaliToday = gregorianToJalali(year, month, day);
 let currentMonthIndex = jalaliToday[1] - 1;
 let currentYear = jalaliToday[0];
 const todayDay = jalaliToday[2];
 const todayMonth = jalaliToday[1];
 const todayYear = jalaliToday[0];

 let container = input.parentNode.querySelector('.datepicker-container');
 if (!container) {
  container = document.createElement("div");
  container.className = "datepicker-container";
  input.parentNode.insertBefore(container, input.nextSibling);
  container.appendChild(input);
 }

 let calendar = container.querySelector('.calendar');
 if (!calendar) {
  calendar = document.createElement("div");
  calendar.className = "calendar";
  calendar.id = `calendar-${input.id || Math.random().toString(36).substring(2)}`;
  container.appendChild(calendar);
 }

 const header = document.createElement("div");
 header.className = "header";
 calendar.appendChild(header);

 const prev = document.createElement("button");
 prev.id = `prev-${input.id || Math.random().toString(36).substring(2)}`;
 prev.textContent = "‹";
 header.appendChild(prev);

 const monthYear = document.createElement("span");
 monthYear.id = `month-year-${input.id || Math.random().toString(36).substring(2)}`;
 header.appendChild(monthYear);

 const next = document.createElement("button");
 next.id = `next-${input.id || Math.random().toString(36).substring(2)}`;
 next.textContent = "›";
 header.appendChild(next);

 const daysContainer = document.createElement("div");
 daysContainer.className = "days";
 daysContainer.id = `days-${input.id || Math.random().toString(36).substring(2)}`;
 calendar.appendChild(daysContainer);

 const monthsContainer = document.createElement("div");
 monthsContainer.className = "months hidden";
 monthsContainer.id = `months-${input.id || Math.random().toString(36).substring(2)}`;
 calendar.appendChild(monthsContainer);

 const yearsContainer = document.createElement("div");
 yearsContainer.className = "years hidden";
 yearsContainer.id = `years-${input.id || Math.random().toString(36).substring(2)}`;
 calendar.appendChild(yearsContainer);

 function addClickListener() {
  input.removeEventListener('click', handleClick);
  input.addEventListener('click', handleClick);
 }

 function handleClick(e) {
  e.stopPropagation();
  calendar.classList.toggle("active");
  if (calendar.classList.contains("active")) {
   renderDays();
  }
 }

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

  const daysInMonth = currentMonthIndex < 6 ? 31 : (currentMonthIndex === 11 ? (isLeapYear(currentYear + 621) ? 30 : 29) : 30);
  const dayNames = ["ش", "ی", "د", "س", "چ", "پ", "ج"];
  dayNames.forEach((name) => {
   const dayName = document.createElement("div");
   dayName.className = "day-name";
   dayName.textContent = name;
   daysContainer.appendChild(dayName);
  });

  for (let day = 1; day <= daysInMonth; day++) {
   const dayElement = document.createElement("div");
   const isToday = day === todayDay && currentMonthIndex === todayMonth - 1 && currentYear === todayYear;
   dayElement.className = `day ${isToday ? 'today' : ''}`;
   dayElement.textContent = day;
   dayElement.addEventListener("click", (e) => {
    e.stopPropagation();
    document.querySelectorAll(`#${daysContainer.id} .day`).forEach(d => d.classList.remove("selected"));
    dayElement.classList.add("selected");
    const persianDate = `${day} ${persianMonths[currentMonthIndex]} ${currentYear}`;
    input.value = persianDate;
    input.dispatchEvent(new Event('input'));
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

 addClickListener();
 renderDays();
 input.dataset.datepickerInitialized = true;
};

document.addEventListener('DOMContentLoaded', () => {
 document.querySelectorAll(".custom-datepicker").forEach(input => {
  window.initializeDatepicker(input);
 });
});