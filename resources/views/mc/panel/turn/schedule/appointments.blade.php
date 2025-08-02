@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/turn/schedule/appointments.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل مرکز درمانی' }}
@endsection
@section('content')
@section('bread-crumb-title', 'لیست نوبت ها')
@livewire('mc.panel.turn.schedule.appointments-list')
@endsection
@section('scripts')
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
<script>
  // مسیرهای مناسب برای مرکز درمانی
  var appointmentsSearchUrl = "{{ route('mc.search.appointments') }}";
  var appointmentsCountUrl = "{{ route('appointments.count') }}";
  var getHolidaysUrl = "{{ route('doctor.get_holidays') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
</script>
@endsection
