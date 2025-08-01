<!DOCTYPE html>
<html lang="fa-IR" dir="rtl" class="scroll-smooth">

<head>
  <title>پنل مدیریت به نوبه</title>
  <!-- Meta Tags -->
  <meta charset="utf-8" />
  <meta name="viewport"
    content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=5.0" />
  <meta name="description"
    content="به نوبه سامانه نوبت دهی بهترین پزشکان متخصص و مراکز درمانی کشور می باشد،‌ شما می‌توانید به راحتی از پزشک مورد نظرتون نوبت و مشاوره آنلاین بگیرید." />
  <meta name="robots" content="index, follow" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <link rel="canonical" href="https://benobe.ir/" />
  <meta property="og:title" content="به نوبه | نوبت دهی اینترنتی و مشاوره آنلاین پزشکان" />
  <meta property="og:description"
    content="به نوبه سامانه نوبت دهی بهترین پزشکان متخصص و مراکز درمانی کشور می باشد،‌ شما می‌توانید به راحتی از پزشک مورد نظرتون نوبت و مشاوره آنلاین بگیرید." />
  <meta property="og:site_name" content="به نوبه | نوبت دهی اینترنتی و مشاوره آنلاین پزشکان" />
  <meta property="og:url" content="" />
  <meta property="og:type" content="div" />
  <meta property="og:locale" content="fa_IR" />
  <meta property="og:image" content="{{ asset('app-assets/logos/benobe.svg') }}" />
  <meta property="og:image:secure_url" content="{{ asset('app-assets/logos/benobe.svg') }}" />
  <meta property="og:image:type" content="image/png" />
  <meta property="og:image:alt" content="به نوبه" />
  <meta name="author" content="به نوبه" />
  <meta name="application-name" content="به نوبه" />
  <meta name="apple-mobile-web-app-title" content="به نوبه" />
  <meta name="theme-color" content="#FFFFFF" />
  <meta name="msapplication-TileColor" content="#FFFFFF" />
  <meta name="mobile-web-app-capable" content="yes" />
  <meta name="msapplication-tap-highlight" content="no" />
  <meta name="format-detection" content="telephone=no" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <meta name="google" content="notranslate" />
  <meta name="keywords"
    content="به نوبه نوبت دهی اینترنتی و مشاوره آنلاین پزشکان, نوبت دهی آنلاین پزشکان, نوبت اینترنتی دکتر, نوبت دهی مطب ها, نوبت دهی مطب های پزشکی, نوبت مطب سنندج, نوبت دهی پزشکان کردستان, نوبت دهی دکتر سنندج, نوبت دهی بیمارستان کردستان, نوبت دهی کلینیک و درمانگاه, نوبت دکتر, نوبت دهی به نوبه, سامانه نوبت دهی به نوبه, benobe, نوبت بیمارستان سنندج, نوبت دهی, مشاوره تلفنی با پزشک کردستان, مشاوره آنلاین دکتر, آدرس دکتر سنندج benobe, ژین, نوبت دهی ژین, zhin724, نوبت دهی و مشاوره پزشکان" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('app-assets/logos/favicon.ico') }}" />
  <link rel="shortcut icon" href="{{ asset('app-assets/images/favicon.ico') }}" />

  <!-- Styles -->
  <link rel="stylesheet" href="{{ asset('admin-assets/login/bootstrap5/bootstrap.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('admin-assets/login/css/login.css') }}" />
  <link rel="stylesheet" href="{{ asset('admin-assets/panel/css/toastr/toastr.min.css') }}" />
  @vite(['resources/js/app.js', 'resources/css/app.css'])

  <!-- Scripts -->
  <script src="{{ asset('admin-assets/panel/js/sweetalert2/sweetalert2.js') }}"></script>
  @livewireStyles
</head>

<body>

  <main class="min-h-screen">
    <div class="login-wrapper d-flex w-100 justify-content-center align-items-center h-100vh">
      {{ $slot }}
    </div>
  </main>

  <!-- Scripts -->
  <script src="{{ asset('admin-assets/js/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('admin-assets/js/bootstrap/bootstrap.min.js') }}"></script>
  <script src="{{ asset('admin-assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('admin-assets/js/main.js') }}"></script>
  <script src="{{ asset('admin-assets/panel/js/toastr/toastr.min.js') }}"></script>
  <script src="{{ asset('admin-assets/js/login.js') }}"></script>

  @livewireScripts
  @once
  <script>

    document.addEventListener('DOMContentLoaded', () => {
      if (typeof toastr !== 'undefined') {
        toastr.options = {
          timeOut: 10000,
          progressBar: true,
          positionClass: 'toast-top-right',
          preventDuplicates: true, // جلوگیری از نمایش توسترهای تکراری
          newestOnTop: true,
          maxOpened: 1, // فقط یک توستر در هر لحظه
          closeButton: false,
          
        };
        
      }
      toastr.options.rtl = true;
    });

    Livewire.on('otpSent', (data) => {
      localStorage.removeItem('otpTimerData');
    });

    // تعریف متغیر isSubmitting در اسکوپ جهانی فقط یک‌بار
    window.isSubmitting = false;

    document.addEventListener('livewire:initialized', () => {
      // انتخاب فرم‌های مختلف با کلاس‌های منحصربه‌فرد
      const forms = document.querySelectorAll(
        'form.login-register-form, form.login-confirm-form, form.login-user-pass-form');
      forms.forEach((form) => {
        form.addEventListener('submit', (e) => {
          if (window.isSubmitting) {
            e.preventDefault();
            return;
          }
          window.isSubmitting = true;
          setTimeout(() => {
            window.isSubmitting = false;
          }, 1000); // ریست فلگ پس از 1 ثانیه
        });
      });
    });
  </script>
  @endonce
 
  @stack('scripts')
</body>

</html>
