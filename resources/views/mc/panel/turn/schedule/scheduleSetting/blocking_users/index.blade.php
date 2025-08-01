@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css"
    href="{{ asset('mc-assets/panel/turn/schedule/schedule-setting/blocking-users/blocking-user.css') }}"
    rel="stylesheet" />

@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('bread-crumb-title', 'مدیریت کاربران مسدود')

@section('content')
  @include('mc.panel.my-tools.loader-btn')
  <div class="blocking_users_container mt-3" dir="rtl">
    <div class="container px-0">
      <!-- هدر -->
      <div class="glass-header text-white p-2 rounded-2 mb-4 shadow-lg">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
          <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <h1 class="m-0 h4 font-thin text-nowrap mb-3 mb-md-0"> کاربران مسدود</h1>
            </div>
            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
              <div class="d-flex gap-2 flex-shrink-0 justify-content-center">
                <div class="search-container position-relative" style="max-width: 100%;">
                  <input type="text" id="searchInput"
                    class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start"
                    placeholder="جستجو (نام، موبایل، دلیل)..."
                    style="padding-right: 20px; text-align: right; direction: auto;">
                  <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                    style="z-index: 5; top: 50%; right: 8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                      stroke-width="2">
                      <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                    </svg>
                  </span>
                </div>
                <button
                  class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center gap-1"
                  data-bs-toggle="modal" data-bs-target="#addUserModal">
                  <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" />
                  </svg>
                  <span>افزودن کاربر</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- کارت اصلی -->
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- عملیات گروهی -->
          <div class="group-actions p-2 border-bottom" id="groupActions" style="display: none;">
            <div class="d-flex align-items-center gap-2 justify-content-end">
              <select id="groupActionSelect" class="form-select form-select-sm" style="max-width: 200px;">
                <option value="">عملیات گروهی</option>
                <option value="delete">حذف انتخاب شده‌ها</option>
                <option value="status_active">فعال کردن</option>
                <option value="status_inactive">غیرفعال کردن</option>
              </select>
              <button id="executeGroupAction" class="btn btn-sm btn-primary" disabled>
                <span>اجرا</span>
                <span class="loader" style="display: none;">در حال اجرا...</span>
              </button>
            </div>
          </div>

          <!-- نمای جدول برای دسکتاپ -->
          <div class="table-responsive text-nowrap d-none d-md-block">
            <table id="blockedUsersTable" class="table table-hover w-100 m-0">
              <thead>
                <tr>
                  <th class="text-center align-middle" style="width: 40px;">
                    <div class="d-flex justify-content-center align-items-center">
                      <input type="checkbox" id="selectAll" class="form-check-input m-0 align-middle">
                    </div>
                  </th>
                  <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                  <th class="align-middle">نام کاربر</th>
                  <th class="align-middle">شماره موبایل</th>
                  <th class="align-middle">تاریخ شروع</th>
                  <th class="align-middle">تاریخ پایان</th>
                  <th class="align-middle">دلیل</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 120px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($blockedUsers as $index => $blockedUser)
                  <tr class="align-middle" data-id="{{ $blockedUser->id }}">
                    <td class="text-center">
                      <div class="d-flex justify-content-center align-items-center">
                        <input type="checkbox" class="form-check-input m-0 align-middle select-user"
                          value="{{ $blockedUser->id }}">
                      </div>
                    </td>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $blockedUser->user->first_name }} {{ $blockedUser->user->last_name }}</td>
                    <td>{{ $blockedUser->user->mobile }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::fromDateTime($blockedUser->blocked_at)->format('Y/m/d') }}</td>
                    <td>
                      {{ $blockedUser->unblocked_at ? \Morilog\Jalali\Jalalian::fromDateTime($blockedUser->unblocked_at)->format('Y/m/d') : '-' }}
                    </td>
                    <td>
                      <div class="text-truncate" style="max-width: 300px;"
                        title="{{ $blockedUser->reason ?? 'بدون دلیل' }}">
                        {{ $blockedUser->reason ?? 'بدون دلیل' }}
                      </div>
                    </td>
                    <td class="text-center">
                      <div class="form-check form-switch d-flex justify-content-center">
                        <input class="form-check-input" type="checkbox" role="switch" data-id="{{ $blockedUser->id }}"
                          data-status="{{ (int) $blockedUser->status }}"
                          {{ $blockedUser->status == 1 ? 'checked' : '' }} onchange="toggleStatus(this)"
                          style="width: 3em; height: 1.5em; margin-top: 0;">
                      </div>
                    </td>
                    <td class="text-center">
                      <div class="d-flex justify-content-center gap-1">
                        <button class="btn btn-sm btn-gradient-danger px-2 py-1 delete-user-btn">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="text-center py-4">
                      <div class="d-flex justify-content-center align-items-center flex-column">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2" class="text-muted mb-2">
                          <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        <p class="text-muted fw-medium">هیچ کاربر مسدودی یافت نشد.</p>
                      </div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <!-- نمای کارت برای موبایل -->
          <div class="notes-cards d-md-none">
            @forelse ($blockedUsers as $index => $blockedUser)
              <div class="note-card mb-3" data-id="{{ $blockedUser->id }}">
                <div class="note-card-header d-flex justify-content-between align-items-center"
                  onclick="toggleMobileCard(this, event)">
                  <div class="d-flex align-items-center gap-2">
                    <input type="checkbox" class="form-check-input m-0 align-middle select-user"
                      value="{{ $blockedUser->id }}">
                    <span class="badge bg-primary-subtle text-primary">
                      {{ $blockedUser->user->first_name }} {{ $blockedUser->user->last_name }} <span
                        class="text-secondary">({{ $blockedUser->user->mobile }})</span>
                    </span>
                  </div>
                  <div class="d-flex gap-1 align-items-center">
                    <button type="button" class="btn btn-sm btn-gradient-danger px-2 py-1 delete-user-btn">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                      </svg>
                    </button>
                    <span class="dropdown-icon">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <polyline points="6 9 12 15 18 9" />
                      </svg>
                    </span>
                  </div>
                </div>
                <div class="note-card-body" style="display:none;">
                  <div class="note-card-item">
                    <span class="note-card-label">تاریخ شروع:</span>
                    <span
                      class="note-card-value">{{ \Morilog\Jalali\Jalalian::fromDateTime($blockedUser->blocked_at)->format('Y/m/d') }}</span>
                  </div>
                  <div class="note-card-item">
                    <span class="note-card-label">تاریخ پایان:</span>
                    <span class="note-card-value">
                      {{ $blockedUser->unblocked_at ? \Morilog\Jalali\Jalalian::fromDateTime($blockedUser->unblocked_at)->format('Y/m/d') : '-' }}
                    </span>
                  </div>
                  <div class="note-card-item">
                    <span class="note-card-label">دلیل:</span>
                    <span class="note-card-value">{{ $blockedUser->reason ?? 'بدون دلیل' }}</span>
                  </div>
                  <div class="note-card-item">
                    <span class="note-card-label">وضعیت:</span>
                    <div class="form-check form-switch d-inline-block">
                      <input class="form-check-input" type="checkbox" role="switch" data-id="{{ $blockedUser->id }}"
                        data-status="{{ (int) $blockedUser->status }}" {{ $blockedUser->status == 1 ? 'checked' : '' }}
                        onchange="toggleStatus(this)" style="width: 3em; height: 1.5em; margin-top: 0;">
                    </div>
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
                  <p class="text-muted fw-medium">هیچ کاربر مسدودی یافت نشد.</p>
                </div>
              </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    <!-- مودال افزودن کاربر -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-radius-6">
          <div class="modal-header">
            <h5 class="modal-title" id="addUserModalLabel">افزودن کاربر مسدود</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="بستن">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="addUserForm" method="POST">
              @csrf
              <div class="form-group position-relative">
                <label class="label-top-input-special-takhasos" for="userMobile">شماره موبایل</label>
                <input type="text" name="mobile" id="userMobile" class="form-control h-50 mb-3"
                  placeholder="09123456789">
              </div>
              <div class="form-group position-relative">
                <label class="label-top-input-special-takhasos" for="startDate">تاریخ شروع مسدودیت</label>
                <input type="text" id="startDate" name="blocked_at" class="form-control h-50 mb-3"
                  placeholder="1403/01/01" data-jdp>
              </div>
              <div class="form-group position-relative">
                <label class="label-top-input-special-takhasos" for="endDate">تاریخ پایان مسدودیت</label>
                <input type="text" id="endDate" name="unblocked_at" class="form-control h-50 mb-3"
                  placeholder="1403/01/10" data-jdp>
              </div>
              <div class="form-group position-relative">
                <textarea id="reason" name="reason" class="form-control h-50 mb-3" placeholder="دلیل مسدودیت را وارد کنید"></textarea>
              </div>
              <div class="mt-2 w-100">
                <button id="saveBlockedUserBtn" type="submit"
                  class="btn my-btn-primary w-100 h-50 d-flex justify-content-center align-items-center">
                  <span class="button_text">ثبت</span>
                  <div class="loader" style="display: none;"></div>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script src="{{ asset('mc-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
  <script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
  <script src="{{ asset('mc-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
  <script src="{{ asset('mc-assets/panel/js/turn/scehedule/sheduleSetting/vacation/vacation.js') }}"></script>
  <script>
    // مدیریت انتخاب‌ها
    let selectedUsers = [];

    // انتخاب همه
    $('#selectAll').on('change', function() {
      const isChecked = $(this).is(':checked');
      $('.select-user').prop('checked', isChecked);
      selectedUsers = isChecked ? $('.select-user').map((i, el) => $(el).val()).get() : [];
      toggleGroupActions();
    });

    // انتخاب تکی
    $(document).on('change', '.select-user', function() {
      selectedUsers = $('.select-user:checked').map((i, el) => $(el).val()).get();
      $('#selectAll').prop('checked', selectedUsers.length === $('.select-user').length);
      toggleGroupActions();
    });

    // نمایش/مخفی کردن بخش عملیات گروهی
    function toggleGroupActions() {
      $('#groupActions').toggle(selectedUsers.length > 0);
      $('#executeGroupAction').prop('disabled', selectedUsers.length === 0 || !$('#groupActionSelect').val());
    }

    // تغییر در منوی کشویی
    $('#groupActionSelect').on('change', function() {
      $('#executeGroupAction').prop('disabled', !$(this).val() || selectedUsers.length === 0);
    });

    // اجرای عملیات گروهی
    $('#executeGroupAction').on('click', function() {
      if (selectedUsers.length === 0) {
        toastr.warning('هیچ کاربری انتخاب نشده است.');
        return;
      }

      const action = $('#groupActionSelect').val();
      if (!action) {
        toastr.warning('لطفاً یک عملیات را انتخاب کنید.');
        return;
      }

      const button = $(this);
      const loader = button.find('.loader');
      const buttonText = button.find('span').first();
      button.prop('disabled', true);
      buttonText.hide();
      loader.show();

      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: `آیا می‌خواهید عملیات "${$('#groupActionSelect option:selected').text()}" را روی ${selectedUsers.length} کاربر اجرا کنید؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله، اجرا کن',
        cancelButtonText: 'لغو',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('doctor-blocking-users.group-action') }}",
            method: 'POST',
            data: {
              _token: '{{ csrf_token() }}',
              user_ids: selectedUsers,
              action: action,
              selectedClinicId: localStorage.getItem('selectedClinicId') || 'default'
            },
            success: function(response) {
              if (response.success) {
                toastr.success(response.message);
                loadBlockedUsers();
                selectedUsers = [];
                $('#selectAll').prop('checked', false);
                toggleGroupActions();
              } else {
                toastr.error(response.message);
              }
            },
            error: function(xhr) {
              toastr.error(xhr.responseJSON?.message || 'خطا در اجرای عملیات گروهی!');
            },
            complete: function() {
              button.prop('disabled', false);
              buttonText.show();
              loader.hide();
            }
          });
        } else {
          button.prop('disabled', false);
          buttonText.show();
          loader.hide();
        }
      });
    });

    // جستجو
    $('#searchInput').on('input', function() {
      const search = $(this).val();
      const selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';
      $.ajax({
        url: "{{ route('doctor-blocking-users.index') }}",
        method: "GET",
        data: {
          search: search,
          selectedClinicId: selectedClinicId
        },
        success: function(response) {
          const tableBody = $('#blockedUsersTable tbody');
          const cardsContainer = $('.notes-cards');
          tableBody.empty();
          cardsContainer.empty();
          selectedUsers = [];
          $('#selectAll').prop('checked', false);
          toggleGroupActions();

          if (!response.blockedUsers || response.blockedUsers.length === 0) {
            tableBody.append(
              '<tr><td colspan="9" class="text-center py-4"><div class="d-flex justify-content-center align-items-center flex-column"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted mb-2"><path d="M5 12h14M12 5l7 7-7 7" /></svg><p class="text-muted fw-medium">هیچ کاربر مسدودی یافت نشد.</p></div></td></tr>'
            );
            cardsContainer.append(
              '<div class="text-center py-4"><div class="d-flex justify-content-center align-items-center flex-column"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted mb-2"><path d="M5 12h14M12 5l7 7-7 7" /></svg><p class="text-muted fw-medium">هیچ کاربر مسدودی یافت نشد.</p></div></div>'
            );
          } else {
            response.blockedUsers.forEach((user, index) => {
              appendBlockedUser(user, index + 1);
              appendBlockedUserCard(user, index + 1);
            });
          }
        },
        error: function() {
          toastr.error("خطا در بارگذاری لیست کاربران!");
        }
      });
    });

    // ثبت کاربر مسدود
    $('#addUserForm').on('submit', function(e) {
      e.preventDefault();
      const form = $(this);
      const formData = form.serializeArray();
      const selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';
      formData.push({
        name: 'selectedClinicId',
        value: selectedClinicId
      });

      const button = form.find('button[type="submit"]');
      const loader = button.find('.loader');
      const buttonText = button.find('.button_text');

      button.prop('disabled', true);
      buttonText.hide();
      loader.show();

      $.ajax({
        url: "{{ route('doctor-blocking-users.store') }}",
        method: "POST",
        data: $.param(formData),
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
          if (response.success) {
            toastr.success(response.message);
            // به جای appendBlockedUser، کل لیست را دوباره لود کنیم
            loadBlockedUsers();
            // پاک کردن فیلد جستجو
            $('#searchInput').val('');
            form[0].reset();
            $('#addUserModal').modal('hide');
          }
        },
        error: function(xhr) {
          const response = xhr.responseJSON;
          toastr.error(response.error || response.message || "خطا در ثبت کاربر مسدود!");
          if (xhr.status === 422 && response.errors) {
            for (const field in response.errors) {
              toastr.error(response.errors[field][0]);
            }
          }
        },
        complete: function() {
          button.prop('disabled', false);
          buttonText.show();
          loader.hide();
        }
      });
    });

    // اضافه کردن کاربر به جدول
    function appendBlockedUser(user, index) {
      const tableBody = $('#blockedUsersTable tbody');
      const statusText = user.status == 1 ? 'مسدود' : 'آزاد';
      const blockedAt = moment(user.blocked_at).locale('fa').format('jYYYY/jMM/jDD');
      const unblockedAt = user.unblocked_at ? moment(user.unblocked_at).locale('fa').format('jYYYY/jMM/jDD') : '-';

      const newRow = `
                <tr class="align-middle" data-id="${user.id}">
                    <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center">
                            <input type="checkbox" class="form-check-input m-0 align-middle select-user" value="${user.id}">
                        </div>
                    </td>
                    <td class="text-center">${index}</td>
                    <td>${user.user.first_name} ${user.user.last_name}</td>
                    <td>${user.user.mobile}</td>
                    <td>${blockedAt}</td>
                    <td>${unblockedAt}</td>
                    <td class="text-truncate" style="max-width: 300px;" title="${user.reason || 'بدون دلیل'}">${user.reason || 'بدون دلیل'}</td>
                    <td class="text-center">
                        <div class="form-check form-switch d-flex justify-content-center">
                            <input class="form-check-input" type="checkbox" role="switch"
                                data-id="${user.id}" 
                                data-status="${parseInt(user.status)}" 
                                ${parseInt(user.status) == 1 ? 'checked' : ''}
                                onchange="toggleStatus(this)"
                                style="width: 3em; height: 1.5em; margin-top: 0;">
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-sm btn-gradient-danger px-2 py-1 delete-user-btn">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>`;
      tableBody.append(newRow);
    }

    // اضافه کردن کارت برای نمایش موبایل
    function appendBlockedUserCard(user, index) {
      const cardsContainer = $('.notes-cards');
      const blockedAt = moment(user.blocked_at).locale('fa').format('jYYYY/jMM/jDD');
      const unblockedAt = user.unblocked_at ? moment(user.unblocked_at).locale('fa').format('jYYYY/jMM/jDD') : '-';

      const card = `
                <div class="note-card mb-3" data-id="${user.id}">
                    <div class="note-card-header d-flex justify-content-between align-items-center" onclick="toggleMobileCard(this, event)">
                        <div class="d-flex align-items-center gap-2">
                            <input type="checkbox" class="form-check-input m-0 align-middle select-user" value="${user.id}">
                            <span class="badge bg-primary-subtle text-primary">
                                ${user.user.first_name} ${user.user.last_name} <span class='text-secondary'>(${user.user.mobile})</span>
                            </span>
                        </div>
                        <div class="d-flex gap-1 align-items-center">
                            <button type="button" class="btn btn-sm btn-gradient-danger px-2 py-1 delete-user-btn">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                </svg>
                            </button>
                            <span class="dropdown-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="note-card-body" style="display:none;">
                        <div class="note-card-item">
                            <span class="note-card-label">تاریخ شروع:</span>
                            <span class="note-card-value">${blockedAt}</span>
                        </div>
                        <div class="note-card-item">
                            <span class="note-card-label">تاریخ پایان:</span>
                            <span class="note-card-value">${unblockedAt}</span>
                        </div>
                        <div class="note-card-item">
                            <span class="note-card-label">دلیل:</span>
                            <span class="note-card-value">${user.reason || 'بدون دلیل'}</span>
                        </div>
                        <div class="note-card-item">
                            <span class="note-card-label">وضعیت:</span>
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input" type="checkbox" role="switch" 
                                    data-id="${user.id}" 
                                    data-status="${parseInt(user.status)}" 
                                    ${parseInt(user.status) == 1 ? 'checked' : ''} 
                                    onchange="toggleStatus(this)"
                                    style="width: 3em; height: 1.5em; margin-top: 0;">
                            </div>
                        </div>
                    </div>
                </div>`;
      cardsContainer.append(card);
    }

    // بارگذاری کاربران مسدود
    function loadBlockedUsers() {
      const selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';
      $.ajax({
        url: "{{ route('doctor-blocking-users.index') }}",
        method: "GET",
        data: {
          selectedClinicId: selectedClinicId
        },
        success: function(response) {
          const tableBody = $('#blockedUsersTable tbody');
          const cardsContainer = $('.notes-cards');

          // پاک کردن محتوای قبلی
          tableBody.empty();
          cardsContainer.empty();
          selectedUsers = [];
          $('#selectAll').prop('checked', false);
          toggleGroupActions();

          // بررسی وجود کاربران
          if (!response.blockedUsers || response.blockedUsers.length === 0) {
            // نمایش پیام "هیچ کاربری یافت نشد"
            tableBody.append(
              '<tr><td colspan="9" class="text-center py-4"><div class="d-flex justify-content-center align-items-center flex-column"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted mb-2"><path d="M5 12h14M12 5l7 7-7 7" /></svg><p class="text-muted fw-medium">هیچ کاربر مسدودی یافت نشد.</p></div></td></tr>'
            );
            cardsContainer.append(
              '<div class="text-center py-4"><div class="d-flex justify-content-center align-items-center flex-column"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted mb-2"><path d="M5 12h14M12 5l7 7-7 7" /></svg><p class="text-muted fw-medium">هیچ کاربر مسدودی یافت نشد.</p></div></div>'
            );
          } else {
            // نمایش کاربران
            response.blockedUsers.forEach((user, index) => {
              appendBlockedUser(user, index + 1);
              appendBlockedUserCard(user, index + 1);
            });
          }
        },
        error: function() {
          toastr.error("خطا در بارگذاری لیست کاربران!");
        }
      });
    }

    // حذف کاربر مسدود
    $(document).on('click', '.delete-user-btn', function(e) {
      e.preventDefault();
      const row = $(this).closest('tr');
      const card = $(this).closest('.note-card');
      const userId = row.data('id') || card.data('id');
      const currentStatus = row.find('.form-check-input').data('status') || card.find('.form-check-input').data(
        'status');

      let swalConfig = {
        title: 'آیا مطمئن هستید؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'بله، حذف شود!',
        cancelButtonText: 'لغو'
      };

      if (currentStatus === 1) {
        swalConfig.text = 'با حذف این آیتم، وضعیت کاربر به "آزاد" تغییر خواهد کرد. آیا می‌خواهید ادامه دهید؟';
        swalConfig.confirmButtonText = 'بله، ادامه بده!';
      } else {
        swalConfig.text = 'این کاربر برای همیشه از لیست مسدودیت حذف خواهد شد!';
      }

      Swal.fire(swalConfig).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('doctor-blocking-users.destroy', ':id') }}".replace(':id', userId),
            method: 'DELETE',
            data: {
              selectedClinicId: localStorage.getItem('selectedClinicId'),
              update_status: currentStatus === 1 ? true : false,
              new_status: 0
            },
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
              if (response.success) {
                toastr.success(response.message);
                // به جای حذف دستی، کل لیست را دوباره لود کنیم
                loadBlockedUsers();
              } else {
                toastr.error(response.message);
              }
            },
            error: function(xhr) {
              toastr.error(xhr.responseJSON?.message || 'خطا در حذف کاربر!');
            }
          });
        }
      });
    });

    // تغییر وضعیت کاربر
    function toggleStatus(element) {
      const userId = $(element).data('id');
      const currentStatus = parseInt($(element).data('status'));
      const newStatus = currentStatus === 1 ? 0 : 1;
      const statusText = newStatus === 1 ? 'مسدود' : 'آزاد';

      Swal.fire({
        title: 'تغییر وضعیت',
        text: `آیا می‌خواهید وضعیت این کاربر را به "${statusText}" تغییر دهید؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله، تغییر بده',
        cancelButtonText: 'لغو',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
      }).then((result) => {
        if (result.isConfirmed) {
          const requestData = {
            _token: '{{ csrf_token() }}',
            selectedClinicId: localStorage.getItem('selectedClinicId'),
            id: userId,
            status: newStatus,
          };

          $.ajax({
            url: "{{ route('doctor-blocking-users.update-status') }}",
            method: "PATCH",
            data: requestData,
            success: function(response) {
              if (response.success) {
                // به‌روزرسانی وضعیت در تمام المان‌های مربوطه
                $(`[data-id="${userId}"] .form-check-input`).each(function() {
                  $(this).prop('checked', newStatus === 1).data('status', newStatus);
                });
                toastr.success(response.message);
              } else {
                toastr.error(response.message || 'خطا در تغییر وضعیت');
                // برگرداندن به وضعیت قبلی
                $(element).prop('checked', currentStatus === 1);
              }
            },
            error: function(xhr) {
              toastr.error(xhr.responseJSON?.message || 'خطا در تغییر وضعیت!');
              // برگرداندن به وضعیت قبلی
              $(element).prop('checked', currentStatus === 1);
            }
          });
        } else {
          // برگرداندن به وضعیت قبلی
          $(element).prop('checked', currentStatus === 1);
        }
      });
    }

    // حذف پیام‌ها
    function deleteMessage(messageId, element) {
      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'این پیام برای همیشه حذف خواهد شد!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'لغو',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('doctor-blocking-users.delete-message') }}",
            method: "POST",
            data: {
              _token: '{{ csrf_token() }}',
              message_ids: [messageId],
              selectedClinicId: localStorage.getItem('selectedClinicId')
            },
            success: function(response) {
              if (response.success) {
                $(element).closest('tr').remove();
                toastr.success(response.message);
              } else {
                toastr.error(response.message);
              }
            },
            error: function(xhr) {
              toastr.error(xhr.responseJSON?.message || 'خطا در حذف پیام!');
            }
          });
        }
      });
    }

    // آکاردئون کارت موبایل
    function toggleMobileCard(header, event) {
      // اگر روی دکمه حذف یا آیکون فلش کلیک شد، آکاردئون اجرا نشود
      if (event && (event.target.closest('.delete-user-btn') || event.target.closest('.dropdown-icon'))) {
        return;
      }
      const card = header.closest('.note-card');
      const body = card.querySelector('.note-card-body');
      if (!card.classList.contains('open')) {
        card.classList.add('open');
        body.style.display = 'block';
      } else {
        card.classList.remove('open');
        body.style.display = 'none';
      }
    }

    $(document).ready(function() {
      loadBlockedUsers();
      $('[data-toggle="tooltip"]').tooltip();

      // گوش دادن به تغییر کلینیک
      $(document).on('clinicChanged', function() {
        loadBlockedUsers();
      });
    });
  </script>
@endsection
