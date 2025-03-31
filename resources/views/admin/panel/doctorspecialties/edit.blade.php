@extends('admin.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('admin-assets/css/panel/doctorspecialty/doctorspecialty.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'ویرایش تخصص')
    @livewire('admin.panel.doctorspecialties.doctorspecialty-edit', ['id' => $id])
@endsection