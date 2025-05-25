<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" type="image/x-icon" href="{{ asset('app-assets/logos/favicon.ico') }}">
<link href="{{ asset('admin-assets/panel/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('admin-assets/panel/css/fontawesome/fontawesome.min.css') }}" rel="stylesheet">
<script src="{{ asset('admin-assets/panel/js/home/bootstrap/bootstrap.popper.min.js') }}"></script>

<script src="{{ asset('admin-assets/panel/js/sweetalert2/sweetalert2.js') }}"></script>
<link rel="stylesheet" href="{{ asset('dr-assets/panel/css/sweetalert2-custom.css') }}">

{{-- persian calander --}}

{{-- persian calander --}}
<link rel="stylesheet" href="{{ asset('admin-assets/panel/css/dr-panel.css') }}">
<link rel="stylesheet" href="{{ asset('admin-assets/panel/css/custom-asset.css') }}">

<script src="{{ asset('admin-assets/panel/js/jquery-3.4.1.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('admin-assets/panel/css/style.css') }}">
<link rel="stylesheet" href="{{ asset('admin-assets/panel/css/responsive_991.css') }}" media="(max-width:991px)">
<link rel="stylesheet" href="{{ asset('admin-assets/panel/css/responsive_768.css') }}" media="(max-width:768px)">
<link rel="stylesheet" href="{{ asset('admin-assets/panel/css/font.css') }}">
<link type="text/css" href="{{ asset('admin-assets/panel/jalali-datepicker/jalalidatepicker.min.css') }}"
  rel="stylesheet" />
{{-- tom select --}}
<link rel="stylesheet" href="{{ asset('dr-assets/panel/css/toastify/toastify.min.css') }}">
{{-- tom select --}}
<link rel="stylesheet" href="{{ asset('dr-assets/panel/css/tom-select.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin-assets/panel/css/toastr/toastr.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin-assets/panel/css/global-loader.css') }}">
<link rel="stylesheet" href="{{ asset('admin-assets/panel/css/selesct2/select2.css') }}">

<link rel="stylesheet" href="{{ asset('admin-assets/panel/css/codemirror/5.65.16/codemirror.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin-assets/panel/custom-datepicker/custom-datepicker.css') }}">
<link rel="stylesheet" href="{{ asset('admin-assets/panel/flatpickr/dist/flatpickr.min.css') }}">
@php
  $excludedRoutes = ['admin-panel', 'admin.tools.page-builder.index'];
@endphp

@unless (Request::routeIs($excludedRoutes))
  <link rel="stylesheet" href="{{ asset('admin-assets/css/my-form.css') }}">
@endunless
{{-- tom select --}}
@vite(['resources/css/app.css', 'resources/css/timepicker.css', 'resources/js/app.js', 'resources/js/timepicker.js'])
