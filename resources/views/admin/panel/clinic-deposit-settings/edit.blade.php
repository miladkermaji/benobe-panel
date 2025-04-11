@extends('admin.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('admin-assets/css/panel/clinic-deposit-setting/clinic-deposit-setting.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'ویرایش بیعانه')
    @livewire('admin.panel.clinic-deposit-settings.clinic-deposit-settings-edit', ['id' => $id])
@endsection