@extends('mc.panel.layouts.master')

@section('styles')
  <style>
    .myPanelOption {
      display: none !important;
    }
  </style>
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مرکز درمانی' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت بیمه‌ها')
@livewire('mc.panel.insurances.insurance-list')
@section('scripts')
  <script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
@endsection
@endsection
