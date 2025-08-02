@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/doctor-clinics/creaete.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | ویرایش مطب' }}
@endsection
@section('content')
@section('bread-crumb-title', 'ویرایش مطب')
@livewire('mc.panel.doctors-clinic.doctor-clinic-edit', ['id' => $clinic->id])
@section('scripts')
  <script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
@endsection
@endsection
