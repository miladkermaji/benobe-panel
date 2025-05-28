@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/user-subscriptions/user-subscriptions.css') }}"
    rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'افزودن اشتراک جدید')
@livewire('admin.panel.user-subscriptions.subscription-create')
@endsection
