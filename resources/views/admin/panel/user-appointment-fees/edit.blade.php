@extends('admin.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('admin-assets/css/panel/user-appointment-fees/user-appointment-fees.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'ویرایش حق نوبت')
    @livewire('admin.panel.user-appointment-fees.appointment-fee-edit', ['userAppointmentFee' => $userAppointmentFee])
@endsection 