@extends('admin.panel.layouts.master')
@section('styles')
  
  <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/tools/notifications/notification-create.css') }}">

@endsection
@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection
@section('content')
@section('bread-crumb-title', 'ایجاد اعلان')
@section('scripts')

  @livewire('admin.panel.tools.notification-create')

@endsection
