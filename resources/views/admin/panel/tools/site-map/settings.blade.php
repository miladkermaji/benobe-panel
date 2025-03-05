@extends('admin.panel.layouts.master')

@section('styles')
 <link type="text/css" href="{{ asset('admin-assets/css/panel/tools/redirects/redirects.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
 {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
 <div class="container-fluid py-1" dir="rtl">
  <!-- هدر -->
  <div
   class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
   <h1 class="m-0 h3 font-thin">تنظیمات کراول نقشه سایت</h1>
   <a href="{{ route('admin.tools.sitemap.index') }}"
    class="btn btn-gradient-primary rounded-pill px-4 d-flex align-items-center gap-2">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
  <path d="M15 19l-7-7 7-7" />
    </svg>
    <span>بازگشت به مدیریت نقشه سایت</span>
   </a>
  </div>

  <div class="card shadow-sm border-0">
   <div class="card-body p-4">
    @if (session('success'))
   <div class="alert alert-success" role="alert">
    {{ session('success') }}
   </div>
 @endif
    <form method="POST" action="{{ route('admin.tools.sitemap.settings.update') }}">
  @csrf
  @method('PUT')
  <div class="row g-3">
   <div class="col-md-6">
    <label class="form-label fw-bold text-dark">آدرس پایه سایت</label>
    <input type="text" name="base_url" value="{{ old('base_url', $settings->base_url) }}"
     class="form-control bg-white border-dark text-dark rounded-pill" placeholder="مثلاً https://benobe.ir">
   </div>
   <div class="col-md-2">
    <label class="form-label fw-bold text-dark">عمق حداکثر</label>
    <input type="number" name="maximum_depth" value="{{ old('maximum_depth', $settings->maximum_depth) }}"
     class="form-control bg-white border-dark text-dark rounded-pill" min="1" max="1000">
   </div>
   <div class="col-md-2">
    <label class="form-label fw-bold text-dark">حداکثر تعداد لینک</label>
    <input type="number" name="total_crawl_limit" value="{{ old('total_crawl_limit', $settings->total_crawl_limit) }}"
     class="form-control bg-white border-dark text-dark rounded-pill" min="1" max="1000">
   </div>
   <div class="col-md-2">
    <label class="form-label fw-bold text-dark">تاخیر بین درخواست‌ها (میلی‌ثانیه)</label>
    <input type="number" name="delay_between_requests"
     value="{{ old('delay_between_requests', $settings->delay_between_requests) }}"
     class="form-control bg-white border-dark text-dark rounded-pill" min="100" max="5000">
   </div>
   <div class="col-md-4">
    <label class="form-label fw-bold text-dark">نادیده گرفتن robots.txt</label>
    <select name="ignore_robots" class="form-select bg-white border-dark text-dark rounded-pill">
     <option value="1" {{ old('ignore_robots', $settings->ignore_robots) ? 'selected' : '' }}>بله</option>
     <option value="0" {{ !old('ignore_robots', $settings->ignore_robots) ? 'selected' : '' }}>خیر</option>
    </select>
   </div>
   <div class="col-md-12 mt-3">
    <button type="submit" class="btn btn-gradient-success rounded-pill px-4">ذخیره تنظیمات</button>
   </div>
  </div>
    </form>
   </div>
  </div>
 </div>
@endsection

<style>
 .glass-header {
  background: linear-gradient(135deg, rgba(79, 70, 229, 0.95), rgba(124, 58, 237, 0.85));
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
 }

 .btn-gradient-primary {
  background: linear-gradient(90deg, #4f46e5, #7c3aed);
  border: none;
  color: white;
  transition: all 0.3s ease;
 }

 .btn-gradient-primary:hover {
  background: linear-gradient(90deg, #4338ca, #4f46e5);
  transform: translateY(-2px);
 }

 .btn-gradient-success {
  background: linear-gradient(90deg, #10b981, #34d399);
  border: none;
  color: white;
  transition: all 0.3s ease;
 }

 .btn-gradient-success:hover {
  background: linear-gradient(90deg, #059669, #10b981);
  transform: translateY(-2px);
 }

 .form-control,
 .form-select {
  background: #ffffff !important;
  border-color: #6b7280 !important;
  color: #1f2937 !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  border-radius: 12px;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  transition: all 0.3s ease;
 }

 .form-control:focus,
 .form-select:focus {
  border-color: #4f46e5 !important;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.3);
  background: #f9f9f9 !important;
 }

 .card {
  border-radius: 10px;
  overflow: hidden;
 }
</style>