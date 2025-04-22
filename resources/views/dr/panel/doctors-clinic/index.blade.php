@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/profile/subuser.css') }}" rel="stylesheet" />
  <style>
    .myPanelOption {
      display: none;
    }
  </style>
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', ' مدیریت مطب ')




<!-- بخش محتوا با جدول بوت‌استرپ -->
<div class="subuser-content w-100 d-flex justify-content-center mt-4">
  <div class="subuser-content-wrapper p-3 w-100">
    <div class="w-100 d-flex justify-content-end">
      <a href="{{ route('dr.panel.clinics.create') }}"
        class="btn my-btn-primary h-50 d-flex justify-content-center align-items-center text-white"
        id="add-clinic-btn">افزودن مطب
        جدید</a>
    </div>
    <div class="p-3">
      <h4 class="text-dark fw-bold">لیست مطب‌های من</h4>
    </div>
    <div class="mt-2">
      <table class="table table-modern table-striped table-bordered table-hover" id="clinic-list">
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
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M4 16v4h4M4 20l4-4M20 8v-4h-4M20 4l-4 4M4 4v4M4 4h4M20 20v-4h-4M20 20l-4-4"></path>
                  </svg>
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
