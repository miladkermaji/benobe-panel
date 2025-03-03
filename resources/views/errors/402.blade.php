@extends('errors.layout')

@section('title', 'پرداخت لازم است - به نوبه')

@section('content')
 <span class="error-icon">💳</span>
 <p class="text-gray-500 mt-4">برای ادامه، باید پرداخت انجام بشه. مثل نوبت گرفتن که نیاز به تأیید داره!</p>
@endsection

<?php
$code = '402';
$message = 'پرداخت لازم است';
?>