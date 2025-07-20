@extends('admin.panel.layouts.master')


@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت منوها')
@livewire('admin.panel.menus.menu-list')
@endsection
