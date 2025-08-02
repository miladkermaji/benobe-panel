@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/css/panel/doctornote/doctornote.css') }}" rel="stylesheet">
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت توضیحات')
@livewire('mc.panel.doctor-notes.doctor-note-list')
@section('scripts')



  <script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
@endsection
@endsection




