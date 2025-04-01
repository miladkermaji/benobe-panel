@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/imagingcenter/imagingcenter.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'افزودن مراکز تصویربرداری جدید')
@livewire('admin.panel.imaging-centers.imaging-center-create')
@endsection
