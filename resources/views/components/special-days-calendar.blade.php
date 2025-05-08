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
    --radius-button: 12px;
    --radius-card: 20px;
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

  .special-days-calendar-container {
    background: var(--background-card);
    border-radius: var(--radius-card);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    direction: rtl;
    margin: 1.5rem auto;
    font-family: var(--font-family);
    max-width: 1000px;
    padding: 2rem;
    position: relative;
    transition: var(--transition);
  }

  .calendar-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0;
    flex-wrap: wrap;
  }

  .btn-nav {
    width: 44px;
    height: 44px;
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

  .btn-nav:hover {
    background: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .btn-nav:hover svg path {
    stroke: var(--background-card);
  }

  .select-wrapper {
    flex: 1;
    min-width: 140px;
  }

  .form-select {
    width: 100%;
    padding: 0.85rem 2.75rem 0.85rem 1.25rem;
    background: var(--background-card);
    border: 1px solid rgba(229, 231, 235, 0.5);
    border-radius: var(--radius-button);
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    appearance: none;
    transition: var(--transition);
    background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="%232563eb"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>');
    background-repeat: no-repeat;
    background-position: left 1rem center;
    background-size: 16px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  }

  .form-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
  }

  .calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.75rem;
    padding: 1rem 0;
  }

  .calendar-day-name {
    text-align: center;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-secondary);
  }

  .calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.75rem;
    padding-bottom: 1.5rem;
  }

  .calendar-day {
    background: var(--background-card);
    border: 1px solid rgba(229, 231, 235, 0.5);
    border-radius: 10px;
    aspect-ratio: 1.5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--text-primary);
    cursor: pointer;
    transition: var(--transition);
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  }

  .calendar-day:hover:not(.empty) {
    background: var(--primary-light);
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
  }

  .calendar-day.empty {
    background: transparent;
    border: none;
    cursor: default;
    box-shadow: none;
  }

  .calendar-day.friday {
    color: #dc2626;
  }

  .calendar-day.today {
    background: var(--gradient-primary);
    border-color: var(--primary);
    color: var(--background-card);
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
  }

  .calendar-day.active {
    background: var(--primary);
    color: var(--background-card);
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
  }

  .calendar-day.selected {
    background: var(--secondary);
    color: var(--background-card);
    border-color: var(--secondary);
    box-shadow: 0 0 16px rgba(34, 197, 94, 0.4);
    transform: scale(1.02);
  }

  .calendar-day.has-appointment {
    background: var(--appointment-bg);
    border-color: var(--appointment-border);
    box-shadow: 0 3px 10px rgba(72, 187, 120, 0.2);
  }

  .calendar-day.has-appointment::before {
    content: '';
    position: absolute;
    top: 8px;
    right: 8px;
    width: 10px;
    height: 10px;
    background: var(--appointment-dot);
    border-radius: 50%;
    box-shadow: 0 0 6px rgba(72, 187, 120, 0.3);
  }

  .calendar-day.holiday {
    background: var(--holiday-bg);
    border-color: var(--holiday-border);
    color: var(--holiday-dot);
    box-shadow: 0 3px 10px rgba(245, 101, 101, 0.2);
  }

  .calendar-day.holiday::after {
    content: '';
    position: absolute;
    top: 8px;
    left: 8px;
    width: 10px;
    height: 10px;
    background: var(--holiday-dot);
    border-radius: 50%;
    box-shadow: 0 0 6px rgba(245, 101, 101, 0.3);
  }

  .calendar-day.today.has-appointment {
    background: var(--gradient-primary);
    border-color: var(--appointment-border);
    color: var(--background-card);
    box-shadow: 0 4px 12px rgba(72, 187, 120, 0.2);
  }

  .calendar-day.selected.has-appointment {
    background: var(--secondary);
    border-color: var(--secondary);
    box-shadow: 0 0 16px rgba(34, 197, 94, 0.4);
  }

  .calendar-legend {
    display: flex;
    justify-content: center;
    gap: 2rem;
    padding: 1.25rem 0;
    border-top: 1px solid rgba(229, 231, 235, 0.5);
  }

  .legend-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .legend-color {
    width: 18px;
    height: 18px;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
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
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-secondary);
  }

  /* ریسپانسیو برای تبلت (960px و کمتر) */
  @media (max-width: 960px) {
    .special-days-calendar-container {
      max-width: 100%;
      padding: 1.5rem;
      border-radius: var(--radius-card-mobile);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .calendar-header {
      gap: 0.75rem;
      padding: 0.75rem 0;
    }

    .btn-nav {
      width: 40px;
      height: 40px;
      border-radius: var(--radius-button-mobile);
    }

    .select-wrapper {
      min-width: 130px;
    }

    .form-select {
      font-size: 0.95rem;
      padding: 0.75rem 2.5rem 0.75rem 1rem;
      border-radius: var(--radius-button-mobile);
      background-size: 15px;
    }

    .calendar-body {
      gap: 0.5rem;
      padding: 0.75rem 0;
    }

    .calendar-day-name {
      font-size: 0.9rem;
      font-weight: 600;
    }

    .calendar-grid {
      gap: 0.5rem;
      padding-bottom: 1rem;
    }

    .calendar-day {
      font-size: 1rem;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .calendar-day.has-appointment::before,
    .calendar-day.holiday::after {
      top: 6px;
      right: 6px;
      left: 6px;
      width: 8px;
      height: 8px;
    }

    .calendar-legend {
      gap: 1.5rem;
      padding: 1rem 0;
    }

    .legend-color {
      width: 16px;
      height: 16px;
      border-radius: 4px;
    }

    .legend-text {
      font-size: 0.85rem;
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
    .special-days-calendar-container {
      padding: 1rem;
      margin: 1rem auto;
      border-radius: var(--radius-card-mobile);
      box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
    }

    .calendar-header {
      flex-wrap: wrap;
      gap: 0.5rem;
      padding: 0.5rem 0;
    }

    .select-wrapper {
      flex: 1 1 45%;
      min-width: 120px;
    }

    .btn-nav {
      width: 36px;
      height: 36px;
      border-radius: var(--radius-button-mobile);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .form-select {
      font-size: 0.9rem;
      padding: 0.65rem 2.25rem 0.65rem 0.9rem;
      background-size: 14px;
      background-position: left 0.8rem center;
      border-radius: var(--radius-button-mobile);
    }

    .calendar-body {
      gap: 0.4rem;
      padding: 0.5rem 0;
    }

    .calendar-day-name {
      font-size: 0.85rem;
      font-weight: 600;
    }

    .calendar-grid {
      gap: 0.4rem;
      padding-bottom: 0.75rem;
    }

    .calendar-day {
      font-size: 0.95rem;
      border-radius: 6px;
      aspect-ratio: 1.3;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
    }

    .calendar-day:hover:not(.empty) {
      transform: translateY(-1px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .calendar-day.has-appointment::before,
    .calendar-day.holiday::after {
      top: 5px;
      right: 5px;
      left: 5px;
      width: 7px;
      height: 7px;
    }

    .calendar-legend {
      gap: 1rem;
      padding: 0.75rem 0;
      flex-wrap: wrap;
      justify-content: flex-start;
    }

    .legend-item {
      gap: 0.5rem;
    }

    .legend-color {
      width: 14px;
      height: 14px;
      border-radius: 3px;
    }

    .legend-text {
      font-size: 0.8rem;
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
    .special-days-calendar-container {
      padding: 0.75rem;
      margin: 0.75rem auto;
      border-radius: var(--radius-card-mobile);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
    }

    .calendar-header {
      gap: 0.4rem;
      padding: 0.4rem 0;
    }

    .select-wrapper {
      flex: 1 1 100%;
      min-width: 100%;
    }

    .btn-nav {
      width: 32px;
      height: 32px;
      border-radius: var(--radius-button-mobile);
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }

    .btn-nav svg {
      width: 16px;
      height: 16px;
    }

    .form-select {
      font-size: 0.85rem;
      padding: 0.6rem 2rem 0.6rem 0.8rem;
      background-size: 13px;
      background-position: left 0.7rem center;
      border-radius: var(--radius-button-mobile);
    }

    .calendar-body {
      gap: 0.3rem;
      padding: 0.4rem 0;
    }

    .calendar-day-name {
      font-size: 0.8rem;
      font-weight: 600;
    }

    .calendar-grid {
      gap: 0.3rem;
      padding-bottom: 0.5rem;
    }

    .calendar-day {
      font-size: 0.9rem;
      border-radius: 5px;
      aspect-ratio: 1.2;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
    }

    .calendar-day:hover:not(.empty) {
      transform: none;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
    }

    .calendar-day.has-appointment::before,
    .calendar-day.holiday::after {
      top: 4px;
      right: 4px;
      left: 4px;
      width: 6px;
      height: 6px;
    }

    .calendar-legend {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.75rem;
      padding: 0.5rem 0;
    }

    .legend-item {
      gap: 0.4rem;
    }

    .legend-color {
      width: 12px;
      height: 12px;
      border-radius: 2px;
    }

    .legend-text {
      font-size: 0.75rem;
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
