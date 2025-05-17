@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />

  <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/appointments_open/appointments_open.css') }}"
    rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/turn/schedule/manual_nobat/manual_nobat.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
  @include('dr.panel.my-tools.loader-btn')
@section('bread-crumb-title', ' ثبت نوبت دستی')
<div class="calendar-and-add-sick-section p-3 mb-4 w-100">
  <div class="d-flex justify-content-center align-items-center gap-3 w-100 flex-nowrap">
    <div class="position-relative flex-grow-1">
      <input type="text" id="search-input" class="form-control" placeholder="نام بیمار، شماره موبایل یا کد ملی ...">
      <div id="search-results" class="search-results">
        <div id="search-results-body"></div>
      </div>
    </div>
    <div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNewPatientModal">افزودن بیمار</button>
    </div>
  </div>
</div>

<!-- مودال افزودن بیمار -->
<!-- مودال افزودن بیمار -->
<div class="modal fade" id="addNewPatientModal" tabindex="-1" aria-labelledby="addNewPatientLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="add-new-patient-form">
        @csrf
        <div class="modal-header p-3">
          <h5 class="modal-title" id="addNewPatientLabel">افزودن بیمار جدید</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <div class="position-relative">
                <label class="label-top-input">نام بیمار:</label>
                <input type="text" name="first_name" class="form-control" placeholder="نام بیمار را وارد کنید">
              </div>
              <small class="text-danger error-first_name mt-1 text-start"></small>
            </div>
            <div class="col-12">
              <div class="position-relative">
                <label class="label-top-input">نام خانوادگی بیمار:</label>
                <input type="text" name="last_name" class="form-control"
                  placeholder="نام و نام خانوادگی بیمار را وارد کنید">
              </div>
              <small class="text-danger error-last_name mt-1 text-start"></small>
            </div>
            <div class="col-12">
              <div class="position-relative">
                <label class="label-top-input">شماره موبایل:</label>
                <input type="text" name="mobile" class="form-control" placeholder="شماره موبایل بیمار را وارد کنید">
              </div>
              <small class="text-danger error-mobile mt-1 text-start"></small>
            </div>
            <div class="col-12">
              <div class="position-relative">
                <label class="label-top-input">کد ملی:</label>
                <input type="text" name="national_code" class="form-control" placeholder="کد ملی بیمار را وارد کنید">
              </div>
              <small class="text-danger error-national_code mt-1 text-start"></small>
            </div>
            <div class="col-12">
              <div class="position-relative">
                <label class="label-top-input">تاریخ مراجعه:</label>
                <input type="text" placeholder="1403/05/02" name="appointment_date" class="form-control text-start"
                  data-jdp>
              </div>
              <small class="text-danger error-appointment_date mt-1 text-start"></small>
            </div>
            <div class="col-12">
              <div class="position-relative timepicker-ui w-100">
                <label class="label-top-input">ساعت مراجعه:</label>
                <input data-timepicker type="text" class="form-control timepicker-ui-input" name="appointment_time"
                  style="width: 100% !important">
              </div>
              <small class="text-danger error-appointment_time mt-1 text-start"></small>
            </div>
            <div class="col-12">
              <div class="position-relative">
                <label class="label-top-input">توضیحات:</label>
                <textarea name="description" class="form-control" rows="4"></textarea>
              </div>
              <small class="text-danger error-description mt-1 text-start"></small>
            </div>
          </div>
        </div>
        <div class="modal-footer p-3">
          <button type="submit"
            class="w-100 btn btn-primary d-flex justify-content-center align-items-center gap-2 h-50">
            <span class="button_text">ثبت تغییرات</span>
            <div class="loader" style="display: none;"></div>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- مودال ویرایش بیمار -->
<div class="modal fade" id="editPatientModal" tabindex="-1" aria-labelledby="editPatientLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="edit-patient-form">
        @csrf
        <div class="modal-header p-3">
          <h5 class="modal-title" id="editPatientLabel">ویرایش بیمار</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <input type="hidden" name="appointment_id" id="edit-appointment-id">
              <div class="position-relative">
                <label class="label-top-input">نام بیمار:</label>
                <input type="text" name="first_name" id="edit-first-name" class="form-control"
                  placeholder="نام بیمار را وارد کنید">
              </div>
              <small class="text-danger error-first_name mt-1 text-start"></small>
            </div>
            <div class="col-12">
              <div class="position-relative">
                <label class="label-top-input">نام خانوادگی بیمار:</label>
                <input type="text" name="last_name" id="edit-last-name" class="form-control"
                  placeholder="نام و نام خانوادگی بیمار را وارد کنید">
              </div>
              <small class="text-danger error-last_name mt-1 text-start"></small>
            </div>
            <div class="col-12">
              <div class="position-relative">
                <label class="label-top-input">شماره موبایل:</label>
                <input type="text" name="mobile" id="edit-mobile" class="form-control"
                  placeholder="شماره موبایل بیمار را وارد کنید">
              </div>
              <small class="text-danger error-mobile mt-1 text-start"></small>
            </div>
            <div class="col-12">
              <div class="position-relative">
                <label class="label-top-input">کد ملی:</label>
                <input type="text" name="national_code" id="edit-national-code" class="form-control"
                  placeholder="کد ملی بیمار را وارد کنید">
              </div>
              <small class="text-danger error-national_code mt-1 text-start"></small>
            </div>
            <div class="col-12">
              <div class="position-relative">
                <label class="label-top-input">تاریخ مراجعه:</label>
                <input type="text" name="appointment_date" placeholder="1403/05/02" id="edit-appointment-date"
                  data-jdp class="form-control">
              </div>
              <small class="text-danger error-appointment_date mt-1 text-start"></small>
            </div>
            <div class="col-12">
              <div class="position-relative timepicker-ui w-100">
                <label class="label-top-input">ساعت مراجعه:</label>
                <input data-timepicker type="text" name="appointment_time" id="edit-appointment-time"
                  class="form-control" style="width: 100% !important">
              </div>
              <small class="text-danger error-appointment_time mt-1 text-start"></small>
            </div>
            <div class="col-12">
              <div class="position-relative">
                <label class="label-top-input">توضیحات:</label>
                <textarea name="description" id="edit-description" class="form-control" rows="4"></textarea>
              </div>
              <small class="text-danger error-description mt-1 text-start"></small>
            </div>
          </div>
        </div>
        <div class="modal-footer p-3">
          <button type="submit" class="w-100 btn btn-primary d-flex justify-content-center align-items-center gap-2">
            <span class="button_text">ذخیره تغییرات</span>
            <div class="loader" style="display: none;"></div>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="endVisitModal" tabindex="-1" aria-labelledby="endVisitLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="end-visit-form">
        @csrf
        <div class="modal-header p-3">
          <h5 class="modal-title" id="endVisitLabel">پایان ویزیت</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" name="appointment_id" id="end-visit-appointment-id">
          <div class="row g-2">
            <div class="col-12">
              <div class="border rounded p-2 bg-light position-relative">
                <label class="form-label fw-bold mb-1">انتخاب بیمه</label>
                <div id="insurance-options"></div>
                <small class="text-danger error-insurance_id mt-1 text-start"></small>
              </div>
            </div>
            <div class="col-12">
              <div class="border rounded p-2 bg-light services-checkbox-container position-relative">
                <div class="loading-overlay-custom" id="services-loading">
                  <div class="spinner-custom-small"></div>
                </div>
                <label class="form-label fw-bold mb-1">انتخاب خدمت</label>
                <div class="checkbox-area" style="max-height: 100px; overflow-y: auto; padding: 0.25rem;">
                  <div id="service-options"></div>
                </div>
                <small class="text-danger error-service_ids mt-1 text-start"></small>
              </div>
            </div>
            <div class="col-12">
              <div class="row g-2">
                <div class="col-md-6">
                  <div class="border rounded p-2 bg-light">
                    <div class="form-check mb-0">
                      <input type="checkbox" class="form-check-input" id="is_free" name="is_free">
                      <label class="form-check-label" for="is_free">ویزیت رایگان</label>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="border rounded p-2 bg-light">
                    <label class="form-label fw-bold mb-1">نوع پرداخت</label>
                    <select class="form-control" name="payment_method" id="payment_method">
                      <option value="online">آنلاین</option>
                      <option value="cash">نقدی</option>
                      <option value="card_to_card">کارت به کارت</option>
                      <option value="pos">کارتخوان</option>
                    </select>
                    <small class="text-danger error-payment_method mt-1 text-start"></small>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded p-2 bg-light position-relative">
                <label class="form-label fw-bold mb-1">تخفیف</label>
                <div class="input-with-spinner">
                  <input type="number" step="0.01" min="0" max="100" class="form-control"
                    name="discount_percentage" id="discount_percentage" placeholder="تخفیف (٪)">
                  <div class="spinner-custom-small" id="discount-loading"></div>
                </div>
                <small class="text-danger error-discount_percentage mt-1 text-start"></small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded p-2 bg-light position-relative">
                <label class="form-label fw-bold mb-1">قیمت نهایی</label>
                <div class="input-with-spinner">
                  <input type="text" class="form-control" name="final_price" id="final_price" readonly>
                  <div class="spinner-custom-small" id="final-price-loading"></div>
                </div>
                <small class="text-danger error-final_price mt-1 text-start"></small>
              </div>
            </div>
            <div class="col-12">
              <div class="border rounded p-2 bg-light">
                <label class="form-label fw-bold mb-1">توضیحات درمان</label>
                <textarea class="form-control" rows="2" name="description" id="end_visit_description"
                  placeholder="توضیحات درمان را وارد کنید..."></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer p-3">
          <button type="submit"
            class="w-100 btn btn-primary d-flex justify-content-center align-items-center gap-2 h-50">
            <span class="button_text">ثبت</span>
            <div class="loader" style="display: none;"></div>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- مودال تخفیف -->
<div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <form id="apply-discount-form">
        @csrf
        <div class="modal-header p-3">
          <h5 class="modal-title" id="discountModalLabel">اعمال تخفیف</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3 position-relative">
            <label class="form-label">درصد تخفیف</label>
            <div class="input-with-spinner">
              <input type="number" class="form-control" name="discount_input_percentage"
                id="discount_input_percentage" placeholder="درصد تخفیف را وارد کنید" min="0" max="100"
                step="0.01">
              <div class="spinner-custom-small" id="discount-input-loading"></div>
            </div>
            <small class="text-danger error-discount_input_percentage mt-1 text-start"></small>
          </div>
          <div class="mb-3 position-relative">
            <label class="form-label">مبلغ تخفیف</label>
            <div class="input-with-spinner">
              <input type="number" class="form-control" name="discount_input_amount" id="discount_input_amount"
                placeholder="مبلغ تخفیف را وارد کنید" min="0" step="1">
              <div class="spinner-custom-small" id="discount-amount-loading"></div>
            </div>
            <small class="text-danger error-discount_input_amount mt-1 text-start"></small>
          </div>
        </div>
        <div class="modal-footer p-3">
          <button type="submit" class="w-100 btn btn-primary d-flex justify-content-center align-items-center gap-2">
            <span class="button_text">تأیید</span>
            <div class="loader" style="display: none;"></div>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="patient-information-content w-100 d-flex justify-content-center">
  <div class="my-patient-content d-none w-100">
    <div class="card">
      <div class="card-header">ثبت نوبت</div>
      <div class="card-body">
        <form method="post" action="" id="manual-appointment-form" autocomplete="off">
          @csrf
          <input type="hidden" id="user-id" name="user_id" value="">
          <input type="hidden" id="doctor-id" name="doctor_id"
            value="{{ auth('doctor')->id() ?? auth('secretary')->user()->doctor_id }}">
          <div class="row g-3">
            <div class="col-12 position-relative">
              <label class="label-top-input">نام بیمار:</label>
              <input type="text" name="fristname" class="form-control" placeholder="نام بیمار را وارد کنید"
                required>
            </div>
            <div class="col-12 position-relative">
              <label class="label-top-input">نام خانوادگی بیمار:</label>
              <input type="text" name="lastname" class="form-control"
                placeholder="نام و نام خانوادگی بیمار را وارد کنید" required>
            </div>
            <div class="col-12 position-relative">
              <label class="label-top-input">شماره موبایل بیمار:</label>
              <input type="text" name="mobile" class="form-control text-end"
                placeholder="شماره موبایل بیمار را وارد کنید" required>
            </div>
            <div class="col-12 position-relative">
              <label class="label-top-input">کد ملی بیمار:</label>
              <input type="text" name="codemeli" class="form-control text-end"
                placeholder="کدملی بیمار را وارد کنید">
            </div>
            <div class="col-12 position-relative">
              <label class="label-top-input">تاریخ مراجعه:</label>
              <input type="text" placeholder="1403/05/02" class="form-control" id="selected-date" data-jdp>
            </div>
            <div class="col-12 position-relative timepicker-ui w-100">
              <label class="label-top-input">ساعت مراجعه:</label>
              <input data-timepicker type="text" class="form-control timepicker-ui-input text-end fw-bold"
                id="appointment-time" value="00:00" style="width: 100% !important">
            </div>
            <div class="col-12 position-relative">
              <label class="label-top-input">توضیحات:</label>
              <textarea id="description" name="description" class="form-control" rows="4"></textarea>
            </div>
            <div class="col-12">
              <button type="submit"
                class="w-100 btn btn-primary d-flex justify-content-center align-items-center gap-2 h-50">
                <span class="button_text">ثبت تغییرات</span>
                <div class="loader" style="display: none;"></div>
              </button>
            </div>
          </div>
        </form>
        <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="exampleModalCenterTitle"
          aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header p-3">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <x-jalali-calendar />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="manual-nobat-content w-100 d-flex justify-content-center mt-4">
  <div class="manual-nobat-content-wrapper p-3">
    <div class="main-content">
      <div class="row g-0">
        <div class="user-panel-content w-100">
          <div class="table-responsive position-relative w-100 d-none d-md-block">
            <table class="table table-hover w-100 text-sm text-center bg-white shadow-sm rounded">
                <thead class="bg-light">
                    <tr>
                        <th><input class="form-check-input" type="checkbox" id="select-all-row"></th>
                        <th scope="col" class="px-6 py-3 fw-bolder">نام بیمار</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">شماره‌ موبایل</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">کد ملی</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">زمان نوبت</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">وضعیت نوبت</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">بیعانه</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">وضعیت پرداخت</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">بیمه</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">قیمت نهایی</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">پایان ویزیت</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">عملیات</th>
                    </tr>
                </thead>
                <tbody id="result_nobat">
                    @if (count($appointments) > 0)
                        @foreach ($appointments as $appointment)
                            <tr>
                                <td><input type="checkbox" class="appointment-checkbox form-check-input" value="{{ $appointment->id }}" data-status="{{ $appointment->status }}" data-mobile="{{ $appointment->user->mobile ?? '' }}"></td>
                                <td class="fw-bold">{{ $appointment->user ? $appointment->user->first_name . ' ' . $appointment->user->last_name : '-' }}</td>
                                <td>{{ $appointment->user ? $appointment->user->mobile : '-' }}</td>
                                <td>{{ $appointment->user ? $appointment->user->national_code : '-' }}</td>
                                <td>
                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($appointment->appointment_date)->format('Y/m/d') }}
                                    <span class="fw-bold d-block">{{ substr($appointment->appointment_time, 0, 5) }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusLabels = [
                                            'scheduled' => ['label' => 'در انتظار', 'class' => 'text-primary'],
                                            'attended' => ['label' => 'ویزیت شده', 'class' => 'text-success'],
                                            'cancelled' => ['label' => 'لغو شده', 'class' => 'text-danger'],
                                            'missed' => ['label' => 'عدم حضور', 'class' => 'text-warning'],
                                            'pending_review' => ['label' => 'در انتظار بررسی', 'class' => 'text-secondary'],
                                        ];
                                        $status = $appointment->status ?? 'scheduled';
                                        $statusInfo = $statusLabels[$status] ?? ['label' => 'نامشخص', 'class' => 'text-muted'];
                                    @endphp
                                    <span class="{{ $statusInfo['class'] }} fw-bold">{{ $statusInfo['label'] }}</span>
                                </td>
                                <td>{{ $appointment->fee ? number_format($appointment->fee) . ' تومان' : '-' }}</td>
                                <td>
                                    @php
                                        $paymentStatusLabels = [
                                            'paid' => ['label' => 'پرداخت شده', 'class' => 'text-success'],
                                            'unpaid' => ['label' => 'پرداخت نشده', 'class' => 'text-danger'],
                                            'pending' => ['label' => 'در انتظار پرداخت', 'class' => 'text-primary'],
                                        ];
                                        $paymentStatus = $appointment->payment_status;
                                        $paymentStatusInfo = $paymentStatusLabels[$paymentStatus] ?? [
                                            'label' => 'نامشخص',
                                            'class' => 'text-muted',
                                        ];
                                        $paymentMethodLabels = [
                                            'online' => 'آنلاین',
                                            'cash' => 'نقدی',
                                            'card_to_card' => 'کارت به کارت',
                                            'pos' => 'کارتخوان',
                                        ];
                                        $paymentMethod = $appointment->payment_method ?? 'online';
                                    @endphp
                                    <span class="{{ $paymentStatusInfo['class'] }} fw-bold">
                                        {{ $paymentStatusInfo['label'] }}
                                        @if ($paymentStatus === 'paid')
                                            ({{ $paymentMethodLabels[$paymentMethod] ?? '-' }})
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $appointment->insurance ? $appointment->insurance->name : '-' }}</td>
                                <td>{{ $appointment->final_price ? number_format($appointment->final_price) . ' تومان' : '-' }}</td>
                                <td>
                                    @if ($appointment->status !== 'attended' && $appointment->status !== 'cancelled')
                                        <button class="btn btn-sm btn-primary shadow-sm end-visit-btn" data-id="{{ $appointment->id }}" data-clinic-id="{{ $appointment->clinic_id ?? '1' }}">پایان ویزیت</button>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-light edit-btn rounded-circle shadow-sm" data-id="{{ $appointment->id }}">
                                        <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
                                    </button>
                                    <button class="btn btn-sm btn-light delete-btn rounded-circle shadow-sm" data-id="{{ $appointment->id }}">
                                        <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="12" class="text-center">نتیجه‌ای یافت نشد</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>


@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
  $(document).ready(function() {
    $('.card').css({
      'width': '850px',
      'height': '100%'
    });
  });
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
</script>
<script>
  $(document).ready(function() {
    let dropdownOpen = false;
    let selectedClinic = localStorage.getItem('selectedClinic');
    let selectedClinicId = localStorage.getItem('selectedClinicId');
    if (selectedClinic && selectedClinicId) {
      $('.dropdown-label').text(selectedClinic);
      $('.option-card').each(function() {
        if ($(this).attr('data-id') === selectedClinicId) {
          $('.option-card').removeClass('card-active');
          $(this).addClass('card-active');
        }
      });
    } else {
      localStorage.setItem('selectedClinic', 'مشاوره آنلاین به نوبه');
      localStorage.setItem('selectedClinicId', 'default');
    }

    function checkInactiveClinics() {
      var hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
      if (hasInactiveClinics) {
        $('.dropdown-trigger').addClass('warning');
      } else {
        $('.dropdown-trigger').removeClass('warning');
      }
    }
    checkInactiveClinics();

    $('.dropdown-trigger').on('click', function(event) {
      event.stopPropagation();
      dropdownOpen = !dropdownOpen;
      $(this).toggleClass('border border-primary');
      $('.my-dropdown-menu').toggleClass('d-none');
      setTimeout(() => {
        dropdownOpen = $('.my-dropdown-menu').is(':visible');
      }, 100);
    });

    $(document).on('click', function() {
      if (dropdownOpen) {
        $('.dropdown-trigger').removeClass('border border-primary');
        $('.my-dropdown-menu').addClass('d-none');
        dropdownOpen = false;
      }
    });

    $('.option-card').on('click', function() {
      var selectedText = $(this).find('.fw-bold.d-block.fs-15').text().trim();
      var selectedId = $(this).attr('data-id');
      $('.option-card').removeClass('card-active');
      $(this).addClass('card-active');
      $('.dropdown-label').text(selectedText);

      localStorage.setItem('selectedClinic', selectedText);
      localStorage.setItem('selectedClinicId', selectedId);
      checkInactiveClinics();
      $('.dropdown-trigger').removeClass('border border-primary');
      $('.my-dropdown-menu').addClass('d-none');
      dropdownOpen = false;

      window.location.href = window.location.pathname + "?selectedClinicId=" + selectedId;
    });
  });

  $(document).ready(function() {
    // AJAX search functionality
    $('#search-input').on('input', function() {
      const query = $(this).val();
      if (query.length > 2) { // حداقل ۳ کاراکتر برای جستجو
        $.ajax({
          url: "{{ route('dr-panel-search.users') }}",
          method: 'GET',
          data: {
            query: query,
            selectedClinicId: localStorage.getItem('selectedClinicId')
          },
          success: function(response) {
            let resultsHtml = '';
            if (response.length > 0) {
              response.forEach(function(user) {
                resultsHtml += `
                    <div class="search-result-card" data-user-id="${user.id}" 
                         data-first-name="${user.first_name}" 
                         data-last-name="${user.last_name}" 
                         data-mobile="${user.mobile}" 
                         data-national-code="${user.national_code}">
                      <div class="search-result-info">
                        <p class="name">${user.first_name} ${user.last_name}</p>
                        <p>موبایل: ${user.mobile}</p>
                        <p>کد ملی: ${user.national_code}</p>
                      </div>
                      <div class="search-result-action">
                        <button>انتخاب</button>
                      </div>
                    </div>`;
              });
            } else {
              resultsHtml = '<div class="no-results">نتیجه‌ای یافت نشد</div>';
            }
            $('#search-results-body').html(resultsHtml);
            $('#search-results').css('display', 'block');
          },
          error: function() {
            toastr.error('خطا در جستجو!');
            $('#search-results').css('display', 'none');
          }
        });
      } else {
        $('#search-results-body').empty();
        $('#search-results').css('display', 'none');
      }
    });

    // Insert selected user data into the form fields and search input
    $(document).on('click', '.search-result-card', function() {
      $('#search-results').css('display', 'none');

      const userId = $(this).data('user-id');
      const firstName = $(this).data('first-name');
      const lastName = $(this).data('last-name');
      const mobile = $(this).data('mobile');
      const nationalCode = $(this).data('national-code');

      // پر کردن فیلدهای فرم
      $('#user-id').val(userId);
      $('input[name="fristname"]').val(firstName);
      $('input[name="lastname"]').val(lastName);
      $('input[name="mobile"]').val(mobile);
      $('input[name="codemeli"]').val(nationalCode);

      // نمایش فرم اطلاعات بیمار
      $('.my-patient-content').removeClass('d-none');

      // پاک کردن نتایج جستجو
      $('#search-results-body').empty();
      $('#search-input').val('');
    });

    // Hide patient information section initially
    $('.my-patient-content').addClass('d-none');
  });

  $(document).on('show.bs.modal', '.modal', function() {
    $("#search-results").addClass('d-none');
  });
  $(document).on('hide.bs.modal', '.modal', function() {
    $("#search-results").removeClass('d-none');
  });
</script>
<script>
function addRowToTable(data) {
    const jalaliDate = moment(data.appointment_date, 'YYYY-MM-DD').format('jYYYY/jMM/jDD');
    const time = data.appointment_time ? data.appointment_time.substring(0, 5) : '---'; // فرمت HH:MM

    // تبدیل وضعیت نوبت به فارسی
    const statusMap = {
        'scheduled': { label: 'در انتظار', class: 'text-primary' },
        'attended': { label: 'ویزیت شده', class: 'text-success' },
        'cancelled': { label: 'لغو شده', class: 'text-danger' },
        'missed': { label: 'عدم حضور', class: 'text-warning' },
        'pending_review': { label: 'در انتظار بررسی', class: 'text-secondary' }
    };
    const statusInfo = statusMap[data.status] || { label: 'نامشخص', class: 'text-muted' };

    // تبدیل وضعیت پرداخت به فارسی
    const paymentStatusMap = {
        'paid': { label: 'پرداخت شده', class: 'text-success' },
        'unpaid': { label: 'پرداخت نشده', class: 'text-danger' },
        'pending': { label: 'در انتظار پرداخت', class: 'text-primary' }
    };
    const paymentStatusInfo = paymentStatusMap[data.payment_status] || { label: 'نامشخص', class: 'text-muted' };

    // تبدیل نوع پرداخت به فارسی
    const paymentMethodMap = {
        'online': 'آنلاین',
        'cash': 'نقدی',
        'card_to_card': 'کارت به کارت',
        'pos': 'کارتخوان'
    };
    const paymentMethod = data.payment_status === 'paid' && data.payment_method
        ? ` (${paymentMethodMap[data.payment_method] || 'نامشخص'})`
        : '';

    // HTML دکمه پایان ویزیت
    const endVisitHtml = (data.status !== 'attended' && data.status !== 'cancelled')
        ? `<button class="btn btn-sm btn-primary shadow-sm end-visit-btn" data-id="${data.id}" data-clinic-id="${data.clinic_id || '1'}">پایان ویزیت</button>`
        : '-';

    const newRow = `
        <tr>
            <td><input type="checkbox" class="appointment-checkbox form-check-input" value="${data.id}" data-status="${data.status}" data-mobile="${data.user?.mobile || ''}"></td>
            <td class="fw-bold">${data.user?.first_name || '-'} ${data.user?.last_name || '-'}</td>
            <td>${data.user?.mobile || '-'}</td>
            <td>${data.user?.national_code || '-'}</td>
            <td>
                ${jalaliDate || '-'}
                <span class="fw-bold d-block">${time}</span>
            </td>
            <td><span class="${statusInfo.class} fw-bold">${statusInfo.label}</span></td>
            <td>${data.fee ? data.fee.toLocaleString('fa-IR') + ' تومان' : '-'}</td>
            <td><span class="${paymentStatusInfo.class} fw-bold">${paymentStatusInfo.label}${paymentMethod}</span></td>
            <td>${data.insurance?.name || '-'}</td>
            <td>${data.final_price ? data.final_price.toLocaleString('fa-IR') + ' تومان' : '-'}</td>
            <td>${endVisitHtml}</td>
            <td>
                <button class="btn btn-sm btn-light edit-btn rounded-circle shadow-sm" data-id="${data.id}">
                    <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
                </button>
                <button class="btn btn-sm btn-light delete-btn rounded-circle shadow-sm" data-id="${data.id}">
                    <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                </button>
            </td>
        </tr>`;
    $('#result_nobat').append(newRow);
}

  function loadAppointments() {
    $.ajax({
      url: "{{ route('dr-manual_nobat') }}",
      method: 'GET',
      data: {
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(response) {
        if (response.success && response.data) {
          $('#result_nobat').empty();
          response.data.forEach(function(appointment) {
            addRowToTable(appointment);
          });
        } else {
          toastr.error('داده‌ای برای نمایش وجود ندارد!');
        }
      },
      error: function() {
        toastr.error('خطا در بارگذاری نوبت‌ها!');
      }
    });
  }

  $(document).ready(function() {
    // ثبت فرم
    $('#manual-appointment-form').on('submit', function(e) {
      e.preventDefault();
      const form = this;
      const submitButton = form.querySelector('button[type="submit"]');
      const loader = submitButton.querySelector('.loader');
      const buttonText = submitButton.querySelector('.button_text');
      const data = {
        user_id: $('#user-id').val(),
        doctor_id: $('#doctor-id').val(),
        appointment_date: $('#selected-date').val(),
        appointment_time: $('#appointment-time').val(),
        description: $('#description').val(),
      };

      if (!data.user_id || !data.doctor_id || !data.appointment_date || !data.appointment_time) {
        toastr.error('لطفاً تمام فیلدهای ضروری را تکمیل کنید!');
        return;
      }

      buttonText.style.display = 'none';
      loader.style.display = 'block';

      $.ajax({
        url: "{{ route('manual-nobat.store') }}",
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        data: {
          ...data,
          selectedClinicId: localStorage.getItem('selectedClinicId')
        },
        success: function(response) {
          toastr.success(response.message || 'نوبت با موفقیت ثبت شد!');
          form.reset();
          $('.patient-information-content').removeClass('d-flex');
          $('.patient-information-content').addClass('d-none');
          loadAppointments();
        },
        error: function(xhr) {
          const errors = xhr.responseJSON.errors || {};
          let errorMessages = Object.values(errors).map(errArray => errArray[0]).join(' - ');
          toastr.error(errorMessages || xhr.responseJSON.message);
          $('.patient-information-content').removeClass('d-flex');
          $('.patient-information-content').addClass('d-none');
        },
        complete: function() {
          buttonText.style.display = 'block';
          loader.style.display = 'none';
        },
      });
    });

    // کلیک روی نتایج جستجو برای نمایش فرم
    $(document).on('click', '.search-result-item', function() {
      const userId = $(this).data('user-id');
      const firstName = $(this).data('first-name');
      const lastName = $(this).data('last-name');
      const mobile = $(this).data('mobile');
      const nationalCode = $(this).data('national-code');

      $('#user-id').val(userId);
      $('input[name="fristname"]').val(firstName);
      $('input[name="lastname"]').val(lastName);
      $('input[name="mobile"]').val(mobile);
      $('input[name="codemeli"]').val(nationalCode);

      $('.patient-information-content').removeClass('d-none');
      $('.patient-information-content').addClass('d-flex justify-content-center');

      $('#search-results-body').empty();
      $('#search-input').val('');
      $('#search-results').css('display', 'none');
    });
  });

  $(document).ready(function() {
    loadAppointments();
    $(document).on('click', '.delete-btn', function() {
      const id = $(this).data('id');
      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: "این عمل قابل بازگشت نیست!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله، حذف شود!',
        cancelButtonText: 'لغو'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `/manual_appointments/${id}`,
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
              if (response.success) {
                toastr.success('نوبت با موفقیت حذف شد!');
                loadAppointments();
              } else {
                toastr.error('خطا در حذف نوبت!');
              }
            },
            error: function() {
              toastr.error('خطا در عملیات حذف!');
            }
          });
        }
      });
    });

    $(document).on('click', '.edit-btn', function() {
      const appointmentId = $(this).data('id');
      $.ajax({
        url: "{{ route('manual-appointments.edit', ':id') }}".replace(':id', appointmentId),
        method: 'GET',
        data: {
          selectedClinicId: localStorage.getItem('selectedClinicId')
        },
        success: function(response) {
          if (response.success) {
            const appointment = response.data;
            $('#edit-appointment-id').val(appointment.id);
            $('#edit-first-name').val(appointment.user.first_name);
            $('#edit-last-name').val(appointment.user.last_name);
            $('#edit-mobile').val(appointment.user.mobile);
            $('#edit-national-code').val(appointment.user.national_code);
            $('#edit-appointment-date').val(moment(appointment.appointment_date, 'YYYY-MM-DD').format(
              'jYYYY/jMM/jDD'));
            $('#edit-appointment-time').val(appointment.appointment_time.substring(0, 5));
            $('#edit-description').val(appointment.description);
            $('#editPatientModal').modal('show');
          } else {
            toastr.error('خطا در دریافت اطلاعات نوبت!');
          }
        },
        error: function() {
          toastr.error('خطا در دریافت اطلاعات نوبت!');
        }
      });
    });

    $('#edit-patient-form').on('submit', function(e) {
      e.preventDefault();
      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.bblesbutton_text');

      form.find('small.text-danger').text('');

      buttonText.hide();
      loader.show();

      const appointmentId = $('#edit-appointment-id').val();
      const data = {
        first_name: $('#edit-first-name').val(),
        last_name: $('#edit-last-name').val(),
        mobile: $('#edit-mobile').val(),
        national_code: $('#edit-national-code').val(),
        appointment_date: $('#edit-appointment-date').val(),
        appointment_time: $('#edit-appointment-time').val(),
        description: $('#edit-description').val(),
      };

      $.ajax({
        url: "{{ route('manual-appointments.update', ':id') }}".replace(':id', appointmentId),
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        data: {
          ...data,
          selectedClinicId: localStorage.getItem('selectedClinicId')
        },
        success: function(response) {
          if (response.success) {
            toastr.success(response.message);
            $('#editPatientModal').modal('hide');
            loadAppointments();
          } else {
            toastr.error(response.message);
          }
        },
        error: function(xhr) {
          const errors = xhr.responseJSON?.errors || [];
          const errorMapping = {
            'نام بیمار الزامی است.': 'first_name',
            'نام خانوادگی بیمار الزامی است.': 'last_name',
            'شماره موبایل الزامی است.': 'mobile',
            'کد ملی الزامی است.': 'national_code',
            'تاریخ نوبت الزامی است.': 'appointment_date',
            'ساعت نوبت الزامی است.': 'appointment_time'
          };

          errors.forEach(function(errorMsg) {
            const field = errorMapping[errorMsg];
            if (field) {
              form.find(`.error-${field}`).text(errorMsg);
            }
          });

          if (errors.length === 0) {
            toastr.error(xhr.responseJSON?.message || 'خطا در ویرایش نوبت!');
          }
        },
        complete: function() {
          buttonText.show();
          loader.hide();
        },
      });
    });

    $('#add-new-patient-form').on('submit', function(e) {
      e.preventDefault();
      const form = $(this);
      const submitButton = form.find('#submit-button');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.button_text');

      form.find('small.text-danger').text('');

      buttonText.hide();
      loader.show();

      $.ajax({
        url: "{{ route('manual-nobat.store-with-user') }}",
        method: 'POST',
        data: form.serialize() + '&selectedClinicId=' + encodeURIComponent(localStorage.getItem(
          'selectedClinicId')),
        success: function(response) {
          if (response.success && response.data) {
            addRowToTable(response.data);
            form.trigger('reset');
            $('#addNewPatientModal').modal('hide');
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
            toastr.success('بیمار با موفقیت اضافه شد!');
          } else {
            toastr.error('خطا در اضافه کردن بیمار!');
          }
        },
        error: function(xhr) {
          const errors = xhr.responseJSON?.errors || [];
          const errorMapping = {
            'نام بیمار الزامی است.': 'first_name',
            'نام خانوادگی بیمار الزامی است.': 'last_name',
            'شماره موبایل الزامی است.': 'mobile',
            'کد ملی الزامی است.': 'national_code',
            'تاریخ نوبت الزامی است.': 'appointment_date',
            'ساعت نوبت الزامی است.': 'appointment_time'
          };

          errors.forEach(function(errorMsg) {
            toastr.error(errorMsg);

            const field = errorMapping[errorMsg];
            if (field) {
              form.find(`.error-${field}`).text(errorMsg);
            }
          });

          if (errors.length === 0) {
            toastr.error(xhr.responseJSON?.message || 'خطا در ثبت اطلاعات!');
          }
        },
        complete: function() {
          buttonText.show();
          loader.hide();
        }
      });
    });

    $(document).on('click', '.delete-btn', function() {
      const id = $(this).data('id');
      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: "این عمل قابل بازگشت نیست!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله، حذف شود!',
        cancelButtonText: 'لغو'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('manual_appointments.destroy', ':id') }}".replace(':id', id),
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            data: {
              selectedClinicId: localStorage.getItem('selectedClinicId')
            },
            success: function(response) {
              if (response.success) {
                toastr.success(response.message);
                loadAppointments();
              } else {
                toastr.error(response.message);
              }
            },
            error: function(xhr) {
              toastr.error('خطا در حذف نوبت!');
            },
          });
        }
      });
    });
  });

  function formatPrice(price) {
    return price.toLocaleString('fa-IR') + ' تومان';
  }

  // محاسبه قیمت نهایی
  function calculateFinalPrice() {
    const serviceIds = $('input[name="service_ids[]"]:checked').map(function() {
      return $(this).val();
    }).get();
    const isFree = $('#is_free').is(':checked');
    const discountPercentage = parseFloat($('#discount_percentage').val()) || 0;
    const discountAmount = parseFloat($('#discount_input_amount').val()) || 0;

    if (!serviceIds.length && !isFree) {
      toastr.error('لطفاً حداقل یک خدمت انتخاب کنید یا ویزیت رایگان را فعال کنید.');
      $('#final-price-loading').removeClass('show-spinner');
      return;
    }

    $('#final-price-loading').addClass('show-spinner');
    $.ajax({
      url: "{{ route('manual-nobat.calculate-final-price') }}",
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      data: {
        service_ids: serviceIds,
        is_free: isFree ? 1 : 0,
        discount_percentage: discountPercentage,
        discount_amount: discountAmount,
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(response) {
        if (response.success) {
          basePrice = response.data.final_price + response.data.discount_amount; // ذخیره قیمت پایه
          $('#final_price').val(formatPrice(response.data.final_price));
          $('#discount_percentage').val(response.data.discount_percentage);
          $('#discount_input_percentage').val(response.data.discount_percentage);
          $('#discount_input_amount').val(response.data.discount_amount);
        } else {
          toastr.error(response.message || 'خطا در محاسبه قیمت!');
        }
      },
      error: function(xhr) {
        const errors = xhr.responseJSON?.errors || [];
        errors.forEach(error => toastr.error(error));
        toastr.error(xhr.responseJSON?.message || 'خطا در محاسبه قیمت!');
      },
      complete: function() {
        $('#final-price-loading').removeClass('show-spinner');
      }
    });
  }

  // بارگذاری بیمه‌ها
  function loadInsurances(appointmentId) {
    $.ajax({
      url: "{{ route('manual-nobat.insurances') }}",
      method: 'GET',
      data: {
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(response) {
        if (response.success && response.data.length > 0) {
          let html = '';
          response.data.forEach(insurance => {
            html += `
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="insurance_id" id="insurance_${insurance.id}" value="${insurance.id}">
                                <label class="form-check-label" for="insurance_${insurance.id}">${insurance.name}</label>
                            </div>`;
          });
          $('#insurance-options').html(html);
        } else {
          $('#insurance-options').html('<p class="text-danger small mb-0">هیچ بیمه‌ای یافت نشد.</p>');
        }
      },
      error: function() {
        toastr.error('خطا در بارگذاری بیمه‌ها!');
      }
    });
  }

  // بارگذاری خدمات
  function loadServices(insuranceId) {
    $('#services-loading').addClass('show-custom');
    $('#service-options').html('');
    $.ajax({
      url: "{{ route('manual-nobat.services', ':insuranceId') }}".replace(':insuranceId', insuranceId),
      method: 'GET',
      data: {
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(response) {
        if (response.success && response.data.length > 0) {
          let html = '';
          response.data.forEach(service => {
            html += `
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="service_ids[]" id="service_${service.id}" value="${service.id}">
                                <label class="form-check-label" for="service_${service.id}">
                                    ${service.name} (${formatPrice(service.price)})
                                </label>
                            </div>`;
          });
          $('#service-options').html(html);
        } else {
          $('#service-options').html('<p class="text-danger small mb-0">هیچ خدمتی یافت نشد.</p>');
        }
      },
      error: function() {
        toastr.error('خطا در بارگذاری خدمات!');
      },
      complete: function() {
        $('#services-loading').removeClass('show-custom');
      }
    });
  }

  // باز کردن مودال پایان ویزیت
  $(document).on('click', '.end-visit-btn', function() {
    const appointmentId = $(this).data('id');
    $('#end-visit-appointment-id').val(appointmentId);
    $('#end-visit-form')[0].reset();
    $('#insurance-options, #service-options').empty();
    $('#final_price').val('');
    $('#discount_percentage').val('');
    $('#discount_input_amount').val('');
    $('#is_free').prop('checked', false);
    $('#payment_method').prop('disabled', false);
    $('#discount_percentage').prop('disabled', false);
    basePrice = 0; // ریست قیمت پایه
    loadInsurances(appointmentId);
    $('#endVisitModal').modal('show');
  });

  // تغییر بیمه و بارگذاری خدمات
  $(document).on('change', 'input[name="insurance_id"]', function() {
    const insuranceId = $(this).val();
    loadServices(insuranceId);
  });

  // محاسبه قیمت هنگام تغییر خدمات یا ویزیت رایگان
  $(document).on('change', 'input[name="service_ids[]"], #is_free', function() {
    if ($('#is_free').is(':checked')) {
      $('#final_price').val(formatPrice(0));
      $('#discount_percentage').val(0).prop('disabled', true);
      $('#discount_input_amount').val(0);
      $('#payment_method').prop('disabled', true);
    } else {
      $('#discount_percentage').prop('disabled', false);
      $('#payment_method').prop('disabled', false);
      calculateFinalPrice();
    }
  });

  // باز کردن مودال تخفیف
  $(document).on('click', '#discount_percentage', function() {
    $('#discount_input_percentage').val($(this).val());
    $('#discount_input_amount').val($('#discount_input_amount').val() || 0);
    $('#discountModal').modal('show');
  });

  // محاسبه پویا هنگام تغییر درصد تخفیف
  $(document).on('input', '#discount_input_percentage', function() {
    const percentage = parseFloat($(this).val()) || 0;
    if (percentage > 100) {
      $(this).val(100);
      toastr.warning('درصد تخفیف نمی‌تواند بیش از ۱۰۰ باشد!');
      return;
    } else if (percentage < 0) {
      $(this).val(0);
      toastr.warning('درصد تخفیف نمی‌تواند منفی باشد!');
      return;
    }

    // محاسبه مبلغ تخفیف بر اساس قیمت پایه
    const discountAmount = basePrice ? round((basePrice * percentage) / 100, 2) : 0;
    $('#discount_input_amount').val(discountAmount);

    // محاسبه قیمت نهایی
    calculateFinalPrice();
  });

  // محاسبه پویا هنگام تغییر مبلغ تخفیف
  $(document).on('input', '#discount_input_amount', function() {
    const amount = parseFloat($(this).val()) || 0;
    if (amount < 0) {
      $(this).val(0);
      toastr.warning('مبلغ تخفیف نمی‌تواند منفی باشد!');
      return;
    } else if (amount > basePrice) {
      $(this).val(basePrice);
      toastr.warning('مبلغ تخفیف نمی‌تواند بیشتر از قیمت پایه باشد!');
      return;
    }

    // محاسبه درصد تخفیف بر اساس قیمت پایه
    const discountPercentage = basePrice ? round((amount / basePrice) * 100, 2) : 0;
    $('#discount_input_percentage').val(discountPercentage);

    // محاسبه قیمت نهایی
    calculateFinalPrice();
  });

  // تابع گرد کردن
  function round(value, decimals) {
    return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
  }

  // ثبت تخفیف
  $('#apply-discount-form').on('submit', function(e) {
    e.preventDefault();
    const percentage = parseFloat($('#discount_input_percentage').val()) || 0;
    const amount = parseFloat($('#discount_input_amount').val()) || 0;

    $('#discount_percentage').val(percentage);
    $('#discount_input_amount').val(amount);
    $('#discountModal').modal('hide');
    calculateFinalPrice();
  });

  $('#end-visit-form').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const submitButton = form.find('button[type="submit"]');
    const loader = submitButton.find('.loader');
    const buttonText = submitButton.find('.button_text');
    const appointmentId = $('#end-visit-appointment-id').val();

    form.find('small.text-danger').text('');
    buttonText.hide();
    loader.show();

    // آماده‌سازی داده‌ها
    const formData = form.serializeArray();
    formData.push({
      name: 'final_price',
      value: parseFloat($('#final_price').val().replace(/[^\d.-]/g, '')) || 0
    });

    // اضافه کردن service_ids به‌صورت آرایه
    const serviceIds = $('input[name="service_ids[]"]:checked').map(function() {
      return $(this).val();
    }).get();

    // اضافه کردن هر service_id به‌صورت جداگانه با کلید service_ids[]
    serviceIds.forEach(function(serviceId) {
      formData.push({
        name: 'service_ids[]',
        value: serviceId
      });
    });

    // اضافه کردن selectedClinicId
    formData.push({
      name: 'selectedClinicId',
      value: localStorage.getItem('selectedClinicId') || '1'
    });

    $.ajax({
      url: "{{ route('manual-nobat.end-visit', ':id') }}".replace(':id', appointmentId),
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      data: formData,
      success: function(response) {
        if (response.success) {
          toastr.success(response.message);
          $('#endVisitModal').modal('hide');
          loadAppointments();
        } else {
          toastr.error(response.message);
        }
      },
      error: function(xhr) {
        const errors = xhr.responseJSON?.errors || [];
        const errorMapping = {
          'لطفاً یک بیمه انتخاب کنید.': 'insurance_id',
          'لطفاً حداقل یک خدمت انتخاب کنید.': 'service_ids',
          'خدمات باید به‌صورت آرایه باشند.': 'service_ids',
          'لطفاً نوع پرداخت را انتخاب کنید.': 'payment_method',
          'قیمت نهایی الزامی است.': 'final_price'
        };

        errors.forEach(errorMsg => {
          const field = errorMapping[errorMsg];
          if (field) {
            form.find(`.error-${field}`).text(errorMsg);
          }
          toastr.error(errorMsg);
        });

        if (errors.length === 0) {
          toastr.error(xhr.responseJSON?.error_details || xhr.responseJSON?.message || 'خطا در ثبت ویزیت!');
        }
      },
      complete: function() {
        buttonText.show();
        loader.hide();
      }
    });
  });
</script>
@endsection
