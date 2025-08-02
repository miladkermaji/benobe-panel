@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />

  <link type="text/css" href="{{ asset('mc-assets/panel/css/turn/schedule/scheduleSetting/workhours.css') }}"
    rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل مرکز درمانی' }}
@endsection
@section('content')
@section('bread-crumb-title', 'تعطیلات و نوبت دهی روز های خاص')


@endsection
@section('scripts')
<script src="{{ asset('mc-assets/panel/js/home/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>

@livewire('mc.panel.turn.schedule.special-days-appointment')
<script src="{{ asset('mc-assets/panel/js/jalali-moment/dist/jalali-moment.browser.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/calendar/special-days-calendar.js') }}"></script>
@endsection
