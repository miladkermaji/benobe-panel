@extends('admin.panel.layouts.master')
@section('styles')
<link type="text/css" href="{{ asset('dr-assets/panel/tickets/tickets.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'مشاهده تیکت | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مشاهده تیکت')
@livewire('admin.panel.tickets.ticket-show', ['ticketId' => $id])
@endsection
