@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/noskhe-electronic/prescription/prescription.css') }}"
    rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'مدیریت نسخه های من')
<div class="prescription-wrapper">
  <div class="top-prescription-d w-100 mt-3">
    <div class="d-flex justify-content-between w-100 gap-20 align-items-center p-3">
      <div class="w-100">
        <form action="" method="get" class="w-100">
          <input type="text" placeholder="جستجو بین نسخه ها" class="my-form-control col-12 w-100">
        </form>
      </div>
    </div>
  </div>
</div>
<div class="all-noskhe-list mt-2">
  <div class="table-responsive">
    <table class="table table-light">
      <tr>
        <th>نام بیمار </th>
        <th>کد توالی</th>
        <th>کد پیگیری</th>
        <th>بیمه</th>
        <th>زمان ثبت</th>
      </tr>
      @forelse($prescriptions as $prescription)
        <tr>
          <td>{{ optional($prescription->patient)->first_name }} {{ optional($prescription->patient)->last_name }}</td>
          <td>{{ $prescription->id }}</td>
          <td>{{ $prescription->tracking_code ?? '-' }}</td>
          <td>{{ optional($prescription->insurance)->name ?? '-' }}</td>
          <td>{{ jdate($prescription->created_at)->format('Y/m/d H:i') }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="text-center">نسخه‌ای ثبت نشده است.</td>
        </tr>
      @endforelse
    </table>
    <div class="d-flex justify-content-center mt-3">
      {{ $prescriptions->links() }}
    </div>
  </div>
</div>
@endsection
