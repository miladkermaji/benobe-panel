<!DOCTYPE html>
<html lang="fa">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> مدت زمان ویزیت</title>
  @include('dr.panel.layouts.partials.head-tags')
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}">

  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/doctors-clininc/activation/index.css') }}">

  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/doctors-clinic/duration/duration.css') }}">
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/doctors-clinic/activation/workhours/workhours.css') }}">

  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/toastify/toastify.min.css') }}">
</head>

<body dir="rtl">
  <header class="bg-light text-dark p-3 my-shodow w-100 d-flex align-items-center">
    <div class="back w-50">
      <a href="{{ route('duration.index', $clinicId) }}" class="btn btn-light">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none">
          <g id="Arrow / Chevron_Right_MD">
            <path id="Vector" d="M10 8L14 12L10 16" stroke="#000000" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round"></path>
          </g>
        </svg>
        <span class="font-weight-bold">بازگشت</span>

      </a>
    </div>
    <div class="w-50">
      <h5 class="font-weight-bold title-header"> ساعات کاری</h5>
    </div>
  </header>
  <!-- لودینگ کلی سایت -->
  <div id="global-loader">
    <div class="loader-backdrop"></div> <!-- بک‌دراپ -->
    <div class="loader-content">
      <div class="spinner"></div> <!-- انیمیشن لودینگ -->
      <p>لطفا منتظر بمانید...</p>
    </div>
  </div>
  <div class="d-flex w-100 justify-content-center align-items-center flex-column">
    <div class="roadmap-container mt-3">
      <div class="step completed">
        <span class="step-title">شروع</span>
        <svg class="icon" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="#0d6efd" stroke-width="2" fill="#0d6efd" />
          <path d="M7 12l3 3l5-5" stroke="#fff" stroke-width="2" fill="none" />
        </svg>
      </div>
      <div class="line completed"></div>
      <div class="step completed">
        <span class="step-title">آدرس</span>
        <svg class="icon" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="#0d6efd" stroke-width="2" fill="#0d6efd" />
          <path d="M7 12l3 3l5-5" stroke="#fff" stroke-width="2" fill="none" />
        </svg>
      </div>
      <div class="line completed"></div>
      <div class="step completed">
        <span class="step-title"> بیعانه</span>
        <svg class="icon" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="#0d6efd" stroke-width="2" fill="#0d6efd" />
          <path d="M7 12l3 3l5-5" stroke="#fff" stroke-width="2" fill="none" />
        </svg>
      </div>
      <div class="line completed"></div>
      <div class="step active">
        <span class="step-title">ساعت کاری</span>
        <svg class="icon" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="#0d6efd" stroke-width="2" fill="#0d6efd" />
          <path d="M7 12l3 3l5-5" stroke="#fff" stroke-width="2" fill="none" />
        </svg>
      </div>
      <div class="line"></div>
      <div class="step active">
        <span class="step-title">پایان</span>
        <svg class="icon" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="#0d6efd" stroke-width="2" fill="#0d6efd" />
          <path d="M7 12l3 3l5-5" stroke="#fff" stroke-width="2" fill="none" />
        </svg>
      </div>
    </div>
    <div class="my-container-fluid mt-2 border-radius-8 d-flex justify-content-center">
      <div class="row justify-content-center">
        <div class="">
          <div class="card p-4">
            @if ($otherSite)
              <div class="alert alert-warning mt-2">
                <p class="font-weight-bold font-size-14">
                  برای عدم تداخل نوبت ها، زمان هایی را انتخاب کنید که با نوبت های حضوری و پلتفرم های دیگر تداخل نداشته
                  باشد.
                </p>
              </div>
            @endif
            <h5 class="text-start font-weight-bold">روزهای کاری</h5>
            <div
              class="d-flex flex-wrap mt-4 gap-10 justify-content-end my-768px-styles-day-and-times flex-row-reverse">
              <div class="" tabindex="0" role="button">
                <span class="badge-time-styles" data-day="friday">جمعه </span>
                <span class="">
                </span>
              </div>
              <div class="" tabindex="0" role="button"><span class="badge-time-styles"
                  data-day="thursday">پنجشنبه</span>
                <span class=""></span>
              </div>
              <div class="" tabindex="0" role="button">
                <span class="badge-time-styles" data-day="wednesday">
                  چهارشنبه

                </span>
                <span class=""></span>
              </div>
              <div class="" tabindex="0" role="button">
                <span class="badge-time-styles" data-day="tuesday">سه شنبه</span>
                <span class="">
                </span>
              </div>
              <div class="" tabindex="0" role="button">
                <span class="badge-time-styles" data-day="monday">دوشنبه </span>
                <span class="">
                </span>
              </div>
              <div class="" tabindex="0" role="button">
                <span class="badge-time-styles" data-day="sunday">یکشنبه</span>
                <span class="">
                </span>
              </div>
              <div class="" tabindex="0" role="button">
                <span class="badge-time-styles active-hours" data-day="saturday">شنبه
                  <svg width="16" height="16" viewBox="0 0 16 16" fill="#7c82fc">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M13.8405 3.44714C14.1458 3.72703 14.1664 4.20146 13.8865 4.5068L6.55319 12.5068C6.41496 12.6576 6.22113 12.7454 6.01662 12.7498C5.8121 12.7543 5.61464 12.675 5.47 12.5303L2.13666 9.197C1.84377 8.90411 1.84377 8.42923 2.13666 8.13634C2.42956 7.84345 2.90443 7.84345 3.19732 8.13634L5.97677 10.9158L12.7808 3.49321C13.0607 3.18787 13.5351 3.16724 13.8405 3.44714Z">
                    </path>
                  </svg></span>
                <span class="">
                </span>
              </div>
            </div>

            <form id="workingHoursForm">
              @csrf
              <input type="hidden" name="clinic_id" value="{{ $clinicId }}">
              <input type="hidden" name="doctor_id" value="{{ $doctorId }}">
              <h5 class="mt-4 font-weight-bold">ساعت کاری</h5>
              <div class="w-100 d-flex mt-4 gap-4 justify-content-center">
                <div class="form-group position-relative timepicker-ui w-100">
                  <label class="label-top-input-special-takhasos">شروع</label>
                  <input type="text"
                    class="form-control  h-50 timepicker-ui-input text-center font-weight-bold font-size-13 W-100"
                    id="startTime" value="00:00" style="direction: ltr">
                </div>
                <div class="form-group position-relative timepicker-ui w-100">
                  <label class="label-top-input-special-takhasos">پایان</label>
                  <input type="text"
                    class="form-control h-50 timepicker-ui-input text-center font-weight-bold font-size-13 W-100"
                    id="endTime" value="23:59" style="direction: ltr">
                </div>
              </div>

              <div class="text-center mt-4">
                <button id="saveButton" type="submit" class="btn btn-primary w-100 h-50">افزودن</button>
              </div>
            </form>
            <div>
              <ul id="workHoursList" class="list-group mt-3"></ul>
            </div>
            <div class="w-100 mt-3">
              <button class="w-100 h-50 btn btn-outline-primary" id="startAppointmentBtn">شروع نوبت دهی</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @include('dr.panel.layouts.partials.scripts')


  <script src="{{ asset('dr-assets/panel/js/toastify/toastify.min.js') }}"></script>
  <script>
    document.getElementById('startAppointmentBtn').addEventListener('click', function() {
      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'می‌خواهید نوبت‌دهی را شروع کنید؟',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله، شروع کن',
        cancelButtonText: 'لغو'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `{{ route('start.appointment') }}`,
            method: 'POST',
            data: {
              doctor_id: "{{ $doctorId }}",
              clinic_id: "{{ $clinicId }}",
              _token: "{{ csrf_token() }}"
            },
            beforeSend: function() {
              Swal.fire({
                title: 'لطفاً صبر کنید...',
                text: 'در حال بررسی...',
                didOpen: () => {
                  Swal.showLoading();
                }
              });
            },
            success: function(response) {
              Swal.fire(
                'موفق!',
                response.message,
                'success'
              ).then(() => {
                window.location.href = response.redirect_url; // هدایت به روت پنل دکتر
              });
            },
            error: function(xhr) {
              Swal.fire(
                'خطا!',
                xhr.responseJSON.message || 'مشکلی رخ داد.',
                'error'
              );
            }
          });
        }
      });
    });

    $(document).ready(function() {
      const DOMElements = document.querySelectorAll('.timepicker-ui');
      const options = {
        clockType: '24h',
        theme: 'basic',
        mobile: 'true',
        inputType: 'selectbox', // یا 'spinbox' بسته به نیاز شما
        timeFormat: 'HH:mm' // فرمت زمان را به صورت دو رقمی تنظیم کنید
      };
      DOMElements.forEach((element) => {
        const newTimepicker = new window.tui.TimepickerUI(element, options);
        newTimepicker.create();
      });
      // Initialize Select2 on existing selects
    });
    let selectedDay = null;

    document.querySelectorAll('.badge-time-styles').forEach(badge => {
      badge.addEventListener('click', function() {
        this.classList.toggle('active-hours');
        const svg = this.querySelector('svg');
        if (this.classList.contains('active-hours')) {
          if (!svg) {
            const svgWrapper = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svgWrapper.setAttribute('width', '16');
            svgWrapper.setAttribute('height', '16');
            svgWrapper.setAttribute('viewBox', '0 0 16 16');
            svgWrapper.setAttribute('fill', '#7c82fc');
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('fill-rule', 'evenodd');
            path.setAttribute('clip-rule', 'evenodd');
            path.setAttribute('d',
              'M13.8405 3.44714C14.1458 3.72703 14.1664 4.20146 13.8865 4.5068L6.55319 12.5068C6.41496 12.6576 6.22113 12.7454 6.01662 12.7498C5.8121 12.7543 5.61464 12.675 5.47 12.5303L2.13666 9.197C1.84377 8.90411 1.84377 8.42923 2.13666 8.13634C2.42956 7.84345 2.90443 7.84345 3.19732 8.13634L5.97677 10.9158L12.7808 3.49321C13.0607 3.18787 13.5351 3.16724 13.8405 3.44714Z'
            );
            svgWrapper.appendChild(path);
            this.appendChild(svgWrapper);
          }
        } else if (svg) {
          svg.remove();
        }
      });
    });

    function loadWorkHours() {
      $.ajax({
        url: `{{ route('workhours.get', ['clinicId' => $clinicId, 'doctorId' => $doctorId]) }}`,
        method: "GET",
        success: function(response) {
          const list = document.getElementById('workHoursList');
          list.innerHTML = '';

          const groupedHours = {};

          // گروه‌بندی ساعات کاری بر اساس بازه زمانی
          response.forEach(item => {
            const hours = JSON.parse(item.work_hours);
            hours.forEach(hour => {
              const key = `${hour.start} تا ${hour.end}`;
              if (!groupedHours[key]) {
                groupedHours[key] = [];
              }
              groupedHours[key].push(dayTranslations[item.day]);
            });
          });

          // افزودن آیتم‌ها به لیست
          for (const [timeRange, days] of Object.entries(groupedHours)) {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `
          <span>${days.join(', ')} - ${timeRange}</span>
          <button class="btn btn-light btn-sm" onclick="deleteWorkHours('${timeRange}', '${days.join(',')}')">
            <img src="{{ asset('dr-assets/icons/trash.svg') }}">
            </button>
        `;
            list.appendChild(li);
          }
        }
      });
    }

    const dayTranslations = {
      saturday: 'شنبه',
      sunday: 'یکشنبه',
      monday: 'دوشنبه',
      tuesday: 'سه‌شنبه',
      wednesday: 'چهارشنبه',
      thursday: 'پنجشنبه',
      friday: 'جمعه',
    };
    const reverseDayTranslations = {
      شنبه: 'saturday',
      یکشنبه: 'sunday',
      دوشنبه: 'monday',
      سه‌شنبه: 'tuesday',
      چهارشنبه: 'wednesday',
      پنجشنبه: 'thursday',
      جمعه: 'friday',
    };

    document.addEventListener('DOMContentLoaded', function() {
      loadWorkHours();
    });

    function deleteWorkHours(timeRange, days) {
      const [startTime, endTime] = timeRange.split(' تا ');

      const englishDays = days.split(',').map(day => reverseDayTranslations[day.trim()]);

      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: "این عملیات قابل بازگشت نیست!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله، حذف کن!',
        cancelButtonText: 'لغو'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `{{ route('activation.workhours.delete') }}`,
            method: "POST",
            data: {
              doctor_id: "{{ $doctorId }}",
              clinic_id: "{{ $clinicId }}",
              days: englishDays,
              start: startTime.trim(),
              end: endTime.trim(),
              _token: "{{ csrf_token() }}"
            },
            success: function(response) {
              Swal.fire(
                'حذف شد!',
                'ساعات کاری با موفقیت حذف شدند.',
                'success'
              );
              loadWorkHours(); // بروزرسانی لیست بدون رفرش
            },
            error: function(xhr) {
              Swal.fire(
                'خطا!',
                'مشکلی در حذف ساعات کاری پیش آمد.',
                'error'
              );
            }
          });
        }
      });
    }


    function formatTime(time) {
      const [hours, minutes] = time.split(':');
      return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
    }

   document.getElementById('workingHoursForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const startTime = formatTime(document.getElementById('startTime').value.trim());
  const endTime = formatTime(document.getElementById('endTime').value.trim());

  const selectedDays = Array.from(document.querySelectorAll('.badge-time-styles.active-hours'))
    .map(badge => badge.getAttribute('data-day'));

  if (!selectedDays || selectedDays.length === 0) {
    toastr.error('لطفاً حداقل یک روز را انتخاب کنید.');
    return;
  }

  if (!startTime || !endTime) {
    toastr.error('لطفاً ساعات کاری را وارد کنید.');
    return;
  }

  if (endTime <= startTime) {
    toastr.error('زمان پایان باید بعد از زمان شروع باشد.');
    return;
  }

  const workHours = [{
    start: startTime,
    end: endTime
  }];

  const formData = {
    doctor_id: "{{ $doctorId }}",
    clinic_id: "{{ $clinicId }}",
    day: selectedDays,
    work_hours: workHours,
    _token: "{{ csrf_token() }}"
  };

  $.ajax({
    url: "{{ route('activation.workhours.store') }}",
    method: "POST",
    data: formData,
    beforeSend: function() {
      $('#saveButton').html(
        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> در حال ذخیره...'
      ).prop('disabled', true);
    },
    success: function(response) {
      toastr.success(response.message);
      loadWorkHours(selectedDays);
    },
    error: function(xhr) {
      const response = xhr.responseJSON;
      if (response && response.message) {
        toastr.error(response.message); // نمایش پیام خطای سرور با toastr
      } else {
        toastr.error('مشکلی در ذخیره ساعات کاری رخ داد.');
      }
    },
    complete: function() {
      $('#saveButton').html('افزودن').prop('disabled', false);
    }
  });
});
  </script>


</body>

</html>
