@extends('Admin.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('Admin-assets/css/panel/imagingcenter/imagingcenter.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'افزودن  مراکز تصویربرداری جدید')
    @livewire('Admin.panel.imagingcenters.imagingcenter-create')
@endsection