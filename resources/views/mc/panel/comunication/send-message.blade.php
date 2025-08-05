@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/comunication/send-message/send-message.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ارسال پیام')
@include('mc.panel.my-tools.loader-btn')

<div class="blocking_users_content">
  <div class="container-fluid mt-4">
    <!-- جدول پیام‌های ارسالی -->
    <div class="mt-4">
      <div class="border-0 shadow-sm rounded-3">
        <div class="card-header text-white p-4">
          <h5 class="mb-0 fw-bold">پیام‌های ارسالی</h5>
        </div>
        <div class="card-body p-4">
          <div class="w-100 d-flex justify-content-end mb-3 flex-wrap gap-2">
            <button class="btn my-btn-primary h-50 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#sendSmsModal">
              ارسال پیام جدید
            </button>
            <button id="delete-multiple" class="btn btn-danger h-50">حذف انتخاب شده ها<img
                src="{{ asset('mc-assets/icons/trash.svg') }}" alt=""></button>
          </div>
          <div class="table-responsive">
            <table id="messagesTable" class="table  table-hover align-middle text-center">
              <thead class="table-light">
                <tr>
                  <th><input type="checkbox" class="form-check-input" id="select-all"></th>
                  <th>عنوان پیام</th>
                  <th>متن پیام</th>
                  <th>تاریخ ارسال</th>
                  <th>گیرنده</th>
                  <th>عملیات</th>

                </tr>
              </thead>
              <tbody id="messagesTableBody">
                @foreach ($messages as $message)
                  <tr data-id="{{ $message->id }}">
                    <td><input type="checkbox" class="form-check-input" id="select-single"></td>
                    <td>{{ $message->title }}</td>
                    <td>{{ $message->content }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::fromDateTime($message->created_at)->format('Y/m/d') }}</td>
                    <td>
                      @if ($message->recipient_type === 'all')
                        همه کاربران
                      @elseif ($message->recipient_type === 'blocked')
                        کاربران مسدود
                      @elseif ($message->recipient_type === 'specific' && $message->user)
                        {{ $message->user->first_name . ' ' . $message->user->last_name }}
                        ({{ $message->user->mobile }})
                      @else
                        نامشخص
                      @endif
                    </td>
                    <td>
                      <button class="btn btn-light btn-sm delete-message-btn rounded-circle"
                        onclick="deleteMessage({{ $message->id }}, this)" aria-label="حذف پیام">
                        <img src="{{ asset('mc-assets/icons/trash.svg') }}" alt="حذف">
                      </button>
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

  <!-- مودال ارسال پیام -->
  <div class="modal fade" id="sendSmsModal" tabindex="-1" aria-labelledby="sendSmsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-radius-6 shadow-sm">
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-dark" id="sendSmsModalLabel">ارسال پیام جدید</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="بستن">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4">
          <form id="sendSmsForm">
            @csrf
            <div class="form-group position-relative mb-4">
              <label class="label-top-input-special-takhasos fw-bold" for="smsTitle">عنوان پیام</label>
              <input type="text" id="smsTitle" name="title" class="form-control h-50 shadow-sm"
                placeholder="عنوان پیام">
            </div>
            <div class="form-group mb-4 mt-3">
              <textarea id="smsMessage" name="content" class="form-control shadow-sm" rows="4" placeholder="متن پیام"></textarea>
            </div>
            <div class="form-group position-relative mb-4">
              <label class="label-top-input-special-takhasos fw-bold" for="smsRecipient">گیرنده</label>
              <select id="smsRecipient" name="recipient_type" class="form-control form-select h-50 shadow-sm">
                <option value="all">همه کاربران</option>
                <option value="blocked">کاربران مسدود</option>
                <option value="specific">کاربر خاص</option>
              </select>
            </div>
            <div class="form-group position-relative mb-4 mt-3" id="specificRecipientField" style="display: none;">
              <label class="label-top-input-special-takhasos fw-bold" for="specificRecipient">شماره موبایل
                گیرنده</label>
              <input type="text" id="specificRecipient" name="specific_recipient" class="form-control h-50 shadow-sm"
                placeholder="09123456789">
            </div>
            <div class="mt-2 w-100">
              <button type="submit"
                class="btn my-btn-primary w-100 h-50 d-flex justify-content-center align-items-center">
                <span class="button_text">ارسال</span>
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
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
<script>
  function deleteMessage(messageId, element) {
    Swal.fire({
      title: 'آیا مطمئن هستید؟',
      text: 'این پیام برای همیشه حذف خواهد شد!',

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
            message_ids: [messageId],
            selectedClinicId: localStorage.getItem('selectedClinicId'),
            _token: '{{ csrf_token() }}'
          },
          success: function(response) {
            if (response.success) {
              $(element).closest('tr').remove();
              toastr.success(response.message);
              toggleDeleteButton();
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

  function validateForm() {
    const title = $('#smsTitle').val().trim();
    const content = $('#smsMessage').val().trim();
    const recipientType = $('#smsRecipient').val();
    const specificRecipient = $('#specificRecipient').val().trim();

    // پاک کردن پیام‌های خطای قبلی
    $('.error-message').remove();

    let isValid = true;
    let errors = [];

    // اعتبارسنجی عنوان
    if (!title) {
      errors.push('لطفاً عنوان پیام را وارد کنید.');
      $('#smsTitle').after('<div class="error-message text-danger mt-1">لطفاً عنوان پیام را وارد کنید.</div>');
      isValid = false;
    } else if (title.length > 255) {
      errors.push('عنوان پیام نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.');
      $('#smsTitle').after(
        '<div class="error-message text-danger mt-1">عنوان پیام نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.</div>');
      isValid = false;
    }

    // اعتبارسنجی متن پیام
    if (!content) {
      errors.push('لطفاً متن پیام را وارد کنید.');
      $('#smsMessage').after('<div class="error-message text-danger mt-1">لطفاً متن پیام را وارد کنید.</div>');
      isValid = false;
    } else if (content.length > 1000) {
      errors.push('متن پیام نمی‌تواند بیش از ۱۰۰۰ کاراکتر باشد.');
      $('#smsMessage').after(
        '<div class="error-message text-danger mt-1">متن پیام نمی‌تواند بیش از ۱۰۰۰ کاراکتر باشد.</div>');
      isValid = false;
    }

    // اعتبارسنجی نوع گیرنده
    if (!recipientType) {
      errors.push('لطفاً نوع گیرنده را انتخاب کنید.');
      $('#smsRecipient').after(
        '<div class="error-message text-danger mt-1">لطفاً نوع گیرنده را انتخاب کنید.</div>');
      isValid = false;
    } else if (!['all', 'blocked', 'specific'].includes(recipientType)) {
      errors.push('نوع گیرنده انتخاب‌شده معتبر نیست.');
      $('#smsRecipient').after(
        '<div class="error-message text-danger mt-1">نوع گیرنده انتخاب‌شده معتبر نیست.</div>');
      isValid = false;
    }

    // اعتبارسنجی شماره موبایل گیرنده خاص
    if (recipientType === 'specific' && !specificRecipient) {
      errors.push('لطفاً شماره موبایل گیرنده را وارد کنید.');
      $('#specificRecipient').after(
        '<div class="error-message text-danger mt-1">لطفاً شماره موبایل گیرنده را وارد کنید.</div>');
      isValid = false;
    } else if (recipientType === 'specific' && !/^\d{11}$/.test(specificRecipient)) {
      errors.push('شماره موبایل باید ۱۱ رقم باشد (مثال: 09123456789).');
      $('#specificRecipient').after(
        '<div class="error-message text-danger mt-1">شماره موبایل باید ۱۱ رقم باشد (مثال: 09123456789).</div>');
      isValid = false;
    }

    // نمایش خطاها با toastr
    if (errors.length > 0) {
      errors.forEach(error => toastr.error(error));
    }

    return isValid;
  }

  function toggleDeleteButton() {
    const checkedCount = $('#messagesTableBody .form-check-input:checked').length;
    $('#delete-multiple').prop('disabled', checkedCount === 0);
  }

  function deleteMultipleMessages(messageIds) {
    $.ajax({
      url: "{{ route('doctor-blocking-users.delete-message') }}",
      method: "POST",
      data: {
        message_ids: messageIds,
        selectedClinicId: localStorage.getItem('selectedClinicId'),
        _token: '{{ csrf_token() }}'
      },
      success: function(response) {
        if (response.success) {
          messageIds.forEach(id => {
            $(`tr[data-id="${id}"]`).remove();
          });
          toastr.success(response.message);
          $('#select-all').prop('checked', false);
          toggleDeleteButton();
        } else {
          toastr.error(response.message);
        }
      },
      error: function(xhr) {
        toastr.error(xhr.responseJSON?.message || 'خطا در حذف پیام‌ها!');
      }
    });
  }

  // بارگذاری پیام‌ها
  function loadMessages() {
    $.ajax({
      url: "{{ route('doctor-blocking-users.messages') }}",
      method: "GET",
      data: {
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(messages) {
        const tableBody = $('#messagesTableBody');
        tableBody.empty();
        messages.forEach(message => {
          let recipientText = 'نامشخص';
          if (message.recipient_type === 'all') {
            recipientText = 'همه کاربران';
          } else if (message.recipient_type === 'blocked') {
            recipientText = 'کاربران مسدود';
          } else if (message.recipient_type === 'specific' && message.user) {
            recipientText =
              `${message.user.first_name} ${message.user.last_name} (${message.user.mobile})`;
          }

          const jalaliDate = new Intl.DateTimeFormat('fa-IR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
          }).format(new Date(message.created_at));

          tableBody.append(`
              <tr data-id="${message.id}">
                <td><input type="checkbox" class="form-check-input" id="select-single"></td>
                <td>${message.title}</td>
                <td>${message.content}</td>
                <td>${jalaliDate}</td>
                <td>${recipientText}</td>
                <td>
                  <button class="btn btn-light btn-sm delete-message-btn rounded-circle" onclick="deleteMessage(${message.id}, this)" aria-label="حذف پیام">
                    <img src="{{ asset('mc-assets/icons/trash.svg') }}" alt="حذف">
                  </button>
                </td>
              </tr>
            `);
        });
        toggleDeleteButton();
      },
      error: function() {
        toastr.error("خطا در بارگذاری پیام‌ها!");
      }
    });
  }
  $(document).ready(function() {
    // نمایش/مخفی کردن فیلد گیرنده خاص
    $('#smsRecipient').on('change', function() {
      const specificField = $('#specificRecipientField');
      const specificInput = $('#specificRecipient');
      if ($(this).val() === 'specific') {
        specificField.show();
        specificInput.prop('required', true);
      } else {
        specificField.hide();
        specificInput.prop('required', false).val('');
      }
    });

    // اعتبارسنجی سمت کلاینت


    // ارسال فرم
    $('#sendSmsForm').on('submit', function(e) {
      e.preventDefault();

      // اعتبارسنجی فرم
      if (!validateForm()) {
        return;
      }

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
        url: "{{ route('doctor-blocking-users.send-message') }}",
        method: "POST",
        data: $.param(formData),
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
          if (response.success) {
            toastr.success(response.message);
            form[0].reset();
            $('#sendSmsModal').modal('hide');
            $('#specificRecipientField').hide();
            loadMessages();
          }
        },
        error: function(xhr) {
          const response = xhr.responseJSON;
          if (xhr.status === 422 && response.errors) {
            Object.values(response.errors).forEach(error => {
              toastr.error(error[0]);
            });
          } else {
            toastr.error(response?.message || 'خطایی در ارسال پیام رخ داد!');
          }
        },
        complete: function() {
          button.prop('disabled', false);
          buttonText.show();
          loader.hide();
        }
      });
    });

    // مدیریت انتخاب همه چک‌باکس‌ها
    $('#select-all').on('change', function() {
      const isChecked = $(this).is(':checked');
      $('#messagesTableBody .form-check-input').prop('checked', isChecked);
      toggleDeleteButton();
    });

    // مدیریت تغییر وضعیت چک‌باکس‌های تکی
    $(document).on('change', '#messagesTableBody .form-check-input', function() {
      if (!$(this).is(':checked')) {
        $('#select-all').prop('checked', false);
      }
      const allChecked = $('#messagesTableBody .form-check-input').length === $(
        '#messagesTableBody .form-check-input:checked').length;
      $('#select-all').prop('checked', allChecked);
      toggleDeleteButton();
    });

    // فعال/غیرفعال کردن دکمه حذف


    // حذف گروهی پیام‌ها
    $('#delete-multiple').on('click', function() {
      const selectedIds = [];
      $('#messagesTableBody .form-check-input:checked').each(function() {
        selectedIds.push($(this).closest('tr').data('id'));
      });

      if (selectedIds.length === 0) {
        toastr.warning('هیچ پیامی انتخاب نشده است!');
        return;
      }

      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: `می‌خواهید ${selectedIds.length} پیام را حذف کنید؟`,

        showCancelButton: true,
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'لغو',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
      }).then((result) => {
        if (result.isConfirmed) {
          deleteMultipleMessages(selectedIds);
        }
      });
    });

    // تابع حذف گروهی پیام‌ها

    loadMessages();

    // حذف پیام تکی


    $(document).ready(function() {
      $('[data-toggle="tooltip"]').tooltip();
    });
  });
</script>
@endsection
