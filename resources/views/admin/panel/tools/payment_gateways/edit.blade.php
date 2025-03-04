@extends('admin.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش درگاه پرداخت')
    @livewire('admin.panel.tools.payment-gateways.gateway-edit', ['name' => Route::current()->parameter('name')])
@endsection