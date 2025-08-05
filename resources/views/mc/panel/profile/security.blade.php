@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/profile/security.css') }}" rel="stylesheet" />

@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'امنیت')

<div class="subuser-content d-flex w-100 justify-content-center" x-data="{ secretaryOpen: false, doctorOpen: false }">
  <div class="subuser-content-wrapper">
    <!-- تاریخچه ورود منشی -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>📜 تاریخچه ورود منشی</span>
        <!-- Mobile Toggle Button -->
        <button class="btn btn-link text-white p-0 d-md-none mobile-toggle-btn" type="button"
          @click="secretaryOpen = !secretaryOpen" :aria-expanded="secretaryOpen">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            class="toggle-icon" :class="{ 'rotate-180': secretaryOpen }">
            <path d="M6 9l6 6 6-6" />
          </svg>
        </button>
      </div>
      <div class="card-body d-md-block" x-show="secretaryOpen" x-transition id="secretaryLogsContainer">
        @include('mc.panel.profile.partials.secretary_logs')
      </div>
    </div>

    <!-- تاریخچه ورود دکتر -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>📜 تاریخچه ورود دکتر</span>
        <!-- Mobile Toggle Button -->
        <button class="btn btn-link text-white p-0 d-md-none mobile-toggle-btn" type="button"
          @click="doctorOpen = !doctorOpen" :aria-expanded="doctorOpen">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            class="toggle-icon" :class="{ 'rotate-180': doctorOpen }">
            <path d="M6 9l6 6 6-6" />
          </svg>
        </button>
      </div>
      <div class="card-body d-md-block" x-show="doctorOpen" x-transition id="doctorLogsContainer">
        @include('mc.panel.profile.partials.doctor_logs')
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')

<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>

<script>
  $(document).ready(function() {
    function loadLogs(page = 1) {
      $.ajax({
        url: "{{ route('mc-edit-profile-security') }}?page=" + page,
        type: 'GET',
        success: function(response) {
          $('#doctorLogsContainer').html(response.doctorLogs);
          $('#secretaryLogsContainer').html(response.secretaryLogs);
        }
      });
    }

    $(document).on('click', '.pagination-links a', function(e) {
      e.preventDefault();
      let page = $(this).attr('href').split('page=')[1];
      loadLogs(page);
    });

    $(document).on('click', '.delete-log', function() {
      let logId = $(this).data('id');
      let row = $(this).closest('tr');
      let card = $(this).closest('.note-card');

      Swal.fire({
        title: 'حذف لاگ',
        text: 'آیا مطمئن هستید که می‌خواهید این لاگ را حذف کنید؟',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'خیر'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('delete-log', ':id') }}".replace(':id', logId),
            type: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
              row.remove();
              card.remove();
              loadLogs();
              toastr.success('تاریخچه مورد نظر با موفقیت حذف شد.');
            }
          });
        }
      });
    });
  });
</script>
<script>
  $(document).ready(function() {
    function loadDoctorLogs(page) {
      $.ajax({
        url: "{{ route('mc-get-doctor-logs') }}?page=" + page,
        type: 'GET',
        success: function(response) {
          $('#doctorLogsContainer').html(response.doctorLogsHtml);
        }
      });
    }

    function loadSecretaryLogs(page) {
      $.ajax({
        url: "{{ route('mc-get-secretary-logs') }}?page=" + page,
        type: 'GET',
        success: function(response) {
          $('#secretaryLogsContainer').html(response.secretaryLogsHtml);
        }
      });
    }

    // پیجینیشن دکتر
    $(document).on('click', '#doctorLogsContainer .pagination-links a', function(e) {
      e.preventDefault();
      let page = $(this).attr('href').split('page=')[1];
      loadDoctorLogs(page);
    });

    // پیجینیشن منشی
    $(document).on('click', '#secretaryLogsContainer .pagination-links a', function(e) {
      e.preventDefault();
      let page = $(this).attr('href').split('page=')[1];
      loadSecretaryLogs(page);
    });
  });
</script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
</script>
@endsection
