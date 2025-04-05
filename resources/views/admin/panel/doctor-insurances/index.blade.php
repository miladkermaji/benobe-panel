@extends('admin.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('admin-assets/css/panel/doctorinsurance/doctorinsurance.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'مدیریت بیمه های پزشک')
    @livewire('admin.panel.doctor-insurances.doctor-insurance-list')
@endsection