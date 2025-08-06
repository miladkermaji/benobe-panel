@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/panel/css/users/users.css') }}" rel="stylesheet" />
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
@section('bread-crumb-title', 'افزودن تخصص')
@livewire('mc.panel.specialties.specialty-create')
@section('scripts')
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>

@endsection
@endsection
