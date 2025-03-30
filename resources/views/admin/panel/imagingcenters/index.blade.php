@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('Admin-assets/css/panel/imagingcenter/imagingcenter.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت مراکز تصویربرداری')
@livewire('admin.panel.imagingcenters.imagingcenter-list')
@endsection
