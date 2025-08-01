@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/secretary/secretaries.css') }}" rel="stylesheet">
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('bread-crumb-title', 'افزودن منشی')

@section('content')
  @livewire('dr.panel.secretary.secretary-create')
@endsection

@section('scripts')
  <script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
  <script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
@endsection
