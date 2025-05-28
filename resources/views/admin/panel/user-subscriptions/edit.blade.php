@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/user-subscriptions/user-subscriptions.css') }}"
    rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش اشتراک')
@livewire('admin.panel.user-subscriptions.subscription-edit', ['userSubscription' => $userSubscription])
@endsection
