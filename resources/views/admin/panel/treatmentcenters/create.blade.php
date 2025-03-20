@extends('admin.panel.layouts.master')

@section('styles')
 <link type="text/css" href="{{ asset('admin-assets/css/panel/treatmentcenter/treatmentcenter.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
 {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'افزودن درمانگاه جدید')
@livewire('admin.panel.treatmentcenters.treatmentcenter-create')
@endsection
