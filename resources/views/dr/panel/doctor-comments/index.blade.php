@extends('dr.panel.layouts.master')

@section('styles')
    <link type="text/css" href="{{ asset('dr-assets/css/panel/doctor-comment/doctor-comment.css') }}" rel="stylesheet" />
    <style>
        .myPanelOption{
            display: none;
        }
    </style>
@endsection

@section('site-header')
    {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
    @section('bread-crumb-title', 'لیست نظرات')
    @livewire('dr.panel.doctor-comments.doctor-comments-list')
@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
@endsection
