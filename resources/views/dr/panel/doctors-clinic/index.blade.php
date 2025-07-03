@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/profile/subuser.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', ' مدیریت مطب ')
<!-- بخش محتوا با جدول بوت‌استرپ -->
<div class="container subuser-content w-100 d-flex justify-content-center mt-4">
  <div class="subuser-content-wrapper w-100">
    <div class="w-100 d-flex justify-content-end">
      <a href="{{ route('dr.panel.clinics.create') }}"
        class="btn my-btn-primary h-50 d-flex justify-content-center align-items-center text-white"
        id="add-clinic-btn">افزودن مطب
        جدید</a>
    </div>
    <div class="p-3">
      <h4 class="text-dark fw-bold">لیست مطب‌های من</h4>
    </div>
    <div class="mt-2 table-responsive d-none d-md-block">
      <table class="table table-modern  table-hover" id="clinic-list">
        <thead>
          <tr>
            <th>ردیف</th>
            <th>نام مطب</th>
            <th>استان</th>
            <th>شهر</th>
            <th>آدرس</th>
            <th>توضیحات</th>
            <th>وضعیت</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($clinics as $index => $clinic)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $clinic->name }}</td>
              <td>{{ optional($clinic->province)->name ?? 'نامشخص' }}</td>
              <td>{{ optional($clinic->city)->name ?? 'نامشخص' }}</td>
              <td>{{ $clinic->address ?? 'نامشخص' }}</td>
              <td>{{ $clinic->description ?? '---' }}</td>
              <td>
                <span class="{{ $clinic->is_active ? 'text-success' : 'text-danger' }}">
                  {{ $clinic->is_active ? 'تایید شده' : 'تایید نشده' }}
                </span>
              </td>
              <td>
                <a href="{{ route('dr.panel.clinics.edit', $clinic->id) }}"
                  class="btn btn-light btn-sm rounded-circle edit-btn" title="ویرایش">
                  <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
                </a>
                <button class="btn btn-light btn-sm rounded-circle delete-btn" data-id="{{ $clinic->id }}"
                  title="حذف">
                  <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                </button>
                <a href="{{ route('dr.panel.clinics.gallery', $clinic->id) }}"
                  class="btn btn-light btn-sm rounded-circle gallery-btn" data-id="{{ $clinic->id }}"
                  title="گالری تصاویر">
                  <img src="{{ asset('dr-assets/icons/gallery.svg') }}" alt="حذف">
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center">هیچ مطبی یافت نشد</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <!-- کارت‌های مطب برای موبایل/تبلت -->
    <div class="notes-cards d-md-none">
      @forelse ($clinics as $index => $clinic)
        <div class="note-card mb-3" data-id="{{ $clinic->id }}">
          <div class="note-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
              <span
                class="badge bg-primary-subtle text-primary">{{ $clinic->is_active ? 'تایید شده' : 'تایید نشده' }}</span>
            </div>
            <div class="d-flex gap-1">
              <a href="{{ route('dr.panel.clinics.edit', $clinic->id) }}"
                class="btn btn-sm btn-gradient-success px-2 py-1 edit-btn" title="ویرایش">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path
                    d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
              </a>
              <button class="btn btn-sm btn-gradient-danger px-2 py-1 delete-btn" data-id="{{ $clinic->id }}"
                title="حذف">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                </svg>
              </button>
              <a href="{{ route('dr.panel.clinics.gallery', $clinic->id) }}"
                class="btn btn-sm btn-gradient-success px-2 py-1 gallery-btn" title="گالری تصاویر">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path
                    d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3.01L15.5 11l4.5 6H4l4.5-6z" />
                </svg>
              </a>
            </div>
          </div>
          <div class="note-card-body">
            <div class="note-card-item">
              <span class="note-card-label">نام مطب:</span>
              <span class="note-card-value">{{ $clinic->name }}</span>
            </div>
            <div class="note-card-item">
              <span class="note-card-label">استان:</span>
              <span class="note-card-value">{{ optional($clinic->province)->name ?? 'نامشخص' }}</span>
            </div>
            <div class="note-card-item">
              <span class="note-card-label">شهر:</span>
              <span class="note-card-value">{{ optional($clinic->city)->name ?? 'نامشخص' }}</span>
            </div>
            <div class="note-card-item">
              <span class="note-card-label">آدرس:</span>
              <span class="note-card-value">{{ $clinic->address ?? 'نامشخص' }}</span>
            </div>
            <div class="note-card-item">
              <span class="note-card-label">توضیحات:</span>
              <span class="note-card-value">{{ $clinic->description ?? '---' }}</span>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center py-4">
          <div class="d-flex justify-content-center align-items-center flex-column">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="text-muted mb-2">
              <path d="M5 12h14M12 5l7 7-7 7" />
            </svg>
            <p class="text-muted fw-medium">هیچ مطبی یافت نشد</p>
          </div>
        </div>
      @endforelse
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
</script>
@endsection
