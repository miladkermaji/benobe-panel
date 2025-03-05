@extends('admin.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('admin-assets/css/panel/tools/sms-gateways/sms-gateways.css') }}"
    rel="stylesheet" />
@endsection

@section('site-header')
 {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'پنل پیامکی')

  @livewire('admin.panel.tools.sms-gateway.sms-gateway-edit', ['name' => $gateway->name])
@endsection