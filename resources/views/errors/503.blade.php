@extends('errors.layout')

@section('title', 'سرویس موقتاً قطع است - به نوبه')

@section('content')
 <span class="error-icon">🛠️</span>
 <p class="text-gray-500 mt-4">در حال بروزرسانی سیستم هستیم. مثل یه کلینیک که موقتاً تعطیله!</p>
@endsection

<?php
$code = '503';
$message = 'سرویس در دسترس نیست';
?>