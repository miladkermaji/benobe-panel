<style>
  body {
    direction: rtl;
    background-color: #f8fafc;
  }

  .calendar {
    margin: 10px auto;
    border: none;
    border-radius: 6px;
    padding: 12px;
    background-color: #ffffff;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.2s ease;
  }

  .calendar:hover {
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07);
  }

  .text-title-calendar-modal {
    position: relative !important;
    right: 10px !important;
    top: 8px !important;
    font-weight: 600;
    font-size: 14px;
    color: #1e293b;
  }

  .my-modal-header {
    position: relative;
    border-bottom: none;
    padding-bottom: 0;
  }

  .my-modal-header button {
    position: relative !important;
    left: 10px !important;
    top: 8px !important;
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #64748b;
    transition: color 0.2s ease;
  }

  .my-modal-header button:hover {
    color: #1e293b;
  }

  .calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    gap: 8px;
    /* فاصله کمتر برای جمع‌وجور بودن */
  }

  .calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    grid-gap: 4px;
    /* فاصله کمتر برای مرتب‌تر شدن */
    margin-top: 10px;
  }

  .calendar-day {
    text-align: center;
    background-color: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    aspect-ratio: 1.5;
    /* نسبت جمع‌وجورتر */
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    font-size: 13px;

    /* فونت کوچکتر برای فضای مودال */
    font-weight: 600;
    color: #475569;
    transition: all 0.2s ease;
    position: relative;
  }

  .calendar-day:hover {
    background-color: #f1f5f9;
    border-color: #d1d5db;
    transform: translateY(-1px);
  }

  .calendar-day.friday {
    color: #ef4444;
  }

  .calendar-day.active {
    background-color: #dbeafe;
    border-color: #3b82f6;
    color: #1e40af;
    box-shadow: 0 2px 6px rgba(59, 130, 246, 0.15);
  }

  .calendar-day.selected {
    background-color: #e0f2fe;
    border-color: #0284c7;
    color: #0c4a6e;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.15);
  }

  .calendar-day-name {
    font-weight: 600;
    text-align: center;
    color: #1e293b;
    font-size: 12px;
    /* فونت کوچکتر برای مرتب‌تر شدن */
    padding: 6px 0;
  }

  .my-badge-success {
    position: absolute;
    top: -4px;
    left: -4px;
    width: 18px;
    height: 18px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #10b981;
    color: #ffffff;
    border-radius: 50%;
    font-size: 0.65rem;
    font-weight: 600;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .my-sm-badge-success {
    position: absolute;
    top: -4px;
    left: -4px;
    width: 14px;
    height: 14px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #10b981;
    color: #ffffff;
    border-radius: 50%;
    font-size: 0.6rem;
    font-weight: 600;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  #calendar-reschedule .calendar-day {
    font-size: 12px;
    font-weight: 600;
  }

  .form-select {
    width: 100%;
    padding: 6px 10px;
    /* اندازه کوچکتر برای مودال */
    border-radius: 4px;
    border: 1px solid #d1d5db;
    background-color: #f9fafb;
    font-size: 13px;
    color: #1e293b;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
  }

  .form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 5px rgba(59, 130, 246, 0.3);
  }

  .btn-light {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 6px;
    /* کوچکتر برای مودال */
    transition: all 0.2s ease;
  }

  .btn-light:hover {
    background: #f1f5f9;
    border-color: #d1d5db;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  }

  .btn-light svg {
    stroke: #475569;
  }

  .btn-light:hover svg {
    stroke: #3b82f6;
  }

  @media only screen and (max-width: 768px) {
    .calendar-day {
      font-size: 11px;
      font-weight: 600;
    }



    .my-badge-success {
      font-size: 0.6rem;
      width: 16px;
      height: 16px;
    }
  }

  @media only screen and (max-width: 425px) {
    .calendar-body-g-425 {
      display: none !important;
    }

    .calendar-day {
      font-size: 10px;
      text-align: left !important;
    }



    .my-badge-success {
      font-size: 0.55rem;
      width: 14px;
      height: 14px;
    }

    .my-sm-badge-success {
      font-size: 0.55rem;
      width: 12px;
      height: 12px;
    }

    .calendar-body-425 {
      display: flex !important;
      text-align: center !important;
      justify-content: space-between !important;
    }

    .calendar-body-425 .calendar-day-name {
      text-align: center !important;
      width: 100%;
    }

    .calendar-day {
      font-size: 10px;
      font-weight: 600;
    }
  }
</style>

<div class="container calendar">
  <div class="calendar-header w-100 d-flex justify-content-between align-items-center gap-4">
    <div>
      <button id="prev-month" class="btn btn-light">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <g id="Arrow / Chevron_Right_MD">
            <path id="Vector" d="M10 8L14 12L10 16" stroke="#000000" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </g>
        </svg>
      </button>
    </div>
    <div class="w-100">
      <select id="year" class="form-select w-100 bg-light border-0"></select>
    </div>
    <div class="w-100">
      <select id="month" class="form-select w-100 bg-light border-0"></select>
    </div>
    <div>
      <button id="next-month" class="btn btn-light">
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
    <!-- عناوین روزهای هفته -->
    <div class="calendar-day-name text-center">ش</div>
    <div class="calendar-day-name text-center">ی</div>
    <div class="calendar-day-name text-center">د</div>
    <div class="calendar-day-name text-center">س</div>
    <div class="calendar-day-name text-center">چ</div>
    <div class="calendar-day-name text-center">پ</div>
    <div class="calendar-day-name text-center">ج</div>
  </div>
  <div class="calendar-body" id="calendar-body">
    <!-- تقویم در اینجا بارگذاری می‌شود -->
  </div>
</div>

@push('scripts')
  <script src="{{ asset('dr-assets/panel/js/calendar/custm-calendar.js') }}"></script>

@endpush
