@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/user-membership-plans/user-membership-plans.css') }}"
    rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش حق عضویت')
@livewire('admin.panel.user-membership-plans.membership-plan-edit', ['userMembershipPlan' => $userMembershipPlan])
@endsection
