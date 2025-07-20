@extends('admin.panel.layouts.master')

@section('site-header')
  {{ 'مشاهده تیکت | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مشاهده تیکت')
@livewire('admin.panel.tickets.ticket-show', ['ticketId' => $id])
@endsection
