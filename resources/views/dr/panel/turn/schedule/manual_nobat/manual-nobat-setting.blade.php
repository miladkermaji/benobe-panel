@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/turn/schedule/manual_nobat/manual_nobat_setting.css') }}"
    rel="stylesheet" />

@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', ' تنظیمات نوبت دستی')
@include('dr.panel.my-tools.loader-btn')
<div class="manual-nobat-content w-100 d-flex justify-content-center mt-3">
  <div class="manual-nobat-content-wrapper">
    <div class="main-content">
      <div class="row g-0 font-size-13">
        <div class="user-panel-content w-100">
          <div class="p-3 w-100 d-flex justify-content-center">
            <div class="w-100" style="max-width: 850px;">
              <div class="card-header"> تنظیمات تایید دو مرحله ای نوبت‌های دستی</div>
              <div class="card-body">
                <div class="alert alert-info">
                  <i class="fa fa-info-circle"></i>
                  <strong>راهنما!</strong>
                  <div>
                    در فیلد اول می‌توانید مشخص کنید که چند ساعت قبل از زمان نوبت پیامک تأیید نهایی نوبت ارسال شود و در
                    فیلد دوم، می‌توانید مشخص کنید بیمار چند ساعت مهلت دارد نوبت خود را تأیید کند، در غیر این صورت نوبت
                    لغو خواهد شد.<br>
                    در زیر با استفاده از گزینه بلی یا خیر می‌توانید این امکان را فعال یا غیرفعال نمایید.
                  </div>
                </div>
                <form method="post" action="" autocomplete="off" id="save_verify_nobat">
                  @csrf
                  <div class="row">
                    <!-- تأیید دو مرحله‌ای نوبت‌های دستی -->
                    <div class="col-md-6 col-12">
                      <div class="mb-3 position-relative">
                        <label class="form-label">تأیید دو مرحله‌ای نوبت‌های دستی
                        </label>
                        <select class="form-control" name="status">
                          <option value="0" {{ isset($settings) && $settings->is_active == 0 ? 'selected' : '' }}>
                            خیر</option>
                          <option value="1" {{ isset($settings) && $settings->is_active == 1 ? 'selected' : '' }}>
                            بلی</option>
                        </select>
                      </div>
                    </div>

                    <!-- زمان ارسال لینک تأیید -->
                    <div class="col-md-6 col-12">
                      <div class="mb-3 position-relative">
                        <label class="label-top-input">زمان ارسال لینک تأیید:</label>
                        <div class="input-group">
                          <input class="form-control ltr text-center" type="tel"
                            value="{{ isset($settings) ? $settings->duration_send_link : '' }}"
                            name="duration_send_link" placeholder="مثلا: 72">
                          <span class="input-group-text">ساعت قبل</span>
                        </div>
                      </div>
                    </div>

                    <!-- مدت زمان اعتبار لینک -->
                    <div class="col-md-6 col-12">
                      <div class="mb-3 position-relative">
                        <label class="label-top-input">مدت زمان اعتبار لینک:</label>
                        <div class="input-group">
                          <input class="form-control ltr text-center" type="tel"
                            value="{{ isset($settings) ? $settings->duration_confirm_link : '' }}"
                            name="duration_confirm_link" placeholder="مثلا: 48">
                          <span class="input-group-text">ساعت</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- دکمه ذخیره -->
                  <div class="mt-3">
                    <button type="submit"
                      class="w-100 btn btn-primary d-flex justify-content-center align-items-center gap-2 h-50">
                      <span class="button_text">ذخیره تغییرات</span>
                      <div class="loader" style="display: none;"></div>
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl =
    "{{ route('updateStatusAppointment', ':id') }}";
</script>
<script>
  $(document).ready(function() {
    $('#save_verify_nobat').on('submit', function(e) {
      e.preventDefault();

      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const buttonText = submitButton.find('.button_text'); // متن دکمه
      const loader = form.find('.loader'); // لودر دکمه
      const selectedClinicId = localStorage.getItem('selectedClinicId');

      // نمایش لودینگ
      submitButton.prop('disabled', true);
      buttonText.hide();
      loader.show();

      $.ajax({
        url: "{{ route('manual-nobat.settings.save') }}",
        method: 'POST',
        data: form.serialize() + '&selectedClinicId=' + selectedClinicId,
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
          if (response.success) {
            toastr.success(response.message);
            // می‌تونید اینجا مقادیر فرم رو با داده‌های برگشتی آپدیت کنید
            console.log('داده‌های ذخیره‌شده:', response.data);
          } else {
            // نمایش خطاها
            let errorMessage = response.message;
            if (response.errors && response.errors.length > 0) {
              errorMessage += '<ul>';
              response.errors.forEach(error => {
                errorMessage += `<li>${error}</li>`;
              });
              errorMessage += '</ul>';
            }
            toastr.error(errorMessage);
          }
        },
        error: function(xhr) {
          let errorMessage = 'خطا در ارتباط با سرور!';
          if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            errorMessage += '<ul>';
            xhr.responseJSON.errors.forEach(error => {
              errorMessage += `<li>${error}</li>`;
            });
            errorMessage += '</ul>';
          } else if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = `${xhr.responseJSON.message}<br>جزئیات: ${xhr.responseJSON.error.details}`;
          }
          toastr.error(errorMessage);
        },
        complete: function() {
          // بازگرداندن حالت اولیه دکمه
          submitButton.prop('disabled', false);
          buttonText.show();
          loader.hide();
        },
      });
    });
  });
</script>
@endsection
