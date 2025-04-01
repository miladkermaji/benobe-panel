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
        <input type="text" id="search-input" class="my-form-control"
          placeholder="نام بیمار، شماره موبایل یا کد ملی ...">
        <div id="search-results" class="table-responsive border mb-0">
          <table class="table table-light table-hover table-striped table-bordered">
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
      <button class="btn btn-primary h-50 fs-13" data-toggle="modal" data-target="#addNewPatientModal"
        data-toggle="modal" data-target="#addNewPatientModal">افزودن
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
                  <input type="text" name="first_name" class="form-control h-50"
                    placeholder="نام بیمار را وارد کنید">
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
                  <input type="text" name="mobile" class="form-control h-50"
                    placeholder="شماره موبایل بیمار را وارد کنید">
                </div>
                <small class="text-danger error-mobile"></small>
                <div class="mt-3 position-relative">
                  <label class="label-top-input-special-takhasos">کد ملی:</label>
                  <input type="text" name="national_code" class="form-control h-50"
                    placeholder="کد ملی بیمار را وارد کنید">
                </div>
                <small class="text-danger error-national_code"></small>
                <div class="mt-3 position-relative">
                  <label class="label-top-input-special-takhasos">تاریخ مراجعه:</label>
                  <input type="text" placeholder="1403/05/02" name="appointment_date"
                    class="form-control w-100 h-50 position-relative text-start" data-jdp>
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
            <input type="text" name="mobile" class="form-control h-50"
              placeholder="شماره موبایل بیمار را وارد کنید" required="">
          </div>
          <div class="mt-3 position-relative">
            <label class="label-top-input-special-takhasos"> کد ملی بیمار:</label>
            <input type="text" name="codemeli" class="form-control h-50" placeholder="کدملی بیمار را وارد کنید">
          </div>
          <div class="mt-3 position-relative">
            <label class="label-top-input-special-takhasos"> تاریخ مراجعه: </label>
            <input type="text" placeholder="1403/05/02" class="form-control h-50" id="selected-date" data-jdp>
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
                          <button class="btn btn-sm btn-light edit-btn rounded-circle"
                            data-id="{{ $appointment->id }}"><img
                              src="{{ asset('dr-assets/icons/edit.svg') }}"></button>
                          <button class="btn btn-sm btn-light rounded-circle delete-btn"
                            data-id="{{ $appointment->id }}"><img
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
            <input type="text" name="appointment_date" placeholder="1403/05/02" id="edit-appointment-date"
              data-jdp="" class="form-control h-50">
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
    $("#search-results").addClass('d-none')
  });
  $(document).on('hide.bs.modal', '.modal', function() {
    $("#search-results").removeClass('d-none')
  });
</script>
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
        appointment_date: $('#selected-date').val(), // اگر باید text باشه، اصلاح کنید
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
          ...data,
          selectedClinicId: localStorage.getItem('selectedClinicId')
        },
        success: function(response) {
          toastr.success(response.message || 'نوبت با موفقیت ثبت شد!');
          form.reset(); // ریست کردن فرم
          $('.patient-information-content').removeClass('d-flex'); // حذف کلاس d-flex
          $('.patient-information-content').addClass('d-none'); // اضافه کردن d-none برای پنهان کردن
          loadAppointments(); // به‌روزرسانی لیست
        },
        error: function(xhr) {
          const errors = xhr.responseJSON.errors || {};
          let errorMessages = Object.values(errors).map(errArray => errArray[0]).join(' - ');
          toastr.error(errorMessages || xhr.responseJSON.message);
          $('.patient-information-content').removeClass('d-flex'); // حذف کلاس d-flex
          $('.patient-information-content').addClass('d-none'); // پنهان کردن در صورت خطا
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

      // پر کردن فیلدهای فرم
      $('#user-id').val(userId);
      $('input[name="fristname"]').val(firstName); // اگر باید firstname باشه، اصلاح کنید
      $('input[name="lastname"]').val(lastName);
      $('input[name="mobile"]').val(mobile);
      $('input[name="codemeli"]').val(nationalCode);

      // نمایش فرم با حذف d-none و اضافه کردن کلاس‌های لازم
      $('.patient-information-content').removeClass('d-none'); // حذف d-none برای نمایش
      $('.patient-information-content').addClass(
        'd-flex justify-content-center'); // اضافه کردن کلاس‌های مورد نیاز

      // پاک کردن نتایج جستجو
      $('#search-results-body').empty();
      $('#search-input').val('');
      $('#search-results').css('display', 'none');
    });
  });;
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

      // پاک کردن خطاهای قبلی
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
            loadAppointments(); // به‌روزرسانی لیست نوبت‌ها
          } else {
            toastr.error(response.message);
          }
        },
        error: function(xhr) {
          const errors = xhr.responseJSON?.errors || [];
          // نگاشت خطاها به فیلدها بر اساس پیام‌ها
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

      // پاک کردن خطاهای قبلی
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
          // نگاشت خطاها به فیلدها بر اساس پیام‌ها
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
            toastr.error(xhr.responseJSON?.message || 'خطا در ثبت اطلاعات!');
          }
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
