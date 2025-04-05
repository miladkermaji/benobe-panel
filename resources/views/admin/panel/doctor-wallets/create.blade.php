@extends('admin.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('admin-assets/css/panel/doctor-wallet/doctor-wallet.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'شارژ کیف پول')
    @livewire('admin.panel.doctor-wallets.doctor-wallet-create')

@endsection