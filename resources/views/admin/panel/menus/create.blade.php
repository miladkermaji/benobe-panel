@extends('Admin.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('Admin-assets/css/panel/menu/menu.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'افزودن منو جدید')
    @livewire('Admin.panel.menus.menu-create')
@endsection