@extends('admin.panel.layouts.master')

@section('styles')
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ایجاد استوری جدید')
@livewire('admin.panel.stories.story-create')
@endsection
