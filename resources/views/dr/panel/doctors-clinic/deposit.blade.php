@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/doctor-clinic/deposite.css') }}" rel="stylesheet" />

@endsection

@section('site-header')
  {{ 'پنل مدیریت | مدیریت بیعانه' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت بیعانه')
<div class="container-fluid">
  <div class="mt-5 d-flex justify-content-between align-items-center mb-4">
    <h1 class="fs-4 fw-bold">مدیریت بیعانه‌ها</h1>
    <button class="btn my-btn-primary h-50" data-bs-toggle="modal" data-bs-target="#depositModal">
      <i class="fas fa-plus me-2"></i> افزودن بیعانه
    </button>
  </div>
  <div class="card table-responsive">
    <table class="table table-hover" dir="ltr">
      <thead>
        <tr>
          <th>عملیات</th>
          <th>مطب</th>
          <th>مبلغ (تومان)</th>
        </tr>
      </thead>
      <tbody id="depositList">
        @foreach ($deposits as $deposit)
          <tr data-id="{{ $deposit->id }}">
            <td>
              <button class="btn btn-icon edit-btn btn-light rounded-circle" data-id="{{ $deposit->id }}">
                <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
              </button>
              <button class="btn btn-icon delete-btn btn-light rounded-circle" data-id="{{ $deposit->id }}">
                <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
              </button>
            </td>
            <td>{{ $deposit->clinic_id ? $clinics->find($deposit->clinic_id)->name : 'ویزیت آنلاین' }}</td>
            <td>{{ $deposit->deposit_amount ? number_format($deposit->deposit_amount) : 'بدون بیعانه' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <!-- کارت‌ها برای موبایل -->
  <div class="table-card">
    @foreach ($deposits as $deposit)
      <div class="card mb-3" data-id="{{ $deposit->id }}">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <strong>مطب:</strong>
              {{ $deposit->clinic_id ? $clinics->find($deposit->clinic_id)->name : 'ویزیت آنلاین' }}<br>
              <strong>مبلغ:</strong>
              {{ $deposit->deposit_amount ? number_format($deposit->deposit_amount) : 'بدون بیعانه' }}
            </div>
            <div>
              <button class="btn btn-icon edit-btn btn-light rounded-circle" data-id="{{ $deposit->id }}">
                <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
              </button>
              <button class="btn btn-icon delete-btn btn-light rounded-circle" data-id="{{ $deposit->id }}">
                <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
              </button>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="depositModalLabel">افزودن بیعانه</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="depositForm">
          @csrf
          <input type="hidden" name="id" id="depositId">
          <input type="hidden" name="selectedClinicId" value="{{ $selectedClinicId }}">
          <input type="hidden" name="is_custom_price" id="isCustomPrice" value="0">
          <div class="mb-3">
            <label for="depositAmount" class="form-label">مبلغ بیعانه</label>
            <select name="deposit_amount" id="depositAmount" class="form-select h-50">
              <option value="">انتخاب کنید</option>
              <option value="50000">50,000 تومان</option>
              <option value="100000">100,000 تومان</option>
              <option value="150000">150,000 تومان</option>
              <option value="custom">قیمت دلخواه</option>
            </select>
          </div>
          <div class="mb-3" id="customPriceContainer" style="display: none;">
            <label for="customPrice" class="form-label">مبلغ دلخواه (تومان)</label>
            <input type="number" name="custom_price" id="customPrice" class="form-control h-50"
              placeholder="مبلغ را وارد کنید" min="0" step="1" required>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="no_deposit" id="noDeposit" value="1">
            <label class="form-check-label" for="noDeposit">بدون بیعانه</label>
          </div>
          <button type="submit" class="btn my-btn-primary h-50 w-100">ذخیره</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>

@endsection
