@extends('admin.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/tools/redirects/redirects.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection
@section('content')
@section('bread-crumb-title', 'نقشه سایت')
@livewire('admin.panel.tools.site-map.sitemap-manager')
@endsection
