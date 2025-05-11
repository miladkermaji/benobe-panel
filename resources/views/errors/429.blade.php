@extends('errors.layout')

@section('title', 'درخواست زیاد - به نوبه')
@section('code', '429')
@section('message', 'درخواست بیش از حد')
@section('icon', '🚦')

@section('content')
    <p>شما زیادی درخواست فرستادید! کمی صبر کنید، مثل انتظار توی صف نوبت.</p>
@endsection