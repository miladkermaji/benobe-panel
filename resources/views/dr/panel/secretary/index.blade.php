@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/secretary/secretaries.css') }}" rel="stylesheet" />

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
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
      <h4 class="text-dark fw-bold">لیست منشی‌ها</h4>
    </div>
    <div class="mt-2 table-responsive">
      <table class="table table-modern table-hover" id="secretary-list">
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
