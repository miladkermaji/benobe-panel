@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/profile/subuser.css') }}" rel="stylesheet" />
  <style>
    .quick-user-form {
      background: #f8fafc;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(46, 134, 193, 0.07);
      border: 1px solid #e5e7eb;
    }

    .quick-user-form input {
      font-size: 13px;
    }

    .quick-user-error {
      font-size: 12px;
    }
  </style>
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت کاربران زیرمجموعه')

<!-- مودال افزودن کاربر زیرمجموعه -->
<x-custom-modal id="addSubUserModal" title="افزودن کاربر زیرمجموعه" size="md">
  <form id="add-subuser-form" method="post">
    @csrf
    <div class="w-100 position-relative mt-4 field-wrapper field-user_id">
      <label class="label-top-input-special-takhasos">جستجوی کاربر:</label>
      <input type="text" id="add-user-search" class="form-control h-50 w-100" autocomplete="off"
        placeholder="نام، نام خانوادگی یا کدملی را وارد کنید...">
      <input type="hidden" name="user_id" id="add-user-id">
      <div id="add-user-search-results" class="search-results-list"></div>
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
</x-custom-modal>

<!-- مودال ویرایش کاربر زیرمجموعه -->
<x-custom-modal id="editSubUserModal" title="ویرایش کاربر زیرمجموعه" size="md">
  <form id="edit-subuser-form" method="post">
    @csrf
    <input type="hidden" name="id" id="edit-subuser-id">
    <div class="w-100 position-relative mt-4 field-wrapper field-user_id">
      <label class="label-top-input-special-takhasos">جستجوی کاربر:</label>
      <input type="text" id="edit-user-search" class="form-control h-50 w-100" autocomplete="off"
        placeholder="نام، نام خانوادگی یا کدملی را وارد کنید...">
      <input type="hidden" name="user_id" id="edit-user-id">
      <div id="edit-user-search-results" class="search-results-list"></div>
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
</x-custom-modal>

<!-- مودال افزودن سریع کاربر جدید -->
<x-custom-modal id="quickAddUserModal" title="افزودن کاربر جدید" size="md">
  <form id="quick-add-user-form">
    <div class="mb-2"><input type="text" class="form-control form-control-sm" name="first_name" placeholder="نام"
        required></div>
    <div class="mb-2"><input type="text" class="form-control form-control-sm" name="last_name"
        placeholder="نام خانوادگی" required></div>
    <div class="mb-2"><input type="text" class="form-control form-control-sm" name="mobile" placeholder="موبایل"
        required></div>
    <div class="mb-2"><input type="text" class="form-control form-control-sm" name="national_code"
        placeholder="کدملی" required></div>
    <div class="quick-user-error text-danger mt-1"></div>
    <div class="mt-3 d-flex justify-content-between align-items-center">
      <button type="submit" class="btn btn-success btn-sm">ذخیره کاربر</button>
      <button type="button" class="btn btn-link btn-sm" onclick="closeXModal('quickAddUserModal')">انصراف</button>
    </div>
  </form>
</x-custom-modal>

<!-- بخش محتوا با جدول بوت‌استرپ -->
<div class="container subuser-content w-100 d-flex justify-content-center mt-4">
  <div class="subuser-content-wrapper w-100">
    <div class="glass-header text-white p-2  shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3 mb-2">
            <h1 class="m-0 h4 font-thin text-nowrap  mb-md-0">کاربران زیرمجموعه من</h1>
          </div>
          <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
            <div class="d-flex gap-2 flex-shrink-0 justify-content-center">
              <div class="search-container position-relative" style="max-width: 100%;">
                <input type="text" id="subuser-search-input"
                  class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start"
                  placeholder="جستجو در کاربران..." style="padding-right: 20px; text-align: right; direction: rtl;">
                <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                  style="z-index: 5; top: 50%; right: 8px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2">
                    <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                  </svg>
                </span>
              </div>
              <button
                class="btn btn-gradient-success rounded-1 px-3 py-1 d-flex align-items-center gap-1 add-subuser-btn"
                id="add-subuser-btn">
                <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span>افزودن</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card shadow-sm rounded-2">
      <div class="card-body p-0">
        <div class="group-actions p-2 border-bottom d-none" id="subuser-group-actions">
          <div class="d-flex align-items-center gap-2 justify-content-end">
            <select class="form-select form-select-sm" style="max-width: 200px;" id="subuser-group-action-select">
              <option value="">عملیات گروهی</option>
              <option value="delete">حذف انتخاب شده‌ها</option>
            </select>
            <button class="btn btn-sm btn-primary" type="button" id="subuser-group-action-btn">
              <span>اجرا</span>
            </button>
          </div>
        </div>
        <div class="table-responsive text-nowrap d-none d-md-block">
          <table class="table table-hover w-100 m-0" id="subuser-list-table">
            <thead>
              <tr>
                <th class="text-center align-middle" style="width: 40px;">
                  <div class="d-flex justify-content-center align-items-center">
                    <input type="checkbox" id="subuser-select-all" class="form-check-input m-0 align-middle">
                  </div>
                </th>
                <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                <th class="align-middle">نام و نام خانوادگی</th>
                <th class="align-middle">شماره موبایل</th>
                <th class="align-middle">کدملی</th>
                <th class="text-center align-middle" style="width: 120px;">عملیات</th>
              </tr>
            </thead>
            <tbody id="subuser-list-tbody">
              <!-- Rows will be rendered by JS -->
            </tbody>
          </table>
        </div>
        <!-- Mobile Card View -->
        <div class="notes-cards d-md-none" id="subuser-notes-cards">
          <!-- Cards will be rendered by JS -->
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3 px-3 flex-wrap gap-2"
          id="subuser-pagination-container">
          <!-- Pagination will be rendered by JS -->
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
  // --- TomSelect Safe Init ---
  function safeInitTomSelect(selector, options = {}) {
    var el = document.querySelector(selector);
    if (el && !el.tomselect) {
      return new TomSelect(el, options);
    }
    return null;
  }
  // --- End TomSelect Safe Init ---

  // Function to handle user selection
  function selectUser(type, id, name) {
    console.log('selectUser called:', type, id, name);
    if (type === 'add') {
      $('#add-user-search').val(name);
      $('#add-user-id').val(id);
      $('#add-user-search-results').empty().hide();
    } else if (type === 'edit') {
      $('#edit-user-search').val(name);
      $('#edit-user-id').val(id);
      $('#edit-user-search-results').empty().hide();
    }
  }

  function renderSubUserLoading() {
    $('#subuser-list-tbody').html(
      `<tr><td colspan="6" class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">در حال بارگذاری...</span>
        </div>
      </td></tr>`
    );
    $('#subuser-notes-cards').html(
      `<div class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">در حال بارگذاری...</span>
        </div>
      </div>`
    );
  }

  function renderSubUserTable(subUsers, from) {
    let html = '';
    if (!subUsers.length) {
      html = `<tr><td colspan="6" class="text-center py-4">شما کاربر زیرمجموعه‌ای ندارید</td></tr>`;
    } else {
      subUsers.forEach(function(subUser, idx) {
        html += `<tr>
          <td class="text-center"><input type="checkbox" class="form-check-input subuser-checkbox" value="${subUser.id}"></td>
          <td class="text-center">${from + idx}</td>
          <td>${subUser.subuserable?.first_name ?? ''} ${subUser.subuserable?.last_name ?? ''}</td>
          <td>${subUser.subuserable?.mobile ?? ''}</td>
          <td>${subUser.subuserable?.national_code ?? ''}</td>
          <td class="text-center d-flex gap-2">
            <button class="btn btn-light btn-sm rounded-circle edit-btn" data-id="${subUser.id}" title="ویرایش">
              <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
            </button>
            <button class="btn btn-light btn-sm rounded-circle delete-btn" data-id="${subUser.id}" title="حذف">
              <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
            </button>
          </td>
        </tr>`;
      });
    }
    $('#subuser-list-tbody').html(html);
  }

  function renderSubUserCards(subUsers) {
    let html = '';
    if (!subUsers.length) {
      html = `<div class="text-center py-4">
        <div class="d-flex justify-content-center align-items-center flex-column">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted mb-2">
            <path d="M5 12h14M12 5l7 7-7 7" />
          </svg>
          <p class="text-muted fw-medium">شما کاربر زیرمجموعه‌ای ندارید</p>
        </div>
      </div>`;
    } else {
      subUsers.forEach(function(subUser) {
        html += `<div class="note-card mb-3" data-id="${subUser.id}">
          <div class="note-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
              <input type="checkbox" class="form-check-input subuser-checkbox" value="${subUser.id}">
              <span class="fw-bold">${subUser.subuserable?.first_name ?? ''} ${subUser.subuserable?.last_name ?? ''}</span>
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
              <span class="note-card-value">${subUser.subuserable?.first_name ?? ''} ${subUser.subuserable?.last_name ?? ''}</span>
            </div>
            <div class="note-card-item">
              <span class="note-card-label">شماره موبایل:</span>
              <span class="note-card-value">${subUser.subuserable?.mobile ?? ''}</span>
            </div>
            <div class="note-card-item">
              <span class="note-card-label">کدملی:</span>
              <span class="note-card-value">${subUser.subuserable?.national_code ?? ''}</span>
            </div>
          </div>
        </div>`;
      });
    }
    $('#subuser-notes-cards').html(html);
  }

  function renderSubUserPagination(paginate) {
    let html = '';
    if (paginate.total > 0) {
      html += `<div class="text-muted">نمایش ${paginate.from} تا ${paginate.to} از ${paginate.total} ردیف</div>`;
      if (paginate.last_page > 1) {
        html += `<nav><ul class="pagination pagination-sm mb-0">`;
        for (let i = 1; i <= paginate.last_page; i++) {
          html += `<li class="page-item${i === paginate.current_page ? ' active' : ''}">
            <a class="page-link subuser-page-link" href="#" data-page="${i}">${i}</a>
          </li>`;
        }
        html += `</ul></nav>`;
      }
    }
    $('#subuser-pagination-container').html(html);
  }

  function fetchSubUsers(page = 1, search = '') {
    renderSubUserLoading();
    $.ajax({
      url: "{{ route('dr-sub-users-list') }}",
      data: {
        page,
        search
      },
      success: function(res) {
        renderSubUserTable(res.data, res.from);
        renderSubUserCards(res.data);
        renderSubUserPagination(res);
      },
      error: function() {
        $('#subuser-list-tbody').html(
          '<tr><td colspan="6" class="text-center text-danger">خطا در دریافت داده‌ها</td></tr>');
        $('#subuser-notes-cards').html('<div class="text-center text-danger">خطا در دریافت داده‌ها</div>');
        $('#subuser-pagination-container').html('');
      }
    });
  }

  // حذف TomSelect و اضافه کردن جستجوی زنده برای هر دو مودال
  $(document).ready(function() {
    // افزودن کاربر زیرمجموعه - جستجوی زنده
    $('#add-user-search').on('input', function() {
      const query = $(this).val();
      const $results = $('#add-user-search-results');
      $('#add-user-id').val('');
      if (query.length < 2) {
        $results.empty().hide();
        return;
      }
      $.ajax({
        url: "{{ route('dr-sub-users-search-users') }}",
        data: {
          q: query
        },
        success: function(res) {
          if (res.length === 0) {
            $results.html('<div class="search-result-item text-muted">نتیجه‌ای یافت نشد</div>' +
              '<button type="button" class="btn btn-sm btn-gradient-success w-100 mt-2 add-quick-user-btn">افزودن کاربر جدید</button>'
            ).show();
          } else {
            let html = '';
            res.forEach(function(user) {
              html +=
                `<div class="search-result-item" data-id="${user.id}" data-name="${user.first_name} ${user.last_name} (${user.national_code})" onclick="selectUser('add', '${user.id}', '${user.first_name} ${user.last_name} (${user.national_code})')">${user.first_name} ${user.last_name} <span class="text-muted">(${user.national_code})</span></div>`;
            });
            $results.html(html).show();
          }
        },
        error: function() {
          $results.html('<div class="search-result-item text-danger">خطا در جستجو</div>').show();
        }
      });
    });

    // بستن لیست نتایج با کلیک بیرون
    $(document).on('click', function(e) {
      if (!$(e.target).closest('#add-user-search, #add-user-search-results').length) {
        $('#add-user-search-results').empty().hide();
      }
      if (!$(e.target).closest('#edit-user-search, #edit-user-search-results').length) {
        $('#edit-user-search-results').empty().hide();
      }
    });

    // Simple click handlers for each modal
    $(document).on('click', '#add-user-search-results .search-result-item', function() {
      const id = $(this).data('id');
      const name = $(this).data('name');
      console.log('Add modal clicked:', id, name);
      $('#add-user-search').val(name);
      $('#add-user-id').val(id);
      $('#add-user-search-results').empty().hide();
    });

    $(document).on('click', '#edit-user-search-results .search-result-item', function() {
      const id = $(this).data('id');
      const name = $(this).data('name');
      console.log('Edit modal clicked:', id, name);
      $('#edit-user-search').val(name);
      $('#edit-user-id').val(id);
      $('#edit-user-search-results').empty().hide();
    });

    // نمایش فرم افزودن سریع کاربر
    $(document).on('click', '.add-quick-user-btn', function() {
      openXModal('quickAddUserModal');
      $('#quick-add-user-form')[0].reset();
      $('#quick-add-user-form .quick-user-error').text('');
    });

    // ارسال فرم افزودن سریع کاربر جدید
    $(document).on('submit', '#quick-add-user-form', function(e) {
      e.preventDefault();
      const $form = $(this);
      const data = $form.serialize();
      $form.find('.quick-user-error').text('');
      $.ajax({
        url: "{{ route('dr-sub-users-quick-create-user') }}",
        method: 'POST',
        data: data + '&_token={{ csrf_token() }}',
        success: function(res) {
          if (res.id && res.full_name) {
            $('#add-user-search').val(res.full_name);
            $('#add-user-id').val(res.id);
            $('#add-user-search-results').empty().hide();
            closeXModal('quickAddUserModal');
          } else {
            $form.find('.quick-user-error').text('خطا در ذخیره کاربر جدید!');
          }
        },
        error: function(xhr) {
          $form.find('.quick-user-error').text(xhr.responseJSON?.error || 'خطا در ذخیره کاربر جدید!');
        }
      });
    });

    // ویرایش کاربر زیرمجموعه - جستجوی زنده
    let editingUserId = null;
    $(document).on('click', '.edit-btn', function() {
      const id = $(this).data('id');
      editingUserId = id;
      openXModal('editSubUserModal');
      $('#edit-user-search').val('');
      $('#edit-user-id').val('');
      $('#edit-user-search-results').empty().hide();
      $.ajax({
        url: "{{ route('dr-sub-users-edit', ':id') }}".replace(':id', id),
        method: 'GET',
        success: function(response) {
          if (response.user) {
            const name =
              `${response.user.first_name} ${response.user.last_name} (${response.user.national_code})`;
            $('#edit-user-search').val(name);
            $('#edit-user-id').val(response.user.id);
          }
          $('#edit-subuser-id').val(response.id);
        }
      });
    });
    $('#edit-user-search').on('input', function() {
      const query = $(this).val();
      const $results = $('#edit-user-search-results');
      $('#edit-user-id').val('');
      if (query.length < 2) {
        $results.empty().hide();
        return;
      }
      $.ajax({
        url: "{{ route('dr-sub-users-search-users') }}",
        data: {
          q: query
        },
        success: function(res) {
          if (res.length === 0) {
            $results.html('<div class="search-result-item text-muted">نتیجه‌ای یافت نشد</div>').show();
          } else {
            let html = '';
            res.forEach(function(user) {
              html +=
                `<div class="search-result-item" data-id="${user.id}" data-name="${user.first_name} ${user.last_name} (${user.national_code})" onclick="selectUser('edit', '${user.id}', '${user.first_name} ${user.last_name} (${user.national_code})')">${user.first_name} ${user.last_name} <span class="text-muted">(${user.national_code})</span></div>`;
            });
            $results.html(html).show();
          }
        },
        error: function() {
          $results.html('<div class="search-result-item text-danger">خطا در جستجو</div>').show();
        }
      });
    });
  });

  $(document).ready(function() {
    console.log('Document ready - DR Panel loaded');

    // Test if jQuery is working
    if (typeof $ !== 'undefined') {
      console.log('jQuery is loaded');
    } else {
      console.log('jQuery is NOT loaded');
    }

    fetchSubUsers();

    $('#subuser-search-input').on('input', function() {
      fetchSubUsers(1, $(this).val());
    });

    $(document).on('click', '.subuser-page-link', function(e) {
      e.preventDefault();
      const page = $(this).data('page');
      const search = $('#subuser-search-input').val();
      fetchSubUsers(page, search);
    });

    // انتخاب گروهی
    function updateGroupActionsVisibility() {
      const checked = $('.subuser-checkbox:checked').length;
      if (checked > 0) {
        $('#subuser-group-actions').removeClass('d-none');
      } else {
        $('#subuser-group-actions').addClass('d-none');
      }
    }

    $(document).on('change', '#subuser-select-all', function() {
      const checked = $(this).is(':checked');
      $('.subuser-checkbox').prop('checked', checked);
      updateGroupActionsVisibility();
    });
    $(document).on('change', '.subuser-checkbox', function() {
      const all = $('.subuser-checkbox').length;
      const checked = $('.subuser-checkbox:checked').length;
      $('#subuser-select-all').prop('checked', all === checked && all > 0);
      updateGroupActionsVisibility();
    });

    // حذف گروهی
    $('#subuser-group-action-btn').on('click', function() {
      const action = $('#subuser-group-action-select').val();
      const ids = $('.subuser-checkbox:checked').map(function() {
        return $(this).val();
      }).get();
      if (action === 'delete' && ids.length > 0) {
        Swal.fire({
          title: 'حذف گروهی',
          text: 'آیا مطمئن هستید که می‌خواهید همه موارد انتخاب شده حذف شوند؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: "{{ route('dr-sub-users-delete-multiple') }}",
              method: 'DELETE',
              data: {
                ids: ids
              },
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(res) {
                toastr.success(res.message);
                fetchSubUsers();
                $('#subuser-select-all').prop('checked', false);
                updateGroupActionsVisibility();
              },
              error: function(xhr) {
                toastr.error(xhr.responseJSON?.error || 'خطا در حذف گروهی!');
              }
            });
          }
        });
      }
    });

    // بهبود لود مودال ویرایش با TomSelect ajax (live search)
    let editUserSelect;
    $(document).on('click', '.edit-btn', function() {
      const id = $(this).data('id');
      const $btn = $(this);
      $btn.prop('disabled', true);
      openXModal('editSubUserModal');
      $('#edit-user-select').html('<option>در حال بارگذاری...</option>');
      $.ajax({
        url: "{{ route('dr-sub-users-edit', ':id') }}".replace(':id', id),
        method: 'GET',
        cache: true,
        success: function(response) {
          $('#edit-subuser-id').val(response.id);
          if (editUserSelect) {
            editUserSelect.destroy();
          }
          // مقدار اولیه را اضافه کن
          $('#edit-user-select').html('');
          if (response.user) {
            $('#edit-user-select').append(
              `<option value="${response.user.id}" selected>${response.user.first_name} ${response.user.last_name} (${response.user.national_code})</option>`
            );
          }
          editUserSelect = new TomSelect("#edit-user-select", {
            valueField: 'id',
            labelField: 'full_name',
            searchField: ['first_name', 'last_name', 'national_code', 'mobile'],
            create: false,
            plugins: ['clear_button'],
            load: function(query, callback) {
              if (!query.length) return callback();
              $.ajax({
                url: "{{ route('dr-sub-users-search-users') }}",
                data: {
                  q: query
                },
                success: function(res) {
                  callback(res.map(user => ({
                    id: user.id,
                    full_name: `${user.first_name} ${user.last_name} (${user.national_code})`,
                  })));
                },
                error: function() {
                  callback();
                }
              });
            },
            render: {
              option: function(data, escape) {
                return `<div>${escape(data.full_name)}</div>`;
              },
              item: function(data, escape) {
                return `<div>${escape(data.full_name)}</div>`;
              }
            }
          });
        },
        error: function() {
          toastr.error('خطا در دریافت اطلاعات کاربر!');
          closeXModal('editSubUserModal');
        },
        complete: function() {
          $btn.prop('disabled', false);
        }
      });
    });

    $('#add-subuser-btn').on('click', function() {
      openXModal('addSubUserModal');
    });

    $('#add-subuser-form').on('submit', function(e) {
      e.preventDefault();
      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.button_text');

      // بررسی انتخاب کاربر
      if (!$('#add-user-id').val()) {
        form.find('.error-user_id').text('لطفاً یک کاربر را انتخاب کنید.');
        form.find('.field-user_id').addClass('has-error');
        return;
      }

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
          closeXModal('addSubUserModal');
          fetchSubUsers();
          // ریست اینپوت‌ها
          $('#add-user-search').val('');
          $('#add-user-id').val('');
          $('#add-user-search-results').empty().hide();
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

    $('#edit-subuser-form').on('submit', function(e) {
      e.preventDefault();
      const id = $('#edit-subuser-id').val();
      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.button_text');

      // بررسی انتخاب کاربر
      if (!$('#edit-user-id').val()) {
        form.find('.error-user_id').text('لطفاً یک کاربر را انتخاب کنید.');
        form.find('.field-user_id').addClass('has-error');
        return;
      }

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
          closeXModal('editSubUserModal');
          fetchSubUsers();
          // ریست اینپوت‌ها
          $('#edit-user-search').val('');
          $('#edit-user-id').val('');
          $('#edit-user-search-results').empty().hide();
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
              fetchSubUsers();
            },
            error: function(xhr) {
              if (xhr.status === 404) {
                toastr.error('کاربر مورد نظر پیدا نشد یا قبلاً حذف شده است.');
                fetchSubUsers();
              } else {
                toastr.error('خطا در حذف کاربر!');
                fetchSubUsers();
              }
            },
          });
        }
      });
    });
  });
</script>
@endsection
