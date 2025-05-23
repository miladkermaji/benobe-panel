@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/css/panel/doctorservice/doctorservice-form.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش خدمات')
@livewire('dr.panel.doctor-services.doctor-service-edit', ['id' => $id])
@section('scripts')


  <script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>

@endsection
@endsection
