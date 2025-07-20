@extends('admin.panel.layouts.master')



@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت شهر و استان')
@livewire('admin.panel.zones.zone-list')
@endsection
