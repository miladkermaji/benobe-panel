@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/prescription/prescription.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'تنظیمات درخواست نسخه')
<div class="container-fluid py-3">
  <div class="row">
    <div class="col-12">
      @livewire('dr.panel.doctor-prescriptions.prescription-settings')
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/bootstrap.bundle.min.js') }}"></script>
@endsection
