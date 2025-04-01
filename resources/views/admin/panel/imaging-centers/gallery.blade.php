@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/imaging-center/imaging-center.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'گالری مراکز تصویربرداری')
@livewire('admin.panel.imaging-centers.imaging-centers-gallery', ['id' => $id])
@endsection
