<!DOCTYPE html>
<html lang="fa-IR" dir="rtl" class="scroll-smooth">

<head>
 <title>پنل مدیریت به نوبه</title>
 <!-- strart meta tag -->
 <meta charSet="utf-8" />
 <meta name="viewport"
  content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=5.0" />
 <meta name="description"
  content="به نوبه سامانه نوبت دهی بهترین پزشکان متخصص و مراکز درمانی کشور می باشد،‌ شما میتوانید به راحتی از پزشک مورد نظرتون نوبت و مشاوره آنلاین بگیرید.">
 <link rel="canonical" href="https://benobe.ir/" />
 <meta name="robots" content="index, follow" />
 <meta property="og:title" content="به نوبه | نوبت دهی اینترنتی و مشاوره آنلاین پزشکان">
 <meta property="og:description"
  content="به نوبه سامانه نوبت دهی بهترین پزشکان متخصص و مراکز درمانی کشور می باشد،‌ شما میتوانید به راحتی از پزشک مورد نظرتون نوبت و مشاوره آنلاین بگیرید.">
 <meta property="og:site_name" content="به نوبه | نوبت دهی اینترنتی و مشاوره آنلاین پزشکان">
 <meta property="og:url" content="">
 <meta property="og:type" content="div">
 <meta property="og:locale" content="fa_IR" />
 <meta property="og:image" content="{{ asset('app-assets/logos/benobe.svg') }}">
 <meta property="og:image:secure_url" content="{{ asset('app-assets/logos/benobe.svg') }}" />
 <meta property="og:image:type" content="image/png" />
 <meta property="og:image:alt" content="به نوبه" />
 <meta name="author" content="به نوبه" />
 <meta name="application-name" content="به نوبه" />
 <meta name="apple-mobile-web-app-title" content="به نوبه" />
 <meta name="next-head-count" content="30" />
 <link rel="icon" type="image/x-icon" href="{{ asset('app-assets/logos/favicon.ico') }}" />
 <link rel="preconnect" href="https://www.googletagmanager.com" />
 <meta name="theme-color" content="#FFFFFF" />
 <meta name="msapplication-TileColor" content="#FFFFFF" />
 <!-- <link rel="manifest" href="/manifest.json" /> -->
 <meta name="mobile-web-app-capable" content="yes" />
 <meta name="msapplication-tap-highlight" content="no" />
 <meta name="format-detection" content="telephone=no" />
 <meta http-equiv="X-UA-Compatible" content="ie=edge" />
 <meta name="google" content="notranslate" />
 <!--  Meta  -->
 <meta name="keywords"
  content="به نوبه نوبت دهی اینترنتی و مشاوره آنلاین پزشکان
      نوبت دهی آنلاین پزشکان ، نوبت اینترنتی دکتر ، نوبت دهی مطب ها ، نوبت دهی مطب های پزشکی ، نوبت مطب سنندج ، نوبت دهی پزشکان کردستان ، نوبت دهی دکتر سنندج ، نوبت دهی بیمارستان کردستان ، نوبت دهی کلینیک و درمانگاه ، نوبت دکتر ، نوبت دهی به نوبه ، سامانه نوبت دهی به نوبه ، benobe ، نوبت بیمارستان سنندج ، نوبت دهی ، مشاوره تلفنی با پزشک کردستان، مشاوره آنلاین دکتر، آدرس دکتر سنندج benobe ،‌ژین ، نوبت دهی ژین ،zhin724 ، نوبت دهی و مشاوره پزشکان">
 <link rel="shortcut icon" href="{{ asset('app-assets/images/favicon.ico') }}">
 <!-- site icon -->
 <link rel="canonical" href="https://benobe.ir/" />
 <script src="{{ asset('admin-assets/panel/js/sweetalert2/sweetalert2.js') }}"></script>
 <link rel="stylesheet" href="{{ asset('admin-assets/panel/css/toastr/toastr.min.css') }}">
 @vite(['resources/js/app.js', 'resources/css/app.css'])
 <link rel="stylesheet" href="{{ asset('admin-assets/login/bootstrap5/bootstrap.min.css') }}">
 <link rel="stylesheet" href="{{ asset('admin-assets/login/css/login.css') }}">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <link rel="stylesheet" href="{{ asset('admin-assets/panel/css/toastr/toastr.min.css') }}">
</head>

<body>
 <main class="">
  <div class="login-wrapper d-flex w-100 justify-content-center align-items-center h-100vh">
   <div class="">
    @php
     $step = $step ?? 1;
    @endphp
    {{-- استپ اول: ورود با موبایل --}}
    @if ($step == 1)
     <div class="justify-content-center align-items-center">
      <div class="col-md-6 login-container position-relative">
       <div class="login-card custom-rounded custom-shadow p-7">
        <div class="logo-wrapper w-100 d-flex justify-content-center">
         <img class="position-absolute mt-3 cursor-pointer" onclick="location.href='/'" width="85px"
          src="{{ asset('app-assets/logos/benobe.svg') }}" alt="">
        </div>
        <div class="d-flex justify-content-between align-items- mb-3 mt-4">
         <div class="d-flex align-items-center">
          <div class="rounded-circle bg-primary me-2" style="width: 16px; height: 16px;"></div>
          <span class="text-custom-gray px-1 fw-bold">ورود کاربر</span>
         </div>
         <a href="javascript:void(0);" class="back-link text-primary d-flex align-items-center go-back"
          data-step="{{ $step }}">
          <span class="ms-2">بازگشت</span>
          <img src="{{ asset('admin-assets/login/images/back.svg') }}" alt="Back Icon" class="img-fluid"
           style="max-width: 24px;">
         </a>
        </div>
        <form id="login-form-step1" method="POST">
         @csrf
         <div class="mb-3">
          <div class="d-flex align-items-center mb-2">
           <img src="{{ asset('admin-assets/login/images/phone.svg') }}" alt="Phone Icon" class="me-2">
           <label class="text-custom-gray">شماره موبایل</label>
          </div>
          <input dir="ltr" class="form-control custom-rounded custom-shadow h-50" type="text" name="mobile"
           value="{{ old('mobile') }}" placeholder="09181234567" maxlength="11">
          <div class="invalid-feedback mobile-error"></div>
         </div>
         <a href="{{ route('admin.auth.login-user-pass-form') }}"
          class="text-primary text-decoration-none mb-3 d-block fw-bold">
          ورود با نام کاربری و کلمه عبور
         </a>
         <button type="submit"
          class="btn btn-primary w-100 custom-gradient custom-rounded py-2 d-flex justify-content-center">
          ادامه
         </button>
        </form>
       </div>
      </div>
     </div>
    @endif
    {{-- استپ دوم: تایید کد OTP --}}
    @if ($step == 2)
     <div class="justify-content-center align-items-center">
      <div class="col-md-6 login-container position-relative">
       <div class="login-card custom-rounded custom-shadow p-7">
        <div class="d-flex justify-content-between align-items-center mb-4">
         <div class="d-flex align-items-center">
          <div class="rounded-circle bg-primary me-2" style="width: 16px; height: 16px;"></div>
          <span class="text-custom-gray px-1">ورود کاربر</span>
         </div>
         <a href="javascript:void(0);" class="back-link text-primary d-flex align-items-center go-back"
          data-step="{{ $step }}">
          <span class="ms-2">بازگشت</span>
          <img src="{{ asset('admin-assets/login/images/back.svg') }}" alt="Back Icon" class="img-fluid"
           style="max-width: 24px;">
         </a>
        </div>
        <form id="otp-form" method="POST" action="{{ route('admin.auth.login-confirm', ['token' => $token]) }}">
         @csrf
         <div class="d-flex justify-content-between mb-3" dir="rtl">
          @for ($i = 0; $i < 4; $i++)
           <input type="text" name="otp[]" maxlength="1"
            class="form-control otp-input text-center custom-rounded border">
          @endfor
         </div>
         <div class="invalid-feedback otp-error" id="otp-error" style="display: none;"></div>
         <button type="submit"
          class="btn btn-primary w-100 custom-gradient custom-rounded py-2 d-flex justify-content-center">
          ادامه
         </button>
         <section id="resend-otp" class="d-none mt-2">
          <a href="{{ route('admin.auth.login-resend-otp', $token) }}"
           class="text-decoration-none text-primary fw-bold">دریافت مجدد کد تایید</a>
         </section>
         <section style="font-size: 14px" class="text-danger fw-bold fs-6 mt-3" id="timer"></section>
        </form>
       </div>
      </div>
     </div>
    @endif
    {{-- استپ سوم: ورود با نام کاربری و کلمه عبور --}}
    @if ($step == 3)
     <div class="justify-content-center align-items-center">
      <div class="col-md-6 login-container position-relative">
       <div class="login-card custom-rounded custom-shadow p-7">
        <div class="d-flex justify-content-between align-items-center mb-4">
         <div class="d-flex align-items-center">
          <div class="rounded-circle bg-primary me-2" style="width: 16px; height: 16px;"></div>
          <span class="text-custom-gray px-1">ورود کاربر</span>
         </div>
         <a href="javascript:void(0);" class="back-link text-primary d-flex align-items-center go-back"
          data-step="{{ $step }}">
          <span class="ms-2">بازگشت</span>
          <img src="{{ asset('admin-assets/login/images/back.svg') }}" alt="Back Icon" class="img-fluid"
           style="max-width: 24px;">
         </a>
        </div>
        <form id="login-with-pass-form" method="POST" action="{{ route('admin-login-with-mobile-pass') }}">
         @csrf
         <div class="mb-3">
          <div class="d-flex align-items-center mb-2">
           <img src="{{ asset('admin-assets/login/images/phone.svg') }}" alt="Phone Icon" class="me-2">
           <label class="text-custom-gray">شماره موبایل</label>
          </div>
          <input dir="ltr" class="form-control custom-rounded custom-shadow h-50" type="text" name="mobile"
           value="{{ old('mobile') }}" placeholder="09181234567" maxlength="11">
          <div class="invalid-feedback mobile-error"></div>
         </div>
         <div class="mb-3">
          <div class="d-flex align-items-center mb-2">
           <img src="{{ asset('admin-assets/login/images/password.svg') }}" alt="Password Icon" class="me-2">
           <label class="text-custom-gray">رمز عبور</label>
          </div>
          <div class="position-relative">
           <input class="form-control custom-rounded custom-shadow h-50 text-end" type="password" name="password"
            placeholder="رمز عبور خود را وارد کنید" id="password-input">
           <img src="{{ asset('admin-assets/login/images/visible.svg') }}" alt="Toggle Visibility"
            class="password-toggle" onclick="togglePasswordVisibility('password-input')">
          </div>
          <div class="invalid-feedback password-error"></div>
         </div>
         <button type="submit"
          class="btn btn-primary w-100 custom-gradient custom-rounded py-2 d-flex justify-content-center">
          ادامه
         </button>
        </form>
       </div>
      </div>
     </div>
    @endif
    {{-- استپ چهارم: ورود با رمز عبور دو مرحله‌ای --}}
    @if ($step == 4)
     <div class="justify-content-center align-items-center">
      <div class="col-md-6 login-container position-relative">
       <div class="login-card custom-rounded custom-shadow p-7">
        <div class="d-flex justify-content-between align-items-center mb-4">
         <div class="d-flex align-items-center">
          <div class="rounded-circle bg-primary me-2" style="width: 16px; height: 16px;"></div>
          <span class="text-custom-gray px-1">ورود کاربر</span>
         </div>
         <a href="javascript:void(0);" class="back-link text-primary d-flex align-items-center go-back"
          data-step="{{ $step }}">
          <span class="ms-2">بازگشت</span>
          <img src="{{ asset('admin-assets/login/images/back.svg') }}" alt="Back Icon" class="img-fluid"
           style="max-width: 24px;">
         </a>
        </div>
        <form id="two-factor-check-form" method="POST" action="{{ route('admin-two-factor-store') }}">
         @csrf
         <div class="mb-3">
          <div class="d-flex align-items-center mb-2">
           <img src="{{ asset('admin-assets/login/images/password.svg') }}" alt="Password Icon" class="me-2">
           <label class="text-custom-gray">رمز دوعاملی</label>
          </div>
          <div class="position-relative">
           <input dir="ltr" class="form-control custom-rounded custom-shadow h-50" type="password"
            name="two_factor_secret" placeholder="رمز عبور خود را وارد کنید" id="two-factor-input">
           <img src="{{ asset('admin-assets/login/images/visible.svg') }}" alt="Toggle Visibility"
            class="password-toggle" onclick="togglePasswordVisibility('two-factor-input')">
          </div>
          <div class="invalid-feedback two-factor-error"></div>
         </div>
         <button type="submit"
          class="btn btn-primary w-100 custom-gradient custom-rounded py-2 d-flex justify-content-center">
          ادامه
         </button>
        </form>
       </div>
      </div>
     </div>
    @endif
   </div>
  </div>
 </main>
 <script src="{{ asset('admin-assets/js/jquery/jquery.min.js') }}"></script>
 <script src="{{ asset('admin-assets/js/bootstrap/bootstrap.min.js') }}"></script>
 <script src="{{ asset('admin-assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
 <script src="{{ asset('admin-assets/js/main.js') }}"></script>
 <script src="{{ asset('admin-assets/panel/js/toastr/toastr.min.js') }}"></script>
 <script src="{{ asset('admin-assets/js/login.js') }}"></script>
 <script src="{{ asset('admin-assets/panel/js/toastr/toastr.min.js') }}"></script>
 @php
  // محاسبه زمان باقی‌مانده برای تایمر
  $remainingTime = 0;
  if (isset($otp) && $otp instanceof \App\Models\Dr\Otp) {
      $remainingTime = max(0, ($otp->created_at->addMinutes(2)->timestamp - now()->timestamp) * 1000);
  } elseif (isset($token)) {
      // اگر توکن موجود است، تلاش برای بازیابی OTP
      $otp = \App\Models\Otp::where('token', $token)->first();
      if ($otp) {
          $remainingTime = max(0, ($otp->created_at->addMinutes(2)->timestamp - now()->timestamp) * 1000);
      }
  }
 @endphp
 <script>
  // استفاده از زمان باقی‌مانده از سمت سرو      ر  
  var countDownDate = new Date().getTime() + {{ $remainingTime }};
  var timer = $('#timer');
  var resendOtp = $('#resend-otp');
  var x = setInterval(function() {
   var now = new Date().getTime();
   var distance = countDownDate - now;
   var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
   var seconds = Math.floor((distance % (1000 * 60)) / 1000);
   // بروزرسانی نمایش تایمر  
   if (minutes === 0 && seconds === 0) {
    timer.html('کد تایید منقضی شده است.');
   } else if (minutes === 0) {
    timer.html('ارسال مجدد کد تایید تا ' + seconds + ' ثانیه دیگر');
   } else {
    timer.html('ارسال مجدد کد تایید تا ' + minutes + ' دقیقه و ' + seconds + ' ثانیه دیگر');
   }
   // اگر زمان به پایان برسد  
   if (distance < 0) {
    clearInterval(x);
    timer.addClass('d-none');
    resendOtp.removeClass('d-none');
   }
  }, 1000);
 </script>
 <script>
  function togglePasswordVisibility(inputId) {
   const input = document.getElementById(inputId);
   const icon = input.nextElementSibling; // آیکون چشم
   if (input.type === 'password') {
    input.type = 'text';
    icon.style.opacity = '0.7'; // تغییر شفافیت برای نشان دادن حالت فعال (اختیاری)
   } else {
    input.type = 'password';
    icon.style.opacity = '1'; // برگشت به حالت اولیه
   }
  }
  /* set timer */
  $(document).ready(function() {
   // Check if we have a token for the AJAX request
   const token = "{{ $token ?? '' }}"; // Use an empty string as a fallback
   let countDownDate;

   function startTimer(remainingTime = 120000) {
    clearInterval(window.timerInterval); // پاک کردن تایمر قبلی
    countDownDate = new Date().getTime() + remainingTime;
    window.timerInterval = setInterval(function() {
     let now = new Date().getTime();
     let distance = countDownDate - now;
     let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
     let seconds = Math.floor((distance % (1000 * 60)) / 1000);
     let timerText = minutes === 0 ?
      `ارسال مجدد کد تایید تا ${seconds} ثانیه دیگر` :
      `ارسال مجدد کد تایید تا ${minutes} دقیقه و ${seconds} ثانیه دیگر`;
     $('#timer').text(timerText);
     if (distance < 0) {
      clearInterval(window.timerInterval);
      $('#timer').addClass('d-none');
      $('#resend-otp').removeClass('d-none');
     }
    }, 1000);
   }

   function showRateLimitAlert(remainingTime) {
    const swalWithProgress = Swal.mixin({
     customClass: {
      confirmButton: 'btn btn-primary',
      cancelButton: 'btn btn-danger'
     },
     buttonsStyling: false
    });
    let timerInterval;
    swalWithProgress.fire({
     icon: 'error',
     title: 'تلاش بیش از حد برای ورود به سایت',
     html: `لطفاً <b id="remaining-time">${formatTime(remainingTime)}</b> دیگر صبر کنید.`,
     timer: remainingTime * 1000,
     timerProgressBar: true,
     didOpen: () => {
      const remainingTimeElement = document.getElementById('remaining-time');
      timerInterval = setInterval(() => {
       remainingTime--;
       remainingTimeElement.innerHTML = formatTime(remainingTime);
      }, 1000);
     },
     willClose: () => {
      clearInterval(timerInterval);
     }
    });
   }
   // ارسال مجدد OTP
   $('#resend-otp').on('click', function(e) {
    e.preventDefault();
    if (!token) {
     toastr.error('توکن نامعتبر است');
     return;
    }
    $.ajax({
     url: "{{ route('admin.auth.login-resend-otp', ['token' => ':token']) }}".replace(':token', token),
     method: 'GET',
     success: function(response) {
      if (response.redirect) {
       toastr.error(response.message);
       window.location.href = response.redirect; // هدایت به استپ اول
      } else {
       toastr.success("کد تأیید جدید با موفقیت ارسال شد");
       startTimer(120000);
      }
     },
     error: function(xhr) {
      if (xhr.status === 410) {
       const response = xhr.responseJSON;
       toastr.error(response.message);
       window.location.href = response.redirect; // هدایت به استپ اول
      } else if (xhr.status === 429) {
       let remainingTime = xhr.responseJSON.remaining_time || 0;
       showRateLimitAlert(remainingTime);
      } else {
       toastr.error('خطا در ارسال مجدد کد');
      }
     }
    });
   });
   // شروع تایمر اولیه
   startTimer({{ $remainingTime }});
   $('.go-back').on('click', function() {
    const currentStep = parseInt($(this).data('step'));
    if (currentStep > 1) { // ریست کردن تایمر
     window.location.href = "{{ route('admin.auth.login-register-form') }}?step=" + (currentStep - 1);
    }
   });
   // متغیرهای عمومی
   function showButtonLoading(button) {
    button.prop('disabled', true);
    button.html('<div class="loader"></div>');
   }

   function resetButton(button, text) {
    button.prop('disabled', false);
    button.html(text);
   }
   $('#login-form-step1').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const submitButton = form.find('button[type="submit"]');
    showButtonLoading(submitButton);
    $('.error-message').remove();
    $.ajax({
     url: "{{ route('admin.auth.login-register') }}",
     method: 'POST',
     data: form.serialize(),
     success: function(response) {
      toastr.success("کد تایید با موفقیت ارسال شد");
      window.location.href = "{{ route('admin.auth.login-confirm-form', ['token' => ':token']) }}".replace(
       ':token', response.token);
     },
     error: function(xhr) {
      resetButton(submitButton, 'ادامه');
      if (xhr.status === 429) {
       let remainingTime = xhr.responseJSON.remaining_time || 0;
       showRateLimitAlert(remainingTime);
      } else if (xhr.status === 422) {
       const errors = xhr.responseJSON.errors;
       Object.keys(errors).forEach(function(key) {
        $(`[name="${key}"]`).addClass('is-invalid');
        $(`[name="${key}"]`).after(`<div class="error-message">${errors[key][0]}</div>`);
       });
      }
     }
    });
   });

   function formatTime(seconds) {
    if (isNaN(seconds) || seconds < 0) {
     return '0 دقیقه و 0 ثانیه'; // مقدار پیش‌فرض برای مقادیر نامعتبر
    }
    const minutes = Math.floor(seconds / 60); // دقیقه‌ها
    const remainingSeconds = Math.floor(seconds % 60); // ثانیه‌ها (بدون اعشار)
    return `${minutes} دقیقه و ${remainingSeconds} ثانیه`;
   }
   $('.otp-input').eq(3).focus();
   $('.otp-input').on('input', function() {
    const currentInput = $(this);
    const value = currentInput.val();
    currentInput.val(value.replace(/[^0-9]/g, ''));
    currentInput.val(value);
    if (value.length === 1) {
     const inputs = $('.otp-input');
     const currentIndex = inputs.index(currentInput);
     if (currentIndex > 0) {
      inputs.eq(currentIndex - 1).focus();
     }
    }
   });
   $('.otp-input').on('keydown', function(e) {
    const inputs = $('.otp-input');
    const currentIndex = inputs.index($(this));
    if (e.key === 'Backspace' && $(this).val().length === 0) {
     if (currentIndex < inputs.length - 1) {
      inputs.eq(currentIndex + 1).focus().select();
     }
    }
   });
   $('.otp-input').on('click', function() {
    $(this).focus();
   });
   $('#otp-form').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const submitButton = form.find('button[type="submit"]');
    $('#otp-error').hide();
    showButtonLoading(submitButton);
    const otpInputs = $('.otp-input');
    const otpValues = otpInputs.map(function() {
     return $(this).val();
    }).get().reverse().join('');
    if (otpValues.length < 4) {
     $('#otp-error').text('لطفا تمام کد را وارد کنید.').show();
     resetButton(submitButton, 'ادامه');
     return;
    }
    $('.error-message').remove();
    $.ajax({
     url: form.attr('action'),
     method: 'POST',
     data: form.serialize(),
     success: function(response) {
      if (response.otp_code) {
       onOtpReceived(response.otp_code);
      }
      toastr.success(" با موفقیت وارد شدید ");
      window.location.href = response.redirect;
     },
     error: function(xhr) {
      resetButton(submitButton, 'ادامه');
      $('#otp-error').text('کد وارد شده نادرست است.').show();
      if (xhr.status === 429) {
       let remainingTime = xhr.responseJSON.remaining_time || 0;
       showRateLimitAlert(remainingTime);
      }
     },
     complete: function() {
      submitButton.prop('disabled', false).text('ارسال');
     },
    });
   });
   $('#login-with-pass-form').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const submitButton = form.find('button[type="submit"]');
    showButtonLoading(submitButton);
    $('.error-message').remove();
    $.ajax({
     url: form.attr('action'),
     method: 'POST',
     data: form.serialize(),
     success: function(response) {
      toastr.success("موفقیت آمیز");
      window.location.href = response.redirect;
     },
     error: function(xhr) {
      resetButton(submitButton, 'ادامه');
      if (xhr.status === 422) {
       const errors = xhr.responseJSON.errors;
       $('.password-error').text(errors['mobile-pass-errors'] || 'لطفا اطلاعات خواسته شده را به درستی وارد کنید')
        .show();
      }
      if (xhr.status === 429) {
       let remainingTime = xhr.responseJSON.remaining_time || 0;
       showRateLimitAlert(remainingTime);
      }
     }
    });
   });
   $('#two-factor-check-form').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const submitButton = form.find('button[type="submit"]');
    showButtonLoading(submitButton);
    $('.error-message').remove();
    $.ajax({
     url: form.attr('action'),
     method: 'POST',
     data: form.serialize(),
     success: function(response) {
      toastr.success("  با موفقیت وارد شدید");
      window.location.href = response.redirect;
     },
     error: function(xhr) {
      resetButton(submitButton, 'ادامه');
      if (xhr.status === 422) {
       const errors = xhr.responseJSON.errors;
       $('.two-factor-error').text(errors['two_factor_secret'] || 'خطا در ورود').show();
      }
      if (xhr.status === 429) {
       let remainingTime = xhr.responseJSON.remaining_time || 0;
       showRateLimitAlert(remainingTime);
      }
     }
    });
   });
   // تابع نمایش/مخفی کردن رمز عبور
  });

  function requestNotificationPermission() {
   if (Notification.permission === "default") {
    Notification.requestPermission();
   }
  }

  function isMobile() {
   return /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
  }

  function sendNotificationOnMobile(code) {
   if (isMobile()) {
    sendNotification(code);
   }
  }

  function autoFillOtp(code) {
   const inputs = $('.otp-input');
   const codeArray = code.split('').reverse();
   inputs.each(function(index) {
    $(this).val(codeArray[index] || '').trigger('input');
   });
  }

  function onOtpReceived(code) {
   sendNotification(code);
   autoFillOtp(code);
  }
  requestNotificationPermission();
 </script>
 @networkStatus
</body>

</html>
