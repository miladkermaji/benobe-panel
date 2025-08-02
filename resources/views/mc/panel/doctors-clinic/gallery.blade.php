@extends('mc.panel.layouts.master')

@section('styles')
 <link type="text/css" href="{{ asset('admin-assets/css/panel/clinic/clinic.css') }}" rel="stylesheet" />
 <style>
  .myPanelOption{
    display: none;
  }
 </style>
@endsection

@section('site-header')
 {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت کلینیک')
@livewire('mc.panel.clinics.clinics-gallery', ['id' => $id])
@endsection
