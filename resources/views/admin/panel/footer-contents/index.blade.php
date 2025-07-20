@extends('admin.panel.layouts.master')


@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت فوتر')
@livewire('admin.panel.footer-contents.footer-content-list')
@endsection
