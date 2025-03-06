@extends('admin.panel.layouts.master')
@section('styles')
<link rel="stylesheet" href="{{ asset('admin-assets/css/panel/tools/page-builder/page-builder.css') }}">
@endsection
@section('site-header')
 {{ 'به نوبه | پنل مدیریت' }}
@endsection
@section('content')
@section('bread-crumb-title', '  صفحه ساز')
@section('scripts')

 @livewire('admin.panel.tools.page-builder')

@endsection