@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/tickets/tickets.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/patient-records/patient-records.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'پرونده الکترونیک')

<div class="container-fluid py-4" dir="rtl">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-10">
      <div class="card shadow-lg border-0">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
          <h5 class="card-title text-white mb-0 fw-bold text-shadow">پرونده الکترونیک بیمار</h5>
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#add-patient-modal">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            افزودن بیمار جدید
          </button>
        </div>
        <div class="card-body p-4">
          <div class="row mb-4">
            <div class="col-12">
              <div class="input-group position-relative">
                <input type="text" class="form-control input-shiny" id="search-patient" placeholder="جستجو...">
                <span class="search-icon">
                  
                </span>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>نام بیمار</th>
                      <th>نام خانوادگی</th>
                      <th>شماره تلفن</th>
                      <th>آدرس</th>
                      <th>عملیات</th>
                    </tr>
                  </thead>
                  <tbody id="patient-list">
                    @foreach ($patients as $patient)
                      <tr>
                        <td>{{ $patient->name }}</td>
                        <td>{{ $patient->family }}</td>
                        <td>{{ $patient->phone }}</td>
                        <td>{{ $patient->address }}</td>
                        <td>
                          <div class="d-flex gap-2">
                            <button class="btn my-btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#edit-patient-modal-{{ $patient->id }}">
                              ویرایش
                            </button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#delete-patient-modal-{{ $patient->id }}">
                              حذف
                            </button>
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="add-patient-modal" tabindex="-1" role="dialog" aria-labelledby="add-patient-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-radius-6">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="add-patient-modal-label">افزودن بیمار جدید</h5>
        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="row g-4">
            <div class="col-md-6 col-12 position-relative">
              <label class="form-label" for="name">نام بیمار</label>
              <input type="text" class="form-control input-shiny" id="name" placeholder="نام بیمار">
            </div>
            <div class="col-md-6 col-12 position-relative">
              <label class="form-label" for="family">نام خانوادگی</label>
              <input type="text" class="form-control input-shiny" id="family" placeholder="نام خانوادگی">
            </div>
            <div class="col-md-6 col-12 position-relative">
              <label class="form-label" for="phone">شماره تلفن</label>
              <input type="text" class="form-control input-shiny" id="phone" placeholder="شماره تلفن">
            </div>
            <div class="col-md-6 col-12 position-relative">
              <label class="form-label" for="address">آدرس</label>
              <input type="text" class="form-control input-shiny" id="address" placeholder="آدرس">
            </div>
            <div class="col-12">
              <button type="button" class="btn  h-50 btn-primary w-100">افزودن</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Patient Modal -->
@foreach ($patients as $patient)
  <div class="modal fade" id="edit-patient-modal-{{ $patient->id }}" tabindex="-1" role="dialog" aria-labelledby="edit-patient-modal-label-{{ $patient->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-radius-6">
        <div class="modal-header border-0">
          <h5 class="modal-title" id="edit-patient-modal-label-{{ $patient->id }}">ویرایش بیمار</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="row g-4">
              <div class="col-md-6 col-12 position-relative">
                <label class="form-label" for="name-{{ $patient->id }}">نام بیمار</label>
                <input type="text" class="form-control input-shiny" id="name-{{ $patient->id }}" value="{{ $patient->name }}" placeholder="نام بیمار">
              </div>
              <div class="col-md-6 col-12 position-relative">
                <label class="form-label" for="family-{{ $patient->id }}">نام خانوادگی</label>
                <input type="text" class="form-control input-shiny" id="family-{{ $patient->id }}" value="{{ $patient->family }}" placeholder="نام خانوادگی">
              </div>
              <div class="col-md-6 col-12 position-relative">
                <label class="form-label" for="phone-{{ $patient->id }}">شماره تلفن</label>
                <input type="text" class="form-control input-shiny" id="phone-{{ $patient->id }}" value="{{ $patient->phone }}" placeholder="شماره تلفن">
              </div>
              <div class="col-md-6 col-12 position-relative">
                <label class="form-label" for="address-{{ $patient->id }}">آدرس</label>
                <input type="text" class="form-control input-shiny" id="address-{{ $patient->id }}" value="{{ $patient->address }}" placeholder="آدرس">
              </div>
              <div class="col-12">
                <button type="button" class="btn my-btn-primary w-100">ویرایش</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endforeach

<!-- Delete Patient Modal -->
@foreach ($patients as $patient)
  <div class="modal fade" id="delete-patient-modal-{{ $patient->id }}" tabindex="-1" role="dialog" aria-labelledby="delete-patient-modal-label-{{ $patient->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-radius-6">
        <div class="modal-header border-0">
          <h5 class="modal-title" id="delete-patient-modal-label-{{ $patient->id }}">حذف بیمار</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          آیا می‌خواهید بیمار {{ $patient->name }} {{ $patient->family }} را حذف کنید؟
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">لغو</button>
          <button type="button" class="btn btn-danger">حذف</button>
        </div>
      </div>
    </div>
  </div>
@endforeach

@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('showModal')) {
      $('#activation-modal').modal('show');
    }
  });
</script>
@endsection