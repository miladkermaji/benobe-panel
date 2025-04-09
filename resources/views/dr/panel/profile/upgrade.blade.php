@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/scheduleSetting/scheduleSetting.css') }}"
    rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />

  <link type="text/css" href="{{ asset('dr-assets/panel/bime/bime.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/profile/upgrade.css') }}" rel="stylesheet" />
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
@section('bread-crumb-title', 'ارتقا حساب کاربری')

<div class="main-content">
  <div class="container-fluid">
    <div class="user-panel-content">
      <div>
        <div class="card-header">
          <span>ارتقاء حساب کاربری</span>
        </div>
        <div class="card-body">
          <div class="alert bg-light-blue">
            <strong>
              <i class="mdi mdi-information"></i> ارتقاء حساب کاربری!
            </strong>
            <p>
              پزشک محترم سلام<br>
              با ارتقا حساب کاربری خود می‌توانید از خدمات ویژه سایت جهت ارائه خدمات بیشتر به کاربران به شرح زیر بهره‌مند
              گردید:
            </p>
            <ul>
              <li>دریافت تیک آبی و نمایش در لیست پزشکان تایید شده</li>
              <li>نمایش در بالای نتایج جستجو</li>
              <li>نمایش در لیست پزشکان برتر در صفحه اصلی سایت</li>
              <li>نمایش در لیست پزشکان پیشنهادی در صفحات داخلی</li>
            </ul>
          </div>
          <div class="tarifms">
            <i class="fa fa-calendar"></i>
            <strong>مدت زمان نمایش:</strong> 90 روز
          </div>
          <div class="tarifms">
            <i class="mdi mdi-phone"></i>
            <strong>هزینه:</strong> 780,000 تومان
          </div>
          <div class="clrfix mb-3" style="float: right; width: 100%; margin-top: 20px">
            <form action="{{ route('doctor.upgrade.pay') }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-primary text-white h-50 mt-3">
                <i class="fa fa-credit-card"></i> پرداخت و ارتقاء حساب کاربری
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- جدول -->
      <div class="table-responsive w-100">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>ردیف</th>
              <th>تاریخ پرداخت</th>
              <th>کد پیگیری</th>
              <th>تاریخ انقضاء</th>
              <th>مبلغ</th>
              <th>تعداد روز</th>
              <th>حذف</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($payments as $index => $payment)
              <tr id="payment-row-{{ $payment->id }}">
                <td>{{ $loop->iteration }}</td>
                <td>
                  @if (!empty($payment->created_at))
                    {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($payment->created_at))->format('Y/m/d') }}
                  @else
                    <span class="text-danger">نامشخص</span>
                  @endif
                </td>
                <td>{{ $payment->payment_reference }}</td>
                <td>
                  @if (!empty($payment->expires_at))
                    {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($payment->expires_at))->format('Y/m/d') }}
                  @else
                    <span class="text-danger">نامشخص</span>
                  @endif
                </td>
                <td>{{ number_format($payment->amount) }} تومان</td>
                <td>{{ $payment->days }}</td>
                <td>
                  <button class="btn btn-light btn-sm delete-payment" data-id="{{ $payment->id }}">
                    <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="">
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center">موردی ثبت نشده است.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- پیجینیشن -->
      <div class="d-flex justify-content-center mt-3">
        {{ $payments->links('pagination::bootstrap-4') }}
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/bime/bime.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
  $(function() {
    $('.card-body').css({
      'width': '100%',
      'height': 'auto' /* حذف ارتفاع ثابت */
    });
  });
</script>
<script>
  $(document).ready(function() {
    // حذف پرداخت با تأیید SweetAlert و پیام موفقیت Toastr
    $(document).on('click', '.delete-payment', function() {
      let paymentId = $(this).data('id');
      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: "این عملیات قابل بازگشت نیست!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'بله، حذف شود!',
        cancelButtonText: 'لغو'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('dr-payment-delete', ':id') }}".replace(':id', paymentId),
            type: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              if (response.success) {
                // حذف ردیف از جدول
                $('#payment-row-' + paymentId).fadeOut(500, function() {
                  $(this).remove();
                });
                toastr.success(response.message);
              } else {
                toastr.error('خطا در حذف پرداخت!');
              }
            },
            error: function() {
              toastr.error('خطایی رخ داد. لطفاً دوباره تلاش کنید.');
            }
          });
        }
      });
    });
  });
</script>
@endsection
