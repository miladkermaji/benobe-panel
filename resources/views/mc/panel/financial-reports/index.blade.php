@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/financial-reports/financial-reports.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | گزارش مالی' }}
@endsection

@section('content')
@section('bread-crumb-title', 'گزارش مالی')

@livewire('dr.panel.financial.financial-report')

@section('scripts')
  <script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
@endsection
@endsection
