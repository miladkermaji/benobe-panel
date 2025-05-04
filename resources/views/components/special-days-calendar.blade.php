<div class="special-days-calendar-container" style="position: relative;">
  <!-- لودینگ اورلی -->
  <div id="loading-overlay"></div>
  <div class="calendar-header">
    <button id="special-days-prev-month" class="btn-nav" aria-label="Previous Month">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
        transform="rotate(180)">
        <path d="M15 18L9 12L15 6" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round" />
      </svg>
    </button>
    <div class="select-wrapper">
      <select id="special-days-year" class="form-select"></select>
    </div>
    <div class="select-wrapper">
      <select id="special-days-month" class="form-select"></select>
    </div>
    <button id="special-days-next-month" class="btn-nav" aria-label="Next Month">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
        transform="rotate(180)">
        <path d="M9 18L15 12L9 6" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round" />
      </svg>
    </button>
  </div>
  <div class="calendar-body" id="calendar-body">
    <div class="calendar-day-name">ش</div>
    <div class="calendar-day-name">ی</div>
    <div class="calendar-day-name">د</div>
    <div class="calendar-day-name">س</div>
    <div class="calendar-day-name">چ</div>
    <div class="calendar-day-name">پ</div>
    <div class="calendar-day-name">ج</div>
  </div>
  <div id="special-days-calendar-body" class="calendar-grid"></div>
  <div class="calendar-legend">
    <div class="legend-item">
      <span class="legend-color selected"></span>
      <span class="legend-text">روز انتخاب‌شده</span>
    </div>
    <div class="legend-item">
      <span class="legend-color appointment"></span>
      <span class="legend-text"> دارای نوبت</span>
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
  --background-light: #F8F8F8;
  --background-card: #FFFFFF;
  --text-primary: #000000;
  --text-secondary: #707070;
  --border-neutral: #E5E7EB;
  --shadow: rgba(0, 0, 0, 0.08);
  --gradient-primary: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
  --radius-button: 10px;
  --radius-card: 16px;
  --appointment-bg: #f0fff4;
  --appointment-border: #48bb78;
  --appointment-dot: #6ee7b7;
  --holiday-bg: #fef2f2;
  --holiday-border: #f56565;
  --holiday-dot: #f56565;
  --transition: all 0.25s ease;
  --font-family: 'Vazirmatn', system-ui, -apple-system, sans-serif;
}

#loading-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  border-radius: var(--radius-card);
  backdrop-filter: blur(2px);
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s ease, visibility 0.3s ease;
}

#loading-overlay.active {
  opacity: 1;
  visibility: visible;
}

#loading-overlay::after {
  content: '';
  width: 32px;
  height: 32px;
  border: 3px solid transparent;
  border-top-color: var(--primary);
  border-right-color: var(--primary-light);
  border-radius: 50%;
  animation: spin 0.8s ease-in-out infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.special-days-calendar-container {
  background: var(--background-card);
  border-radius: var(--radius-card);
  box-shadow: 0 8px 32px var(--shadow);
  direction: rtl;
  margin: 1rem auto;
  font-family: var(--font-family);
  max-width: 1000px;
  padding: 1.5rem;
  position: relative;
  transition: var(--transition);
}

.calendar-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 0;
  flex-wrap: wrap;
}

.btn-nav {
  width: 40px;
  height: 40px;
  background: var(--background-light);
  border: none;
  border-radius: var(--radius-button);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--transition);
  cursor: pointer;
}

.btn-nav:hover {
  background: var(--primary);
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.btn-nav:hover svg path {
  stroke: var(--background-card);
}

.select-wrapper {
  flex: 1;
  min-width: 120px;
}

.form-select {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  background: var(--background-light);
  border: 1px solid var(--border-neutral);
  border-radius: var(--radius-button);
  font-size: 0.95rem;
  font-weight: 500;
  color: var(--text-primary);
  appearance: none;
  transition: var(--transition);
  background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="%232563eb"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>');
  background-repeat: no-repeat;
  background-position: left 0.75rem center;
  background-size: 14px;
}

.form-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.calendar-body {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.5rem;
  padding: 0.75rem 0;
}

.calendar-day-name {
  text-align: center;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.5rem;
  padding-bottom: 1rem;
}

.calendar-day {
  background: var(--background-card);
  border: 1px solid var(--border-neutral);
  border-radius: 8px;
  aspect-ratio: 1.5;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: 500;
  color: var(--text-primary);
  cursor: pointer;
  transition: var(--transition);
  position: relative;
}

.calendar-day:hover:not(.empty) {
  background: var(--primary-light);
  border-color: var(--primary);
  transform: scale(1.03);
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

.calendar-day.empty {
  background: transparent;
  border: none;
  cursor: default;
}

.calendar-day.friday {
  color: #dc2626;
}

.calendar-day.today {
  background: var(--gradient-primary);
  border-color: var(--primary);
  color: var(--background-card);
  font-weight: 600;
}

.calendar-day.active {
  background: var(--primary);
  color: var(--background-card);
  border-color: var(--primary);
}

.calendar-day.selected {
  background: var(--secondary);
  color: var(--background-card);
  border-color: var(--secondary);
  box-shadow: 0 0 12px rgba(34, 197, 94, 0.3);
}

.calendar-day.has-appointment {
  background: var(--appointment-bg);
  border-color: var(--appointment-border);
}

.calendar-day.has-appointment::before {
  content: '';
  position: absolute;
  top: 6px;
  right: 6px;
  width: 8px;
  height: 8px;
  background: var(--appointment-dot);
  border-radius: 50%;
}

.calendar-day.holiday {
  background: var(--holiday-bg);
  border-color: var(--holiday-border);
  color: var(--holiday-dot);
}

.calendar-day.holiday::after {
  content: '';
  position: absolute;
  top: 6px;
  left: 6px;
  width: 8px;
  height: 8px;
  background: var(--holiday-dot);
  border-radius: 50%;
}

.calendar-day.today.has-appointment {
  background: var(--gradient-primary);
  border-color: var(--appointment-border);
  color: var(--background-card);
}

.calendar-day.selected.has-appointment {
  background: var(--secondary);
  border-color: var(--secondary);
}

.calendar-legend {
  display: flex;
  justify-content: center;
  gap: 1.5rem;
  padding: 1rem 0;
  border-top: 1px solid var(--border-neutral);
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.legend-color {
  width: 16px;
  height: 16px;
  border-radius: 4px;
}

.legend-color.selected {
  background: var(--secondary);
}

.legend-color.appointment {
  background: var(--appointment-bg);
  border: 1px solid var(--appointment-border);
}

.legend-color.holiday {
  background: var(--holiday-dot);
}

.legend-text {
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--text-secondary);
}

@media (max-width: 960px) {
  .special-days-calendar-container {
    max-width: 100%;
    padding: 1rem;
  }

  .calendar-header {
    gap: 0.5rem;
  }

  .btn-nav {
    width: 36px;
    height: 36px;
  }

  .form-select {
    font-size: 0.9rem;
    padding: 0.6rem 2rem 0.6rem 0.8rem;
  }

  .calendar-day {
    font-size: 0.95rem;
  }
}

@media (max-width: 768px) {
  .special-days-calendar-container {
    padding: 0.75rem;
  }

  .calendar-header {
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .select-wrapper {
    flex: 1 1 45%;
  }

  .btn-nav {
    width: 34px;
    height: 34px;
  }

  .form-select {
    font-size: 0.85rem;
  }

  .calendar-day {
    font-size: 0.9rem;
  }

  .calendar-legend {
    gap: 1rem;
  }
}

@media (max-width: 425px) {
  .special-days-calendar-container {
    padding: 0.5rem;
  }

  .btn-nav {
    width: 32px;
    height: 32px;
  }

  .form-select {
    font-size: 0.8rem;
    padding: 0.5rem 1.5rem 0.5rem 0.6rem;
  }

  .calendar-day {
    font-size: 0.85rem;
  }

  .calendar-day-name {
    font-size: 0.8rem;
  }

  .calendar-legend {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
}
</style>
