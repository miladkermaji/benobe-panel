@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/css/panel/doctor-faq/doctor-faq.css') }}" rel="stylesheet">
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet">
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'لیست سوالات متداول')
<livewire:mc.panel.doctor-faqs.doctor-faqs-list />
@section('scripts')
  <script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
@endsection
@endsection
