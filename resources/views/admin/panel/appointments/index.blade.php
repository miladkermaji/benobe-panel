@extends('admin.panel.layouts.master')


@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'مدیریت نوبت ها')
    @livewire('admin.panel.appointments.appointment-list')
@endsection