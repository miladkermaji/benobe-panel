@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/doctor-clinics/creaete.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | افزودن مطب' }}
@endsection
@section('content')
@section('bread-crumb-title', 'افزودن مطب')
@livewire('dr.panel.doctors-clinic.doctor-clinic-create')
@section('scripts')
  <script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
@endsection
@endsection
