@extends('errors.layout')

@section('title', 'انقضای صفحه - به نوبه')

@section('content')
 <span class="error-icon">⏳</span>
 <p class="text-gray-500 mt-4">صفحه شما منقضی شده. لطفاً دوباره تلاش کنید، مثل یه نوبت که باید تازه بشه!</p>
@endsection

<?php
$code = '419';
$message = 'انقضای توکن';
?>