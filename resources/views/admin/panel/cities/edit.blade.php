@extends('admin.panel.layouts.master')

@section('styles')
 <link type="text/css" href="{{ asset('admin-assets/css/panel/zone/zone.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
 {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش شهر')
@livewire('admin.panel.cities.city-edit', ['id' => $id])
@endsection
