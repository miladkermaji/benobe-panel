@extends('admin.panel.layouts.master')



@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت مدارک پزشک')
@livewire('admin.panel.doctor-documents.doctor-document-list')
@endsection
