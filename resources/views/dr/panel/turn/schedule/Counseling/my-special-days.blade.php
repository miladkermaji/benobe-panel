@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/schedule-setting/my-special-days.css') }}"
    rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'تعطیلات و نوبت دهی روز های خاص مشاوره')
<div class="container calendar mt-2">
  <div class="calendar-header w-100 d-flex justify-content-between align-items-center gap-4">
    <div class="">
      <button id="prev-month" class="btn btn-light">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
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
    <div class="">
      <button id="next-month" class="btn btn-light"><svg xmlns="http://www.w3.org/2000/svg" width="24"
          height="24" viewBox="0 0 24 24" fill="none">
          <g id="Arrow / Chevron_Left_MD">
            <path id="Vector" d="M14 16L10 12L14 8" stroke="#000000" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </g>
        </svg>
      </button>
    </div>
  </div>
  <div class="calendar-body calendar-body-g-425"> <!-- عناوین روزهای هفته -->
    <div class="calendar-day-name text-center">شنبه</div>
    <div class="calendar-day-name text-center">یک‌شنبه</div>
    <div class="calendar-day-name text-center">دوشنبه</div>
    <div class="calendar-day-name text-center">سه‌شنبه</div>
    <div class="calendar-day-name text-center">چهارشنبه</div>
    <div class="calendar-day-name text-center">پنج‌شنبه</div>
    <div class="calendar-day-name text-center">جمعه</div>
  </div>
  <div class="calendar-body-425 d-none"> <!-- عناوین روزهای هفته -->
    <div class="calendar-day-name text-center">ش</div>
    <div class="calendar-day-name text-center">ی</div>
    <div class="calendar-day-name text-center">د</div>
    <div class="calendar-day-name text-center">س</div>
    <div class="calendar-day-name text-center">چ</div>
    <div class="calendar-day-name text-center">پ</div>
    <div class="calendar-day-name text-center">ج</div>
  </div>
  <div class="calendar-body" id="calendar-body"> <!-- تقویم در اینجا بارگذاری می‌شود --> </div>
</div> <!-- Modal -->
<div class="modal fade" id="dateModal" tabindex="-1" aria-labelledby="dateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">

    <div class="modal-content border-radius-6">

      <div class="modal-header">
        <h6 class="modal-title" id="dateModalLabel">تاریخ</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">

      </div>
    </div>
  </div>
</div>
<!-- جابجایی نوبت Modal -->
<div class="modal  fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h6 class="modal-title" id="rescheduleModalLabel">جابجایی نوبت</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="calendar-header w-100 d-flex justify-content-between align-items-center gap-4">
          <div class="">
            <button id="prev-month-reschedule" class="btn btn-light">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none">
                <g id="Arrow / Chevron_Right_MD">
                  <path id="Vector" d="M10 8L14 12L10 16" stroke="#000000" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" />
                </g>
              </svg>
            </button>
          </div>
          <div class="w-100">
            <select id="year-reschedule" class="form-select w-100 bg-light border-0"></select>
          </div>
          <div class="w-100">
            <select id="month-reschedule" class="form-select w-100 bg-light border-0"></select>
          </div>
          <div class="">
            <button id="next-month-reschedule" class="btn btn-light"><svg xmlns="http://www.w3.org/2000/svg"
                width="24" height="24" viewBox="0 0 24 24" fill="none">
                <g id="Arrow / Chevron_Left_MD">
                  <path id="Vector" d="M14 16L10 12L14 8" stroke="#000000" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" />
                </g>
              </svg>
            </button>
          </div>
        </div>
        <div class="w-100 d-flex justify-content-end">
          <button id="goToFirstAvailable" class="btn btn-light w-100 border">برو به اولین نوبت خالی</button>
        </div>
        <div class="calendar-body calendar-body-g-425"> <!-- عناوین روزهای هفته -->
          <div class="calendar-day-name text-center">شنبه</div>
          <div class="calendar-day-name text-center">یک‌شنبه</div>
          <div class="calendar-day-name text-center">دوشنبه</div>
          <div class="calendar-day-name text-center">سه‌شنبه</div>
          <div class="calendar-day-name text-center">چهارشنبه</div>
          <div class="calendar-day-name text-center">پنج‌شنبه</div>
          <div class="calendar-day-name text-center">جمعه</div>
        </div>
        <div class="calendar-body-425 d-none"> <!-- عناوین روزهای هفته -->
          <div class="calendar-day-name text-center">ش</div>
          <div class="calendar-day-name text-center">ی</div>
          <div class="calendar-day-name text-center">د</div>
          <div class="calendar-day-name text-center">س</div>
          <div class="calendar-day-name text-center">چ</div>
          <div class="calendar-day-name text-center">پ</div>
          <div class="calendar-day-name text-center">ج</div>
        </div>
        <div id="calendar-reschedule" class="calendar-body"></div>
      </div>
    </div>
  </div>
</div>
<div class="calendar-legend mt-4">
  <div class="d-flex flex-wrap justify-content-center gap-3">
    <div class="legend-item d-flex align-items-center">
      <div class="legend-color bg-primary"></div>
      <span class="legend-text">امروز</span>
    </div>
    <div class="legend-item d-flex align-items-center">
      <div class="legend-color bg-success"></div>
      <span class="legend-text">روز با نوبت</span>
    </div>
    <div class="legend-item d-flex align-items-center">
      <div class="legend-color bg-danger"></div>
      <span class="legend-text">روز تعطیل</span>
    </div>
    <div class="legend-item d-flex align-items-center">
      <div class="legend-color holiday-dot"></div>
      <span class="legend-text">نشانگر تعطیلی</span>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl =
    "{{ route('updateStatusAppointment', ':id') }}";
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('showModal')) {
      // فرض کنید ID مودال شما "activation-modal" است
      $('#activation-modal').modal('show');
    }
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/jalali-moment/dist/jalali-moment.browser.js"></script>
@include('dr.panel.my-tools.mySpecialDayCounseling')
@endsection
