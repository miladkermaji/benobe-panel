@extends('errors.layout')

@section('title', 'خطای پایگاه داده - به نوبه')
@section('code', '500')
@section('message', 'خطای پایگاه داده')
@section('icon', '🗄️')

@section('content')
  <p>متأسفانه مشکلی در ارتباط با پایگاه داده رخ داده است. تیم فنی ما در حال بررسی و رفع مشکل است.</p>
  <p style="margin-top: 1rem; font-size: 0.95rem; color: #888;">
    <strong>لطفاً:</strong><br>
    • چند دقیقه صبر کنید و دوباره تلاش کنید<br>
    • در صورت تداوم مشکل، صفحه را مجدداً بارگذاری کنید<br>
    • با پشتیبانی فنی تماس بگیرید
  </p>
@endsection
