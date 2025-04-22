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
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f0 100%);
      min-height: 100vh;
      padding: 20px;
    }

    .card {
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
      background: #ffffff;
      border: none;
      overflow: hidden;
    }

    .card-header {
      background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
      border-radius: 16px 16px 0 0;
      padding: 1.5rem;
      position: relative;
    }

    .card-header::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 100%;
      height: 10px;
      background: linear-gradient(to bottom, rgba(255, 255, 255, 0.1), transparent);
    }

    .card-body {
      padding: 2.5rem;
    }

    /* Table Styling */
    .table {
      border-radius: 10px;
      overflow: hidden;
      background: #fff;
    }

    .table th {
      color: #2c3e50;
      width: 25%;
      font-weight: 600;
      padding: 15px;
      border-bottom: 2px solid #e9ecef;
    }

    .table td {
      color: #34495e;
      padding: 15px;
      vertical-align: middle;
    }

    .badge {
      padding: 0.5em 1.2em;
      font-size: 0.9rem;
      border-radius: 50px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .badge:hover {
      transform: scale(1.05);
    }

    /* Response Section */
    .response-list {
      max-height: 450px;
      overflow-y: auto;
      padding: 1.5rem;
      background: #fafbfc;
      border-radius: 12px;
      border: 1px solid #ebedf0;
      scrollbar-width: thin;
      scrollbar-color: #a0a9b2 #fafbfc;
    }

    .response-card {
      max-width: 75%;
      margin-bottom: 1.5rem;
      padding: 1.2rem;
      border-radius: 16px;
      position: relative;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .response-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    }

    .response-card.manager {
      background: linear-gradient(135deg, #e9dfff 0%, #d8cfff 100%);
      color: #5e2b97;
      margin-right: auto;
      border-bottom-left-radius: 4px;
    }

    .response-card.doctor {
      background: linear-gradient(135deg, #e3f0ff 0%, #d6eaff 100%);
      color: #1e429f;
      margin-left: auto;
      border-bottom-right-radius: 4px;
      text-align: right;
    }

    .response-card strong {
      font-size: 0.95rem;
      font-weight: 600;
      margin-bottom: 0.6rem;
      display: block;
    }

    .response-card p {
      margin: 0;
      font-size: 1rem;
      line-height: 1.6;
    }

    .response-card small {
      margin-top: 0.6rem;
      font-size: 0.8rem;
      color: #7f8c8d;
      opacity: 0.8;
    }

    /* Form Styling */
    .form-group {
      margin-bottom: 1.8rem;
      position: relative;
    }

    .form-group label {
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 0.6rem;
      display: block;
    }

    .form-control {
      border-radius: 10px;
      border: 1px solid #dfe6e9;
      padding: 0.9rem 1.2rem;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: #fff;
    }

    textarea.form-control {
      height: 140px;
      resize: none;
    }

    .form-control:focus {
      border-color: #4a90e2;
      box-shadow: 0 0 8px rgba(74, 144, 226, 0.2);
      outline: none;
    }

    .form-group.has-error .form-control {
      border-color: #e74c3c;
    }

    .form-group.has-error .error-message {
      display: block;
      color: #e74c3c;
      font-size: 0.85rem;
      margin-top: 0.4rem;
    }

    .error-message {
      display: none;
    }

    .btn-custom {
      background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
      border: none;
      border-radius: 10px;
      padding: 0.9rem 2.5rem;
      font-weight: 600;
      transition: all 0.3s ease;
      color: white;
    }

    .btn-custom:hover {
      background: linear-gradient(135deg, #357abd 0%, #4a90e2 100%);
      transform: translateY(-3px);
      box-shadow: 0 6px 12px rgba(74, 144, 226, 0.3);
    }

    .btn-custom:disabled {
      background: #b0bec5;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .alert-warning {
      border-radius: 10px;
      background: #fef5e7;
      color: #d97706;
      padding: 1rem;
      border: 1px solid #fed7aa;
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
      <h5 class="text-dark fw-bold mb-4">اطلاعات تیکت</h5>

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

      <h5 class="mt-5 mb-3 text-dark fw-bold">پاسخ‌ها</h5>
      <div class="response-list">
        @forelse ($ticket->responses as $response)
          <div class="response-card {{ $response->manager_id ? 'manager' : 'doctor' }} p-3 mb-3">
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
          <div class="alert alert-info text-center">هیچ پاسخی برای این تیکت ثبت نشده است.</div>
        @endforelse
      </div>

      <form id="add-response-form" class="mt-5">
        @csrf
        <input type="hidden" id="ticket-id" value="{{ $ticket->id }}">
        <div class="form-group">
          <label for="response-message">ارسال پاسخ</label>
          <textarea class="form-control" id="response-message" placeholder="پاسخ خود را اینجا بنویسید..."></textarea>
          <small class="error-message"></small>
        </div>
        <button type="submit" class="btn btn-custom w-100 d-flex justify-content-center align-items-center"
          id="save-response" @if ($ticket->status == 'closed') disabled @endif>
          <span class="button_text">ارسال پاسخ</span>
          <div class="loader" style="display: none; margin-right: 10px;"></div>
        </button>
        @if ($ticket->status == 'closed')
          <div class="alert alert-warning mt-3 text-center">
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
    const responseList = $('.response-list')[0];
    responseList.scrollTop = responseList.scrollHeight;

    $('#add-response-form').on('submit', function(e) {
      e.preventDefault();
      const ticketId = $('#ticket-id').val();
      const message = $('#response-message').val().trim();
      const $button = $(this).find('#save-response');
      const $loader = $button.find('.loader');
      const $buttonText = $button.find('.button_text');

      if ($button.is(':disabled')) {
        toastr.warning('این تیکت بسته شده است و نمی‌توانید پاسخ ارسال کنید!');
        return;
      }

      if (!message) {
        $('.form-group').addClass('has-error');
        $('.error-message').text('لطفاً متن پاسخ را وارد کنید!');
        return;
      }

      $buttonText.hide();
      $loader.show();
      $('.form-group').removeClass('has-error');
      $('.error-message').text('');

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
            <div class="response-card doctor p-3 mb-3 animate__animated animate__fadeIn">
              <strong>${response.user}</strong>
              <p>${response.message}</p>
              <small class="text-muted">${response.created_at}</small>
            </div>
          `);
          toastr.success("پاسخ شما با موفقیت ارسال شد!");
          responseList.scrollTo({
            top: responseList.scrollHeight,
            behavior: 'smooth'
          });
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            $('.form-group').addClass('has-error');
            $('.error-message').text(xhr.responseJSON.errors.message[0]);
          } else {
            toastr.error("خطا در ارسال پاسخ!");
          }
        },
        complete: function() {
          $buttonText.show();
          $loader.hide();
        }
      });
    });
  });
</script>
@endsection
