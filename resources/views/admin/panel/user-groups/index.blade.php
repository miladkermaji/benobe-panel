@extends('admin.panel.layouts.master')

@section('styles')

@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت گروه کاربری')
@livewire('admin.panel.user-groups.user-group-list')
@endsection
