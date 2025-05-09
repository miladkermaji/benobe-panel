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
    <div class="mt-2 table-responsive">
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
                <svg width="25px" height="25px" viewBox="0 -0.5 21 21" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>gallery_grid_view [#1405]</title> <desc>Created with Sketch.</desc> <defs> </defs> <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Dribbble-Light-Preview" transform="translate(-259.000000, -680.000000)" fill="#000000"> <g id="icons" transform="translate(56.000000, 160.000000)"> <path d="M209.3,538 L206.15,538 C205.5704,538 205.1,537.552 205.1,537 C205.1,536.448 205.5704,536 206.15,536 L209.3,536 C209.8796,536 210.35,536.448 210.35,537 C210.35,537.552 209.8796,538 209.3,538 L209.3,538 Z M210.35,534 L205.1,534 C203.93975,534 203,534.895 203,536 L203,538 C203,539.105 203.93975,540 205.1,540 L210.35,540 C211.51025,540 212.45,539.105 212.45,538 L212.45,536 C212.45,534.895 211.51025,534 210.35,534 L210.35,534 Z M220.85,524 L217.7,524 C217.1204,524 216.65,523.552 216.65,523 C216.65,522.448 217.1204,522 217.7,522 L220.85,522 C221.4296,522 221.9,522.448 221.9,523 C221.9,523.552 221.4296,524 220.85,524 L220.85,524 Z M221.9,520 L216.65,520 C215.48975,520 214.55,520.895 214.55,522 L214.55,524 C214.55,525.105 215.48975,526 216.65,526 L221.9,526 C223.06025,526 224,525.105 224,524 L224,522 C224,520.895 223.06025,520 221.9,520 L221.9,520 Z M221.9,537 C221.9,537.552 221.4296,538 220.85,538 L217.7,538 C217.1204,538 216.65,537.552 216.65,537 L216.65,531 C216.65,530.448 217.1204,530 217.7,530 L220.85,530 C221.4296,530 221.9,530.448 221.9,531 L221.9,537 Z M221.9,528 L216.65,528 C215.48975,528 214.55,528.895 214.55,530 L214.55,538 C214.55,539.105 215.48975,540 216.65,540 L221.9,540 C223.06025,540 224,539.105 224,538 L224,530 C224,528.895 223.06025,528 221.9,528 L221.9,528 Z M210.35,529 C210.35,529.552 209.8796,530 209.3,530 L206.15,530 C205.5704,530 205.1,529.552 205.1,529 L205.1,523 C205.1,522.448 205.5704,522 206.15,522 L209.3,522 C209.8796,522 210.35,522.448 210.35,523 L210.35,529 Z M210.35,520 L205.1,520 C203.93975,520 203,520.895 203,522 L203,530 C203,531.105 203.93975,532 205.1,532 L210.35,532 C211.51025,532 212.45,531.105 212.45,530 L212.45,522 C212.45,520.895 211.51025,520 210.35,520 L210.35,520 Z" id="gallery_grid_view-[#1405]"> </path> </g> </g> </g> </g></svg>
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
