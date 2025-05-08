<style>
  :root {
    --primary: #2E86C1;
    --primary-light: #84CAF9;
    --secondary: #1DEB3C;
    --secondary-hover: #15802A;
    --background-light: #F0F8FF;
    --background-footer: #D4ECFD;
    --background-card: #FFFFFF;
    --text-primary: #000000;
    --text-secondary: #707070;
    --text-discount: #008000;
    --text-original: #FF0000;
    --border-neutral: #E5E7EB;
    --shadow: rgba(0, 0, 0, 0.08);
    --gradient-instagram-from: #F92CA7;
    --gradient-instagram-to: #6B1A93;
    --button-mobile: #4F9ACD;
    --button-mobile-light: #A2CDEB;
    --support-section: #2E86C1;
    --support-text: #084D7C;
    --radius-button: 0.5rem;
    --radius-card: 1.125rem;
    --radius-button-large: 1rem;
    --radius-button-xl: 1.25rem;
    --radius-footer: 1.875rem;
    --radius-nav: 1.25rem;
    --radius-circle: 9999px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --font-family: 'Vazirmatn', system-ui, -apple-system, sans-serif;
  }

  #mini-calendar-container .calendar {
    background: var(--background-card);
    padding: 8px;
    margin-top: -29px;
    border-radius: var(--radius-card);
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
  }

  #mini-calendar-container .calendar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    gap: 4px;
  }

  #mini-calendar-container .select-group {
    display: flex;
    gap: 6px;
    flex: 1;
    justify-content: center;
  }

  #mini-calendar-container .calendar-select {
    background: var(--background-card);
    border: 1px solid rgba(229, 231, 235, 0.5);
    border-radius: 6px;
    padding: 5px 10px 5px 24px;
    font-size: 15px;
    font-weight: 600;
    color: var(--text-primary);
    cursor: pointer;
    transition: var(--transition);
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23666666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: left 8px center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  }

  #mini-calendar-container .calendar-select:focus {
    outline: none;
    border-color: var(--primary);
    background: var(--background-card);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
  }

  #mini-calendar-container .calendar-select:hover {
    background: var(--primary-light);
    border-color: var(--primary);
  }

  #mini-calendar-container .nav-btn {
    background: var(--background-light);
    border: none;
    padding: 6px;
    cursor: pointer;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-circle);
    transition: var(--transition);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  }

  #mini-calendar-container .nav-btn svg {
    stroke: var(--text-secondary);
    width: 16px;
    height: 16px;
  }

  #mini-calendar-container .nav-btn:hover {
    background: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  #mini-calendar-container .nav-btn:hover svg {
    stroke: var(--background-card);
  }

  #mini-calendar-container .calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
  }

  #mini-calendar-container .calendar-day-name {
    text-align: center;
    font-size: 13px;
    font-weight: 700;
    color: var(--text-secondary);
    padding: 8px 0;
  }

  #mini-calendar-container .calendar-day {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    font-size: 15px;
    font-weight: 600;
    color: var(--text-primary);
    cursor: pointer;
    border-radius: var(--radius-circle);
    transition: var(--transition);
    position: relative;
    margin: 2px;
  }

  #mini-calendar-container .calendar-day.empty {
    background: transparent;
    cursor: default;
    box-shadow: none;
  }

  #mini-calendar-container .calendar-day:hover:not(.empty) {
    background: var(--primary-light);
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
  }

  #mini-calendar-container .calendar-day.friday {
    color: #ff4d4f !important;
  }

  #mini-calendar-container .calendar-day.today {
    background: var(--gradient-primary);
    color: var(--background-card);
    font-weight: 700;
    border-radius: var(--radius-circle);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
  }

  #mini-calendar-container .calendar-day.active {
    background: var(--primary-light);
    color: var(--primary);
    font-weight: 600;
    border-radius: var(--radius-circle);
    box-shadow: 0 3px 10px rgba(37, 99, 235, 0.15);
  }

  #mini-calendar-container .calendar-day.selected {
    background: var(--secondary);
    color: var(--background-card);
    font-weight: 700;
    border-radius: var(--radius-circle);
    box-shadow: 0 0 16px rgba(34, 197, 94, 0.4);
    transform: scale(1.02);
  }

  #mini-calendar-container .calendar-day.has-event::after {
    content: '';
    position: absolute;
    bottom: 4px;
    width: 6px;
    height: 6px;
    background: var(--secondary);
    border-radius: var(--radius-circle);
    box-shadow: 0 0 6px rgba(34, 197, 94, 0.3);
  }

  #mini-calendar-container .calendar-footer {
    text-align: center;
    padding-top: 12px;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
  }

  @media (max-width: 576px) {
    #mini-calendar-container .calendar {
      padding: 12px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    #mini-calendar-container .calendar-header {
      gap: 6px;
    }

    #mini-calendar-container .select-group {
      gap: 4px;
    }

    #mini-calendar-container .calendar-select {
      font-size: 14px;
      padding: 4px 8px 4px 20px;
      background-position: left 6px center;
      border-radius: 5px;
      font-weight: 600;
    }

    #mini-calendar-container .nav-btn {
      width: 30px;
      height: 30px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    #mini-calendar-container .nav-btn svg {
      width: 14px;
      height: 14px;
    }

    #mini-calendar-container .calendar-day {
      width: 36px;
      height: 36px;
      font-size: 14px;
      border-radius: var(--radius-circle);
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
    }

    #mini-calendar-container .calendar-day-name {
      font-size: 12px;
      font-weight: 600;
    }

    #mini-calendar-container .calendar-day.has-event::after {
      width: 5px;
      height: 5px;
      bottom: 3px;
    }
  }

  @media (max-width: 425px) {
    #mini-calendar-container .calendar {
      padding: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
    }

    #mini-calendar-container .calendar-select {
      font-size: 13px;
      padding: 3px 7px 3px 18px;
      background-position: left 5px center;
      border-radius: 4px;
    }

    #mini-calendar-container .nav-btn {
      width: 28px;
      height: 28px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }

    #mini-calendar-container .nav-btn svg {
      width: 12px;
      height: 12px;
    }

    #mini-calendar-container .calendar-day {
      width: 32px;
      height: 32px;
      font-size: 13px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
    }

    #mini-calendar-container .calendar-day:hover:not(.empty) {
      transform: scale(1.05);
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
    }

    #mini-calendar-container .calendar-day-name {
      font-size: 11px;
      font-weight: 600;
    }

    #mini-calendar-container .calendar-day.has-event::after {
      width: 4px;
      height: 4px;
      bottom: 2px;
    }
  }
</style>

<div class="container calendar" id="mini-calendar-container">
  <div class="calendar-header w-100 d-flex justify-content-between align-items-center gap-4">
    <div>
      <button id="prev-month" class="btn btn-light nav-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <g id="Arrow / Chevron_Right_MD">
            <path id="Vector" d="M10 8L14 12L10 16" stroke="#000000" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </g>
        </svg>
      </button>
    </div>
    <div class="w-100 select-group">
      <select id="year" class="form-select w-100 bg-light border-0 calendar-select"></select>
    </div>
    <div class="w-100 select-group">
      <select id="month" class="form-select w-100 bg-light border-0 calendar-select"></select>
    </div>
    <div>
      <button id="next-month" class="btn btn-light nav-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <g id="Arrow / Chevron_Left_MD">
            <path id="Vector" d="M14 16L10 12L14 8" stroke="#000000" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </g>
        </svg>
      </button>
    </div>
  </div>
  <div class="calendar-body calendar-body-g-425">
    <div class="calendar-day-name text-center">ش</div>
    <div class="calendar-day-name text-center">ی</div>
    <div class="calendar-day-name text-center">د</div>
    <div class="calendar-day-name text-center">س</div>
    <div class="calendar-day-name text-center">چ</div>
    <div class="calendar-day-name text-center">پ</div>
    <div class="calendar-day-name text-center">ج</div>
  </div>
  <div class="calendar-body" id="calendar-body"></div>
</div>

