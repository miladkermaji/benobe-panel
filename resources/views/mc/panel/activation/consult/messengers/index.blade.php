@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/turn/schedule/scheduleSetting/scheduleSetting.css') }}"
    rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/turn/schedule/scheduleSetting/workhours.css') }}"
    rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/activation/consult/rules/index.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'پیامرسان')

@php
  // Get messenger data for each type
  $itaMessenger = $messengers->where('messenger_type', 'ita')->first();
  $telegramMessenger = $messengers->where('messenger_type', 'telegram')->first();
  $whatsappMessenger = $messengers->where('messenger_type', 'whatsapp')->first();
  $instagramMessenger = $messengers->where('messenger_type', 'instagram')->first();
@endphp

<div class="workhours-content w-100 d-flex justify-content-center mt-4">
  <div class="workhours-wrapper-content consult-wrapper p-3">
    <div class="">
      <div class="messangers-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12 col-md-12 col-lg-12" id="messengers-section"
          style="overflow:hidden; position:relative;">
          <div class="d-flex justify-content-between align-items-center">
            <div>

              <img src="{{ asset('mc-assets/icons/message.svg') }}" alt="" srcset="">

              <span class="txt-card-span mx-1"> پیام رسان ها</span>
            </div>
            <div>
              <img src="{{ asset('mc-assets/icons/caret.svg') }}" alt="" srcset="">
            </div>
          </div>
          <div class=" messangers-data-drop-toggle">
            <div class="loading-spinner d-none"></div>
            <div>
              <div class="alert alert-warning mt-2 text-center">
                <span class="text-sm fw-bold d-block font-size-15">لطفا شماره و نام کاربری پیام رسان ایتا، تلگرام یا نام
                  کاربری اینستاگرام خود را وارد کنید (اختیاری).</span>
                <span class="font-size-15 mt-1">اطلاعات پیام‌رسان‌ها در صورت نیاز در دسترس بیمار قرار می‌گیرد.</span>
              </div>

              @if ($messengers->count() == 0)
                <div class="alert alert-info mt-2 text-center">
                  <span class="text-sm fw-bold d-block font-size-15">هیچ اطلاعات پیام‌رسانی برای این پزشک ثبت نشده
                    است.</span>
                  <span class="font-size-15 mt-1">لطفاً اطلاعات پیام‌رسان‌ها را وارد کنید.</span>
                </div>
              @endif
              <form id="messengersForm">
                @csrf
                @method('PUT')
                <div>
                  <h6 class="text-right fw-bold d-block font-size-13">پیام رسان های داخلی</h6>
                </div>
                <div class="d-flex align-items-center justify-content-start gap-20">
                  <div
                    class="d-flex justify-content-start gap-1 align-items-center  border border-solid py-2 px-4 rounded-lg">
                    <img src="{{ asset('mc-assets/icons/eitaa-icon-colorful.svg') }}" alt=""><span
                      class="text-sm mx-1">ایتا</span>
                  </div>
                  <div class="w-100">
                    <div class="w-100">
                      <input type="text" name="ita_phone" class="form-control h-50 border-radius-4"
                        placeholder="شماره موبایل" maxlength="11" value="{{ $itaMessenger->phone_number ?? '' }}">
                    </div>
                    <div class="mt-2 w-100">
                      <input type="text" name="ita_username" class="form-control h-50 border-radius-4 mt-2"
                        placeholder="نام کاربری ایتا" value="{{ $itaMessenger->username ?? '' }}">
                    </div>
                  </div>
                </div>
                <div class="mt-2">
                  <h6 class="text-right fw-bold d-block font-size-13">پیام رسان های خارجی</h6>
                </div>
                <div class="d-flex align-items-center justify-content-start gap-20 mt-2">
                  <div
                    class="d-flex justify-content-center gap-1 align-items-center border border-solid py-2 px-3 rounded-lg">
                    <img src="{{ asset('mc-assets/icons/telegram.svg') }}" alt="">
                    <span class="text-sm mx-1 font-size-13">تلگرام</span>
                  </div>
                  <div class="w-100">
                    <div class="w-100">
                      <input type="text" name="telegram_phone" class="form-control h-50 border-radius-4 col-12"
                        placeholder="شماره موبایل (اختیاری)" maxlength="11"
                        value="{{ $telegramMessenger->phone_number ?? '' }}">
                    </div>
                    <div class="mt-2 w-100">
                      <input type="text" name="telegram_username" class="form-control h-50 border-radius-4 mt-2"
                        placeholder="نام کاربری تلگرام (اختیاری)" value="{{ $telegramMessenger->username ?? '' }}">
                    </div>
                  </div>
                </div>
                <div class="d-flex align-items-center justify-content-start gap-20 mt-2">
                  <div
                    class="d-flex justify-content-center gap-1 align-items-center border border-solid py-2 px-3 rounded-lg">
                    <img src="{{ asset('mc-assets/icons/whatsapp-svgrepo-com.svg') }}" alt="">
                    <span class="text-sm mx-1 font-size-13">واتساپ</span>
                  </div>
                  <div class="w-100">
                    <div class="w-100">
                      <input type="text" name="whatsapp_phone" class="form-control h-50 border-radius-4 col-12"
                        placeholder="شماره موبایل (اختیاری)" maxlength="11"
                        value="{{ $whatsappMessenger->phone_number ?? '' }}">
                    </div>
                  </div>
                </div>
                <div class="d-flex align-items-center justify-content-start gap-20 mt-2">
                  <div
                    class="d-flex justify-content-center gap-1 align-items-center border border-solid py-2 px-3 rounded-lg">
                    <img src="{{ asset('mc-assets/icons/instagram.svg') }}" alt="">
                    <span class="text-sm mx-1 font-size-13">اینستاگرام</span>
                  </div>
                  <div class="w-100">
                    <div class="w-100">
                      <input type="text" name="instagram_username"
                        class="form-control h-50 border-radius-4 col-12" placeholder="نام کاربری اینستاگرام (اختیاری)"
                        value="{{ $instagramMessenger->username ?? '' }}">
                    </div>
                  </div>
                </div>
                <div class="mt-2">
                  <h6 class="text-right fw-bold d-block font-size-13"> تماس امن</h6>
                </div>
                <div
                  class="d-flex gap-4 justify-content-between align-items-center p-3 border border-solid rounded-lg border-slate-200 mt-2">
                  <div>
                    <span class="text-responsive font-size-13 fw-bold">تماس امن به عنوان راه ارتباط جانبی در کنار
                      هر یک از
                      پیام‌رسان‌ها قرار می‌گیرد.</span>
                    <img src="{{ asset('mc-assets/icons/help.svg') }}" alt="">

                  </div>
                  <div class="flex flex-col gap-2">
                    <div class="flex items-center rounded-lg elative MuiBox-root muirtl-0">
                      <div class="password_toggle__AXK9v">
                        <input type="checkbox" id="secure_call" name="secure_call" value="1"
                          {{ ($itaMessenger->is_secure_call ?? false) || ($telegramMessenger->is_secure_call ?? false) ? 'checked' : '' }}>
                        <label for="secure_call">Toggle</label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mt-3">
                  <button type="submit"
                    class="btn my-btn-primary w-100 h-50 border-radius-4 d-flex justify-content-center align-items-center">
                    <span class="button_text">ثبت تغییرات</span>
                    <div class="loader"></div>
                  </button>
                </div>
              </form>
              <div>
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
<script src="{{ asset('mc-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl =
    "{{ route('updateStatusAppointment', ':id') }}";
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('messengersForm');
    const submitButton = form.querySelector('button[type="submit"]');
    const buttonText = submitButton.querySelector('.button_text');
    const loader = submitButton.querySelector('.loader');
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      // نمایش لودینگ و مخفی کردن متن دکمه
      buttonText.style.display = 'none';
      loader.style.display = 'block';
      // ارسال درخواست Ajax
      fetch("{{ route('mc-messengers-update') }}", {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            ita_phone: form.querySelector('input[name="ita_phone"]').value,
            ita_username: form.querySelector('input[name="ita_username"]').value,
            telegram_phone: form.querySelector('input[name="telegram_phone"]').value,
            telegram_username: form.querySelector('input[name="telegram_username"]').value,
            whatsapp_phone: form.querySelector('input[name="whatsapp_phone"]').value,
            instagram_username: form.querySelector('input[name="instagram_username"]').value,
            secure_call: form.querySelector('input[name="secure_call"]').checked ? 1 : 0,
          }),
        })
        .then(response => response.json())
        .then(data => {
          // بازگرداندن دکمه به حالت اولیه
          buttonText.style.display = 'block';
          loader.style.display = 'none';
          // نمایش پیام موفقیت یا خطا
          if (data.success) {
            toastr.success(data.message);
            location.href = "{{ route('mc-workhours', 'activation-path=true') }}"

          } else {
            toastr.error(data.message || "خطا در به‌روزرسانی اطلاعات");
          }
        })
        .catch(error => {
          // بازگرداندن دکمه به حالت اولیه
          buttonText.style.display = 'block';
          loader.style.display = 'none';
          // نمایش خطا
          toastr.error("خطا در برقراری ارتباط با سرور");
        });
    });
  });
</script>
@endsection
