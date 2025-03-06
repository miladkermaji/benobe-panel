@extends('admin.panel.layouts.master')
@section('styles')
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
 <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>


@endsection
@section('site-header')
 {{ 'به نوبه | پنل مدیریت' }}
@endsection
@section('content')
@section('bread-crumb-title', ' قالب ایمیل')
@section('scripts')

 @livewire('admin.panel.tools.mail-templates')

@endsection