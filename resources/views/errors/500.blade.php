@extends('errors.layout')

@section('title', 'خطای سرور - به نوبه')

@section('content')
 <span class="error-icon">⚠️</span>
 <p class="text-gray-500 mt-4">اوپس! یه مشکلی پیش اومده. تیم ما مثل یه دکتر خوب در حال معاینه‌ست.</p>
@endsection

<?php
$code = '500';
$message = 'خطای داخلی سرور';
?>