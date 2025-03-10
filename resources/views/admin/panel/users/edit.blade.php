@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('public/admin-assets/panel/css/users/users.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
 {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش کاربر')
 @livewire('admin.panel.users.user-edit', ['id' => $id])
@endsection