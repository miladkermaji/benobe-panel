@extends('admin.panel.layouts.master')
@section('styles')


@endsection
@section('site-header')
  {{ 'افزودن تیکت جدید | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'افزودن تیکت جدید')
@livewire('admin.panel.tickets.ticket-create')
@endsection
