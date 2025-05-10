@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/css/panel/doctor-faq/doctor-faq.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet">

@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش سوالات متداول')
@livewire('dr.panel.doctor-faqs.doctor-faqs-edit', ['id' => $id])
@section('scripts')
  <script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
@endsection
@endsection
