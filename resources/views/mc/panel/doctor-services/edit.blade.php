@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/css/panel/doctorservice/doctorservice-form.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش خدمات')
@livewire('mc.panel.doctor-services.doctor-service-edit', ['id' => $id])
@section('scripts')


  <script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>

@endsection
@endsection
