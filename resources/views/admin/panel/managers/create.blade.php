@extends('admin.panel.layouts.master')

@section('styles')
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'افزودن مدیر جدید')
@livewire('admin.panel.managers.manager-create')
@endsection
