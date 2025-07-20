@extends('admin.panel.layouts.master')

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت تیکت‌های پشتیبانی')
@livewire('admin.panel.tickets.ticket-list')
@endsection
