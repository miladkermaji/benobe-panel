@extends('admin.panel.layouts.master')

@section('styles')

@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت دسترسی مراکز درمانی')
@livewire('admin.panel.medical-centers.medical-center-permissions')
@endsection
