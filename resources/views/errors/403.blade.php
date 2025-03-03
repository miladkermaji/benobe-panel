@extends('errors.layout')

@section('title', 'دسترسی غیرمجاز - به نوبه')

@section('content')
 <span class="error-icon">🔒</span>
 <p class="text-gray-500 mt-4">شما اجازه ورود به این بخش رو ندارید. مثل یه کلینیک که فقط با نوبت کار می‌کنه!</p>
@endsection

<?php
$code = '403';
$message = 'دسترسی غیرمجاز';
?>