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

<div class="subuser-content d-flex w-100 justify-content-center">
  <div class="subuser-content-wrapper">
    <!-- تاریخچه ورود منشی -->
    <div class="card">
      <div class="card-header">
        <span>📜 تاریخچه ورود منشی</span>
      </div>
      <div class="card-body" id="secretaryLogsContainer">
        @include('mc.panel.profile.partials.secretary_logs')
      </div>
    </div>

    <!-- تاریخچه ورود دکتر -->
    <div class="card">
      <div class="card-header">
        <span>📜 تاریخچه ورود دکتر</span>
      </div>
      <div class="card-body" id="doctorLogsContainer">
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
        },
        error: function(xhr) {
          if (xhr.status === 400) {
            toastr.error('لطفاً ابتدا یک پزشک انتخاب کنید.');
          } else {
            toastr.error('خطا در بارگذاری اطلاعات.');
          }
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

      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: "این عمل قابل بازگشت نیست!",

        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'بله، حذف شود!',
        cancelButtonText: 'لغو'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('delete-log', ':id') }}".replace(':id', logId),
            type: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              row.remove();
              loadLogs();
              toastr.success(response.message || 'تاریخچه مورد نظر با موفقیت حذف شد.');
            },
            error: function(xhr) {
              let message = 'خطا در حذف تاریخچه.';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
              }
              toastr.error(message);
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
        },
        error: function(xhr) {
          if (xhr.status === 400) {
            toastr.error('لطفاً ابتدا یک پزشک انتخاب کنید.');
          } else {
            toastr.error('خطا در بارگذاری تاریخچه دکتر.');
          }
        }
      });
    }

    function loadSecretaryLogs(page) {
      $.ajax({
        url: "{{ route('mc-get-secretary-logs') }}?page=" + page,
        type: 'GET',
        success: function(response) {
          $('#secretaryLogsContainer').html(response.secretaryLogsHtml);
        },
        error: function(xhr) {
          if (xhr.status === 400) {
            toastr.error('لطفاً ابتدا یک پزشک انتخاب کنید.');
          } else {
            toastr.error('خطا در بارگذاری تاریخچه منشی.');
          }
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
