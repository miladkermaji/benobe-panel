@extends('admin.panel.layouts.master')


@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت تخصص های پزشک')
@livewire('admin.panel.doctor-specialties.doctor-specialty-list')
@endsection
