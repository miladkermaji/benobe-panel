@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/scheduleSetting/scheduleSetting.css') }}"
    rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/scheduleSetting/workhours.css') }}"
    rel="stylesheet" />
  <link type="text/css"
    href="{{ asset('dr-assets/panel/turn/schedule/schedule-setting/blocking-users/blocking-user.css') }}"
    rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'ارسال پیام')
@include('dr.panel.my-tools.loader-btn')
<div class="blocking_users_content">
  <div class="container-fluid mt-4">
    <!-- جدول پیام‌های ارسالی -->
    <div class="mt-4">
      <div class="border-0 shadow-sm rounded-3">
        <div class="card-header bg-dark text-white p-4">
          <h5 class="mb-0 font-weight-bold">پیام‌های ارسالی</h5>
        </div>
        <div class="card-body p-4">
          <div class="w-100 d-flex justify-content-end mb-3">
            <button class="btn btn-success h-50 py-2 fw-bold" data-toggle="modal" data-target="#sendSmsModal"
              style="background: linear-gradient(to right, #2ecc71, #27ae60); border: none;">
              ارسال پیام جدید
            </button>
          </div>
          <div class="table-responsive">
            <table id="messagesTable" class="table table-striped table-bordered table-hover align-middle text-center">
              <thead class="table-light">
                <tr>
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
                      <button class="btn btn-outline-danger btn-sm delete-message-btn rounded-circle"
                        onclick="deleteMessage({{ $message->id }}, this)">
                        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24"
                          fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
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
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title font-weight-bold" id="sendSmsModalLabel">ارسال پیام جدید</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="بستن">
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
              <button type="submit" class="btn btn-primary w-100 h-50 d-flex justify-content-center align-items-center"
                style="background: linear-gradient(to right, #2ecc71, #27ae60); border: none;">
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
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/turn/scehedule/sheduleSetting/vacation/vacation.js') }}"></script>
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

    // نمایش/مخفی کردن فیلد گیرنده خاص
    $('#smsRecipient').on('change', function() {
      const specificField = $('#specificRecipientField');
      if ($(this).val() === 'specific') {
        specificField.show();
      } else {
        specificField.hide();
      }
    });
  });

  // ارسال پیام
  $('#sendSmsForm').on('submit', function(e) {
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
          $('#specificRecipientField').hide(); // مخفی کردن فیلد کاربر خاص بعد از ریست
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

  // نمایش/مخفی کردن فیلد گیرنده خاص
  $('#smsRecipient').on('change', function() {
    const specificField = $('#specificRecipientField');
    if ($(this).val() === 'specific') {
      specificField.show();
    } else {
      specificField.hide();
      $('#specificRecipient').val(''); // خالی کردن فیلد در صورت تغییر گزینه
    }
  });

  // حذف کاربر مسدود
  $(document).on('click', '#blockedUsersTable .delete-user-btn', function(e) {
    e.preventDefault();
    const row = $(this).closest('tr');
    const userId = row.data('id');

    Swal.fire({
      title: 'آیا مطمئن هستید؟',
      text: 'این کاربر برای همیشه حذف خواهد شد!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'بله، حذف شود!',
      cancelButtonText: 'لغو'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "{{ route('doctor-blocking-users.destroy', ['id' => ':userId']) }}".replace(':userId',
            userId),
          method: 'DELETE',
          data: {
            selectedClinicId: localStorage.getItem('selectedClinicId')
          },
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          success: function(response) {
            if (response.success) {
              toastr.success(response.message);
              row.remove();
            } else {
              toastr.error(response.message);
            }
          },
          error: function(xhr) {
            const response = xhr.responseJSON;
            toastr.error(response?.message || 'خطا در حذف کاربر!');
          }
        });
      }
    });
  });

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
            recipientText = `${message.user.first_name} ${message.user.last_name} (${message.user.mobile})`;
          }

          const jalaliDate = new Intl.DateTimeFormat('fa-IR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
          }).format(new Date(message.created_at));

          tableBody.append(`
                    <tr data-id="${message.id}">
                        <td>${message.title}</td>
                        <td>${message.content}</td>
                        <td>${jalaliDate}</td>
                        <td>${recipientText}</td>
                        <td>
                            <button class="btn btn-outline-danger btn-sm delete-message-btn rounded-circle" onclick="deleteMessage(${message.id}, this)">
                                 <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                `);
        });
      },
      error: function() {
        toastr.error("خطا در بارگذاری پیام‌ها!");
      }
    });
  }
  loadMessages();

  // حذف پیام
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
          url: "{{ route('doctor-blocking-users.delete-message', '') }}/" + messageId,
          method: "DELETE",
          data: {
            selectedClinicId: localStorage.getItem('selectedClinicId')
          },
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
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

  $(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
  });
</script>
@endsection
