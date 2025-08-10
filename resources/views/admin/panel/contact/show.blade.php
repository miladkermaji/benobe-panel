@extends('admin.panel.layouts.master')

@section('content')
@section('bread-crumb-title', 'مشاهده پیام تماس')
@livewire('admin.panel.contact.contact-show', ['id' => $id])
@endsection
