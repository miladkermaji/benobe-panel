@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/clinic/clinic.css') }}" rel="stylesheet" />
  <style>
    .myPanelOption {
      display: none !important;
    }
  </style>
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدارک من')
@if (Auth::guard('medical_center')->check())
  @livewire('mc.panel.doctors.doctor-documents')
@else
  @livewire('mc.panel.doctors.doctor-documents', ['doctorId' => $doctorId])
@endif
@endsection
@section('scripts')
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
@endsection
