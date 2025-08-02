@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/turn/schedule/appointments.css') }}" rel="stylesheet" />


@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'لیست نوبت های مشاوره')
@livewire('mc.panel.turn.schedule.counseling-appointments-list')
@endsection
@section('scripts')
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
@once
  <script src="{{ asset('mc-assets/panel/js/calendar/counseling-reschedule-calendar.js') }}"></script>

@endonce
@once
  <script src="{{ asset('mc-assets/panel/js/calendar/custm-calendar-row-counseling.js') }}"></script>

  <script src="{{ asset('mc-assets/panel/js/calendar/custm-calendar.js') }}"></script>
@endonce

<script>
  var appointmentsSearchUrl = "{{ route('search.appointments.counseling') }}";
  var appointmentsCountUrl = "{{ route('appointments.count.counseling') }}";
  var getHolidaysUrl = "{{ route('doctor.get_holidays') }}";
  var updateStatusAppointmentUrl =
    "{{ route('updateStatusAppointment', ':id') }}";
</script>

@endsection
