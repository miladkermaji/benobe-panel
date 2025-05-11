@extends('errors.layout')

@section('title', 'دسترسی غیرمجاز - به نوبه')
@section('code', '403')
@section('message', 'دسترسی غیرمجاز')
@section('icon', '🔒')

@section('content')
    <p>شما اجازه ورود به این بخش رو ندارید. مثل یه کلینیک که فقط با نوبت کار می‌کنه!</p>
@endsection