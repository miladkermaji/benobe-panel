@extends('errors.layout')

@section('title', 'وضعیت سیستم - به نوبه')
@section('code', '🔧')
@section('message', 'وضعیت سیستم')
@section('icon', '⚙️')

@section('content')
  <div
    style="text-align: left; direction: ltr; font-family: 'Courier New', monospace; background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0; font-size: 0.9rem;">
    <div style="margin-bottom: 0.5rem;">
      <strong>📊 وضعیت سیستم:</strong>
    </div>
    <div style="color: #28a745;">✅ سرور وب: فعال</div>
    <div style="color: #dc3545;">❌ پایگاه داده: غیرفعال</div>
    <div style="color: #28a745;">✅ PHP: {{ phpversion() }}</div>
    <div style="color: #28a745;">✅ Laravel: {{ app()->version() }}</div>
  </div>

  <p>برای بررسی وضعیت پایگاه داده، دستور زیر را در ترمینال اجرا کنید:</p>
  <div
    style="background: #2d3748; color: #e2e8f0; padding: 0.75rem; border-radius: 0.5rem; font-family: 'Courier New', monospace; font-size: 0.9rem; margin: 1rem 0;">
    php artisan db:status
  </div>

  <p style="margin-top: 1rem; font-size: 0.95rem; color: #888;">
    <strong>راه‌حل‌های سریع:</strong><br>
    • سرور MySQL را راه‌اندازی کنید<br>
    • تنظیمات .env را بررسی کنید<br>
    • سرویس‌های مربوطه را restart کنید
  </p>
@endsection
