@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/tickets/tickets.css') }}" rel="stylesheet" />
  <style>
    .myPanelOption {
      display: none;
    }

    .container {
      background: #f8f9fa;
      min-height: 100vh;
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      background: #ffffff;
    }

    .card-header {
      background: linear-gradient(135deg, #007bff, #0056b3);
      border-radius: 12px 12px 0 0;
      padding: 1.5rem;
    }

    .card-body {
      padding: 2rem;
    }

    .table th {
      background: #f1f3f5;
      color: #333;
      width: 25%;
      font-weight: 500;
    }

    .table td {
      color: #555;
    }

    .badge {
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
      border-radius: 20px;
    }

    /* استایل مسنجر */
    .response-list {
      max-height: 400px;
      overflow-y: auto;
      padding: 1rem;
      background: #f9f9f9;
      border-radius: 8px;
      border: 1px solid #e9ecef;
    }

    .response-card {
      max-width: 70%;
      margin-bottom: 1.5rem;
      padding: 1rem;
      border-radius: 12px;
      position: relative;
      transition: all 0.3s ease;
    }

    .response-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    /* پاسخ‌های مدیر (سمت چپ، بنفش روشن) */
    .response-card.manager {
      background: #e8d9ff;
      color: #4b0082;
      margin-right: auto;
      border-bottom-left-radius: 0;
    }

    /* پاسخ‌های دکتر (سمت راست، آبی روشن) */
    .response-card.doctor {
      background: #d6eaff;
      color: #004085;
      margin-left: auto;
      border-bottom-right-radius: 0;
      text-align: right;
    }

    .response-card strong {
      display: block;
      font-size: 0.95rem;
      margin-bottom: 0.5rem;
    }

    .response-card p {
      margin: 0;
      font-size: 1rem;
      line-height: 1.5;
    }

    .response-card small {
      display: block;
      margin-top: 0.5rem;
      font-size: 0.8rem;
      color: #888;
    }

    /* فرم ارسال پاسخ */
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

    .btn-custom {
      background: linear-gradient(135deg, #007bff, #0056b3);
      border: none;
      border-radius: 8px;
      padding: 0.75rem 2rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-custom:hover {
      background: linear-gradient(135deg, #0056b3, #007bff);
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .alert-warning {
      border-radius: 8px;
      background: #fff3cd;
      color: #856404;
    }
  </style>
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مشاهده تیکت')

<div class="container mt-4">
  <div class="card shadow border-0">
    <div class="card-header text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">جزئیات تیکت #{{ $ticket->id }}</h5>
      <a href="{{ route('dr-panel-tickets') }}" class="btn btn-light btn-sm btn-custom">
        <i class="fas fa-arrow-right mr-2"></i> بازگشت
      </a>
    </div>

    <div class="card-body">
      <h5 class="text-dark font-weight-bold mb-4">اطلاعات تیکت</h5>

      <!-- جدول اطلاعات تیکت -->
      <div class="table-responsive">
        <table class="table table-bordered">
          <tbody>
            <tr>
              <th>عنوان تیکت</th>
              <td>{{ $ticket->title }}</td>
            </tr>
            <tr>
              <th>توضیحات</th>
              <td>{{ $ticket->description }}</td>
            </tr>
            <tr>
              <th>وضعیت</th>
              <td>
                <span
                  class="badge 
                  @if ($ticket->status == 'open') badge-success
                  @elseif ($ticket->status == 'pending') badge-warning
                  @elseif ($ticket->status == 'closed') badge-danger
                  @elseif ($ticket->status == 'answered') badge-info
                  @else badge-secondary @endif">
                  @if ($ticket->status == 'open')
                    باز
                  @elseif ($ticket->status == 'pending')
                    در حال بررسی
                  @elseif ($ticket->status == 'closed')
                    بسته
                  @elseif ($ticket->status == 'answered')
                    پاسخ داده شده
                  @else
                    نامشخص
                  @endif
                </span>
              </td>
            </tr>
            <tr>
              <th>تاریخ ایجاد</th>
              <td>{{ \Morilog\Jalali\Jalalian::forge($ticket->created_at)->format('Y/m/d - H:i') }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <h5 class="mt-4 mb-3">پاسخ‌ها</h5>
      <div class="response-list">
        @forelse ($ticket->responses as $response)
          <div class="response-card {{ $response->manager_id ? 'manager' : 'doctor' }} p-3 border mb-3">
            <strong>
              @if ($response->manager_id)
                {{ $response->manager ? 'مدیر: ' . $response->manager->first_name . ' ' . $response->manager->last_name : 'مدیر (نامشخص)' }}
              @else
                {{ $response->doctor ? 'دکتر: ' . $response->doctor->first_name . ' ' . $response->doctor->last_name : 'دکتر (نامشخص)' }}
              @endif
            </strong>
            <p>{{ $response->message }}</p>
            <small class="text-muted">
              {{ \Morilog\Jalali\Jalalian::forge($response->created_at)->ago() }}
            </small>
          </div>
        @empty
          <div class="alert alert-info">هیچ پاسخی برای این تیکت ثبت نشده است.</div>
        @endforelse
      </div>

      <!-- فرم ارسال پاسخ -->
      <form id="add-response-form" class="mt-4">
        @csrf
        <input type="hidden" id="ticket-id" value="{{ $ticket->id }}">
        <div class="form-group">
          <label for="response-message" class="font-weight-bold">ارسال پاسخ</label>
          <textarea class="form-control" id="response-message" placeholder="پاسخ خود را وارد کنید"></textarea>
          <small class="error-message error-message"></small>
        </div>
        <button type="submit" class="btn btn-custom w-100 d-flex justify-content-center align-items-center"
          id="save-response" @if ($ticket->status == 'closed') disabled @endif>
          <span class="button_text">ارسال پاسخ</span>
          <div class="loader"></div>
        </button>
        @if ($ticket->status == 'closed')
          <div class="alert alert-warning mt-3">
            این تیکت بسته شده است و امکان ارسال پاسخ وجود ندارد.
          </div>
        @endif
      </form>
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
    // اسکرول خودکار به پایین لیست پاسخ‌ها
    const responseList = document.querySelector('.response-list');
    responseList.scrollTop = responseList.scrollHeight;

    $('#add-response-form').on('submit', function(e) {
      e.preventDefault();
      let ticketId = $('#ticket-id').val();
      let message = $('#response-message').val();
      let button = $(this).find('button');
      let loader = button.find('.loader');
      const buttonText = button.find('.button_text');

      if (button.is(':disabled')) {
        toastr.warning('این تیکت بسته شده است و نمی‌توانید پاسخ ارسال کنید!');
        return;
      }

      buttonText.hide();
      loader.show();
      $('.error-message').text('');
      $('.form-group').removeClass('has-error');

      if (message.trim() === '') {
        $('.error-message').text('لطفاً متن پاسخ را وارد کنید!');
        $('.form-group').addClass('has-error');
        buttonText.show();
        loader.hide();
        return;
      }

      $.ajax({
        url: "{{ route('dr-panel-tickets.responses.store', ':id') }}".replace(':id', ticketId),
        method: "POST",
        data: {
          _token: "{{ csrf_token() }}",
          message: message
        },
        success: function(response) {
          $('#response-message').val('');
          $('.response-list').append(`
            <div class="response-card doctor p-3 border rounded mb-3">
              <strong>${response.user}</strong>
              <p>${response.message}</p>
              <small class="text-muted">${response.created_at}</small>
            </div>
          `);
          toastr.success("پاسخ شما با موفقیت ارسال شد!");
          // اسکرول به پایین بعد از اضافه کردن پاسخ
          responseList.scrollTop = responseList.scrollHeight;
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            $('.error-message').text(xhr.responseJSON.errors.message[0]);
            $('.form-group').addClass('has-error');
          } else {
            toastr.error("خطا در ارسال پاسخ!");
          }
        },
        complete: function() {
          buttonText.show();
          loader.hide();
        }
      });
    });
  });
</script>
@endsection
