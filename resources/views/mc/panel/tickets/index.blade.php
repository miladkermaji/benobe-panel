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

<div class="container-fluid mt-4">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="card-title">تیکت‌های من</h4>
          <button type="button" class="btn btn-light btn-custom h-50" onclick="openXModal('add-ticket-modal')">
            <i class="fas fa-plus mr-2"></i> افزودن تیکت جدید
          </button>
        </div>
        <div class="card-body">
          <div class="table-responsive">
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
            <div id="pagination-links" class="w-100 d-flex justify-content-center mt-3">
              {{ $tickets->links('pagination::bootstrap-4') }}
            </div>
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
      <input type="text" class="form-control" id="title" name="title" placeholder="عنوان تیکت را وارد کنید">
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
                icon: 'warning',
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
        title: 'آیا مطمئن هستید؟',
        text: 'این عمل قابل بازگشت نیست!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله، حذف شود',
        cancelButtonText: 'لغو'
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
      const container = $('#ticket-list');
      container.empty();

      if (tickets.length === 0) {
        container.append('<tr><td colspan="5" class="text-center">هیچ تیکتی یافت نشد.</td></tr>');
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

          const row = `
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
          container.append(row);
        });
      }
    }

    function getShowRoute(ticketId) {
      return "{{ route('mc-panel-tickets.show', ':id') }}".replace(':id', ticketId);
    }
  });
</script>
@endsection
