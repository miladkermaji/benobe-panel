@extends('admin.panel.layouts.master')

@section('styles')
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت سوالات متداول')
@livewire('admin.panel.faqs.faq-list')
@endsection
