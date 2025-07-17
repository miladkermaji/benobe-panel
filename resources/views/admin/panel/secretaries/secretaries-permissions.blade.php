@extends('admin.panel.layouts.master')



@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'مدیریت  دسترسی منشی ها')
    @livewire('admin.panel.secretaries.secretary-permissions')
@endsection