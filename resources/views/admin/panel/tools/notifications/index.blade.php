@extends('admin.panel.layouts.master')
@section('styles')
<link rel="stylesheet" href="{{ asset('admin-assets/css/panel/tools/page-builder/page-builder.css') }}">
<link rel="stylesheet" href="{{ asset('admin-assets/css/panel/doctor/doctor.css') }}">
@endsection
@section('site-header')
 {{ 'به نوبه | پنل مدیریت' }}
@endsection
@section('content')
@section('bread-crumb-title', '   مدیریت اعلان ها')
@section('scripts')

 @livewire('admin.panel.tools.notification-list')

@endsection