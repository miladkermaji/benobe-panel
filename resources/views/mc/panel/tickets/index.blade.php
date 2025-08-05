@extends('mc.panel.layouts.master')

@section('styles')

  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/tickets/tickets.css') }}" rel="stylesheet" />

@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت تیکت‌ها')

<div class="container-fluid mt-4" x-data="{ mobileSearchOpen: false }">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-3">
            <h4 class="card-title">تیکت‌های من</h4>
            <!-- Mobile Toggle Button -->
            <button class="btn btn-link text-white p-0 d-md-none mobile-toggle-btn" type="button"
              @click="mobileSearchOpen = !mobileSearchOpen" :aria-expanded="mobileSearchOpen">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="toggle-icon" :class="{ 'rotate-180': mobileSearchOpen }">
                <path d="M6 9l6 6 6-6" />
              </svg>
            </button>
          </div>
          <!-- Mobile Collapsible Section -->
          <div x-show="mobileSearchOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2" class="d-md-none">
            <button type="button" class="btn btn-light btn-custom h-50" onclick="openXModal('add-ticket-modal')">
              <i class="fas fa-plus mr-2"></i> افزودن تیکت جدید
            </button>
          </div>
          <!-- Desktop Add Button -->
          <button type="button" class="btn btn-light btn-custom h-50 d-none d-md-block"
            onclick="openXModal('add-ticket-modal')">
            <i class="fas fa-plus mr-2"></i> افزودن تیکت جدید
          </button>
        </div>
        <div class="card-body">
          <!-- Desktop Table View -->
          <div class="table-responsive d-none d-md-block">
            <table class="table table-striped table-modern">
              <thead>
                <tr>
                  <th>شناسه</th>
                  <th>عنوان</th>
                  <th>توضیحات</th>
                  <th>وضعیت</th>
                  <th>عملیات</th>
                </tr>
              </thead>
              <tbody id="ticket-list">
                @foreach ($tickets as $ticket)
                  <tr>
                    <td>{{ $ticket->id }}</td>
                    <td>{{ $ticket->title }}</td>
                    <td>{{ Str::limit($ticket->description, 50) }}</td>
                    <td>
                      @if ($ticket->status == 'open')
                        <span class="badge badge-success">باز</span>
                      @elseif ($ticket->status == 'answered')
                        <span class="badge badge-info">پاسخ داده شده</span>
                      @elseif ($ticket->status == 'pending')
                        <span class="badge badge-warning">در حال بررسی</span>
                      @else
                        <span class="badge badge-danger">بسته</span>
                      @endif
                    </td>
                    <td>
                      <button class="btn btn-light rounded-circle btn-sm  delete-btn" data-id="{{ $ticket->id }}">
                        <img src="{{ asset('mc-assets/icons/trash.svg') }}" alt="حذف">
                      </button>
                      <button onclick="location.href='{{ route('mc-panel-tickets.show', $ticket->id) }}'"
                        class="btn btn-light rounded-circle btn-sm  view-btn" data-id="{{ $ticket->id }}">
                        <img src="{{ asset('mc-assets/icons/eye.svg') }}" alt="مشاهده">
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <!-- Mobile Card View -->
          <div class="notes-cards d-md-none" id="ticket-notes-cards">
            @foreach ($tickets as $ticket)
              <div class="note-card mb-2" data-id="{{ $ticket->id }}">
                <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2">
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold">{{ $ticket->title }}</span>
                    @if ($ticket->status == 'open')
                      <span class="badge badge-success">باز</span>
                    @elseif ($ticket->status == 'answered')
                      <span class="badge badge-info">پاسخ داده شده</span>
                    @elseif ($ticket->status == 'pending')
                      <span class="badge badge-warning">در حال بررسی</span>
                    @else
                      <span class="badge badge-danger">بسته</span>
                    @endif
                  </div>
                  <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-gradient-danger px-2 py-1 delete-btn" data-id="{{ $ticket->id }}"
                      title="حذف">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                      </svg>
                    </button>
                    <button onclick="location.href='{{ route('mc-panel-tickets.show', $ticket->id) }}'"
                      class="btn btn-sm btn-gradient-success px-2 py-1 view-btn" data-id="{{ $ticket->id }}"
                      title="مشاهده">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                        <circle cx="12" cy="12" r="3" />
                      </svg>
                    </button>
                  </div>
                </div>
                <div class="note-card-body px-2 py-2">
                  <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                    <span class="note-card-label">شناسه:</span>
                    <span class="note-card-value">{{ $ticket->id }}</span>
                  </div>
                  <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                    <span class="note-card-label">توضیحات:</span>
                    <span class="note-card-value">{{ Str::limit($ticket->description, 50) }}</span>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          <div id="pagination-links" class="w-100 d-flex justify-content-center mt-3">
            {{ $tickets->links('pagination::bootstrap-4') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- مودال افزودن تیکت -->
<x-custom-modal id="add-ticket-modal" title="ایجاد تیکت جدید" size="md">
  <form id="add-ticket-form">
    @csrf
    <div class="form-group">
      <label for="title">عنوان تیکت</label>
      <input type="text" class="form-control" id="title" name="title"
        placeholder="عنوان تیکت را وارد کنید">
      <small class="error-message error-title"></small>
    </div>
    <div class="form-group mt-2">
      <label for="description">توضیحات</label>
      <textarea class="form-control" id="description" name="description" placeholder="توضیحات تیکت را وارد کنید"></textarea>
      <small class="error-message error-description"></small>
    </div>
    <button type="submit" class="btn modal-footer-btn w-100 d-flex justify-content-center align-items-center">
      <span class="button_text">ارسال تیکت</span>
      <div class="loader"></div>
    </button>
  </form>
</x-custom-modal>

@endsection

@section('scripts')
<script src="{{ asset('mc-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
</script>
<script>
  $(document).ready(function() {
    // مدیریت بستن مودال
    document.addEventListener('x-modal-closed', function(e) {
      if (e.detail.modalId === 'add-ticket-modal') {
        $('#add-ticket-form')[0].reset();
        $('.error-message').text('');
        $('.form-group').removeClass('has-error');
      }
    });

    // مدیریت صفحه‌بندی
    $(document).on('click', '#pagination-links a', function(e) {
      e.preventDefault();
      let page = $(this).attr('href').split('page=')[1];
      fetchTickets(page);
    });

    function fetchTickets(page) {
      $.ajax({
        url: "?page=" + page,
        type: "GET",
        success: function(response) {
          $('#ticket-list').html($(response).find('#ticket-list').html());
          $('#ticket-notes-cards').html($(response).find('#ticket-notes-cards').html());
          $('#pagination-links').html($(response).find('#pagination-links').html());
        },
        error: function() {
          toastr.error("خطا در بارگذاری تیکت‌ها!");
        }
      });
    }

    // مدیریت ارسال تیکت جدید
    $('#add-ticket-form').on('submit', function(e) {
      e.preventDefault();
      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.button_text');

      buttonText.hide();
      loader.show();
      form.find('.error-message').text('');
      form.find('.form-group').removeClass('has-error');

      $.ajax({
        url: "{{ route('mc-panel-tickets.store') }}",
        method: 'POST',
        data: form.serialize(),
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          // نمایش SweetAlert موفقیت
          Swal.fire({
            title: 'پزشک گرامی، تیکت شما با موفقیت ثبت شد!',
            text: 'تیکت شما با موفقیت در سیستم ثبت گردید و به زودی توسط تیم پشتیبانی بررسی خواهد شد. از صبوری شما سپاسگزاریم.',
            icon: 'success',
            confirmButtonText: 'بسیار خوب',
            confirmButtonColor: '#2E86C1',
            timer: 5000,
            timerProgressBar: true,
            backdrop: 'rgba(0,0,0,0.4)' // پس‌زمینه ساده‌تر
          });

          closeXModal('add-ticket-modal');
          updateTicketList(response.tickets);
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;
            if (errors.limit) {
              // نمایش SweetAlert برای محدودیت تیکت
              Swal.fire({
                title: 'پزشک گرامی، شما بیش از 2 تیکت باز یا پاسخ‌نشده دارید!',
                text: 'لطفاً ابتدا تیکت‌های موجود را تکمیل یا بررسی کنید تا بتوانید تیکت جدید ثبت کنید.',
                confirmButtonText: 'متوجه شدم',
                confirmButtonColor: '#2E86C1',
                timer: 5000,
                timerProgressBar: true,
                backdrop: 'rgba(0,0,0,0.4)'
              });
            } else {
              // خطاهای اعتبارسنجی فرم
              Object.keys(errors).forEach(function(key) {
                form.find(`.error-${key}`).text(errors[key][0]);
                form.find(`.form-group:has(#${key})`).addClass('has-error');
              });
            }
          } else {
            toastr.error('خطا در افزودن تیکت!');
          }
        },
        complete: function() {
          buttonText.show();
          loader.hide();
        }
      });
    });

    // مدیریت حذف تیکت
    $(document).on('click', '.delete-btn', function() {
      const id = $(this).data('id');
      Swal.fire({
        title: 'حذف تیکت',
        text: 'آیا مطمئن هستید که می‌خواهید این تیکت را حذف کنید؟',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'خیر'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('mc-panel-tickets.destroy', ':id') }}".replace(':id', id),
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              toastr.success('تیکت با موفقیت حذف شد!');
              updateTicketList(response.tickets);
            },
            error: function() {
              toastr.error('خطا در حذف تیکت!');
            }
          });
        }
      });
    });

    // تابع بروزرسانی لیست تیکت‌ها
    function updateTicketList(tickets) {
      const tableContainer = $('#ticket-list');
      const cardsContainer = $('#ticket-notes-cards');

      tableContainer.empty();
      cardsContainer.empty();

      if (tickets.length === 0) {
        tableContainer.append('<tr><td colspan="5" class="text-center">هیچ تیکتی یافت نشد.</td></tr>');
        cardsContainer.append('<div class="text-center py-4"><p class="text-muted">هیچ تیکتی یافت نشد.</p></div>');
      } else {
        tickets.forEach(ticket => {
          let statusBadge;
          if (ticket.status === 'open') {
            statusBadge = '<span class="badge badge-success">باز</span>';
          } else if (ticket.status === 'answered') {
            statusBadge = '<span class="badge badge-info">پاسخ داده شده</span>';
          } else if (ticket.status === 'pending') {
            statusBadge = '<span class="badge badge-warning">در حال بررسی</span>';
          } else {
            statusBadge = '<span class="badge badge-danger">بسته</span>';
          }

          // Desktop table row
          const tableRow = `
            <tr>
              <td>${ticket.id}</td>
              <td>${ticket.title}</td>
              <td>${ticket.description.substring(0, 50)}${ticket.description.length > 50 ? '...' : ''}</td>
              <td>${statusBadge}</td>
              <td>
                <button class="btn btn-light rounded-circle btn-sm  delete-btn" data-id="${ticket.id}">
                  <img src="{{ asset('mc-assets/icons/trash.svg') }}" alt="حذف">
                </button>
                <button onclick="window.location.href='${getShowRoute(ticket.id)}'"
                  class="btn btn-light rounded-circle btn-sm  view-btn">
                  <img src="{{ asset('mc-assets/icons/eye.svg') }}" alt="مشاهده">
                </button>
              </td>
            </tr>`;
          tableContainer.append(tableRow);

          // Mobile card
          const card = `
            <div class="note-card mb-2" data-id="${ticket.id}">
              <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2">
                <div class="d-flex align-items-center gap-2">
                  <span class="fw-bold">${ticket.title}</span>
                  ${statusBadge}
                </div>
                <div class="d-flex gap-1">
                  <button class="btn btn-sm btn-gradient-danger px-2 py-1 delete-btn" data-id="${ticket.id}" title="حذف">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                    </svg>
                  </button>
                  <button onclick="window.location.href='${getShowRoute(ticket.id)}'"
                    class="btn btn-sm btn-gradient-success px-2 py-1 view-btn" title="مشاهده">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                  </button>
                </div>
              </div>
              <div class="note-card-body px-2 py-2">
                <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                  <span class="note-card-label">شناسه:</span>
                  <span class="note-card-value">${ticket.id}</span>
                </div>
                <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                  <span class="note-card-label">توضیحات:</span>
                  <span class="note-card-value">${ticket.description.substring(0, 50)}${ticket.description.length > 50 ? '...' : ''}</span>
                </div>
              </div>
            </div>`;
          cardsContainer.append(card);
        });
      }
    }

    function getShowRoute(ticketId) {
      return "{{ route('mc-panel-tickets.show', ':id') }}".replace(':id', ticketId);
    }
  });
</script>
@endsection
