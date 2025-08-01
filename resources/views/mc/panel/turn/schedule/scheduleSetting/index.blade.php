@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/scheduleSetting/scheduleSetting.css') }}"
    rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'تنظیمات نوبت دهی')

<div class="schedule-setting-content d-flex justify-content-center w-100 mt-4">
  <div class="schedule-setting-wrapper col-xs-12 col-sm-12 col-md-8 col-lg-8">
    <ul>
      <li class="d-flex align-items-center cursor-pointer" tabindex="0" role="button"
        onclick="location.href='{{ route('dr-workhours') }}'">
        <div class="my-svg-setting-nobat">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="#2d3748" stroke-width="1.5" />
            <path d="M12 6V12L16 14" stroke="#2d3748" stroke-width="1.5" stroke-linecap="round"
              stroke-linejoin="round" />
          </svg>
        </div>
        <div class="ml-2">
          <span class="fw-bold">ساعت کاری</span>
          <p>ساعت کاری خود را همیشه بروز نگه دارید</p>
        </div>
      </li>
      <li class="d-flex align-items-center cursor-pointer" tabindex="0" role="button"
        onclick="location.href='{{ route('dr-vacation') }}'">
        <div class="my-svg-setting-nobat">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M9.03023 21.69L11.3602 19.73C11.7102 19.43 12.2902 19.43 12.6402 19.73L14.9702 21.69C15.5102 21.96 16.1702 21.69 16.3702 21.11L16.8102 19.78C16.9202 19.46 16.8102 18.99 16.5702 18.75L14.3002 16.47C14.1302 16.31 14.0002 15.99 14.0002 15.76V12.91C14.0002 12.49 14.3102 12.29 14.7002 12.45L19.6102 14.57C20.3802 14.9 21.0102 14.49 21.0102 13.65V12.36C21.0102 11.69 20.5102 10.92 19.8902 10.66L14.3002 8.25001C14.1402 8.18001 14.0002 7.97001 14.0002 7.79001V4.79001C14.0002 3.85001 13.3102 2.74001 12.4702 2.31001C12.1702 2.16001 11.8202 2.16001 11.5202 2.31001C10.6802 2.74001 9.99023 3.86001 9.99023 4.80001V7.80001C9.99023 7.98001 9.85023 8.19001 9.69023 8.26001L4.11023 10.67C3.49023 10.92 2.99023 11.69 2.99023 12.36V13.65C2.99023 14.49 3.62023 14.9 4.39023 14.57L9.30023 12.45C9.68023 12.28 10.0002 12.49 10.0002 12.91V15.76C10.0002 15.99 9.87023 16.31 9.71023 16.47L7.44023 18.75C7.20023 18.99 7.09023 19.45 7.20023 19.78L7.64023 21.11C7.82023 21.69 8.48023 21.97 9.03023 21.69Z"
              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        </div>
        <div class="ml-2">
          <span class="fw-bold">روزهای تعطیل</span>
          <p>اعلام روز تعطیل مشاوره آنلاین</p>
        </div>
      </li>
      <li class="d-flex align-items-center cursor-pointer" tabindex="0" role="button"
        onclick="location.href='{{ route('doctor-blocking-users.index') }}'">
        <div class="my-svg-setting-nobat">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path
              d="M14.8086 19.7053L19.127 16.3467M4 21C4 17.134 7.13401 14 11 14M20 18C20 19.6569 18.6569 21 17 21C15.3431 21 14 19.6569 14 18C14 16.3431 15.3431 15 17 15C18.6569 15 20 16.3431 20 18ZM15 7C15 9.20914 13.2091 11 11 11C8.79086 11 7 9.20914 7 7C7 4.79086 8.79086 3 11 3C13.2091 3 15 4.79086 15 7Z"
              stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>
        <div class="ml-2">
          <span class="fw-bold">مسدود کردن کاربر</span>
          <p>مدیریت کاربران مسدود</p>
        </div>
      </li>
    </ul>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
</script>
@endsection
