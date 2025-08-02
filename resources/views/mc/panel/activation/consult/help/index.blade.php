@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/turn/schedule/scheduleSetting/scheduleSetting.css') }}"
    rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/turn/schedule/scheduleSetting/workhours.css') }}"
    rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/activation/consult/rules/index.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/activation/consult/help/help.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'آموزش')

<div class="workhours-content w-100 d-flex justify-content-center mt-4">
  <div class="workhours-wrapper-content consult-wrapper p-3">
    <div class="">
      <div
        class="MuiContainer-root MuiContainer-maxWidthSm flex flex-col h-full pt-4 space-y-5 bg-white rounded-md md:h-auto md:p-5 md:mt-8 md:shadow-2xl md:shadow-slate-300 muirtl-bbjvwn page-enter-done">
        <span class="d-block w-100  fw-bold text-center">ویدیو آموزشی پزشک مشاوره آنلاین به نوبه</span>
        <div class="overflow-scroll mt-3">
          <video src="https://benobe.ir/uploads/home_video/1666005351_benobe.mp4" class="border-radius-6 w-100"
            controls></video>

        </div>
        <button onclick="location.href='{{ route('activation.consult.messengers') }}'"
          class="btn my-btn-primary h-50 w-100 mt-2" tabindex="0" type="button">ادامه
        </button>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('mc-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl =
    "{{ route('updateStatusAppointment', ':id') }}";
</script>
@endsection
