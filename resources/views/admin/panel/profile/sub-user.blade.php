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

@section('site-header', 'به نوبه | پنل دکتر')

@section('content')
@section('bread-crumb-title', ' مدیریت کاربران زیرمجموعه ')
<!-- Modal افزودن -->
<div class="modal fade" id="addSubUserModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h5 class="modal-title">افزودن کاربر</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <form id="add-subuser-form">
          @csrf
          <div class="form-group position-relative">
            <label class="label-top-input-special-takhasos">انتخاب کاربر:</label>
            <select name="user_id" id="user-select" class="form-control h-50 mb-3">
              @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }} --
                  {{ $user->national_code }}
                </option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="btn my-btn-primary w-100 h-50 d-flex justify-content-center align-items-center">
            <span class="button_text">ذخیره</span>
            <div class="loader"></div>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal ویرایش -->
<div class="modal fade" id="editSubUserModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h5 class="modal-title">ویرایش کاربر</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <form id="edit-subuser-form">
          @csrf
          <input type="hidden" name="id" id="edit-subuser-id">
          <div class="form-group position-relative">
            <label class="label-top-input-special-takhasos">انتخاب کاربر:</label>
            <select name="user_id" id="edit-user-select" class="form-control h-50 mb-3"></select>
          </div>
          <button type="submit" class="btn my-btn-primary w-100 h-50 d-flex justify-content-center align-items-center">
            <span class="button_text">ذخیره</span>
            <div class="loader"></div>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="subuser-content w-100 d-flex justify-content-center mt-4">
  <div class="subuser-content-wrapper p-3">
    <div class="w-100 mt-3 d-flex justify-content-end">
      <button class="btn my-btn-primary h-50 add-subuser-btn">افزودن کاربر جدید</button>
    </div>

    <div class="p-3">
      <h4 class="text-dark fw-bold">لیست کاربران زیرمجموعه</h4>
    </div>

    <div class="subuser-cards mt-4">
      @foreach ($subUsers as $subUser)
        <div class="subuser-card p-3 w-100 d-flex justify-content-between align-items-end"
          data-id="{{ $subUser->id }}">
          <div>
            <span class="d-block fw-bold text-dark">
              {{ $subUser->user->first_name }} {{ $subUser->user->last_name . '--' . $subUser->user->national_code }}
            </span>
            <span class="font-size-13 fw-bold">
              شماره موبایل: <span>{{ $subUser->user->mobile }}</span>
              کد ملی: <span>{{ $subUser->user->national_code }}</span>
            </span>
          </div>
          <div>
            <div class="d-flex gap-4">
              <button class="btn btn-light btn-sm rounded-circle edit-btn" data-id="{{ $subUser->id }}"><img
                  src="{{ asset('dr-assets/icons/edit.svg') }}" alt="" srcset=""></button>
              <button class="btn btn-light btn-sm rounded-circle delete-btn" data-id="{{ $subUser->id }}"><img
                  src="{{ asset('dr-assets/icons/trash.svg') }}" alt="" srcset=""></button>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>



@endsection

@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl =
    "{{ route('updateStatusAppointment', ':id') }}";
</script>
<script>
  $(document).on('click', '.add-subuser-btn', function() {
    $('#addSubUserModal').modal('show');
  });
  $(document).ready(function() {
    new TomSelect("#user-select", {
      create: false,
      plugins: ['clear_button']
    });
  })

  function updateTomSelectList() {
    // اگر قبلاً TomSelect مقداردهی شده، ابتدا آن را حذف کنیم
    if ($("#user-select").data("tomselect")) {
      $("#user-select")[0].tomselect.destroy();
    }

    if ($("#edit-user-select").data("tomselect")) {
      $("#edit-user-select")[0].tomselect.destroy();
    }

    // مقداردهی مجدد بدون حذف مقادیر موجود
    new TomSelect("#user-select", {
      create: false,
      plugins: ['clear_button']
    });

    new TomSelect("#edit-user-select", {
      create: false
    });
  }






  function updateSubUserList(subUsers) {
    if (!subUsers || !Array.isArray(subUsers)) {
      return;
    }

    $('.sub-user-cards').empty();
    subUsers.forEach(subUser => {
      $('.sub-user-cards').append(`
            <div class="subuser-card p-3 w-100 d-flex justify-content-between align-items-end" data-id="${subUser.id}">
                <div>
                    <span class="d-block fw-bold text-dark">${subUser.user.first_name} ${subUser.user.last_name}--${subUser.user.national_code}</span>
                    <span class="font-size-13 fw-bold">شماره موبایل: <span>${subUser.user.mobile}</span></span>
                    <span class="font-size-13 fw-bold"> کد ملی: <span>${subUser.user.national_code}</span></span>
                </div>
                <div>
                    <div class="d-flex gap-4">
                        <button class="btn btn-light btn-sm rounded-circle edit-btn" data-id="${subUser.id}"><img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="" srcset=""></button>
                        <button class="btn btn-light btn-sm rounded-circle delete-btn" data-id="${subUser.id}"><img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="" srcset=""></button>
                    </div>
                </div>
            </div>
        `);
    });
  }





  $('#add-subuser-form').on('submit', function(e) {
    e.preventDefault();
    let form = $(this);
    let button = form.find('button');
    let loader = button.find('.loader');
    let buttonText = button.find('.button_text');

    buttonText.hide();
    loader.show();

    $.ajax({
      url: "{{ route('dr-sub-users-store') }}",
      method: "POST",
      data: form.serialize(),
      success: function(response) {
        if (response.message) {
          toastr.success(response.message);

        }
        $('#addSubUserModal').modal('hide');
        updateSubUserList(response.subUsers);
      },
      error: function(xhr) {
        if (xhr.responseJSON && xhr.responseJSON.error) {
          toastr.error(xhr.responseJSON.error);

        } else {
          toastr.error('خطایی رخ داده است!');

        }
      },
      complete: function() {
        buttonText.show();
        loader.hide();
      }
    });
  });



  $('#edit-subuser-form').on('submit', function(e) {
    e.preventDefault();
    let id = $('#edit-subuser-id').val();
    let form = $(this);
    let button = form.find('button');
    let loader = button.find('.loader');
    let buttonText = button.find('.button_text');

    buttonText.hide();
    loader.show();

    $.ajax({
      url: "{{ route('dr-sub-users-update', ':id') }}".replace(':id', id),
      method: "POST",
      data: form.serialize(),
      success: function(response) {
        if (response.message) {
          toastr.success(response.message);

        }
        $('#editSubUserModal').modal('hide');
        updateSubUserList(response.subUsers);
      },
      error: function(xhr) {
        if (xhr.responseJSON && xhr.responseJSON.error) {
          toastr.error(xhr.responseJSON.error);

        } else {
          toastr.error('خطایی در بروزرسانی رخ داد!');

        }
      },
      complete: function() {
        buttonText.show();
        loader.hide();
      }
    });
  });




  $(document).on('click', '.delete-btn', function() {
    let id = $(this).data('id');
    Swal.fire({
      title: 'آیا مطمئن هستید؟',
      text: 'این عملیات قابل بازگشت نیست!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'بله',
      cancelButtonText: 'لغو',
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "{{ route('dr-sub-users-delete', ':id') }}".replace(':id', id),
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function(response) {
            toastr.success('کاربر حذف شد!')
            updateSubUserList(response.subUsers);
          }
        });
      }
    });
  });

  $(document).on('click', '.edit-btn', function() {
    let id = $(this).data('id');

    // دریافت اطلاعات کاربر از سرور
    $.get("{{ route('dr-sub-users-edit', ':id') }}".replace(':id', id), function(response) {
      $('#edit-subuser-id').val(response.id);

      // از بین بردن مقدار قبلی Tom Select
      if (window.editUserSelect) {
        window.editUserSelect.destroy();
      }

      // مقداردهی مجدد Tom Select
      $('#edit-user-select').html('');
      response.users.forEach(user => {
        let selected = user.id === response.user_id ? 'selected' : '';
        $('#edit-user-select').append(
          `<option value="${user.id}" ${selected}>${user.first_name} ${user.last_name} -- ${user.national_code}</option>`
        );
      });

      window.editUserSelect = new TomSelect("#edit-user-select", {
        create: false
      });

      // باز کردن مودال
      $('#editSubUserModal').modal('show');
    });
  });


  // حذف کلاس‌های اضافی `modal-backdrop` هنگام بستن مودال
  $('.modal').on('hidden.bs.modal', function() {
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
  });
</script>
@endsection
