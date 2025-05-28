@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/user-appointment-fees/user-appointment-fees.css') }}"
    rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'افزودن حق نوبت جدید')
@livewire('admin.panel.user-appointment-fees.appointment-fee-create')
@endsection
