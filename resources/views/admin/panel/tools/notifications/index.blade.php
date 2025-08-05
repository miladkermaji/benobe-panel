@extends('admin.panel.layouts.master')

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection
@section('content')
@section('bread-crumb-title', ' مدیریت اعلان ها')
@section('scripts')

  @livewire('admin.panel.tools.notification-list')

@endsection
