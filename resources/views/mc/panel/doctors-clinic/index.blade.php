@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/css/panel/doctornote/doctornote.css') }}" rel="stylesheet">
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت مطب‌ها')
@livewire('dr.panel.doctors-clinic.doctor-clinic-list')
@section('scripts')
  <script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
@endsection
@endsection
