@extends('admin.panel.layouts.master')
@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'مدیریت اشتراک‌ها')
    @livewire('admin.panel.user-subscriptions.subscription-list')
@endsection 