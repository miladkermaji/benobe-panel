@extends('errors.layout')

@section('title', 'صفحه پیدا نشد - به نوبه')

@section('content')
 <span class="error-icon">⏰</span>
 <p class="text-gray-500 mt-4">متأسفیم! انگار این صفحه مثل یه نوبت گم‌شده پیدا نمی‌شه.</p>
@endsection

<?php
$code = '404';
$message = 'صفحه مورد نظر یافت نشد';
?>