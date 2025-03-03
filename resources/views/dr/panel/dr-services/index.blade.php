@extends('dr.panel.layouts.master')

@section('styles')
 <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
 {{ 'به نوبه | خدمات دکتر' }}
@endsection
@section('bread-crumb-title', 'خدمات دکتر')

@section('content')
 <div class="container my-4">
  <div class="card shadow-sm">
   <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="mb-0">لیست خدمات دکتر</h4>
    <a href="{{ route('dr-services.create') }}" class="btn btn-primary text-white">ایجاد خدمت جدید</a>
   </div>
   <div class="card-body">
    @livewire('dr.panel.doctor-services.doctor-services')
   </div>
  </div>
 </div>
@endsection

@section('scripts')
 <script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
@endsection
