@extends('admin.panel.layouts.master')



@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت خدمات')
@livewire('admin.panel.services.service-list')
@endsection
