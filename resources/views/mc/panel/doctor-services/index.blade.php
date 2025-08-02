@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/css/panel/doctorservice/doctorservice.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('mc-assets/panel/css/panel.css') }}">

@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت توضیحات')
@livewire('mc.panel.doctor-services.doctor-service-list')
@section('scripts')


  <script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>


@endsection
@endsection
