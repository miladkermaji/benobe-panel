@extends('admin.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('admin-assets/css/panel/manualappointmentsetting/manualappointmentsetting.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'افزودن تنظیمات جدید')
    @livewire('admin.panel.manual-appointment-settings.manual-appointment-setting-create')
@endsection