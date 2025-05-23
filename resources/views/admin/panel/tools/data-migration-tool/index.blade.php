@extends('admin.panel.layouts.master')

@section('site-header', 'به نوبه | ابزار مهاجرت داده‌ها')
@section('bread-crumb-title', 'ابزار مهاجرت داده‌ها')

@section('styles')
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/data-migration-tool.css') }}">
@endsection

@section('content')
  @livewire('admin.panel.tools.data-migration-tool')
@endsection
