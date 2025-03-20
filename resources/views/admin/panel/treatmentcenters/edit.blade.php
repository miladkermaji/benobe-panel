@extends('admin.panel.layouts.master')

@section('styles')
 <link type="text/css" href="{{ asset('admin-assets/css/panel/treatmentcenter/treatmentcenter.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
 {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش درمانگاه')
@livewire('admin.panel.treatmentcenters.treatmentcenter-edit', ['id' => $id])
@endsection
