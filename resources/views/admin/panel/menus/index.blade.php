@extends('admin.panel.layouts.master')

@section('styles')
 <link type="text/css" href="{{ asset('admin-assets/css/panel/menu/menu.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
 {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت منوها')
@livewire('admin.panel.menus.menu-list')
@endsection
