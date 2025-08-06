@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/panel/css/users/users.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مرکز درمانی' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش پزشک')
@livewire('mc.panel.doctors.doctor-edit', ['id' => $id])
@endsection
