@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/doctorcomment/doctorcomment.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش نظر')
@livewire('admin.panel.doctor-comments.doctor-comment-edit', ['id' => $id])
@endsection
