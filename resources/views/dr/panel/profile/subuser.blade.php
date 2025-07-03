@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/profile/subuser.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت کاربران زیرمجموعه')

<!-- مودال افزودن کاربر زیرمجموعه -->
<div class="modal fade" id="addSubUserModal" tabindex="-1" role="dialog" aria-labelledby="addSubUserModalTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h5 class="modal-title" id="addSubUserModalTitle">افزودن کاربر زیرمجموعه</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="add-subuser-form" method="post">
          @csrf
          <div class="w-100 position-relative mt-4 field-wrapper field-user_id">
            <label class="label-top-input-special-takhasos">انتخاب کاربر:</label>
            <select name="user_id" id="user-select" class="form-control h-50 w-100">
              @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }} --
                  {{ $user->national_code }}</option>
              @endforeach
            </select>
            <small class="text-danger error-user_id mt-1"></small>
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

<!-- مودال ویرایش کاربر زیرمجموعه -->
<div class="modal fade" id="editSubUserModal" tabindex="-1" role="dialog" aria-labelledby="editSubUserModalTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h5 class="modal-title" id="editSubUserModalTitle">ویرایش کاربر زیرمجموعه</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="edit-subuser-form" method="post">
          @csrf
          <input type="hidden" name="id" id="edit-subuser-id">
          <div class="w-100 position-relative mt-4 field-wrapper field-user_id">
            <label class="label-top-input-special-takhasos">انتخاب کاربر:</label>
            <select name="user_id" id="edit-user-select" class="form-control h-50 w-100"></select>
            <small class="text-danger error-user_id mt-1"></small>
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

<!-- بخش محتوا با جدول بوت‌استرپ -->
<div class="container subuser-content w-100 d-flex justify-content-center mt-4">
  <div class="subuser-content-wrapper  w-100">
    <div class="w-100 d-flex justify-content-end">
      <button class="btn my-btn-primary h-50 add-subuser-btn" id="add-subuser-btn">افزودن کاربر جدید</button>
    </div>
    <div class="p-3">
      <h4 class="text-dark fw-bold">لیست کاربران زیرمجموعه</h4>
    </div>
    <div class="mt-2 table-responsive d-none d-md-block">
      <table class="table table-modern table-hover" id="subuser-list">
        <thead>
          <tr>
            <th>ردیف</th>
            <th>نام و نام خانوادگی</th>
            <th>شماره موبایل</th>
            <th>کدملی</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($subUsers as $index => $subUser)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $subUser->subuserable->first_name ?? '' }} {{ $subUser->subuserable->last_name ?? '' }}</td>
              <td>{{ $subUser->subuserable->mobile ?? '' }}</td>
              <td>{{ $subUser->subuserable->national_code ?? '' }}</td>
              <td>
                <button class="btn btn-light btn-sm rounded-circle edit-btn" data-id="{{ $subUser->id }}"
                  title="ویرایش">
                  <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
                </button>
                <button class="btn btn-light btn-sm rounded-circle delete-btn" data-id="{{ $subUser->id }}"
                  title="حذف">
                  <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center">شما کاربر زیرمجموعه‌ای ندارید</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <!-- کارت‌های کاربران زیرمجموعه برای موبایل/تبلت -->
    <div class="notes-cards d-md-none">
      @forelse ($subUsers as $index => $subUser)
        <div class="note-card mb-3" data-id="{{ $subUser->id }}">
          <div class="note-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
              <span
                class="badge bg-primary-subtle text-primary">{{ $subUser->subuserable->national_code ?? '' }}</span>
            </div>
            <div class="d-flex gap-1">
              <button class="btn btn-sm btn-gradient-success px-2 py-1 edit-btn" data-id="{{ $subUser->id }}"
                title="ویرایش">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path
                    d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
              </button>
              <button class="btn btn-sm btn-gradient-danger px-2 py-1 delete-btn" data-id="{{ $subUser->id }}"
                title="حذف">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                </svg>
              </button>
            </div>
          </div>
          <div class="note-card-body">
            <div class="note-card-item">
              <span class="note-card-label">نام و نام خانوادگی:</span>
              <span class="note-card-value">{{ $subUser->subuserable->first_name ?? '' }}
                {{ $subUser->subuserable->last_name ?? '' }}</span>
            </div>
            <div class="note-card-item">
              <span class="note-card-label">شماره موبایل:</span>
              <span class="note-card-value">{{ $subUser->subuserable->mobile ?? '' }}</span>
            </div>
            <div class="note-card-item">
              <span class="note-card-label">کدملی:</span>
              <span class="note-card-value">{{ $subUser->subuserable->national_code ?? '' }}</span>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center py-4">
          <div class="d-flex justify-content-center align-items-center flex-column">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" class="text-muted mb-2">
              <path d="M5 12h14M12 5l7 7-7 7" />
            </svg>
            <p class="text-muted fw-medium">شما کاربر زیرمجموعه‌ای ندارید</p>
          </div>
        </div>
      @endforelse
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="{{ asset('dr-assets/js/select2/select2.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
</script>
<script>
  $(document).ready(function() {
    // Initialize TomSelect once on page load
    const addUserSelect = new TomSelect("#user-select", {
      create: false,
      plugins: ['clear_button'],
      maxOptions: 50,
      render: {
        option: function(data, escape) {
          return `<div>${escape(data.text)}</div>`;
        }
      }
    });

    let editUserSelect;

    $('#add-subuser-btn').on('click', function() {
      $('#addSubUserModal').modal('show');
    });

    function updateSubUserList(subUsers) {
      const container = $('#subuser-list tbody');
      container.empty();

      // آپدیت جدول دسکتاپ
      if (subUsers.length === 0) {
        container.append(
          `<tr><td colspan="5" class="text-center">شما کاربر زیرمجموعه‌ای ندارید</td></tr>`
        );
      } else {
        subUsers.forEach((subUser, index) => {
          const row = `
            <tr>
              <td>${index + 1}</td>
              <td>${subUser.subuserable.first_name} ${subUser.subuserable.last_name}</td>
              <td>${subUser.subuserable.mobile}</td>
              <td>${subUser.subuserable.national_code}</td>
              <td>
                <button class="btn btn-light btn-sm rounded-circle edit-btn" data-id="${subUser.id}" title="ویرایش">
                  <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
                </button>
                <button class="btn btn-light btn-sm rounded-circle delete-btn" data-id="${subUser.id}" title="حذف">
                  <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                </button>
              </td>
            </tr>`;
          container.append(row);
        });
      }

      // آپدیت کارت‌های موبایل
      const mobileContainer = $('.notes-cards');
      mobileContainer.empty();
      if (subUsers.length === 0) {
        mobileContainer.append(`
          <div class="text-center py-4">
            <div class="d-flex justify-content-center align-items-center flex-column">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted mb-2">
                <path d="M5 12h14M12 5l7 7-7 7" />
              </svg>
              <p class="text-muted fw-medium">شما کاربر زیرمجموعه‌ای ندارید</p>
            </div>
          </div>
        `);
      } else {
        subUsers.forEach((subUser, index) => {
          const card = `
            <div class="note-card mb-3" data-id="${subUser.id}">
              <div class="note-card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-primary-subtle text-primary">${subUser.subuserable.national_code ?? ''}</span>
                </div>
                <div class="d-flex gap-1">
                  <button class="btn btn-sm btn-gradient-success px-2 py-1 edit-btn" data-id="${subUser.id}" title="ویرایش">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                  </button>
                  <button class="btn btn-sm btn-gradient-danger px-2 py-1 delete-btn" data-id="${subUser.id}" title="حذف">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                    </svg>
                  </button>
                </div>
              </div>
              <div class="note-card-body">
                <div class="note-card-item">
                  <span class="note-card-label">نام و نام خانوادگی:</span>
                  <span class="note-card-value">${subUser.subuserable.first_name ?? ''} ${subUser.subuserable.last_name ?? ''}</span>
                </div>
                <div class="note-card-item">
                  <span class="note-card-label">شماره موبایل:</span>
                  <span class="note-card-value">${subUser.subuserable.mobile ?? ''}</span>
                </div>
                <div class="note-card-item">
                  <span class="note-card-label">کدملی:</span>
                  <span class="note-card-value">${subUser.subuserable.national_code ?? ''}</span>
                </div>
              </div>
            </div>
          `;
          mobileContainer.append(card);
        });
      }
    }

    $('#add-subuser-form').on('submit', function(e) {
      e.preventDefault();
      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.button_text');

      buttonText.hide();
      loader.show();

      form.find('.text-danger').text('');
      form.find('.field-wrapper').removeClass('has-error');

      $.ajax({
        url: "{{ route('dr-sub-users-store') }}",
        method: 'POST',
        data: form.serialize(),
        success: function(response) {
          toastr.success('کاربر زیرمجموعه با موفقیت اضافه شد!');
          $('#addSubUserModal').modal('hide');
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
          updateSubUserList(response.subUsers);
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;
            Object.keys(errors).forEach(function(key) {
              form.find(`.error-${key}`).text(errors[key][0]);
              form.find(`.field-${key}`).addClass('has-error');
            });
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
      const $btn = $(this);
      $btn.prop('disabled', true);

      // Show modal immediately with loading state
      $('#editSubUserModal').modal('show');
      $('#edit-user-select').html('<option>در حال بارگذاری...</option>');

      $.ajax({
        url: "{{ route('dr-sub-users-edit', ':id') }}".replace(':id', id),
        method: 'GET',
        cache: true,
        success: function(response) {
          $('#edit-subuser-id').val(response.id);

          // Destroy existing TomSelect instance if exists
          if (editUserSelect) {
            editUserSelect.destroy();
          }

          // Populate select options
          const options = response.users.map(user => ({
            value: user.id,
            text: `${user.first_name} ${user.last_name} -- ${user.national_code}`,
            selected: user.id === response.user_id
          }));

          // Initialize new TomSelect instance
          editUserSelect = new TomSelect("#edit-user-select", {
            options: options,
            items: [response.user_id],
            create: false,
            plugins: ['clear_button'],
            maxOptions: 50
          });
        },
        error: function() {
          toastr.error('خطا در دریافت اطلاعات کاربر!');
          $('#editSubUserModal').modal('hide');
        },
        complete: function() {
          $btn.prop('disabled', false);
        }
      });
    });

    $('#edit-subuser-form').on('submit', function(e) {
      e.preventDefault();
      const id = $('#edit-subuser-id').val();
      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.button_text');

      buttonText.hide();
      loader.show();

      form.find('.text-danger').text('');
      form.find('.field-wrapper').removeClass('has-error');

      $.ajax({
        url: "{{ route('dr-sub-users-update', ':id') }}".replace(':id', id),
        method: 'POST',
        data: form.serialize(),
        success: function(response) {
          toastr.success('کاربر زیرمجموعه با موفقیت ویرایش شد!');
          $('#editSubUserModal').modal('hide');
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
          updateSubUserList(response.subUsers);
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;
            Object.keys(errors).forEach(function(key) {
              form.find(`.error-${key}`).text(errors[key][0]);
              form.find(`.field-${key}`).addClass('has-error');
            });
          } else {
            toastr.error('خطا در ویرایش کاربر!');
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
            url: "{{ route('dr-sub-users-delete', ':id') }}".replace(':id', id),
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              toastr.success('کاربر زیرمجموعه با موفقیت حذف شد!');
              updateSubUserList(response.subUsers);
            },
            error: function() {
              toastr.error('خطا در حذف کاربر!');
            },
          });
        }
      });
    });
  });
</script>
@endsection
