@extends('mc.panel.layouts.master')

@section('styles')
<link type="text/css" href="{{ asset('mc-assets/panel/css/mc-panel.css') }}" rel="stylesheet">
<link type="text/css" href="{{ asset('mc-assets/css/panel/doctornote/doctornote.css') }}" rel="stylesheet">
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت مطب‌ها')
@livewire('mc.panel.doctors-clinic.doctor-clinic-list')
@section('scripts')
  <script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
@endsection
@endsection
