@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/userblocking/userblocking.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش کاربران مسدود')
@livewire('admin.panel.user-blockings.user-blocking-edit', ['id' => $id])
@endsection
