@extends('admin.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('admin-assets/css/panel/doctor-wallet/doctor-wallet.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'ویرایش doctorwallets')
    @livewire('admin.panel.doctor-wallets.doctor-wallet-edit', ['id' => $id])
@endsection