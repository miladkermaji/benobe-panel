@extends('admin.panel.layouts.master')



@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت بنر صفحه اصلی')
@livewire('admin.panel.banner-texts.banner-text-list')
@endsection
