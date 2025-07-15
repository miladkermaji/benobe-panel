@extends('admin.panel.layouts.master')
@section('styles')
  <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/tools/site-map/sitemap-manager-setting.css') }}">
@endsection
@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection
@section('bread-crumb-title', 'تنظیمات نقشه سایت')
@section('content')
<div class="container-fluid py-4" dir="rtl">
  <!-- Header -->
  <div class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="flex-grow-1">
      <h1 class="m-0 h3 fw-light">تنظیمات پیمایش نقشه سایت</h1>
    </div>
    <div>
      <a href="{{ route('admin.tools.sitemap.index') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
        <svg width="16" height="16" style="transform: rotate(180deg)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M15 19l-7-7 7-7" />
        </svg>
        <span>بازگشت</span>
      </a>
    </div>
  </div>
  <!-- Settings Form -->
  <div class="card shadow-sm border-0">
    <div class="card-body p-4">
      @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      <form method="POST" action="{{ route('admin.tools.sitemap.settings.update') }}">
        @csrf
        @method('PUT')
        <div class="row g-4">
          <div class="col-lg-6">
            <label for="base_url" class="form-label fw-bold">آدرس پایه سایت</label>
            <input type="text" name="base_url" id="base_url" value="{{ old('base_url', $settings->base_url) }}"
              class="form-control" placeholder="مثلاً https://benobe.ir">
            @error('base_url')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-lg-6">
            <label for="ignore_robots" class="form-label fw-bold">نادیده گرفتن robots.txt</label>
            <select name="ignore_robots" id="ignore_robots" class="form-select">
              <option value="1" {{ old('ignore_robots', $settings->ignore_robots) ? 'selected' : '' }}>بله</option>
              <option value="0" {{ !old('ignore_robots', $settings->ignore_robots) ? 'selected' : '' }}>خیر</option>
            </select>
            @error('ignore_robots')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-lg-4">
            <label for="maximum_depth" class="form-label fw-bold">عمق حداکثر</label>
            <input type="number" name="maximum_depth" id="maximum_depth" value="{{ old('maximum_depth', $settings->maximum_depth) }}"
              class="form-control" min="1" max="1000">
            @error('maximum_depth')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-lg-4">
            <label for="total_crawl_limit" class="form-label fw-bold">حداکثر تعداد لینک</label>
            <input type="number" name="total_crawl_limit" id="total_crawl_limit"
              value="{{ old('total_crawl_limit', $settings->total_crawl_limit) }}"
              class="form-control" min="1" max="1000">
            @error('total_crawl_limit')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-lg-4">
            <label for="delay_between_requests" class="form-label fw-bold">تاخیر بین درخواست‌ها (میلی‌ثانیه)</label>
            <input type="number" name="delay_between_requests" id="delay_between_requests"
              value="{{ old('delay_between_requests', $settings->delay_between_requests) }}"
              class="form-control" min="100" max="5000">
            @error('delay_between_requests')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-12 mt-4 text-end">
            <button type="submit" class="btn btn-success px-4 py-2">ذخیره تنظیمات</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection