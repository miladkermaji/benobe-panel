@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/tickets/tickets.css') }}" rel="stylesheet" />
  <style>
    .myPanelOption {
      display: none;
    }
  </style>
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', ' مشاهده پاسخ تیکت ')
@section('content')
  <div class="container mt-4">
    <div class="card shadow border-0">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">جزئیات تیکت #{{ $ticket->id }}</h5>
        <a href="{{ route('dr-panel-tickets') }}" class="btn btn-light btn-sm">
          بازگشت
        </a>
      </div>

      <div class="card-body border-radius-4">
        <h5 class="text-dark font-weight-bold">اطلاعات تیکت</h5>

        <!-- جدول نمایش اطلاعات تیکت -->
        <div class="table-responsive mt-3">
          <table class="table table-bordered">
            <tbody>
              <tr>
                <th class="bg-light">عنوان تیکت</th>
                <td>{{ $ticket->title }}</td>
              </tr>
              <tr>
                <th class="bg-light">متن تیکت</th>
                <td>{{ $ticket->description }}</td>
              </tr>
              <tr>
                <th class="bg-light">وضعیت</th>
                <td>
                  <span
                    class="badge badge-lg   
            @if ($ticket->status == 'open') badge-success   
            @elseif ($ticket->status == 'pending') badge-warning   
           @elseif ($ticket->status == 'closed') badge-danger  
           @elseif ($ticket->status == 'answered') badge-info   
          @else badge-secondary @endif">
                    @if ($ticket->status == 'open')
                      باز
                    @elseif ($ticket->status == 'pending')
                      در انتظار پا
                      سخ
                    @elseif ($ticket->status == 'closed')
                      بسته ش
                      ده
                    @elseif ($ticket->status == 'answered')
                      پاسخ داده ش
                      ده
                    @else
                      نامشخص
                    @endif
                  </span>
                </td>
              </tr>
              <tr>
                <th class="bg-light">تاریخ ایجاد</th>
                <td>{{ \Morilog\Jalali\Jalalian::forge($ticket->created_at)->format('Y/m/d - H:i') }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <h5 class="mt-4">پاسخ‌ها</h5>
        <div class="response-list mt-3">
          @forelse ($ticket->responses as $response)
            <div class="response-card p-3 border rounded mb-2 bg-light">
              <strong>
                {{ $response->doctor ? 'دکتر ' . $response->doctor->first_name . ' ' . $response->doctor->last_name : 'نامشخص' }}
              </strong>
              <p class="mb-1">{{ $response->message }}</p>
              <small class="text-muted">
                {{ \Morilog\Jalali\Jalalian::forge($response->created_at)->ago() }}
              </small>
            </div>
          @empty
            <div class="alert alert-info mt-3">هیچ پاسخی برای این تیکت ثبت نشده است.</div>
          @endforelse

        </div>


        <!-- فرم ارسال پاسخ -->
        <form id="add-response-form" class="mt-3">
          @csrf
          <input type="hidden" id="ticket-id" value="{{ $ticket->id }}">
          <div class="form-group">
            <label for="response-message">ارسال پاسخ</label>
            <textarea class="form-control" id="response-message" rows="3" placeholder="پاسخ خود را وارد کنید"></textarea>
          </div>

          <button type="submit" class="btn btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
            id="save-response" @if ($ticket->status == 'closed') disabled @endif>
            <span class="button_text">پاسخ تیکت</span>
            <div class="loader"></div>
          </button>

          <!-- پیام هشدار در صورت بسته بودن تیکت -->
          @if ($ticket->status == 'closed')
            <div class="alert alert-warning mt-2">
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
    var updateStatusAppointmentUrl =
      "{{ route('updateStatusAppointment', ':id') }}";
  </script>
  <script>
    $(document).ready(function() {
      $('#add-response-form').on('submit', function(e) {
        e.preventDefault();
        let ticketId = $('#ticket-id').val();
        let message = $('#response-message').val();
        let button = $(this).find('button');
        let loader = button.find('.loader');
        const buttonText = button.find('.button_text');

        // بررسی وضعیت تیکت و جلوگیری از ارسال درخواست
        if (button.is(':disabled')) {
          toastr.warning('این تیکت بسته شده است و نمی‌توانید پاسخ ارسال کنید!');
          return;
        }

        // نمایش لودر
        buttonText.hide();
        loader.show();

        // بررسی مقدار خالی و نمایش خطا
        if (message.trim() === '') {
          toastr.error('لطفاً متن پاسخ را وارد کنید!');
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
                    <div class="response-card p-3 border rounded mb-2 bg-light">
                        <strong>${response.user}</strong>
                        <p class="mb-1">${response.message}</p>
                        <small class="text-muted">${response.created_at}</small>
                    </div>
                `);
            toastr.success("پاسخ شما ارسال شد!");
          },
          error: function() {
            toastr.error("خطا در ارسال پاسخ!");
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
