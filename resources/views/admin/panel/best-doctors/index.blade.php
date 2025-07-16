@extends('admin.panel.layouts.master')



@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت پزشکان برتر')
@livewire('admin.panel.best-doctors.best-doctor-list')
@endsection
