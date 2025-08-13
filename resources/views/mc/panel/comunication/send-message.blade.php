@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />

  <link type="text/css" href="{{ asset('dr-assets/css/panel/doctornote/doctornote.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ارسال پیام')
@include('mc.panel.my-tools.loader-btn')

<div class="doctor-notes-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl">
    <!-- Header -->
    <div class="glass-header text-white p-2 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3 mb-2">
            <h1 class="m-0 h4 font-thin text-nowrap mb-md-0">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
              </svg>
              پیام‌های ارسالی
            </h1>
            <!-- Mobile Toggle Button -->
            <button class="btn btn-link text-white p-0 d-md-none mobile-toggle-btn" type="button"
              @click="mobileSearchOpen = !mobileSearchOpen" :aria-expanded="mobileSearchOpen">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                class="toggle-icon" :class="{ 'rotate-180': mobileSearchOpen }">
                <path d="M6 9l6 6 6-6" />
              </svg>
            </button>
          </div>
          <!-- Mobile Collapsible Section -->
          <div x-show="mobileSearchOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2" class="w-100 d-md-none">
            <div class="d-flex flex-column gap-2">
              <div class="search-container position-relative" style="max-width: 100%;">
                <input type="text"
                  class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start h-50"
                  placeholder="جستجو در پیام‌ها..."
                  style="padding-right: 20px; text-align: right; direction: rtl;">
                <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                  style="z-index: 5; top: 50%; right: 8px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                    <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                  </svg>
                </span>
              </div>
              <button class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center gap-1 h-50"
                onclick="openXModal('sendMessageModal')">
                <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span>ارسال پیام جدید</span>
              </button>
            </div>
          </div>
          <!-- Desktop Actions -->
          <div class="d-none d-md-flex gap-2 flex-shrink-0 justify-content-center">
            <div class="search-container position-relative" style="max-width: 300px;">
              <input type="text"
                class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start h-50"
                placeholder="جستجو در پیام‌ها..."
                style="padding-right: 20px; text-align: right; direction: rtl;">
              <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                style="z-index: 5; top: 50%; right: 8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                  <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                </svg>
              </span>
            </div>
            <button class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center gap-1 h-50"
              onclick="openXModal('sendMessageModal')">
              <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              <span>ارسال پیام جدید</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Group Actions -->
          <div class="group-actions p-2 border-bottom" x-data="{ show: false }" id="groupActions" style="display: none;">
            <div class="d-flex align-items-center gap-2 justify-content-end">
              <button id="delete-multiple" class="btn btn-gradient-danger btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                  <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                </svg>
                حذف انتخاب شده‌ها
              </button>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="table-responsive text-nowrap d-none d-md-block">
            <table class="table table-hover w-100 m-0">
              <thead>
                <tr>
                  <th class="text-center align-middle" style="width: 40px;">
                    <div class="d-flex justify-content-center align-items-center">
                      <input type="checkbox" class="form-check-input m-0 align-middle" id="select-all">
                    </div>
                  </th>
                  <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                  <th class="align-middle">عنوان پیام</th>
                  <th class="align-middle">متن پیام</th>
                  <th class="align-middle">تاریخ ارسال</th>
                  <th class="align-middle">گیرنده</th>
                  <th class="text-center align-middle" style="width: 120px;">عملیات</th>
                </tr>
              </thead>
              <tbody id="messagesTableBody">
                @foreach ($messages as $index => $message)
                  <tr class="align-middle" data-id="{{ $message->id }}">
                    <td class="text-center">
                      <div class="d-flex justify-content-center align-items-center">
                        <input type="checkbox" class="form-check-input m-0 align-middle select-single">
                      </div>
                    </td>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                      <div class="text-truncate" style="max-width: 200px;" title="{{ $message->title }}">
                        {{ $message->title }}
                      </div>
                    </td>
                    <td>
                      <div class="text-truncate" style="max-width: 300px;" title="{{ $message->content }}">
                        {{ $message->content }}
                      </div>
                    </td>
                    <td>{{ \Morilog\Jalali\Jalalian::fromDateTime($message->created_at)->format('Y/m/d') }}</td>
                    <td>
                      @if ($message->recipient_type === 'all')
                        <span class="badge bg-primary-subtle text-primary">همه کاربران</span>
                      @elseif ($message->recipient_type === 'blocked')
                        <span class="badge bg-warning-subtle text-warning">کاربران مسدود</span>
                      @elseif ($message->recipient_type === 'specific' && $message->user)
                        <span class="badge bg-info-subtle text-info">
                          {{ $message->user->first_name . ' ' . $message->user->last_name }}
                        </span>
                      @else
                        <span class="badge bg-secondary-subtle text-secondary">نامشخص</span>
                      @endif
                    </td>
                    <td class="text-center">
                      <button class="btn btn-outline-danger btn-sm rounded-circle delete-message-btn"
                        onclick="deleteMessage({{ $message->id }}, this)" aria-label="حذف پیام">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                        </svg>
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <!-- Mobile Card View -->
          <div class="notes-cards d-md-none">
            @foreach ($messages as $index => $message)
              <div class="note-card mb-2" x-data="{ open: false }">
                <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2"
                  @click="open = !open" style="cursor:pointer;">
                  <div class="d-flex align-items-center gap-2">
                    <input type="checkbox" class="form-check-input m-0 align-middle select-single" @click.stop>
                    <span class="fw-bold">{{ $message->title }}</span>
                    <span class="text-muted">({{ \Morilog\Jalali\Jalalian::fromDateTime($message->created_at)->format('Y/m/d') }})</span>
                  </div>
                  <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                    <path d="M6 9l6 6 6-6" />
                  </svg>
                </div>
                <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                  <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                    <span class="note-card-label">متن پیام:</span>
                    <span class="note-card-value">{{ $message->content }}</span>
                  </div>
                  <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                    <span class="note-card-label">گیرنده:</span>
                    <span class="note-card-value">
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
                    </span>
                  </div>
                  <div class="note-card-actions d-flex gap-1 mt-2 pt-2 border-top">
                    <button class="btn btn-outline-danger btn-sm flex-fill delete-message-btn"
                      onclick="deleteMessage({{ $message->id }}, this)">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                        <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                      </svg>
                      حذف
                    </button>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Custom Modal for Send Message -->
<x-custom-modal id="sendMessageModal" title="ارسال پیام جدید" size="md">
  <form id="sendSmsForm">
    @csrf
    <div class="form-group position-relative mb-4">
      <label class="label-top-input-special-takhasos fw-bold" for="smsTitle">عنوان پیام</label>
      <input type="text" id="smsTitle" name="title" class="form-control h-50 shadow-sm" placeholder="عنوان پیام">
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
      <label class="label-top-input-special-takhasos fw-bold" for="specificRecipient">شماره موبایل گیرنده</label>
      <input type="text" id="specificRecipient" name="specific_recipient" class="form-control h-50 shadow-sm"
        placeholder="09123456789">
    </div>
    <div class="mt-3 d-flex gap-2">
      <button type="submit" class="btn btn-gradient-success flex-fill h-50 d-flex justify-content-center align-items-center">
        <span class="button_text">ارسال</span>
        <div class="loader" style="display: none;"></div>
      </button>
      <button type="button" class="btn btn-outline-secondary h-50" onclick="closeXModal('sendMessageModal')">
        لغو
      </button>
    </div>
  </form>
</x-custom-modal>
@endsection

@section('scripts')
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
<script>
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
          url: "{{ route('mc-doctor-blocking-users.delete-message') }}",
          method: "POST",
          data: {
            message_ids: [messageId],
            selectedClinicId: localStorage.getItem('selectedClinicId'),
            _token: '{{ csrf_token() }}'
          },
          success: function(response) {
            if (response.success) {
              $(element).closest('tr, .note-card').remove();
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
    const checkedCount = $('.select-single:checked').length;
    const groupActions = $('#groupActions');
    
    if (checkedCount > 0) {
      groupActions.show();
      $('#delete-multiple').prop('disabled', false);
    } else {
      groupActions.hide();
      $('#delete-multiple').prop('disabled', true);
    }
  }

  function deleteMultipleMessages(messageIds) {
    $.ajax({
      url: "{{ route('mc-doctor-blocking-users.delete-message') }}",
      method: "POST",
      data: {
        message_ids: messageIds,
        selectedClinicId: localStorage.getItem('selectedClinicId'),
        _token: '{{ csrf_token() }}'
      },
      success: function(response) {
        if (response.success) {
          messageIds.forEach(id => {
            $(`tr[data-id="${id}"], .note-card[data-id="${id}"]`).remove();
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
      url: "{{ route('mc-doctor-blocking-users.messages') }}",
      method: "GET",
      data: {
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(messages) {
        const tableBody = $('#messagesTableBody');
        const cardsContainer = $('.notes-cards');
        
        tableBody.empty();
        cardsContainer.empty();
        
        messages.forEach((message, index) => {
          let recipientText = 'نامشخص';
          let recipientBadge = '<span class="badge bg-secondary-subtle text-secondary">نامشخص</span>';
          
          if (message.recipient_type === 'all') {
            recipientText = 'همه کاربران';
            recipientBadge = '<span class="badge bg-primary-subtle text-primary">همه کاربران</span>';
          } else if (message.recipient_type === 'blocked') {
            recipientText = 'کاربران مسدود';
            recipientBadge = '<span class="badge bg-warning-subtle text-warning">کاربران مسدود</span>';
          } else if (message.recipient_type === 'specific' && message.user) {
            recipientText = `${message.user.first_name} ${message.user.last_name} (${message.user.mobile})`;
            recipientBadge = `<span class="badge bg-info-subtle text-info">${message.user.first_name} ${message.user.last_name}</span>`;
          }

          const jalaliDate = new Intl.DateTimeFormat('fa-IR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
          }).format(new Date(message.created_at));

          // Desktop table row
          tableBody.append(`
            <tr class="align-middle" data-id="${message.id}">
              <td class="text-center">
                <div class="d-flex justify-content-center align-items-center">
                  <input type="checkbox" class="form-check-input m-0 align-middle select-single">
                </div>
              </td>
              <td class="text-center">${index + 1}</td>
              <td>
                <div class="text-truncate" style="max-width: 200px;" title="${message.title}">
                  ${message.title}
                </div>
              </td>
              <td>
                <div class="text-truncate" style="max-width: 300px;" title="${message.content}">
                  ${message.content}
                </div>
              </td>
              <td>${jalaliDate}</td>
              <td>${recipientBadge}</td>
              <td class="text-center">
                <button class="btn btn-outline-danger btn-sm rounded-circle delete-message-btn" onclick="deleteMessage(${message.id}, this)" aria-label="حذف پیام">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  </svg>
                </button>
              </td>
            </tr>
          `);

          // Mobile card
          cardsContainer.append(`
            <div class="note-card mb-2" x-data="{ open: false }" data-id="${message.id}">
              <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2" @click="open = !open" style="cursor:pointer;">
                <div class="d-flex align-items-center gap-2">
                  <input type="checkbox" class="form-check-input m-0 align-middle select-single" @click.stop>
                  <span class="fw-bold">${message.title}</span>
                  <span class="text-muted">(${jalaliDate})</span>
                </div>
                <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                  <path d="M6 9l6 6 6-6" />
                </svg>
              </div>
              <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                  <span class="note-card-label">متن پیام:</span>
                  <span class="note-card-value">${message.content}</span>
                </div>
                <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                  <span class="note-card-label">گیرنده:</span>
                  <span class="note-card-value">${recipientText}</span>
                </div>
                <div class="note-card-actions d-flex gap-1 mt-2 pt-2 border-top">
                  <button class="btn btn-outline-danger btn-sm flex-fill delete-message-btn" onclick="deleteMessage(${message.id}, this)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                      <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    </svg>
                    حذف
                  </button>
                </div>
              </div>
            </div>
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
        url: "{{ route('mc-doctor-blocking-users.send-message') }}",
        method: "POST",
        data: $.param(formData),
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
          if (response.success) {
            toastr.success(response.message);
            form[0].reset();
            closeXModal('sendMessageModal');
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
      $('.select-single').prop('checked', isChecked);
      toggleDeleteButton();
    });

    // مدیریت تغییر وضعیت چک‌باکس‌های تکی
    $(document).on('change', '.select-single', function() {
      if (!$(this).is(':checked')) {
        $('#select-all').prop('checked', false);
      }
      const allChecked = $('.select-single').length === $('.select-single:checked').length;
      $('#select-all').prop('checked', allChecked);
      toggleDeleteButton();
    });

    // حذف گروهی پیام‌ها
    $('#delete-multiple').on('click', function() {
      const selectedIds = [];
      $('.select-single:checked').each(function() {
        selectedIds.push($(this).closest('tr, .note-card').data('id'));
      });

      if (selectedIds.length === 0) {
        toastr.warning('هیچ پیامی انتخاب نشده است!');
        return;
      }

      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: `می‌خواهید ${selectedIds.length} پیام را حذف کنید؟`,
        icon: 'warning',
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

    // بارگذاری اولیه پیام‌ها
    loadMessages();

    // تنظیم tooltip
    $(document).ready(function() {
      $('[data-toggle="tooltip"]').tooltip();
    });
  });
</script>
@endsection