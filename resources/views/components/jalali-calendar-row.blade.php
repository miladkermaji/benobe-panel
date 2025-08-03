<link rel="stylesheet" href="{{ asset('mc-assets/panel/css/calendar/custom-calendar-row.css') }}">
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
    
  </div>
</div>

<script src="{{ asset('mc-assets/panel/js/jquery-easing/1.4.1/jquery.easing.min.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/calendar/custm-calendar-row.js') }}"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
  // تعیین نوع گارد و مسیرهای مناسب
  (function() {
    const currentPath = window.location.pathname;
    let guardType = 'doctor'; // پیش‌فرض
    let appointmentsCountUrl = '';
    let getHolidaysUrl = '';
    let searchAppointmentsUrl = '';

    // تشخیص نوع گارد بر اساس مسیر
    if (currentPath.includes('/mc/')) {
      guardType = 'medical_center';
      appointmentsCountUrl = "{{ route('appointments.count') }}";
      getHolidaysUrl = "{{ route('doctor.get_holidays') }}";
      searchAppointmentsUrl = "{{ route('mc.search.appointments') }}";
    } else if (currentPath.includes('/dr/')) {
      guardType = 'doctor';
      appointmentsCountUrl = "{{ route('appointments.count') }}";
      getHolidaysUrl = "{{ route('doctor.get_holidays') }}";
      searchAppointmentsUrl = "{{ route('search.appointments') }}";
    } else if (currentPath.includes('/secretary/')) {
      guardType = 'secretary';
      appointmentsCountUrl = "{{ route('appointments.count') }}";
      getHolidaysUrl = "{{ route('doctor.get_holidays') }}";
      searchAppointmentsUrl = "{{ route('search.appointments') }}";
    }

    // تنظیم متغیرهای سراسری
    window.guardType = guardType;
    window.appointmentsCountUrl = appointmentsCountUrl;
    window.getHolidaysUrl = getHolidaysUrl;
    window.searchAppointmentsUrl = searchAppointmentsUrl;

    // برای مراکز درمانی، نیازی به selectedClinicId نیست
    if (guardType === 'medical_center') {
      window.selectedClinicId = null;
    } else {
      // برای پزشک و منشی، از localStorage استفاده کن
      window.selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';
    }
  })();
</script>
