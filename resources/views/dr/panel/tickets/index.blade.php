@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/tickets/tickets.css') }}" rel="stylesheet" />
  <style>
    .myPanelOption {
      display: none;
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .card-header {
      background: linear-gradient(135deg, #007bff, #0056b3);
      border-radius: 12px 12px 0 0;
      padding: 1.5rem;
    }

    .card-title {
      color: white;
      font-weight: 600;
    }

    .table-modern {
      border-radius: 8px;
      overflow: hidden;
    }

    .table-modern thead {
      background: #f8f9fa;
      color: #333;
    }

    .table-modern th,
    .table-modern td {
      vertical-align: middle;
      text-align: center;
      padding: 1rem;
    }

    .btn-custom {
      transition: all 0.3s ease;
    }

    .btn-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .badge {
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
      border-radius: 20px;
    }

    /* استایل مودال */
    .modal-content {
      border-radius: 12px;
      box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
      border: none;
    }

    .modal-header {
      background: linear-gradient(135deg, #6f42c1, #5a32a3);
      border-radius: 12px 12px 0 0;
      border-bottom: none;
      padding: 1.5rem;
    }

    .modal-title {
      color: white;
      font-weight: 600;
    }

    .modal-body {
      padding: 2rem;
    }

    .form-group {
      position: relative;
      margin-bottom: 1.5rem;
    }

    .form-group label {
      font-weight: 500;
      color: #444;
      margin-bottom: 0.5rem;
      display: block;
    }

    .form-control,
    .form-control:focus {
      border-radius: 8px;
      border: 1px solid #ced4da;
      box-shadow: none;
      padding: 0.75rem;
      height: 45px;
    }

    textarea.form-control {
      height: 120px;
      resize: none;
    }

    .form-group.has-error .form-control {
      border-color: #dc3545;
    }

    .form-group.has-error .error-message {
      display: block;
      color: #dc3545;
      font-size: 0.85rem;
      margin-top: 0.25rem;
    }

    .error-message {
      display: none;
    }

    .modal-footer-btn {
      background: linear-gradient(135deg, #6f42c1, #5a32a3);
      border: none;
      border-radius: 8px;
      padding: 0.75rem 2rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .modal-footer-btn:hover {
      background: linear-gradient(135deg, #5a32a3, #6f42c1);
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
  </style>
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
          <button type="button" class="btn btn-light btn-custom h-50" data-toggle="modal"
            data-target="#add-ticket-modal">
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
                      <button class="btn btn-light rounded-circle btn-sm btn-custom delete-btn"
                        data-id="{{ $ticket->id }}">
                        <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                      </button>
                      <button onclick="location.href='{{ route('dr-panel-tickets.show', $ticket->id) }}'"
                        class="btn btn-light rounded-circle btn-sm btn-custom view-btn" data-id="{{ $ticket->id }}">
                        <img src="{{ asset('dr-assets/icons/eye.svg') }}" alt="مشاهده">
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
<div class="modal fade" id="add-ticket-modal" tabindex="-1" role="dialog" aria-labelledby="add-ticket-modal-label"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="add-ticket-modal-label">ایجاد تیکت جدید</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="add-ticket-form">
          @csrf
          <div class="form-group">
            <label for="title">عنوان تیکت</label>
            <input type="text" class="form-control" id="title" name="title"
              placeholder="عنوان تیکت را وارد کنید">
            <small class="error-message error-title"></small>
          </div>
          <div class="form-group">
            <label for="description">توضیحات</label>
            <textarea class="form-control" id="description" name="description" placeholder="توضیحات تیکت را وارد کنید"></textarea>
            <small class="error-message error-description"></small>
          </div>
          <button type="submit" class="btn modal-footer-btn w-100 d-flex justify-content-center align-items-center">
            <span class="button_text">ارسال تیکت</span>
            <div class="loader"></div>
          </button>
        </form>
      </div>
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
  $(document).ready(function() {
    // مدیریت بستن مودال
    $('#add-ticket-modal').on('hidden.bs.modal', function() {
      $('body').removeClass('modal-open');
      $('.modal-backdrop').remove();
      $('#add-ticket-form')[0].reset();
      $('.error-message').text('');
      $('.form-group').removeClass('has-error');
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
        url: "{{ route('dr-panel-tickets.store') }}",
        method: 'POST',
        data: form.serialize(),
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          toastr.success('تیکت با موفقیت اضافه شد!');
          $('#add-ticket-modal').modal('hide');
          updateTicketList(response.tickets);
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;
            Object.keys(errors).forEach(function(key) {
              form.find(`.error-${key}`).text(errors[key][0]);
              form.find(`.form-group:has(#${key})`).addClass('has-error');
            });
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
            url: "{{ route('dr-panel-tickets.destroy', ':id') }}".replace(':id', id),
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
                <button class="btn btn-light rounded-circle btn-sm btn-custom delete-btn" data-id="${ticket.id}">
                  <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                </button>
                <button onclick="window.location.href='${getShowRoute(ticket.id)}'"
                  class="btn btn-light rounded-circle btn-sm btn-custom view-btn">
                  <img src="{{ asset('dr-assets/icons/eye.svg') }}" alt="مشاهده">
                </button>
              </td>
            </tr>`;
          container.append(row);
        });
      }
    }

    function getShowRoute(ticketId) {
      return "{{ route('dr-panel-tickets.show', ':id') }}".replace(':id', ticketId);
    }
  });
</script>
@endsection
