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
    --radius-button-mobile: 8px;
    --radius-card-mobile: 12px;
    --appointment-bg: #f0fff4;
    --appointment-border: #48bb78;
    --appointment-dot: #6ee7b7;
    --holiday-bg: #fef2f2;
    --holiday-border: #f56565;
    --holiday-dot: #f56565;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --font-family: 'Vazirmatn', system-ui, -apple-system, sans-serif;
  }

  #loading-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.95);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    border-radius: var(--radius-card);
    backdrop-filter: blur(4px);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.4s ease, visibility 0.4s ease;
  }

  #loading-overlay.active {
    opacity: 1;
    visibility: visible;
  }

  #loading-overlay::after {
    content: '';
    width: 36px;
    height: 36px;
    border: 4px solid transparent;
    border-top-color: var(--primary);
    border-right-color: var(--primary-light);
    border-radius: 50%;
    animation: spin 0.7s ease-in-out infinite;
  }

  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }

  .reschedule-calendar-container {
    background: var(--background-card);
    border-radius: var(--radius-card);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    direction: rtl;
    margin: 0 auto;
    font-family: var(--font-family);
    padding: 1.5rem;
    position: relative;
    transition: var(--transition);
  }

  .reschedule-calendar-container .calendar-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    flex-wrap: wrap;
  }

  .reschedule-calendar-container .btn-nav {
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
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  }

  .reschedule-calendar-container .btn-nav:hover {
    background: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .reschedule-calendar-container .btn-nav:hover svg path {
    stroke: var(--background-card);
  }

  .reschedule-calendar-container .select-wrapper {
    flex: 1;
    min-width: 120px;
  }

  .reschedule-calendar-container .form-select {
    width: 100%;
    padding: 0.75rem 2.5rem 0.75rem 1rem;
    background: var(--background-card);
    border: 1px solid rgba(229, 231, 235, 0.5);
    border-radius: var(--radius-button);
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text-primary);
    appearance: none;
    transition: var(--transition);
    background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="%232563eb"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>');
    background-repeat: no-repeat;
    background-position: left 0.75rem center;
    background-size: 14px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  }

  .reschedule-calendar-container .form-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
  }

  .reschedule-calendar-container .calendar-controls {
    display: flex;
    justify-content: flex-end;
    padding: 0.75rem 0;
  }

  .reschedule-calendar-container .btn-primary {
    background: var(--primary);
    color: var(--background-card);
    padding: 0.5rem 1.5rem;
    border-radius: var(--radius-button);
    font-size: 0.95rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .reschedule-calendar-container .btn-primary:hover {
    background: var(--primary-light);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .reschedule-calendar-container .calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
    padding: 0.75rem 0;
  }

  .reschedule-calendar-container .calendar-day-name {
    text-align: center;
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text-secondary);
  }

  .reschedule-calendar-container .calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
    padding-bottom: 1rem;
  }

  .reschedule-calendar-container .calendar-day {
    background: var(--background-card);
    border: 1px solid rgba(229, 231, 235, 0.5);
    border-radius: 8px;
    aspect-ratio: 1.5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    cursor: pointer;
    transition: var(--transition);
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  }

  .reschedule-calendar-container .calendar-day:hover:not(.empty) {
    background: var(--primary-light);
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
  }

  .reschedule-calendar-container .calendar-day.empty {
    background: transparent;
    border: none;
    cursor: default;
    box-shadow: none;
  }

  .reschedule-calendar-container .calendar-day.friday {
    color: #ef4444;
  }

  .reschedule-calendar-container .calendar-day.today {
    background: var(--gradient-primary);
    border-color: var(--primary);
    color: var(--background-card);
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
  }

  .reschedule-calendar-container .calendar-day.active {
    background: var(--primary);
    color: var(--background-card);
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
  }

  .reschedule-calendar-container .calendar-day.selected {
    background: var(--secondary);
    color: var(--background-card);
    border-color: var(--secondary);
    box-shadow: 0 0 16px rgba(34, 197, 94, 0.4);
    transform: scale(1.02);
  }

  .reschedule-calendar-container .calendar-day.has-appointment {
    background: var(--appointment-bg);
    border-color: var(--appointment-border);
    box-shadow: 0 3px 10px rgba(72, 187, 120, 0.2);
  }

  .reschedule-calendar-container .calendar-day.has-appointment::before {
    content: '';
    position: absolute;
    top: 6px;
    right: 6px;
    width: 8px;
    height: 8px;
    background: var(--appointment-dot);
    border-radius: 50%;
    box-shadow: 0 0 6px rgba(72, 187, 120, 0.3);
  }

  .reschedule-calendar-container .calendar-day.holiday {
    background: var(--holiday-bg);
    border-color: var(--holiday-border);
    color: var(--holiday-dot);
    box-shadow: 0 3px 10px rgba(245, 101, 101, 0.2);
  }

  .reschedule-calendar-container .calendar-day.holiday::after {
    content: '';
    position: absolute;
    top: 6px;
    left: 6px;
    width: 8px;
    height: 8px;
    background: var(--holiday-dot);
    border-radius: 50%;
    box-shadow: 0 0 6px rgba(245, 101, 101, 0.3);
  }

  .reschedule-calendar-container .calendar-day.today.has-appointment {
    background: var(--gradient-primary);
    border-color: var(--appointment-border);
    color: var(--background-card);
    box-shadow: 0 4px 12px rgba(72, 187, 120, 0.2);
  }

  .reschedule-calendar-container .calendar-day.selected.has-appointment {
    background: var(--secondary);
    border-color: var(--secondary);
    box-shadow: 0 0 16px rgba(34, 197, 94, 0.4);
  }

  .reschedule-calendar-container .calendar-legend {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    padding: 1rem 0;
    border-top: 1px solid rgba(229, 231, 235, 0.5);
  }

  .reschedule-calendar-container .legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .reschedule-calendar-container .legend-color {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  }

  .reschedule-calendar-container .legend-color.selected {
    background: var(--secondary);
  }

  .reschedule-calendar-container .legend-color.appointment {
    background: var(--appointment-bg);
    border: 1px solid var(--appointment-border);
  }

  .reschedule-calendar-container .legend-color.holiday {
    background: var(--holiday-dot);
  }

  .reschedule-calendar-container .legend-text {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-secondary);
  }

  /* ریسپانسیو برای تبلت (950px و کمتر) */
  @media (max-width: 950px) {
    .reschedule-calendar-container {
      max-width: 100%;
      padding: 1.25rem;
      border-radius: var(--radius-card-mobile);
      box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
    }

    .reschedule-calendar-container .calendar-header {
      gap: 0.5rem;
      padding: 0.5rem 0;
    }

    .reschedule-calendar-container .btn-nav {
      width: 36px;
      height: 36px;
      border-radius: var(--radius-button-mobile);
    }

    .reschedule-calendar-container .select-wrapper {
      min-width: 110px;
    }

    .reschedule-calendar-container .form-select {
      font-size: 0.9rem;
      padding: 0.65rem 2.25rem 0.65rem 0.9rem;
      border-radius: var(--radius-button-mobile);
      background-size: 13px;
    }

    .reschedule-calendar-container .calendar-controls {
      padding: 0.5rem 0;
    }

    .reschedule-calendar-container .btn-primary {
      font-size: 0.9rem;
      padding: 0.4rem 1.2rem;
      border-radius: var(--radius-button-mobile);
    }

    .reschedule-calendar-container .calendar-body {
      gap: 0.4rem;
      padding: 0.5rem 0;
    }

    .reschedule-calendar-container .calendar-day-name {
      font-size: 0.85rem;
      font-weight: 600;
    }

    .reschedule-calendar-container .calendar-grid {
      gap: 0.4rem;
      padding-bottom: 0.75rem;
    }

    .reschedule-calendar-container .calendar-day {
      font-size: 0.95rem;
      border-radius: 6px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .reschedule-calendar-container .calendar-day.has-appointment::before,
    .reschedule-calendar-container .calendar-day.holiday::after {
      top: 5px;
      right: 5px;
      left: 5px;
      width: 7px;
      height: 7px;
    }

    .reschedule-calendar-container .calendar-legend {
      gap: 1rem;
      padding: 0.75rem 0;
    }

    .reschedule-calendar-container .legend-color {
      width: 14px;
      height: 14px;
      border-radius: 3px;
    }

    .reschedule-calendar-container .legend-text {
      font-size: 0.8rem;
    }

    #loading-overlay {
      border-radius: var(--radius-card-mobile);
    }

    #loading-overlay::after {
      width: 32px;
      height: 32px;
      border-width: 3px;
    }
  }

  /* ریسپانسیو برای موبایل و تبلت کوچک (768px و کمتر) */
  @media (max-width: 768px) {
    .reschedule-calendar-container {
      padding: 1rem;
      border-radius: var(--radius-card-mobile);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .reschedule-calendar-container .calendar-header {
      flex-wrap: wrap;
      gap: 0.4rem;
      padding: 0.4rem 0;
    }

    .reschedule-calendar-container .select-wrapper {
      flex: 1 1 48%;
      min-width: 100px;
    }

    .reschedule-calendar-container .btn-nav {
      width: 34px;
      height: 34px;
      border-radius: var(--radius-button-mobile);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .reschedule-calendar-container .form-select {
      font-size: 0.85rem;
      padding: 0.6rem 2rem 0.6rem 0.8rem;
      background-size: 12px;
      background-position: left 0.7rem center;
      border-radius: var(--radius-button-mobile);
    }

    .reschedule-calendar-container .calendar-controls {
      padding: 0.4rem 0;
    }

    .reschedule-calendar-container .btn-primary {
      font-size: 0.85rem;
      padding: 0.35rem 1rem;
      border-radius: var(--radius-button-mobile);
    }

    .reschedule-calendar-container .calendar-body {
      gap: 0.3rem;
      padding: 0.4rem 0;
    }

    .reschedule-calendar-container .calendar-day-name {
      font-size: 0.8rem;
      font-weight: 600;
    }

    .reschedule-calendar-container .calendar-grid {
      gap: 0.3rem;
      padding-bottom: 0.5rem;
    }

    .reschedule-calendar-container .calendar-day {
      font-size: 0.9rem;
      border-radius: 5px;
      aspect-ratio: 1.3;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
    }

    .reschedule-calendar-container .calendar-day:hover:not(.empty) {
      transform: translateY(-1px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .reschedule-calendar-container .calendar-day.has-appointment::before,
    .reschedule-calendar-container .calendar-day.holiday::after {
      top: 4px;
      right: 4px;
      left: 4px;
      width: 6px;
      height: 6px;
    }

    .reschedule-calendar-container .calendar-legend {
      gap: 0.75rem;
      padding: 0.5rem 0;
      flex-wrap: wrap;
      justify-content: flex-start;
    }

    .reschedule-calendar-container .legend-item {
      gap: 0.4rem;
    }

    .reschedule-calendar-container .legend-color {
      width: 12px;
      height: 12px;
      border-radius: 2px;
    }

    .reschedule-calendar-container .legend-text {
      font-size: 0.75rem;
      font-weight: 600;
    }

    #loading-overlay::after {
      width: 30px;
      height: 30px;
      border-width: 3px;
    }
  }

  /* ریسپانسیو برای موبایل‌های کوچک (425px و کمتر) */
  @media (max-width: 425px) {
    .reschedule-calendar-container {
      padding: 0.75rem;
      border-radius: var(--radius-card-mobile);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
    }

    .reschedule-calendar-container .calendar-header {
      gap: 0.3rem;
      padding: 0.3rem 0;
    }

    .reschedule-calendar-container .select-wrapper {
      flex: 1 1 100%;
      min-width: 100%;
    }

    .reschedule-calendar-container .btn-nav {
      width: 30px;
      height: 30px;
      border-radius: var(--radius-button-mobile);
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }

    .reschedule-calendar-container .btn-nav svg {
      width: 14px;
      height: 14px;
    }

    .reschedule-calendar-container .form-select {
      font-size: 0.8rem;
      padding: 0.5rem 1.75rem 0.5rem 0.7rem;
      background-size: 11px;
      background-position: left 0.6rem center;
      border-radius: var(--radius-button-mobile);
    }

    .reschedule-calendar-container .calendar-controls {
      padding: 0.3rem 0;
    }

    .reschedule-calendar-container .btn-primary {
      font-size: 0.8rem;
      padding: 0.3rem 0.8rem;
      border-radius: var(--radius-button-mobile);
    }

    .reschedule-calendar-container .calendar-body {
      gap: 0.2rem;
      padding: 0.3rem 0;
    }

    .reschedule-calendar-container .calendar-day-name {
      font-size: 0.75rem;
      font-weight: 600;
    }

    .reschedule-calendar-container .calendar-grid {
      gap: 0.2rem;
      padding-bottom: 0.4rem;
    }

    .reschedule-calendar-container .calendar-day {
      font-size: 0.85rem;
      border-radius: 4px;
      aspect-ratio: 1.2;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
    }

    .reschedule-calendar-container .calendar-day:hover:not(.empty) {
      transform: none;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
    }

    .reschedule-calendar-container .calendar-day.has-appointment::before,
    .reschedule-calendar-container .calendar-day.holiday::after {
      top: 3px;
      right: 3px;
      left: 3px;
      width: 5px;
      height: 5px;
    }

    .reschedule-calendar-container .calendar-legend {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.5rem;
      padding: 0.4rem 0;
    }

    .reschedule-calendar-container .legend-item {
      gap: 0.3rem;
    }

    .reschedule-calendar-container .legend-color {
      width: 10px;
      height: 10px;
      border-radius: 2px;
    }

    .reschedule-calendar-container .legend-text {
      font-size: 0.7rem;
      font-weight: 600;
    }

    #loading-overlay {
      border-radius: var(--radius-card-mobile);
    }

    #loading-overlay::after {
      width: 28px;
      height: 28px;
      border-width: 2px;
    }
  }
</style>
