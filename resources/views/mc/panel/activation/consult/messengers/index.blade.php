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

<div class="workhours-content w-100 d-flex justify-content-center mt-4">
  <div class="workhours-wrapper-content consult-wrapper p-3">
    <div class="">
      <div class="drop-toggle-styles messangers-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div>
          <div class="alert alert-warning mt-2 text-center">
            <span class="text-sm fw-bold d-block font-size-15">لطفا شماره و نام کاربری پیام رسان ایتا یا شماره
              واتساپ خود را وارد.</span>
            <span class="font-size-15 mt-1">شماره موبایل این پیام رسان ها در دسترس بیمار قرار میگیرد.</span>
          </div>
          <form id="messengersForm">
            @csrf
            @method('PUT')
            <div>
              <h6 class="text-left fw-bold d-block font-size-13">پیام رسان های داخلی</h6>
            </div>
            <div class="d-flex align-items-center justify-content-start gap-20">
              <div
                class="d-flex justify-content-start gap-1 align-items-center  border border-solid py-2 px-4 rounded-lg">
                <img
                  src="data:image/png;base64,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"
                  alt=""><span class="text-sm mx-1">ایتا</span>
              </div>
              <div class="w-100">
                <div class="w-100">
                  <input type="text" name="ita_phone" class="form-control h-50 border-radius-4"
                    placeholder="شماره موبایل" maxlength="11"
                    value="{{ $messengers->where('messenger_type', 'ita')->first()->phone_number ?? '' }}">
                </div>
                <div class="mt-2 w-100">
                  <input type="text" name="ita_username" class="form-control h-50 border-radius-4 mt-2"
                    placeholder="نام کاربری ایتا"
                    value="{{ $messengers->where('messenger_type', 'ita')->first()->username ?? '' }}">
                </div>
              </div>
            </div>
            <div class="mt-2">
              <h6 class="text-left fw-bold d-block font-size-13">پیام رسان های خارجی</h6>
            </div>
            <div class="d-flex align-items-center justify-content-start gap-20 mt-2">
              <div
                class="d-flex justify-content-center gap-1 align-items-center border border-solid py-2 px-3 rounded-lg">
                <img
                  src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAUkSURBVHgBhVVrTFxFGD1zdxe2gLAt0AalsG1aU62m0KSx8VXalKa2KJDGik1UamPjDyvVxMSYJgWtJiYmBRONCbXF2KLVKPCjUtNgaXwFURbsQ4EAi9DyKMWlu8tj994Zv5m7dxdasDe5O3e+mTnnzJlvv2H4n6fIc9CVHDddxhjyqOsWYNmgH/r2CYZ2cKPB0Bx1tfd/1L8QBpsvWOIpddsd8ScILM+aJScKQS+Tv5HFkozYKVBjhGcqvsyt8d6RYM/Fl8rARTktdMm+UOsRQRIQ8ywWcoBpPsZFRe266qoFCXZ3vHiYZpczK2zJZnN1iMgm2OytQSqRc1H+1brjFbcR7PI8X6bBVhlTKKLDibYE5C15BOlxaap/PTSG33xtuB6+YeLH0IQk14DXvs6tqYoSFHlK3IzHeUiRS26XMdPYRHsCXsnahw2u9RgjsL8C3US2CKmOVGQ7M9E8/hNOD9eL0ZkxZhFArqYkAAvnfLu+tt+utBqOwxw8hSkJcudcLItPZ0fufUupfaf3A1wJdMKyTlohd/NyZineXvUmO9T1HkZDNyC4qY1mpEBoNfSxmW3/tcRtt2l9KkEitiZqiaha+y4uB/7GxwPHY35b/s/ypfSeEmxIycWrVw6JSSMIlcbmcbCAY3KxxgQrMjgndk66BTgXKFi6TQF9OnASS2yL8caKA8iMvxthQ4fODei62Ya5jmMDpxDUJ/Fkej4zDM4MYRCUoJbDGXKWaQRbyKmjU24atEi+W1MfQ+3Vb+AL+7EzPR8PpazHJjpkCWiRqNYw2/rhRhQuewIGYUhwOc6VaJGnUSeH2CLgHGl0gNLf7mCfAuARL+SCsASkeSHDJApHCH4cb0GCtggrnFlMYnCJJ1SbrdFCl2mRYBIkPT5VIXb6exAmK74balIE/nCQCHXzlTYRuM7N74mQX81xMiekWC7tJiwSvUJTTBTQZYC+r02OMHNyvAKQRB/2HMMLWbtRnLFDKU5zLMGDyffNsUuemVBnyNUuJJZs7UJwL6dCZv1XhqZHlFr3oix4fJdU7It/6pUFB1buw7rktVidtBIZzqU4PdiAyu5q5KSsVfO6gj3qHGSNooShrGQTdlLupaoVqZJmKjaONqF0+TNoHW+HVdo+6fkcg1PDKMjIV+B+PajGpfrtS7fQmh9wMxQwi4VVvsDa7QbXm6lQbWIqxlW8K9BLizarQ5xdMuoHz6JuoBHJcUmK2B8OYM/yYuxYtgVP/76fEoXHipb8MniDFtKcVdwwZP6qA5KttKF1vANOLR4rE9yRjIml6Pj0BHwzEyjJLMTrq/ejqqcag8EhxpXvEsdMgpDG6hRj7rn889HaL5VuPIEke6I6tGRHEq5NjSg7Wv/tUONrklfhqYxt6vtI51FcGGsxRbNYYaWS8Vnb1u/3mrVIt+0VtrCHwi7yV1ydGmZtvj/xh++i6A70sE1pD+Px1I14LnsX7iLCTn8vqr2ncGb4HG5SQrDYxWC6LzChcaMiWk3l88DZvDLqHpXcUR2RmhIz1SpDArePzbqKmHbw0vYmVa5tVmz0pLcl7Vm39DFPqDtKXo4y41R2Q6gEEKpeqZxXl6eIXj5WnAYqLu84//7ss57zrD7zaJkmtHKa6Yokm8prccsCEY1HrzwfA6/oLPi5cjbevJf+mrqNbsNho+uTlUpVUdNgXfqR4m1ZxFizofHSvp2/9N+KNS+B9biJSNNYIaktIqk51KYoUAEv3YteZrALHHGV3uJm30IY/wHQyRqM6Hi8vgAAAABJRU5ErkJggg=="
                  alt="">
                <span class="text-sm mx-1 font-size-13">واتساپ</span>
              </div>
              <div class="w-100">
                <div class="w-100">
                  <input type="text" name="whatsapp_phone" class="form-control h-50 border-radius-4 col-12"
                    placeholder="شماره موبایل" maxlength="11"
                    value="{{ $messengers->where('messenger_type', 'whatsapp')->first()->phone_number ?? '' }}">
                </div>
              </div>
            </div>
            <div class="mt-2">
              <h6 class="text-left fw-bold d-block font-size-13"> تماس امن</h6>
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
                      {{ ($messengers->where('messenger_type', 'ita')->first()->is_secure_call ?? false) ||
                      ($messengers->where('messenger_type', 'whatsapp')->first()->is_secure_call ?? false)
                          ? 'checked'
                          : '' }}>
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
            whatsapp_phone: form.querySelector('input[name="whatsapp_phone"]').value,
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
