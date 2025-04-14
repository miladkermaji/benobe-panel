@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <style>
    .field-wrapper {
      position: relative;
    }

    .field-wrapper.has-error .label-top-input-special-takhasos {
      position: absolute;
      bottom: 56px;
      /* موقعیت پیش‌فرض برای لیبل‌ها در صورت وجود خطا */
    }

    .label-top-input-special-takhasos {
      min-height: 10px;
      min-width: 20px;
      background-color: #fff;
      box-shadow: rgba(27, 31, 35, 0.04) 0px 1px 0px,
        rgba(255, 255, 255, 0.25) 0px 1px 0px inset;
      border-radius: 4px;
      position: absolute;
      z-index: 2;
      padding: 2px;
      font-weight: bold;
      right: inherit;
      font-size: 12px;
      bottom: 58px;
    }

    .field-wrapper.has-error .error-message {
      display: block;
    }

    /* اگر ردیف اول خطا داشته باشد، لیبل‌های هر دو فیلد (نام و نام خانوادگی) جابه‌جا شوند */
    .upper-row.upper-row-error .field-first_name .label-top-input-special-takhasos,
    .upper-row.upper-row-error .field-last_name .label-top-input-special-takhasos {
      position: absolute;
      bottom: 56px !important;
    }

    /* تنظیم موقعیت لیبل‌های پایینی فقط وقتی هر دو فیلد بالایی (نام و نام خانوادگی) خطا داشته باشند */
    .both-upper-fields-error .field-row .field-national_code .label-top-input-special-takhasos {
      position: absolute;
      bottom: 58px !important;
    }

    .both-upper-fields-error .field-row .field-gender .sex-label {
      position: absolute;
      bottom: 58px !important;
    }

    .both-upper-fields-error .field-mobile .mobile-label {
      position: absolute;
      bottom: 56px !important;
    }

    .both-upper-fields-error .field-password .password-label {
      position: absolute;
      bottom: 32px !important;
    }

    /* تنظیم موقعیت لیبل‌های پایینی بر اساس خطاهای دیگر */
    .field-national_code.has-error~.field-gender .sex-label {
      position: absolute;
      bottom: 58px !important;
    }

    .field-national_code.has-error~.field-mobile .mobile-label,
    .field-gender.has-error~.field-mobile .mobile-label {
      position: absolute;
      bottom: 56px !important;
    }

    .field-national_code.has-error~.field-password .password-label,
    .field-gender.has-error~.field-password .password-label,
    .field-mobile.has-error~.field-password .password-label {
      position: absolute;
      bottom: 32px !important;
    }

    /* تنظیم موقعیت لیبل کلمه عبور در صورت وجود خطا در خودش */
    .field-password.has-error .password-label {
      position: absolute;
      bottom: 32px !important;
    }

    /* استایل‌های جدول */
    .table-modern th,
    .table-modern td {
      vertical-align: middle;
      text-align: center;
    }

    .table-modern .btn-sm {
      padding: 5px;
      margin: 0 2px;
    }

    .table-modern {
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .table-modern thead {
      background-color: #007bff;
      color: white;
    }
  </style>
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت منشی')

<!-- مودال افزودن منشی (دست‌نخورده) -->
<div class="modal fade" id="addSecretaryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">افزودن منشی</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div>
          <form id="add-secretary-form" method="post">
            @csrf
            <div class="d-flex flex-column mt-2">
              <div class="position-relative d-flex gap-4 w-100 upper-row">
                <div class="position-relative w-100 field-wrapper field-first_name">
                  <label class="label-top-input-special-takhasos">نام:</label>
                  <input type="text" name="first_name" class="form-control h-50 w-100 position-relative">
                  <small class="text-danger error-first_name mt-1"></small>
                </div>
                <div class="position-relative w-100 field-wrapper field-last_name">
                  <label class="label-top-input-special-takhasos">نام خانوادگی:</label>
                  <input type="text" name="last_name" class="form-control h-50 w-100">
                  <small class="text-danger error-last_name mt-1"></small>
                </div>
              </div>
              <div class="d-flex justify-content-between gap-4 mt-4 field-row">
                <div class="form-group position-relative w-100 hide-show-input-edit field-wrapper field-national_code">
                  <label class="label-top-input-special-takhasos">کدملی:</label>
                  <input type="text" name="national_code" class="form-control h-50 w-100">
                  <small class="text-danger error-national_code mt-1"></small>
                </div>
                <div class="form-group position-relative w-100 hide-show-input-edit field-wrapper field-gender">
                  <label class="label-top-input-special-takhasos sex-label">جنسیت:</label>
                  <select name="gender" class="form-control h-50 w-100">
                    <option value="male">مرد</option>
                    <option value="female">زن</option>
                  </select>
                  <small class="text-danger error-gender mt-1"></small>
                </div>
              </div>
            </div>
            <div class="w-100 position-relative mt-4 field-wrapper field-mobile">
              <label class="label-top-input-special-takhasos mobile-label">شماره موبایل:</label>
              <input name="mobile" type="text" class="form-control h-50 w-100">
              <small class="text-danger error-mobile mt-1"></small>
            </div>
            <div class="w-100 position-relative mt-4 field-wrapper field-password">
              <label class="label-top-input-special-takhasos password-label">کلمه عبور(اختیاری):</label>
              <input type="password" name="password" class="form-control h-50 w-100">
              <small class="text-danger error-password mt-1"></small>
            </div>
            <div class="w-100 mt-2">
              <button type="submit"
                class="w-100 btn my-btn-primary h-50 border-radius-4 d-flex justify-content-center align-items-center">
                <span class="button_text">ذخیره تغییرات</span>
                <div class="loader"></div>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- مودال ویرایش منشی (دست‌نخورده) -->
<div class="modal fade" id="editSecretaryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">ویرایش منشی</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div>
          <form id="edit-secretary-form" method="post">
            @csrf
            <input type="hidden" name="id" id="edit-secretary-id">
            <div class="d-flex flex-column mt-2">
              <div class="position-relative d-flex gap-4 w-100 upper-row">
                <div class="position-relative w-100 field-wrapper field-first_name">
                  <label class="label-top-input-special-takhasos">نام:</label>
                  <input type="text" name="first_name" id="edit-first-name" class="form-control h-50 w-100">
                  <small class="text-danger error-first_name mt-1"></small>
                </div>
                <div class="position-relative w-100 field-wrapper field-last_name">
                  <label class="label-top-input-special-takhasos">نام خانوادگی:</label>
                  <input type="text" name="last_name" id="edit-last-name" class="form-control h-50 w-100">
                  <small class="text-danger error-last_name mt-1"></small>
                </div>
              </div>
              <div class="d-flex justify-content-between gap-4 mt-4 field-row">
                <div class="form-group position-relative w-100 hide-show-input-edit field-wrapper field-national_code">
                  <label class="label-top-input-special-takhasos">کدملی:</label>
                  <input type="text" name="national_code" id="edit-national-code"
                    class="form-control h-50 w-100">
                  <small class="text-danger error-national_code mt-1"></small>
                </div>
                <div class="form-group position-relative w-100 hide-show-input-edit field-wrapper field-gender">
                  <label class="label-top-input-special-takhasos sex-label">جنسیت:</label>
                  <select name="gender" id="edit-gender" class="form-control h-50 w-100">
                    <option value="male">مرد</option>
                    <option value="female">زن</option>
                  </select>
                  <small class="text-danger error-gender mt-1"></small>
                </div>
              </div>
            </div>
            <div class="w-100 position-relative mt-4 field-wrapper field-mobile">
              <label class="label-top-input-special-takhasos mobile-label">شماره موبایل:</label>
              <input type="text" name="mobile" id="edit-mobile" class="form-control h-50 w-100">
              <small class="text-danger error-mobile mt-1"></small>
            </div>
            <div class="w-100 position-relative mt-4 field-wrapper field-password">
              <label class="label-top-input-special-takhasos password-label">کلمه عبور(اختیاری):</label>
              <input type="password" name="password" id="edit-password" class="form-control h-50 w-100">
              <small class="text-danger error-password mt-1"></small>
            </div>
            <div class="w-100 mt-2">
              <button type="submit"
                class="w-100 btn my-btn-primary h-50 border-radius-4 d-flex justify-content-center align-items-center">
                <span class="button_text">ذخیره تغییرات</span>
                <div class="loader"></div>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- بخش محتوا با جدول بوت‌استرپ -->
<div class="subuser-content w-100 d-flex justify-content-center mt-4">
  <div class="subuser-content-wrapper p-3 w-100">
    <div class="w-100 d-flex justify-content-end">
      <button class="btn my-btn-primary h-50 add-secretary-btn" id="add-secretary-btn">افزودن منشی جدید</button>
    </div>
    <div class="p-3">
      <h4 class="text-dark font-weight-bold">لیست منشی‌ها</h4>
    </div>
    <div class="mt-2">
      <table class="table table-modern table-striped table-bordered table-hover" id="secretary-list">
        <thead>
          <tr>
            <th>ردیف</th>
            <th>نام و نام خانوادگی</th>
            <th>شماره موبایل</th>
            <th>کدملی</th>
            <th>جنسیت</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($secretaries as $index => $secretary)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $secretary->first_name }} {{ $secretary->last_name }}</td>
              <td>{{ $secretary->mobile }}</td>
              <td>{{ $secretary->national_code }}</td>
              <td>{{ $secretary->gender === 'male' ? 'مرد' : 'زن' }}</td>
              <td>
                <button class="btn btn-light btn-sm rounded-circle edit-btn" data-id="{{ $secretary->id }}"
                  title="ویرایش">
                  <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
                </button>
                <button class="btn btn-light btn-sm rounded-circle delete-btn" data-id="{{ $secretary->id }}"
                  title="حذف">
                  <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center">شما منشی فعالی ندارید</td>
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

    $('.my-dropdown-menu').on('click', function(event) {
      event.stopPropagation();
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

      window.location.href = window.location.pathname + "?selectedClinicId=" + selectedId;
    });
  });
  $('#add-secretary-btn').on('click', function() {
    $('#addSecretaryModal').modal('show');

  });

  function updateSecretaryList(secretaries) {
    const container = $('#secretary-list tbody');
    container.empty(); // پاک کردن لیست قبلی

    if (secretaries.length === 0) {
      container.append(
        `<tr><td colspan="6" class="text-center">شما منشی فعالی ندارید</td></tr>`
      );
    } else {
      secretaries.forEach((secretary, index) => {
        const row = `
          <tr>
            <td>${index + 1}</td>
            <td>${secretary.first_name} ${secretary.last_name}</td>
            <td>${secretary.mobile}</td>
            <td>${secretary.national_code}</td>
            <td>${secretary.gender === 'male' ? 'مرد' : 'زن'}</td>
            <td>
              <button class="btn btn-light btn-sm rounded-circle edit-btn" data-id="${secretary.id}" title="ویرایش">
           <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
              </button>
              <button class="btn btn-light btn-sm rounded-circle delete-btn" data-id="${secretary.id}" title="حذف">
              <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
              </button>
            </td>
          </tr>`;
        container.append(row);
      });
    }
  }

  $(document).ready(function() {
    $('#add-secretary-form').on('submit', function(e) {
      e.preventDefault();

      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.button_text');

      buttonText.hide();
      loader.show();

      form.find('.text-danger').text('');
      form.find('.field-wrapper').removeClass('has-error');
      form.removeClass('both-upper-fields-error');
      form.find('.upper-row').removeClass('upper-row-error');

      $.ajax({
        url: "{{ route('dr-secretary-store') }}",
        method: 'POST',
        data: form.serialize() + '&selectedClinicId=' + localStorage.getItem('selectedClinicId'),
        success: function(response) {
          toastr.success('منشی با موفقیت اضافه شد!');
          $('#addSecretaryModal').modal('hide');
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
          updateSecretaryList(response.secretaries);
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;

            Object.keys(errors).forEach(function(key) {
              form.find(`.error-${key}`).text(errors[key][0]);
              form.find(`.field-${key}`).addClass('has-error');
            });

            const hasFirstNameError = form.find('.field-first_name').hasClass('has-error');
            const hasLastNameError = form.find('.field-last_name').hasClass('has-error');
            if (hasFirstNameError || hasLastNameError) {
              form.find('.upper-row').addClass('upper-row-error');
            }

            if (hasFirstNameError && hasLastNameError) {
              form.addClass('both-upper-fields-error');
            }
          } else {
            toastr.error('خطا در ذخیره اطلاعات!');
          }
        },
        complete: function() {
          buttonText.show();
          loader.hide();
        },
      });
    });

    $(document).on('click', '.edit-btn', function() {
      const id = $(this).data('id');
      const selectedClinicId = localStorage.getItem('selectedClinicId') ?? 'default';

      $.get(
        "{{ route('dr-secretary-edit', ':id') }}".replace(':id', id) +
        '?selectedClinicId=' + selectedClinicId,
        function(response) {
          $('#edit-secretary-id').val(response.id);
          $('#edit-first-name').val(response.first_name);
          $('#edit-last-name').val(response.last_name);
          $('#edit-mobile').val(response.mobile);
          $('#edit-national-code').val(response.national_code);
          $('#edit-gender').val(response.gender);
          $('#editSecretaryModal').modal('show');
        }
      ).fail(function() {
        toastr.error('خطا در دریافت اطلاعات منشی!');
      });
    });

    $('#edit-secretary-form').on('submit', function(e) {
      e.preventDefault();
      const id = $('#edit-secretary-id').val();
      const selectedClinicId = localStorage.getItem('selectedClinicId') ?? 'default';
      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.button_text');

      buttonText.hide();
      loader.show();

      form.find('.text-danger').text('');
      form.find('.field-wrapper').removeClass('has-error');
      form.removeClass('both-upper-fields-error');
      form.find('.upper-row').removeClass('upper-row-error');

      const formData = form.serialize() + '&selectedClinicId=' + selectedClinicId;

      $.ajax({
        url: "{{ route('dr-secretary-update', ':id') }}".replace(':id', id),
        method: 'POST',
        data: formData,
        success: function(response) {
          toastr.success('منشی با موفقیت ویرایش شد!');
          buttonText.show();
          loader.hide();
          $('#editSecretaryModal').modal('hide');
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
          updateSecretaryList(response.secretaries);
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;

            Object.keys(errors).forEach(function(key) {
              form.find(`.error-${key}`).text(errors[key][0]);
              form.find(`.field-${key}`).addClass('has-error');
            });

            const hasFirstNameError = form.find('.field-first_name').hasClass('has-error');
            const hasLastNameError = form.find('.field-last_name').hasClass('has-error');
            if (hasFirstNameError || hasLastNameError) {
              form.find('.upper-row').addClass('upper-row-error');
            }

            if (hasFirstNameError && hasLastNameError) {
              form.addClass('both-upper-fields-error');
            }
          } else {
            toastr.error('خطا در ویرایش منشی!');
          }
        },
        complete: function() {
          buttonText.show();
          loader.hide();
        },
      });
    });

    $(document).on('click', '.delete-btn', function() {
      const id = $(this).data('id');
      const selectedClinicId = localStorage.getItem('selectedClinicId') ?? 'default';

      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'این عمل قابل بازگشت نیست!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'لغو',
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('dr-secretary-delete', ':id') }}".replace(':id', id) +
              '?selectedClinicId=' +
              selectedClinicId,
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              toastr.success('منشی با موفقیت حذف شد!');
              updateSecretaryList(response.secretaries);
            },
            error: function() {
              toastr.error('خطا در حذف منشی!');
            },
          });
        }
      });
    });
  });
</script>
@endsection
