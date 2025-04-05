@extends('admin.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('admin-assets/css/panel/doctorinsurance/doctorinsurance.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'افزودن بیمه جدید')
    @livewire('admin.panel.doctor-insurances.doctor-insurance-create')
@endsection