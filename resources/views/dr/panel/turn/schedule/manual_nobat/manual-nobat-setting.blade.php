@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
{{--   <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/profile/subuser.css') }}" rel="stylesheet" /> --}}
  <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/scheduleSetting/workhours.css') }}"
    rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/appointments_open/appointments_open.css') }}"
    rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/turn/schedule/manual_nobat/manual_nobat.css') }}"
    rel="stylesheet" />
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
  <div class="manual-nobat-content-wrapper p-3">
    <div class="main-content">
      <div class="row no-gutters font-size-13 margin-bottom-10">
        <div class="user-panel-content w-100">
          <div class="p-3 w-100 d-flex justify-content-center">
            <div class="" style="width: 850px;height: 100%;">
              <div class="card-header"> تنظیمات تایید دو مرحله ای نوبت‌های دستی</div>
              <div class="card-body">
                <div class="alert alert-info">
                  <i class="fa fa-info-circle"></i>
                  <strong>راهنما!</strong>
                  <div>
                    در فیلد اول میتوانید مشخص کنید که چند ساعت قبل از زمان نوبت پیامک تایید نهایی نوبت ارسال شود و در
                    فیلد
                    دوم، می‌توانید مشخص کنید بیمار چند ساعت مهلت دارد نوبت خود را تایید کند، در غیر اینصورت نوبت لغو
                    خواهد
                    شد.<br>
                    در زیر با استفاده از گزینه بلی یا خیر میتوانید این امکان را فعال یا غیرفعال نمایید.
                  </div>
                </div>
                <form method="post" action="" autocomplete="off" id="save_verify_nobat">
                  @csrf
                  <div class="row">
                    <!-- تایید دو مرحله‌ای نوبت‌های دستی -->
                    <div class="col-md-6 col-sm-12 col-12">
                      <div class="mt-3 position-relative">
                        <label>آیا تایید دو مرحله‌ای نوبت‌های دستی فعال باشد؟</label>
                        <select class="form-control h-50" name="status">
                          <option value="0" {{ isset($settings) && $settings->is_active == 0 ? 'selected' : '' }}>
                            خیر</option>
                          <option value="1" {{ isset($settings) && $settings->is_active == 1 ? 'selected' : '' }}>
                            بلی</option>
                        </select>
                      </div>
                    </div>

                    <!-- زمان ارسال لینک تایید -->
                    <div class="col-md-6 col-sm-12 col-12 stmvd" style="display: block">
                      <div class="mt-3 position-relative">
                        <label class="label-top-input-special-takhasos">زمان ارسال لینک تایید:</label>
                        <div class="input-group">
                          <input class="form-control ltr center h-50 border-radius-0" type="tel"
                            value="{{ isset($settings) ? $settings->duration_send_link : '' }}"
                            name="duration_send_link" placeholder="مثلا: 72">
                          <div class="input-group-append">
                            <span class="input-group-text">ساعت قبل</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- مدت زمان اعتبار لینک -->
                    <div class="col-md-6 col-sm-12 col-12 stmvd" style="display: block">
                      <div class="mt-3 position-relative">
                        <label class="label-top-input-special-takhasos">مدت زمان اعتبار لینک:</label>
                        <div class="input-group">
                          <input class="form-control ltr center h-50 border-radius-0" type="tel"
                            value="{{ isset($settings) ? $settings->duration_confirm_link : '' }}"
                            name="duration_confirm_link" placeholder="مثلا: 48">
                          <div class="input-group-append">
                            <span class="input-group-text">ساعت</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- دکمه ذخیره -->
                  <div class="mt-3 position-relative p-3">
                    <button type="submit"
                      class="w-100 btn btn-primary h-50 border-radius-4 d-flex justify-content-center align-items-center">
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
    let dropdownOpen = false;
    let selectedClinic = localStorage.getItem('selectedClinic');
    let selectedClinicId = localStorage.getItem('selectedClinicId');
    if (selectedClinic && selectedClinicId) {
      $('.dropdown-label').text(selectedClinic);
      $('.option-card').each(function() {
        if ($(this).attr('data-id') === selectedClinicId) {
          $('.option-card').removeClass('card-active');
          $(this).addClass('card-active');
        }
      });
    } else {
      localStorage.setItem('selectedClinic', 'ویزیت آنلاین به نوبه');
      localStorage.setItem('selectedClinicId', 'default');
    }

    function checkInactiveClinics() {
      var hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
      if (hasInactiveClinics) {
        $('.dropdown-trigger').addClass('warning');
      } else {
        $('.dropdown-trigger').removeClass('warning');
      }
    }
    checkInactiveClinics();

    $('.dropdown-trigger').on('click', function(event) {
      event.stopPropagation();
      dropdownOpen = !dropdownOpen;
      $(this).toggleClass('border border-primary');
      $('.my-dropdown-menu').toggleClass('d-none');
      setTimeout(() => {
        dropdownOpen = $('.my-dropdown-menu').is(':visible');
      }, 100);
    });

    $(document).on('click', function() {
      if (dropdownOpen) {
        $('.dropdown-trigger').removeClass('border border-primary');
        $('.my-dropdown-menu').addClass('d-none');
        dropdownOpen = false;
      }
    });

    $('.option-card').on('click', function() {
      var selectedText = $(this).find('.font-weight-bold.d-block.fs-15').text().trim();
      var selectedId = $(this).attr('data-id');
      $('.option-card').removeClass('card-active');
      $(this).addClass('card-active');
      $('.dropdown-label').text(selectedText);

      localStorage.setItem('selectedClinic', selectedText);
      localStorage.setItem('selectedClinicId', selectedId);
      checkInactiveClinics();
      $('.dropdown-trigger').removeClass('border border-primary');
      $('.my-dropdown-menu').addClass('d-none');
      dropdownOpen = false;

      // ریلود صفحه با پارامتر جدید
      window.location.href = window.location.pathname + "?selectedClinicId=" + selectedId;
    });
  });
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
            toastr.error(errorMessage, 'خطا', {
              closeButton: true,
              timeOut: 5000
            });
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
          toastr.error(errorMessage, 'خطا', {
            closeButton: true,
            timeOut: 5000
          });
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
