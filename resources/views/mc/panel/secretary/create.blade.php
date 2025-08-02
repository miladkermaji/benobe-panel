@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/secretary/secretaries.css') }}" rel="stylesheet">
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('bread-crumb-title', 'افزودن منشی')

@section('content')
  @livewire('mc.panel.secretary.secretary-create')
@endsection

@section('scripts')
  <script src="{{ asset('mc-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
  <script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
@endsection
