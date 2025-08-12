@extends('admin.panel.layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('admin-assets/panel/css/transactions.css') }}">
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت تراکنش ها')
@livewire('admin.panel.transactions.transaction-list')
@endsection
