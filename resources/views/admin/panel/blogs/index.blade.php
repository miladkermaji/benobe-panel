@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/css/panel/blogs/blogs.css') }}" rel="stylesheet" />
 
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
  @section('bread-crumb-title', 'مدیریت بلاگ')
  <div class="blogs-content container py-5">
    <div class="row g-4">
      @forelse ($posts as $post)
        <div class="col-12 col-md-6 col-lg-4"> <!-- تغییر به col-lg-3 برای 4 کارت در دسکتاپ -->
          <div class="card h-100">
            @if ($post['featured_media'])
              <img src="{{ $post['featured_media'] }}" class="card-img-top" alt="{{ $post['title'] }}">
            @else
              <img src="https://via.placeholder.com/400x250?text=بدون+تصویر" class="card-img-top" alt="تصویر پیش‌فرض">
            @endif
            <div class="card-body">
              <h5 class="card-title">{{ $post['title'] }}</h5>
              <p class="card-date">{{ \Carbon\Carbon::parse($post['date'])->translatedFormat('j F Y') }}</p>
              <div class="d-flex gap-3">
                <a href="{{ $post['read_more_link'] }}" class="btn btn-primary" target="_blank">بیشتر بخوانید</a>
                <a href="{{ $post['all_posts_link'] }}" class="btn btn-outline-secondary" target="_blank">همه پست‌ها</a>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12 text-center py-5">
          <p class="text-muted fs-4">هیچ پستی برای نمایش وجود ندارد!</p>
        </div>
      @endforelse
    </div>

    <!-- صفحه‌بندی -->
    @if ($total_pages > 1)
      <div class="mt-5">
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center">
            <li class="page-item {{ $current_page == 1 ? 'disabled' : '' }}">
              <a class="page-link" href="{{ route('admin.panel.blogs.index', ['page' => $current_page - 1]) }}">قبلی</a>
            </li>
            @for ($i = 1; $i <= $total_pages; $i++)
              <li class="page-item {{ $current_page == $i ? 'active' : '' }}">
                <a class="page-link" href="{{ route('admin.panel.blogs.index', ['page' => $i]) }}">{{ $i }}</a>
              </li>
            @endfor
            <li class="page-item {{ $current_page == $total_pages ? 'disabled' : '' }}">
              <a class="page-link" href="{{ route('admin.panel.blogs.index', ['page' => $current_page + 1]) }}">بعدی</a>
            </li>
          </ul>
        </nav>
      </div>
    @endif

    <!-- دکمه رفرش -->
    <div class="mt-4 text-center">
      <a href="{{ route('admin.panel.blogs.index', ['page' => $current_page, 'refresh' => 1]) }}" class="btn btn-warning">به‌روزرسانی پست‌ها</a>
    </div>
  </div>
@endsection