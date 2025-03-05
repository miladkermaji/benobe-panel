@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/tools/payment-gateways/payment-gateways.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'درگاه های پرداخت')

  @livewire('admin.panel.tools.payment-gateways.gateway-list')
@endsection