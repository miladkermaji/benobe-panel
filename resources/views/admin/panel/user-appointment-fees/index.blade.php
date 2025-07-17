@extends('admin.panel.layouts.master')


@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت حق نوبت')
@livewire('admin.panel.user-appointment-fees.appointment-fee-list')
@endsection
