@extends('admin.panel.layouts.master')

@section('styles')

@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت کاربران مسدود')
@livewire('admin.panel.user-blockings.user-blocking-list')
@endsection
