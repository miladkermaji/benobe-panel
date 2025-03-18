@extends('admin.panel.layouts.master')

@section('styles')
 <link type="text/css" href="{{ asset('admin-assets/css/panel/doctor/doctor.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
 {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش doctor')
@livewire('admin.panel.doctors.doctor-edit', ['id' => $id])
@endsection
