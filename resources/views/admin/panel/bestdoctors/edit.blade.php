@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/bestdoctor/bestdoctor.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش پزشک برتر')
@livewire('admin.panel.bestdoctors.bestdoctor-edit', ['bestdoctorId' => $bestdoctorId])
@endsection
