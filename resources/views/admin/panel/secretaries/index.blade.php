@extends('admin.panel.layouts.master')



@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'مدیریت منشی ها')
    @livewire('admin.panel.secretaries.secretary-list')
@endsection