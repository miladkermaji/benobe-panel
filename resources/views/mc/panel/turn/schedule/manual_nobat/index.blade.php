@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/turn/schedule/appointments.css') }}" rel="stylesheet" />


@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'لیست نوبت ها')
@livewire('mc.panel.turn.schedule.manual-nobat-list')
@endsection
@section('scripts')
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>


<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var appointmentsCountUrl = "{{ route('appointments.count') }}?manual_only=true";
  var getHolidaysUrl = "{{ route('doctor.get_holidays') }}";
  var updateStatusAppointmentUrl =
    "{{ route('updateStatusAppointment', ':id') }}";
</script>

@endsection
