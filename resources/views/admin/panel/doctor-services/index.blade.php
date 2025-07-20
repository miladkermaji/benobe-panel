@extends('admin.panel.layouts.master')


@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت خدمات دکتر')
@livewire('admin.panel.doctor-services.doctor-service-list')
@endsection
