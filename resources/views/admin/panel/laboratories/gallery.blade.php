@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/laboratory/laboratory.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', ' گالری تصاویر')
@livewire('admin.panel.laboratories.laboratories-gallery', ['id' => $id])
@endsection
