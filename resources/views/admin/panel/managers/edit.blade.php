@extends('admin.panel.layouts.master')

@section('styles')
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش مدیر')
@livewire('admin.panel.managers.manager-edit', ['id' => $id])
@endsection
