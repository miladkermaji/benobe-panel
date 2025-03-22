@extends('dr.panel.layouts.master')
@section('styles')
 <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
 <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
 <link type="text/css" href="{{ asset('dr-assets/panel/css/profile/subuser.css') }}" rel="stylesheet" />
 <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/scheduleSetting/workhours.css') }}"
  rel="stylesheet" />
 <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/appointments_open/appointments_open.css') }}"
  rel="stylesheet" />
 <link type="text/css" href="{{ asset('dr-assets/panel/turn/schedule/manual_nobat/manual_nobat.css') }}"
  rel="stylesheet" />
@endsection
@section('site-header')
 {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
 @include('dr.panel.my-tools.loader-btn')
@section('bread-crumb-title', ' ثبت نوبت دستی')
<div class="calendar-and-add-sick-section p-3">
 <div class="d-flex justify-content-center gap-10 align-items-center c-a-wrapper">
  <div>
   <div class="turning_search-wrapper__loGVc">
    <input type="text" id="search-input" class="my-form-control" placeholder="نام بیمار، شماره موبایل یا کد ملی ...">
    <div id="search-results" class="table-responsive border mb-0">
     <table class="table table-light table-hover">
      <thead>
       <tr>
        <th>نام</th>
        <th>نام خانوادگی</th>
        <th>شماره موبایل</th>
        <th>کد ملی</th>
       </tr>
      </thead>
      <tbody id="search-results-body">
      </tbody>
     </table>
    </div>
   </div>
  </div>
  <div class="btn-425-left">
   <button class="btn btn-primary h-50 fs-13" data-toggle="modal" data-target="#addNewPatientModal" data-toggle="modal"
    data-target="#addNewPatientModal">افزودن
    بیمار</button>
   <!-- فرم افزودن بیمار -->
   <div class="modal fade" id="addNewPatientModal" tabindex="-1" role="dialog" aria-labelledby="addNewPatientLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
     <div class="modal-content border-radius-6">
      <form id="add-new-patient-form">
       @csrf
       <div class="modal-header">
        <h5 class="modal-title" id="addNewPatientLabel">افزودن بیمار جدید</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
         <span aria-hidden="true">&times;</span>
        </button>
       </div>
       <div class="modal-body">
        <div class="mt-3 position-relative">
         <label class="label-top-input-special-takhasos">نام بیمار:</label>
         <input type="text" name="first_name" class="form-control h-50" placeholder="نام بیمار را وارد کنید">
        </div>
        <small class="text-danger error-first_name"></small>
        <div class="mt-3 position-relative">
         <label class="label-top-input-special-takhasos">نام خانوادگی بیمار:</label>
         <input type="text" name="last_name" class="form-control h-50"
          placeholder="نام و نام خانوادگی بیمار را وارد کنید">
        </div>
        <small class="text-danger error-last_name"></small>
        <div class="mt-3 position-relative">
         <label class="label-top-input-special-takhasos">شماره موبایل:</label>
         <input type="text" name="mobile" class="form-control h-50" placeholder="شماره موبایل بیمار را وارد کنید">
        </div>
        <small class="text-danger error-mobile"></small>
        <div class="mt-3 position-relative">
         <label class="label-top-input-special-takhasos">کد ملی:</label>
         <input type="text" name="national_code" class="form-control h-50" placeholder="کد ملی بیمار را وارد کنید">
        </div>
        <small class="text-danger error-national_code"></small>
        <div class="mt-3 position-relative">
         <label class="label-top-input-special-takhasos">تاریخ مراجعه:</label>
         <input type="text" placeholder="1403/05/02" name="appointment_date"
          class="form-control w-100 h-50 position-relative text-start">
        </div>
        <small class="text-danger error-appointment_date"></small>
        <div class="mt-3 position-relative timepicker-ui w-100">
         <label class="label-top-input-special-takhasos">ساعت مراجعه:</label>
         <input type="text" class="form-control w-100 h-50 position-relative timepicker-ui-input"
          style="width: 100% !important" name="appointment_time">
        </div>
        <small class="text-danger error-appointment_time"></small>
        <div class="mt-3 position-relative">
         <label class="label-top-input-special-takhasos">توضیحات:</label>
         <textarea name="description" class="form-control h-50" rows="3"></textarea>
        </div>
        <small class="text-danger error-description"></small>
       </div>
       <div class="modal-footer">
        <button type="submit" id="submit-button"
         class="w-100 btn btn-primary h-50 border-radius-4 d-flex justify-content-center align-items-center">
         <span class="button_text">ثبت تغیرات</span>
         <div class="loader"></div>
        </button>
       </div>
      </form>
     </div>
    </div>
   </div>
  </div>
 </div>
</div>
<div class="patient-information-content w-100 d-flex justify-content-center">
 <div class="my-patient-content d-none">
  <div class="card gray clrfix" style="padding-bottom: 0;">
   <div class="card-header">ثبت نوبت</div>
   <div class="card-body">
    <form method="post" action="" id="manual-appointment-form" autocomplete="off">
     @csrf
     <input type="hidden" id="user-id" name="user_id" value="">
     <input type="hidden" id="doctor-id" name="doctor_id"
      value="{{ auth('doctor')->id() ?? auth('secretary')->user()->doctor_id }}">
     <div class="mt-3 position-relative">
      <label class="label-top-input-special-takhasos"> نام بیمار:</label>
      <input type="text" name="fristname" class="form-control h-50" placeholder="نام بیمار را وارد کنید"
       required="">
     </div>
     <div class="mt-3 position-relative">
      <label class="label-top-input-special-takhasos"> نام خانوادگی بیمار:</label>
      <input type="text" name="lastname" class="form-control h-50"
       placeholder="نام و نام خانوادگی بیمار را وارد کنید" required="">
     </div>
     <div class="mt-3 position-relative">
      <label class="label-top-input-special-takhasos"> شماره موبایل بیمار:</label>
      <input type="text" name="mobile" class="form-control h-50" placeholder="شماره موبایل بیمار را وارد کنید"
       required="">
     </div>
     <div class="mt-3 position-relative">
      <label class="label-top-input-special-takhasos"> کد ملی بیمار:</label>
      <input type="text" name="codemeli" class="form-control h-50" placeholder="کدملی بیمار را وارد کنید">
     </div>
     <div class="mt-3 position-relative">
      <label class="label-top-input-special-takhasos"> تاریخ مراجعه: </label>
      <div class="turning_selectDate__MLRSb  w-100">
       <button
        class="selectDate_datepicker__xkZeS cursor-pointer text-center h-50 bg-light-blue d-flex justify-content-center align-items-center w-100"
        data-toggle="modal" data-target="#calendarModal">
        <span id="selected-date" class="mx-1"></span>
        {{-- <span type="text" class="observer-example bg-transparent text-center cursor-pointer"></span> --}}
        <svg style="margin-top: -4px" width="20" height="20" viewBox="0 0 20 20" fill="none"
         class="calendar-svg" xmlns="http://www.w3.org/2000/svg">
         <rect x="2.63989" y="3.49097" width="15" height="14" rx="4" fill="#000"
          fill-opacity="0">
         </rect>
         <path fill-rule="evenodd" clip-rule="evenodd"
          d="M7.41668 1.59094C7.41668 1.17673 7.08089 0.840942 6.66668 0.840942C6.25247 0.840942 5.91668 1.17673 5.91668 1.59094V2.54303C5.89207 2.54591 5.86779 2.54901 5.84377 2.55236C3.7414 2.84563 2.08883 4.49821 1.79556 6.60057C1.7499 6.92787 1.74994 7.30407 1.75 7.89547L1.75001 7.96269V10.7568L1.75001 10.8057C1.75 12.408 1.74999 13.6773 1.86869 14.6816C1.99055 15.7125 2.24639 16.5612 2.82821 17.2702C3.02559 17.5107 3.24613 17.7312 3.48664 17.9286C4.19559 18.5104 5.04429 18.7663 6.07526 18.8881C7.07948 19.0068 8.34883 19.0068 9.9511 19.0068H10H10.0489C11.6512 19.0068 12.9205 19.0068 13.9248 18.8881C14.9557 18.7663 15.8044 18.5104 16.5134 17.9286C16.7539 17.7312 16.9744 17.5107 17.1718 17.2702C17.7536 16.5612 18.0095 15.7125 18.1313 14.6816C18.25 13.6773 18.25 12.408 18.25 10.8057V10.7568V10.7079C18.25 9.10564 18.25 7.83628 18.1313 6.83206C18.0095 5.80109 17.7536 4.95239 17.1718 4.24344C16.9744 4.00293 16.7539 3.78239 16.5134 3.58501C15.8411 3.0333 15.0432 2.7747 14.0833 2.64551V1.59094C14.0833 1.17673 13.7475 0.840942 13.3333 0.840942C12.9191 0.840942 12.5833 1.17673 12.5833 1.59094V2.53227C11.8482 2.5068 11.0082 2.5068 10.0489 2.50681H10.0489L10 2.50681H7.41668V1.59094ZM12.5833 4.09175V4.03308C11.8742 4.00728 11.0294 4.00681 10 4.00681H7.41668V4.09175C7.41668 4.50596 7.08089 4.84175 6.66668 4.84175C6.25247 4.84175 5.91668 4.50596 5.91668 4.09175V4.05956C4.54258 4.30981 3.47554 5.41443 3.28118 6.8078C3.25182 7.01823 3.25001 7.28176 3.25001 7.96269V10.7568C3.25001 12.4189 3.25124 13.5996 3.35832 14.5055C3.46344 15.3948 3.66158 15.9212 3.98773 16.3186C4.12278 16.4832 4.27367 16.634 4.43823 16.7691C4.83563 17.0952 5.36198 17.2934 6.25134 17.3985C7.15725 17.5056 8.3379 17.5068 10 17.5068C11.6621 17.5068 12.8428 17.5056 13.7487 17.3985C14.638 17.2934 15.1644 17.0952 15.5618 16.7691C15.7263 16.634 15.8772 16.4832 16.0123 16.3186C16.3384 15.9212 16.5366 15.3948 16.6417 14.5055C16.7488 13.5996 16.75 12.4189 16.75 10.7568C16.75 9.0947 16.7488 7.91405 16.6417 7.00814C16.5366 6.11878 16.3384 5.59244 16.0123 5.19503C15.8772 5.03047 15.7263 4.87958 15.5618 4.74453C15.2165 4.46113 14.7738 4.27438 14.0801 4.16132C14.045 4.54292 13.7241 4.84175 13.3333 4.84175C12.9191 4.84175 12.5833 4.50596 12.5833 4.09175ZM5.83334 6.67429C5.41913 6.67429 5.08334 7.01007 5.08334 7.42429C5.08334 7.8385 5.41913 8.17429 5.83334 8.17429H14.1667C14.5809 8.17429 14.9167 7.8385 14.9167 7.42429C14.9167 7.01007 14.5809 6.67429 14.1667 6.67429H5.83334ZM7.50001 10.7576C7.50001 11.2179 7.12692 11.5909 6.66668 11.5909C6.20644 11.5909 5.83334 11.2179 5.83334 10.7576C5.83334 10.2974 6.20644 9.92428 6.66668 9.92428C7.12692 9.92428 7.50001 10.2974 7.50001 10.7576ZM6.66668 14.9243C7.12692 14.9243 7.50001 14.5512 7.50001 14.0909C7.50001 13.6307 7.12692 13.2576 6.66668 13.2576C6.20644 13.2576 5.83334 13.6307 5.83334 14.0909C5.83334 14.5512 6.20644 14.9243 6.66668 14.9243ZM10.8334 14.0909C10.8334 14.5512 10.4603 14.9243 10 14.9243C9.53978 14.9243 9.16669 14.5512 9.16669 14.0909C9.16669 13.6307 9.53978 13.2576 10 13.2576C10.4603 13.2576 10.8334 13.6307 10.8334 14.0909ZM13.3334 14.9243C13.7936 14.9243 14.1667 14.5512 14.1667 14.0909C14.1667 13.6307 13.7936 13.2576 13.3334 13.2576C12.8731 13.2576 12.5 13.6307 12.5 14.0909C12.5 14.5512 12.8731 14.9243 13.3334 14.9243ZM10.8334 10.7576C10.8334 11.2179 10.4603 11.5909 10 11.5909C9.53978 11.5909 9.16669 11.2179 9.16669 10.7576C9.16669 10.2974 9.53978 9.92428 10 9.92428C10.4603 9.92428 10.8334 10.2974 10.8334 10.7576ZM13.3334 11.5909C13.7936 11.5909 14.1667 11.2179 14.1667 10.7576C14.1667 10.2974 13.7936 9.92428 13.3334 9.92428C12.8731 9.92428 12.5 10.2974 12.5 10.7576C12.5 11.2179 12.8731 11.5909 13.3334 11.5909Z"
          fill="#000"></path>
        </svg>
       </button>
      </div>
     </div>
     <div class="mt-3 position-relative timepicker-ui w-100">
      <label class="label-top-input-special-takhasos"> ساعت مراجعه:</label>
      <input type="text"
       class="form-control w-100  h-50 timepicker-ui-input text-end font-weight-bold font-size-13"
       id="appointment-time" value="00:00" style="width: 100% !important">
     </div>
     <div class="mt-3 position-relative">
      <label class="label-top-input-special-takhasos"> توضیحات : </label>
      <textarea id="description" name="description" class="form-control h-50" id="" cols="30"
       rows="10"></textarea>
     </div>
     <div class="mt-3 position-relative mb-3 w-100">
      <button type="submit" id="submit-button"
       class="w-100 btn btn-primary h-50 border-radius-4 d-flex justify-content-center align-items-center">
       <span class="button_text">ثبت تغیرات</span>
       <div class="loader"></div>
      </button>
     </div>
    </form>
    <div class="modal fade " id="calendarModal" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered " role="document">
      <div class="modal-content border-radius-6">
       <div class="my-modal-header p-3">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
         <span aria-hidden="true">&times;</span>
        </button>
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
<div class="manual-nobat-content w-100 d-flex justify-content-center mt-3">
 <div class="manual-nobat-content-wrapper p-3">
  <div class="main-content">
   <div class="row no-gutters font-size-13 margin-bottom-10">
    <div class="user-panel-content w-100">
     <div class="row w-100">
      <div class="w-100 d-flex justify-content-center">
       <div class="table-responsive">
        <table class="table table-bordered table-striped">
         <thead>
          <tr>
           <th>ردیف</th>
           <th>نام</th>
           <th>موبایل</th>
           <th>کدملی</th>
           <th>تاریخ</th>
           <th>ساعت</th>
           <th>توضیحات</th>
           <th>عملیات</th>
          </tr>
         </thead>
         <tbody id="result_nobat">
          @foreach ($appointments as $appointment)
           <tr>
            <td>{{ $appointment->id }}</td>
            <td>{{ $appointment->user->first_name }} {{ $appointment->user->last_name }}</td>
            <td>{{ $appointment->user->mobile }}</td>
            <td>{{ $appointment->user->national_code }}</td>
            <td>{{ $appointment->appointment_date }}</td>
            <td>{{ $appointment->appointment_time }}</td>
            <td>{{ $appointment->description ?? '---' }}</td>
            <td>
             <button class="btn btn-sm btn-light edit-btn rounded-circle" data-id="{{ $appointment->id }}"><img
               src="{{ asset('dr-assets/icons/edit.svg') }}"></button>
             <button class="btn btn-sm btn-light rounded-circle delete-btn" data-id="{{ $appointment->id }}"><img
               src="{{ asset('dr-assets/icons/trash.svg') }}"></button>
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
<!-- مودال ویرایش بیمار -->
<div class="modal fade" id="editPatientModal" tabindex="-1" role="dialog" aria-labelledby="editPatientLabel"
 aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered" role="document">
  <div class="modal-content border-radius-6">
   <form id="edit-patient-form">
    @csrf
    <div class="modal-header">
     <h5 class="modal-title" id="editPatientLabel">ویرایش بیمار</h5>
     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span aria-hidden="true">&times;</span>
     </button>
    </div>
    <div class="modal-body">
     <input type="hidden" name="appointment_id" id="edit-appointment-id">
     <div class="mt-3 position-relative">
      <label class="label-top-input-special-takhasos">نام بیمار:</label>
      <input type="text" name="first_name" id="edit-first-name" class="form-control h-50"
       placeholder="نام بیمار را وارد کنید">
     </div>
     <small class="text-danger error-first_name"></small>
     <div class="mt-3 position-relative">
      <label class="label-top-input-special-takhasos">نام خانوادگی بیمار:</label>
      <input type="text" name="last_name" id="edit-last-name" class="form-control h-50"
       placeholder="نام و نام خانوادگی بیمار را وارد کنید">
     </div>
     <small class="text-danger error-last_name"></small>
     <div class="mt-3 position-relative">
      <label class="label-top-input-special-takhasos">شماره موبایل:</label>
      <input type="text" name="mobile" id="edit-mobile" class="form-control h-50"
       placeholder="شماره موبایل بیمار را وارد کنید">
     </div>
     <small class="text-danger error-mobile"></small>
     <div class="mt-3 position-relative">
      <label class="label-top-input-special-takhasos">کد ملی:</label>
      <input type="text" name="national_code" id="edit-national-code" class="form-control h-50"
       placeholder="کد ملی بیمار را وارد کنید">
     </div>
     <small class="text-danger error-national_code"></small>
     <div class="mt-3 position-relative">
      <label class="label-top-input-special-takhasos">تاریخ مراجعه:</label>
      <input type="text" name="appointment_date" id="edit-appointment-date" class="form-control h-50">
     </div>
     <small class="text-danger error-appointment_date"></small>
     <div class="mt-3 position-relative timepicker-ui w-100">
      <label class="label-top-input-special-takhasos">ساعت مراجعه:</label>
      <input type="text" name="appointment_time" id="edit-appointment-time" class="form-control w-100 h-50"
       style="width: 100% !important">
     </div>
     <small class="text-danger error-appointment_time"></small>
     <div class="mt-3 position-relative">
      <label class="label-top-input-special-takhasos">توضیحات:</label>
      <textarea name="description" id="edit-description" class="form-control h-50" rows="3"></textarea>
     </div>
     <small class="text-danger error-description"></small>
    </div>
    <div class="modal-footer">
     <button type="submit"
      class="w-100 btn btn-primary h-50 border-radius-4 d-flex justify-content-center align-items-center">
      <span class="button_text">ذخیره تغییرات</span>
      <div class="loader"></div>
     </button>
    </div>
   </form>
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
  })
 });
 var appointmentsSearchUrl = "{{ route('search.appointments') }}";
 var updateStatusAppointmentUrl =
  "{{ route('updateStatusAppointment', ':id') }}";
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
   localStorage.setItem('selectedClinic', 'ویزیت آنلاین به نوبه');
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
   var selectedText = $(this).find('.font-weight-bold.d-block.fs-15').text().trim();
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

   // ریلود صفحه با پارامتر جدید
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
    <tr class="search-result-item" data-user-id="${user.id}" data-first-name="${user.first_name}" data-last-name="${user.last_name}" data-mobile="${user.mobile}" data-national-code="${user.national_code}">
    <td>${user.first_name}</td>
    <td>${user.last_name}</td>
    <td>${user.mobile}</td>
    <td>${user.national_code}</td>
    </tr>`;
       });
      } else {
       resultsHtml = '<tr><td colspan="4" class="text-center">نتیجه‌ای یافت نشد</td></tr>';
      }
      $('#search-results-body').html(resultsHtml);
      $('#search-results-body').html(resultsHtml);
      // نمایش جدول در صورت وجود نتایج
      if (resultsHtml.trim() !== '') {
       $('#search-results').css('display', 'block'); // جدول را نمایش می‌دهد
      } else {
       $('#search-results').css('display', 'none'); // در صورت خالی بودن نتایج، جدول را مخفی می‌کند
      }
     },
     error: function() {
      toastr.error('خطا در جستجو!');
     }
    });
   } else {
    $('#search-results-body').empty(); // پاک کردن جدول
   }
  });
  // Insert selected user data into the form fields and search input
  $(document).on('click', '.search-result-item', function() {
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
   $('#search-results').css('display', 'none');
  });
  // Hide patient information section initially
  $('.my-patient-content').addClass('d-none');
 });
</script>
<script src="{{ asset('dr-assets/panel/js/calendar/custm-calendar.js') }}"></script>
<script>
 // نمونه استفاده
 function addRowToTable(data) {
  // تبدیل تاریخ میلادی به شمسی
  const jalaliDate = moment(data.appointment_date, 'YYYY-MM-DD').format('jYYYY/jMM/jDD');
  const newRow = `
    <tr>
    <td>${data.id || '---'}</td>
    <td>${data.user?.first_name || '---'} ${data.user?.last_name || '---'}</td>
    <td>${data.user?.mobile || '---'}</td>
    <td>${data.user?.national_code || '---'}</td>
    <td>${jalaliDate || '---'}</td>
    <td>${data.appointment_time || '---'}</td>
    <td>${data.description || '---'}</td>
    <td>
    <button class="btn btn-sm btn-light edit-btn rounded-circle" data-id="${data.id}"><img src="{{ asset('dr-assets/icons/edit.svg') }}"></button>
    <button class="btn btn-sm btn-light delete-btn rounded-circle" data-id="${data.id}"><img src="{{ asset('dr-assets/icons/trash.svg') }}"></button>
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
  $('#manual-appointment-form').on('submit', function(e) {
   e.preventDefault();
   const form = this;
   const submitButton = form.querySelector('button[type="submit"]');
   const loader = submitButton.querySelector('.loader');
   const buttonText = submitButton.querySelector('.button_text');
   const data = {
    user_id: $('#user-id').val(),
    doctor_id: $('#doctor-id').val(),
    appointment_date: $('#selected-date').text(),
    appointment_time: $('#appointment-time').val(),
    description: $('#description').val(),
   };
   // بررسی خالی نبودن فیلدها
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
     ...data, // گسترش شیء داده‌ها (اگر data یک شیء باشد)
     selectedClinicId: localStorage.getItem('selectedClinicId')
    },
    success: function(response) {
     toastr.success(response.message || 'نوبت با موفقیت ثبت شد!');
     form.reset();
     $('.patient-information-content').removeClass('d-flex')
     $('.patient-information-content').addClass('d-none')
     loadAppointments();
    },
    error: function(xhr) {
     const errors = xhr.responseJSON.errors || {};
     let errorMessages = Object.values(errors).map(errArray => errArray[0]).join(' - ');
     toastr.error(errorMessages || xhr.responseJSON.message);
     $('.patient-information-content').removeClass('d-flex')
     $('.patient-information-content').addClass('d-none')
    },
    complete: function() {
     buttonText.style.display = 'block';
     loader.style.display = 'none';
    },
   });
  });
 });
 $(document).ready(function() {
  // افزودن کاربر جدید و ثبت نوبت
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
        loadAppointments(); // بازخوانی لیست
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
   // درخواست AJAX برای دریافت اطلاعات نوبت
   $.ajax({
    url: "{{ route('manual-appointments.edit', ':id') }}".replace(':id', appointmentId),
    method: 'GET',
    data: {
     selectedClinicId: localStorage.getItem('selectedClinicId')

    },
    success: function(response) {
     if (response.success) {
      const appointment = response.data;
      // مقداردهی فیلدهای مودال
      $('#edit-appointment-id').val(appointment.id);
      $('#edit-first-name').val(appointment.user.first_name);
      $('#edit-last-name').val(appointment.user.last_name);
      $('#edit-mobile').val(appointment.user.mobile);
      $('#edit-national-code').val(appointment.user.national_code);
      $('#edit-appointment-date').val(moment(appointment.appointment_date, 'YYYY-MM-DD').format(
       'jYYYY/jMM/jDD'));
      $('#edit-appointment-time').val(appointment.appointment_time.substring(0, 5));
      $('#edit-description').val(appointment.description);
      // نمایش مودال
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
   const buttonText = submitButton.find('.button_text');
   // مخفی کردن متن دکمه و نمایش لودینگ
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
     ...data, // گسترش شیء داده‌ها (اگر data یک شیء باشد)
     selectedClinicId: localStorage.getItem('selectedClinicId')
    },
    success: function(response) {
     if (response.success) {
      toastr.success(response.message);
      $('#editPatientModal').modal('hide');
      loadAppointments(); // به‌روزرسانی لیست نوبت‌ها
     } else {
      toastr.error(response.message);
     }
    },
    error: function(xhr) {
     const errors = xhr.responseJSON.errors || {};
     Object.keys(errors).forEach(function(key) {
      $(`.error-${key}`).text(errors[key][0]);
     });
    },
    complete: function() {
     // بازگرداندن وضعیت دکمه به حالت اولیه
     buttonText.show();
     loader.hide();
    },
   });
  });
  // اضافه کردن ردیف به جدول
  function addRowToTable(data) {
   const jalaliDate = moment(data.appointment_date, 'YYYY-MM-DD').format('jYYYY/jMM/jDD'); // تبدیل تاریخ به شمسی
   const newRow = `
    <tr>
    <td>${data.id || '---'}</td>
    <td>${data.user?.first_name || '---'} ${data.user?.last_name || '---'}</td>
    <td>${data.user?.mobile || '---'}</td>
    <td>${data.user?.national_code || '---'}</td>
    <td>${jalaliDate || '---'}</td>
    <td>${data.appointment_time || '---'}</td>
    <td>${data.description || '---'}</td>
    <td>
    <button class="btn btn-sm btn-warning edit-btn" data-id="${data.id}">ویرایش</button>
    <button class="btn btn-sm btn-danger delete-btn" data-id="${data.id}">حذف</button>
    </td>
    </tr>`;
   $('#result_nobat').append(newRow);
  }
  $('#add-new-patient-form').on('submit', function(e) {
   e.preventDefault();
   const form = $(this);
   const submitButton = form.find('#submit-button');
   const loader = submitButton.find('.loader');
   const buttonText = submitButton.find('.button_text');
   buttonText.hide();
   loader.show();
   $.ajax({
    url: "{{ route('manual-nobat.store-with-user') }}",
    method: 'POST',
    data: form.serialize() + '&selectedClinicId=' + encodeURIComponent(localStorage.getItem('selectedClinicId')),
    success: function(response) {
     if (response.data) {
      addRowToTable(response.data); // افزودن داده به جدول
      form.trigger('reset'); // بازنشانی فرم
      $('#addNewPatientModal').modal('hide');
      $('body').removeClass('modal-open'); // حذف کلاس مربوط به باز بودن مودال
      $('.modal-backdrop').remove(); // بستن مودال
      toastr.success('بیمار با موفقیت اضافه شد!');
     } else {
      toastr.error('خطا در اضافه کردن بیمار!');
     }
    },
    error: function(xhr) {
     const errors = xhr.responseJSON.errors || {};
     Object.keys(errors).forEach(function(key) {
      form.find(`.error-${key}`).text(errors[key][0]);
     });
    },
    complete: function() {
     buttonText.show();
     loader.hide();
    }
   });
  });
  // حذف نوبت
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
        loadAppointments(); // جدول را مجدداً بارگذاری کنید.
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
  // ویرایش نوبت
  $(document).on('click', '.edit-btn', function() {
   const id = $(this).data('id');
   // منطق ویرایش را اینجا اضافه کنید
  });
 });
</script>
@endsection
