@extends('mc.panel.layouts.master')

@section('styles')
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مرکز درمانی' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت پزشکان')
@livewire('mc.panel.doctors.doctor-list')
@section('scripts')
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>

@endsection
@endsection
