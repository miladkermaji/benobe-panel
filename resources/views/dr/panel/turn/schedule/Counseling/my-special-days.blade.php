@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />

  <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/scheduleSetting/workhours.css') }}"
    rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'تعطیلات و نوبت دهی روز های خاص')


@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/js/home/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>

@livewire('dr.panel.turn.schedule.counseling-special-days-apoointment')
<script src="{{ asset('dr-assets/panel/js/jalali-moment/dist/jalali-moment.browser.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/calendar/counseling-special-days-calendar.js') }}"></script>

@endsection
