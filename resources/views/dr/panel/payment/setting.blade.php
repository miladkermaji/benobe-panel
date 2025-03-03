@extends('dr.panel.layouts.master')
@section('styles')
    <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
    <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
    <link type="text/css" href="{{ asset('dr-assets/panel/css/payment/setting.css') }}" rel="stylesheet" />
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
@section('bread-crumb-title', 'پرداخت')
    <livewire:dr.panel.payment.payment-setting-component />
@endsection
@section('scripts')
    <script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
    <script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
    <script src="{{ asset('dr-assets/panel/js/payment/setting.js') }}"></script>
    <script>
        var appointmentsSearchUrl = "{{ route('search.appointments') }}";
        var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
    </script>
@endsection