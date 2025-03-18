@extends('admin.panel.layouts.master')

@section('site-header', 'به نوبه | ابزار مهاجرت داده‌ها')
@section('bread-crumb-title', 'ابزار مهاجرت داده‌ها')

@section('content')
 @livewire('admin.panel.tools.data-migration-tool')
@endsection
