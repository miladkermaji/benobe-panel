@extends('errors.layout')

@section('title', 'سرویس موقتاً قطع است - به نوبه')
@section('code', '503')
@section('message', 'سرویس در دسترس نیست')
@section('icon', '🛠️')

@section('content')
    <p>در حال بروزرسانی سیستم هستیم. مثل یه کلینیک که موقتاً تعطیله!</p>
@endsection