@extends('errors.layout')

@section('title', 'خطای اتصال به پایگاه داده - به نوبه')
@section('code', '503')
@section('message', 'خطای اتصال به سرور')
@section('icon', '🔌')

@section('content')
  <p>متأسفانه ارتباط با سرور پایگاه داده برقرار نشد. این مشکل معمولاً موقتی است و به زودی برطرف خواهد شد.</p>
  <p style="margin-top: 1rem; font-size: 0.95rem; color: #888;">
    <strong>راه‌حل‌های پیشنهادی:</strong><br>
    • اطمینان حاصل کنید که سرور MySQL در حال اجرا است<br>
    • چند دقیقه صبر کنید و صفحه را مجدداً بارگذاری کنید<br>
    • در صورت تداوم مشکل، با پشتیبانی تماس بگیرید
  </p>
@endsection
