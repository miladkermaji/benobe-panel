@extends('admin.panel.layouts.master')

@section('styles')
   <link type="text/css" href="{{ asset('admin-assets/css/panel/secretary/secretary.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت دسترسی پزشکان')
@livewire('admin.panel.doctors.doctor-permissions')
@endsection
