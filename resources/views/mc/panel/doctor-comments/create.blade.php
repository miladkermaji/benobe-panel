@extends('dr.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('dr-assets/css/panel/doctor-comment/doctor-comment.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'افزودن doctorcomments جدید')
    @livewire('dr.panel.doctor-comments.doctor-comments-create')

@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
@endsection