@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/financial-reports/financial-reports.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | گزارش مالی' }}
@endsection

@section('content')
@section('bread-crumb-title', 'گزارش مالی')

@livewire('mc.panel.financial.financial-report')

@section('scripts')
  <script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
@endsection
@endsection
