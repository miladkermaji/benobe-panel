
<link rel="stylesheet" href="{{ asset('dr-assets/panel/css/calendar/custom-calendar-row.css') }}">
<div class="w-100 d-flex justify-content-around align-items-center" style="margin: 0; padding: 0; position: relative;">
  <div class="w-100 d-flex align-items-center gap-2">
    <div>
      <button id="prevRow" class="btn btn-light">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <g id="Arrow / Chevron_Right_MD">
            <path id="Vector" d="M10 8L14 12L10 16" stroke="#000000" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </g>
        </svg>
      </button>
    </div>
    <div id="calendar" class="d-flex w-100" style="display: none;">
      <!-- تقویم اولیه با تاریخ کنونی پر می‌شود -->
    </div>
    <div class="loading-overlay" id="calendar-loading">
      <div class="loading-spinner"></div>
    </div>
    <div>
      <button id="nextRow" class="btn btn-light">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <g id="Arrow / Chevron_Left_MD">
            <path id="Vector" d="M14 16L10 12L14 8" stroke="#000000" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </g>
        </svg>
      </button>
    </div>
  </div>
  <div id="calendar-error" class="error-message">
    خطا در بارگذاری تعداد نوبت‌ها. لطفاً دوباره تلاش کنید.
  </div>
</div>

<script src="{{ asset('dr-assets/panel/js/jquery-easing/1.4.1/jquery.easing.min.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/calendar/custm-calendar-row.js') }}"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">