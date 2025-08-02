@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/css/panel/doctornote/doctornote.css') }}" rel="stylesheet" />
  <style>
    .myPanelOption {
      display: none
    }
  </style>
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش توضیحات')
@livewire('mc.panel.doctor-notes.doctor-note-edit', ['id' => $id])
@section('scripts')


  <script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>


@endsection
@endsection
