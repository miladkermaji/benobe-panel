@extends('admin.panel.layouts.master')
@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت کاربران زیر مجموعه')
@livewire('admin.panel.sub-users.sub-user-list')
@endsection
