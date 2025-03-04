@extends('admin.panel.layouts.master')
@section('styles')
 <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
 {{ 'به نوبه | پنل مدیریت' }}
@endsection
@section('content')
@section('bread-crumb-title', 'داشبورد')
@section('scripts')


<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
 var appointmentsSearchUrl = "{{ route('search.appointments') }}";
 var updateStatusAppointmentUrl =
  "{{ route('updateStatusAppointment', ':id') }}";
</script>
<script>
 document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('showModal')) {
   // فرض کنید ID مودال شما "activation-modal" است
   $('#activation-modal').modal('show');
  }
 });
</script>
@endsection
