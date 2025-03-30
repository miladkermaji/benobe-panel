@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('Admin-assets/css/panel/review/review.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'افزودن نظر جدید')
@livewire('admin.panel.reviews.review-create')
@endsection
