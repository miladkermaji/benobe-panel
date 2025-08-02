@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/css/panel/doctor-comment/doctor-comment.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'افزودن doctorcomments جدید')
@livewire('mc.panel.doctor-comments.doctor-comments-create')

@endsection
@section('scripts')
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
@endsection
