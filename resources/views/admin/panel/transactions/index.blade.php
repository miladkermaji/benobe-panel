@extends('admin.panel.layouts.master')


@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'مدیریت تراکنش ها')
    @livewire('admin.panel.transactions.transaction-list')
@endsection