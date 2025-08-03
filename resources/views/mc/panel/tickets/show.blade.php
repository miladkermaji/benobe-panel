@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/tickets/tickets.css') }}" rel="stylesheet" />

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
      <a href="{{ route('mc-panel-tickets') }}" class="btn btn-light btn-sm btn-custom">
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
        <!-- نمایش پیام اصلی تیکت به‌عنوان اولین پاسخ -->
        <div class="response-card doctor p-3 mb-3">
          <strong>دکتر: {{ $ticket->doctor->first_name ?? 'نامشخص' }} {{ $ticket->doctor->last_name ?? '' }}</strong>
          <p>{{ $ticket->description }}</p>
          <small class="text-muted">{{ \Morilog\Jalali\Jalalian::forge($ticket->created_at)->ago() }}</small>
        </div>

        <!-- نمایش پاسخ‌های بعدی -->
        @forelse ($ticket->responses as $response)
          <div class="response-card {{ $response->manager_id ? 'manager' : 'doctor' }} p-3 mb-3">
            <strong>
              @if ($response->manager_id)
                {{ $response->manager ? 'مدیر: ' . $response->manager->first_name . ' ' . $response->manager->last_name : 'مدیر (نامشخص)' }}
              @else
                @if ($response->doctor_id)
                  {{ $response->doctor ? 'دکتر: ' . $response->doctor->first_name . ' ' . $response->doctor->last_name : 'دکتر (نامشخص)' }}
                @elseif($response->secretary_id)
                  {{ $response->secretary ? 'منشی: ' . $response->secretary->first_name . ' ' . $response->secretary->last_name : 'منشی (نامشخص)' }}
                @endif
              @endif
            </strong>
            <p>{{ $response->message }}</p>
            <small class="text-muted">
              {{ \Morilog\Jalali\Jalalian::forge($response->created_at)->ago() }}
            </small>
          </div>
        @empty
          <div class="alert alert-info text-center">هیچ پاسخ دیگری برای این تیکت ثبت نشده است.</div>
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
<script src="{{ asset('mc-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
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
        url: "{{ route('mc-panel-tickets.responses.store', ':id') }}".replace(':id', ticketId),
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
