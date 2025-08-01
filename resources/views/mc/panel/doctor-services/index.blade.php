@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/css/panel/doctorservice/doctorservice.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/panel.css') }}">

@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت توضیحات')
@livewire('dr.panel.doctor-services.doctor-service-list')
@section('scripts')


  <script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>

  
@endsection
@endsection
