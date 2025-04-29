<div class="reschedule-calendar-container" style="position: relative;">
  <!-- لودینگ اورلی -->
  <div id="loading-overlay"></div>

  <div class="calendar-header">
    <button id="reschedule-prev-month" class="btn-nav" aria-label="Previous Month">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
        transform="rotate(180)">
        <path d="M15 18L9 12L15 6" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round" />
      </svg>
    </button>
    <div class="select-wrapper">
      <select id="reschedule-year" class="form-select"></select>
    </div>
    <div class="select-wrapper">
      <select id="reschedule-month" class="form-select"></select>
    </div>
    <button id="reschedule-next-month" class="btn-nav" aria-label="Next Month">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
        transform="rotate(180)">
        <path d="M9 18L15 12L9 6" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round" />
      </svg>
    </button>
  </div>

  <div class="calendar-controls">
    <button wire:click="goToFirstAvailableDate" class="btn-primary">
      برو به اولین نوبت خالی
    </button>
  </div>

  <div class="calendar-body">
    <div class="calendar-day-name">ش</div>
    <div class="calendar-day-name">ی</div>
    <div class="calendar-day-name">د</div>
    <div class="calendar-day-name">س</div>
    <div class="calendar-day-name">چ</div>
    <div class="calendar-day-name">پ</div>
    <div class="calendar-day-name">ج</div>
  </div>

  <div id="reschedule-calendar-body" class="calendar-grid"></div>

  <div class="calendar-legend">
    <div class="legend-item">
      <span class="legend-color selected"></span>
      <span class="legend-text">روز انتخاب‌شده</span>
    </div>
    <div class="legend-item">
      <span class="legend-color appointment"></span>
      <span class="legend-text">نوبت‌های رزروشده</span>
    </div>
    <div class="legend-item">
      <span class="legend-color holiday"></span>
      <span class="legend-text">تعطیل</span>
    </div>
  </div>
</div>

<style>
 :root {
  --primary: #2E86C1;
  --primary-light: #84CAF9;
  --secondary: #1DEB3C;
  --secondary-hover: #15802A;
  --background-light: #F EightyEightF;
  --background-card: #FFFFFF;
  --text-primary: #000000;
  --text-secondary: #707070;
  --border-neutral: #E5E7EB;
  --shadow: rgba(0, 0, 0, 0.1);
  --gradient-primary: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
  --radius-button: 8px;
  --radius-card: 12px;
}

#loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.8);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  border-radius: var(--radius-card);
}

#loading-overlay::after {
  content: '';
  width: 40px;
  height: 40px;
  border: 4px solid var(--background-card);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.reschedule-calendar-container {
  background: var(--background-card);
  border-radius: var(--radius-card);
  box-shadow: 0 6px 24px var(--shadow);
  direction: rtl;
  margin: 0 auto;
  font-family: 'Vazirmatn', system-ui, -apple-system, sans-serif;
  transition: all 0.3s ease-in-out;
  position: relative; /* برای محدود کردن لودینگ به مودال */
}

/* استایل‌های هدر تقویم */
.reschedule-calendar-container .calendar-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0;
}

.reschedule-calendar-container .btn-nav {
  width: 36px;
  height: 36px;
  background: var(--background-light);
  border: 1px solid var(--border-neutral);
  border-radius: var(--radius-button);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.reschedule-calendar-container .btn-nav:hover {
  background: var(--primary);
  border-color: var(--primary);
  transform: translateY(-2px);
}

.reschedule-calendar-container .btn-nav:hover svg path {
  stroke: var(--background-card);
}

.select-wrapper {
  flex: 1;
}

.reschedule-calendar-container .form-select {
  width: 100%;
  padding: 0.5rem 2rem 0.5rem 0.75rem;
  background: var(--background-light);
  border: 1px solid var(--border-neutral);
  border-radius: var(--radius-button);
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--text-primary);
  appearance: none;
  transition: all 0.2s ease;
  background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="%232e86c1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>');
  background-repeat: no-repeat;
  background-position: left 0.5rem center;
  background-size: 12px;
}

.reschedule-calendar-container .form-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 4px rgba(46, 134, 193, 0.15);
}

/* استایل‌های کنترل‌ها */
.reschedule-calendar-container .calendar-controls {
  display: flex;
  justify-content: flex-end;
  padding: 0.5rem 0;
}

.reschedule-calendar-container .btn-primary {
  background: var(--secondary);
  color: var(--background-card);
  padding: 0.6rem 1.5rem;
  border-radius: var(--radius-button);
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.2s ease;
}

.reschedule-calendar-container .btn-primary:hover {
  background: var(--secondary-hover);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

/* استایل‌های بدنه تقویم */
.reschedule-calendar-container .calendar-body {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.4rem;
  padding: 0.5rem 0;
}

.reschedule-calendar-container .calendar-day-name {
  text-align: center;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.reschedule-calendar-container .calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.4rem;
  padding: 0 0 0.75rem;
}

.reschedule-calendar-container .calendar-day {
  background: var(--background-card);
  border: 1px solid var(--border-neutral);
  border-radius: 6px;
  aspect-ratio: 1.6;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  font-weight: 500;
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.reschedule-calendar-container .calendar-day:hover:not(.empty) {
  background: var(--primary-light);
  border-color: var(--primary);
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.reschedule-calendar-container .calendar-day.empty {
  background: transparent;
  border: none;
  cursor: default;
}

.reschedule-calendar-container .calendar-day.friday {
  color: #ef4444;
}

.reschedule-calendar-container .calendar-day.today {
  background: var(--gradient-primary);
  border-color: var(--primary);
  color: var(--background-card);
  font-weight: 600;
}

.reschedule-calendar-container .calendar-day.active {
  background: var(--primary);
  color: var(--background-card);
  border-color: var(--primary);
}

.reschedule-calendar-container .calendar-day.selected {
  background: var(--secondary);
  color: var(--background-card);
  border-color: var(--secondary);
  box-shadow: 0 0 10px rgba(29, 235, 60, 0.3);
}

.reschedule-calendar-container .calendar-day.has-appointment {
  position: relative;
}

.reschedule-calendar-container .calendar-day.has-appointment::before {
  content: '';
  position: absolute;
  top: 5px;
  right: 5px;
  width: 6px;
  height: 6px;
  background: #6ee7b7;
  border-radius: 50%;
  z-index: 1;
}

.reschedule-calendar-container .calendar-day.holiday {
  background: #fef2f2;
  border-color: #f56565;
  color: #c53030;
}

.reschedule-calendar-container .calendar-day.holiday::after {
  content: '';
  position: absolute;
  top: 5px;
  left: 5px;
  width: 6px;
  height: 6px;
  background: #f56565;
  border-radius: 50%;
  z-index: 1;
}

/* استایل‌های لجند */
.reschedule-calendar-container .calendar-legend {
  display: flex;
  justify-content: center;
  gap: 1rem;
  padding: 0.75rem 0;
  border-top: 1px solid var(--border-neutral);
}

.reschedule-calendar-container .legend-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.reschedule-calendar-container .legend-color {
  width: 14px;
  height: 14px;
  border-radius: 4px;
}

.reschedule-calendar-container .legend-color.selected {
  background: var(--secondary);
}

.reschedule-calendar-container .legend-color.appointment {
  background: #.preface;
  border: 1px solid #6ee7b7;
}

.reschedule-calendar-container .legend-color.holiday {
  background: #f56565;
}

.reschedule-calendar-container .legend-text {
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--text-secondary);
}

/* پاسخ‌گویی به صفحه‌نمایش‌های کوچک */
@media (max-width: 950px) {
  .reschedule-calendar-container {
    max-width: 100%;
    padding: 0.75rem;
  }

  .reschedule-calendar-container .calendar-header {
    gap: 0.4rem;
  }

  .reschedule-calendar-container .btn-nav {
    width: 34px;
    height: 34px;
  }

  .reschedule-calendar-container .form-select {
    font-size: 0.85rem;
    padding: 0.5rem 1.5rem 0.5rem 0.6rem;
  }

  .reschedule-calendar-container .calendar-day {
    font-size: 0.9rem;
  }
}

@media (max-width: 768px) {
  .reschedule-calendar-container {
    padding: 0.6rem;
  }

  .reschedule-calendar-container .calendar-header {
    flex-wrap: wrap;
    gap: 0.4rem;
  }

  .reschedule-calendar-container .select-wrapper {
    flex: 1 1 48%;
  }

  .reschedule-calendar-container .btn-nav {
    width: 32px;
    height: 32px;
  }

  .reschedule-calendar-container .form-select {
    font-size: 0.8rem;
  }

  .reschedule-calendar-container .calendar-day {
    font-size: 0.85rem;
  }

  .reschedule-calendar-container .calendar-legend {
    gap: 0.75rem;
  }
}

@media (max-width: 425px) {
  .reschedule-calendar-container {
    padding: 0.5rem;
  }

  .reschedule-calendar-container .btn-nav {
    width: 30px;
    height: 30px;
  }

  .reschedule-calendar-container .form-select {
    font-size: 0.75rem;
    padding: 0.4rem 1.2rem 0.4rem 0.5rem;
  }

  .reschedule-calendar-container .calendar-day {
    font-size: 0.8rem;
  }

  .reschedule-calendar-container .calendar-day-name {
    font-size: 0.75rem;
  }

  .reschedule-calendar-container .calendar-legend {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
}
</style>
