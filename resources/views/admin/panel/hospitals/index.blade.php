@extends('admin.panel.layouts.master')
@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت بیمارستان')
@livewire('admin.panel.hospitals.hospital-list')
@endsection
