@extends('errors.layout')

@section('title', 'نیاز به ورود - به نوبه')

@section('content')
 <span class="error-icon">🔑</span>
 <p class="text-gray-500 mt-4">برای دسترسی به این بخش، لطفاً ابتدا وارد حساب کاربری‌تون بشید!</p>
@endsection

<?php
$code = '401';
$message = 'نیاز به احراز هویت';
?>