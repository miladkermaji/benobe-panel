@extends('admin.panel.layouts.master')

@section('styles')
 <link type="text/css" href="{{ asset('admin-assets/css/panel/tools/payment-gateways/payment-gateways.css') }}"
  rel="stylesheet" />
@endsection

@section('site-header')
 {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
 @livewire('admin.panel.tools.telescope.telescope-viewer')
@endsection
