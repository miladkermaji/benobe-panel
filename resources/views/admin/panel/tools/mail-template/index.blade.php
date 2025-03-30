@extends('admin.panel.layouts.master')
@section('styles')
@endsection
@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection
@section('content')
@section('bread-crumb-title', ' قالب ایمیل')
@section('scripts')

  @livewire('admin.panel.tools.mail-templates')

@endsection
