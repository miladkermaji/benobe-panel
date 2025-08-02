@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/turn/schedule/scheduleSetting/scheduleSetting.css') }}"
    rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/turn/schedule/scheduleSetting/workhours.css') }}"
    rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/bime/bime.css') }}" rel="stylesheet" />

@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'بیمه')

<div class="main-content w-100 d-flex justify-content-center mt-4">
  <div class="subuser-content-wrapper p-3 w-100">
    @livewire('mc.panel.insurance.insurance-component')
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('mc-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/bime/bime.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
  $(function() {
    $('.card').css('width', '100%');
  });
</script>
@endsection
