@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/tools/redirects/redirects.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/tools/site-map/sitemap-manager.css') }}">
  <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/tools/site-map/sitemap-manager-setting.css') }}">

@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', ' تنظیمات نقشه سایت')

<div class="container-fluid py-1" dir="rtl">
  <!-- هدر -->
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="header-title flex-grow-1">
      <h1 class="m-0 h3 font-thin">تنظیمات پیمایش نقشه سایت</h1>
    </div>
    <div class="buttons-container">
      <a href="{{ route('admin.tools.sitemap.index') }}"
        class="btn btn-gradient-primary px-4 py-2 d-flex align-items-center gap-2 text-white">
        <svg width="16" height="16" style="transform: rotate(180deg)" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M15 19l-7-7 7-7" />
        </svg>
        <span>بازگشت</span>
      </a>
    </div>
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
        <div class="row g-4">
          <div class="col-lg-6">
            <label class="form-label fw-bold text-dark">آدرس پایه سایت</label>
            <input type="text" name="base_url" value="{{ old('base_url', $settings->base_url) }}"
              class="form-control bg-white border-dark text-dark" placeholder="مثلاً https://benobe.ir">
          </div>
          <div class="col-lg-6">
            <label class="form-label fw-bold text-dark">نادیده گرفتن robots.txt</label>
            <select name="ignore_robots" class="form-select bg-white border-dark text-dark custom-select">
              <option value="1" {{ old('ignore_robots', $settings->ignore_robots) ? 'selected' : '' }}>بله
              </option>
              <option value="0" {{ !old('ignore_robots', $settings->ignore_robots) ? 'selected' : '' }}>خیر
              </option>
            </select>
          </div>
          <div class="col-lg-4">
            <label class="form-label fw-bold text-dark">عمق حداکثر</label>
            <input type="number" name="maximum_depth" value="{{ old('maximum_depth', $settings->maximum_depth) }}"
              class="form-control bg-white border-dark text-dark" min="1" max="1000">
          </div>
          <div class="col-lg-4">
            <label class="form-label fw-bold text-dark">حداکثر تعداد لینک</label>
            <input type="number" name="total_crawl_limit"
              value="{{ old('total_crawl_limit', $settings->total_crawl_limit) }}"
              class="form-control bg-white border-dark text-dark" min="1" max="1000">
          </div>
          <div class="col-lg-4">
            <label class="form-label fw-bold text-dark">تاخیر بین درخواست‌ها (میلی‌ثانیه)</label>
            <input type="number" name="delay_between_requests"
              value="{{ old('delay_between_requests', $settings->delay_between_requests) }}"
              class="form-control bg-white border-dark text-dark" min="100" max="5000">
          </div>
          <div class="col-12 mt-3 text-end">
            <button type="submit" class="btn btn-gradient-success px-4 py-2">ذخیره تنظیمات</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
