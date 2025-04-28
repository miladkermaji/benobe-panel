<div class="reschedule-calendar-container">
  <div class="calendar-header">
    <button id="reschedule-prev-month" class="btn-nav" aria-label="Previous Month">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" transform="rotate(180)">
        <path d="M15 18L9 12L15 6" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </button>
    <div class="select-wrapper">
      <select id="reschedule-year" class="form-select"></select>
    </div>
    <div class="select-wrapper">
      <select id="reschedule-month" class="form-select"></select>
    </div>
    <button id="reschedule-next-month" class="btn-nav" aria-label="Next Month">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" transform="rotate(180)">
        <path d="M9 18L15 12L9 6" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
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
    --background-light: #F0F8FF;
    --background-card: #FFFFFF;
    --text-primary: #000000;
    --text-secondary: #707070;
    --border-neutral: #E5E7EB;
    --shadow: rgba(0, 0, 0, 0.35);
    --gradient-primary: linear-gradient(90deg, var(--primary-light) 0%, var(--primary) 100%);
    --radius-button: 0.5rem;
    --radius-card: 0.75rem;
  }

  .reschedule-calendar-container {
    background: var(--background-card);
    border-radius: var(--radius-card);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    direction: rtl;
    width:770px;
    margin: 0 auto;
    padding: 1rem;
    font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    transition: all 0.3s ease;
  }

  .calendar-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
  }

  .btn-nav {
    width: 36px;
    height: 36px;
    background: var(--background-card);
    border: 1px solid var(--border-neutral);
    border-radius: var(--radius-button);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
  }

  .btn-nav:hover {
    background: var(--primary);
    border-color: var(--primary);
    transform: scale(1.08);
  }

  .btn-nav:hover svg path {
    stroke: var(--background-card);
  }

  .select-wrapper {
    flex: 1;
    position: relative;
  }

  .form-select {
    width: 100%;
    padding: 0.5rem 2rem 0.5rem 0.75rem;
    background: var(--background-card);
    border: 1px solid var(--border-neutral);
    border-radius: var(--radius-button);
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--text-primary);
    appearance: none;
    transition: all 0.3s ease;
    background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="%232e86c1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>');
    background-repeat: no-repeat;
    background-position: left 0.5rem center;
    background-size: 12px;
  }

  .form-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(46, 134, 193, 0.2);
  }

  .calendar-controls {
    display: flex;
    justify-content: flex-end;
    padding: 0.75rem;
  }

  .btn-primary {
    background: var(--secondary);
    color: var(--background-card);
    padding: 0.5rem 1.5rem;
    border-radius: var(--radius-button);
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
  }

  .btn-primary:hover {
    background: var(--secondary-hover);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
    padding: 0.5rem;
  }

  .calendar-day-name {
    text-align: center;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-secondary);
  }

  .calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
    padding: 0 0.5rem 0.75rem;
  }

  .calendar-day {
    background: var(--background-card);
    border: 1px solid var(--border-neutral);
    border-radius: 6px;
    aspect-ratio: 1.5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    font-weight: 500;
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
  }

  .calendar-day:hover:not(.empty) {
    background: var(--primary-light);
    border-color: var(--primary);
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .calendar-day.empty {
    background: transparent;
    border: none;
    cursor: default;
  }

  .calendar-day.friday {
    color: #ef4444;
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
    box-shadow: 0 0 8px rgba(29, 235, 60, 0.3);
  }

  .calendar-day.has-appointment {
    background: #d1fae5;
    border-color: #6ee7b7;
  }

  .calendar-day.holiday {
    background: #fef2f2;
    border-color: #f56565;
    color: #c53030;
  }

  .calendar-day.holiday::after {
    content: '';
    position: absolute;
    top: 4px;
    left: 4px;
    width: 5px;
    height: 5px;
    background: #f56565;
    border-radius: 50%;
  }

  .calendar-legend {
    display: flex;
    justify-content: center;
    gap: 1rem;
    padding: 0.75rem;
    border-top: 1px solid var(--border-neutral);
  }

  .legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .legend-color {
    width: 14px;
    height: 14px;
    border-radius: 4px;
  }

  .legend-color.selected {
    background: var(--secondary);
  }

  .legend-color.appointment {
    background: #d1fae5;
    border: 1px solid #6ee7b7;
  }

  .legend-color.holiday {
    background: #f56565;
  }

  .legend-text {
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--text-secondary);
  }

  @media (max-width: 950px) {
    .reschedule-calendar-container {
      width: 100%;
      padding: 0.75rem;
    }

    .calendar-header {
      padding: 0.5rem;
      gap: 0.4rem;
    }

    .btn-nav {
      width: 32px;
      height: 32px;
    }

    .form-select {
      font-size: 0.85rem;
      padding: 0.4rem 1.5rem 0.4rem 0.6rem;
    }

    .calendar-day {
      font-size: 0.9rem;
    }

    .calendar-day-name {
      font-size: 0.8rem;
    }
  }

  @media (max-width: 768px) {
    .reschedule-calendar-container {
      padding: 0.5rem;
    }

    .calendar-header {
      flex-wrap: wrap;
      gap: 0.3rem;
    }

    .select-wrapper {
      flex: 1 1 45%;
    }

    .btn-nav {
      width: 30px;
      height: 30px;
    }

    .form-select {
      font-size: 0.8rem;
    }

    .calendar-day {
      font-size: 0.85rem;
    }

    .calendar-legend {
      gap: 0.75rem;
    }

    .legend-text {
      font-size: 0.75rem;
    }
  }

  @media (max-width: 425px) {
    .reschedule-calendar-container {
      padding: 0.4rem;
    }

    .btn-nav {
      width: 28px;
      height: 28px;
    }

    .form-select {
      font-size: 0.75rem;
      padding: 0.4rem 1.2rem 0.4rem 0.5rem;
    }

    .calendar-day {
      font-size: 0.8rem;
    }

    .calendar-day-name {
      font-size: 0.75rem;
    }

    .calendar-legend {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.5rem;
    }

    .legend-text {
      font-size: 0.7rem;
    }
  }
</style>