<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ورود مراکز درمانی - به نوبه</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/x-icon" href="{{ asset('app-assets/logos/favicon.ico') }}">
  <link rel="stylesheet" href="{{ asset('app-assets/css/bootstrap.rtl.min.css') }}">
  <link rel="stylesheet" href="{{ asset('app-assets/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('app-assets/css/toastr.min.css') }}">
  <link rel="stylesheet" href="{{ asset('app-assets/css/sweetalert2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('mc-assets/css/mc-auth.css') }}">
  @stack('styles')
</head>

<body>
  <div id="app">
    {{ $slot }}
  </div>

  <script src="{{ asset('app-assets/js/jquery.min.js') }}"></script>
  <script src="{{ asset('app-assets/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('app-assets/js/toastr.min.js') }}"></script>
  <script src="{{ asset('app-assets/js/sweetalert2.min.js') }}"></script>
  <script src="{{ asset('app-assets/js/livewire.js') }}"></script>
  @stack('scripts')
</body>

</html>
