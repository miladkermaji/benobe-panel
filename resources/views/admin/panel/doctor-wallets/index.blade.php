@extends('admin.panel.layouts.master')



@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'لیست تراکنش ها')
    @livewire('admin.panel.doctor-wallets.doctor-wallet-list')
@endsection