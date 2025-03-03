@extends('errors.layout')

@section('title', 'درخواست زیاد - به نوبه')

@section('content')
 <span class="error-icon">🚦</span>
 <p class="text-gray-500 mt-4">شما زیادی درخواست فرستادید! کمی صبر کنید، مثل انتظار توی صف نوبت.</p>
@endsection

<?php
$code = '429';
$message = 'درخواست بیش از حد';
?>