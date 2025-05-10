@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/profile/security.css') }}" rel="stylesheet" />
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
@section('bread-crumb-title', 'امنیت')

<div class="subuser-content d-flex w-100 justify-content-center">
  <div class="subuser-content-wrapper">
    <!-- تاریخچه ورود منشی -->
    <div class="card">
      <div class="card-header">
        <span>📜 تاریخچه ورود منشی</span>
      </div>
      <div class="card-body" id="secretaryLogsContainer">
        @include('dr.panel.profile.partials.secretary_logs')
      </div>
    </div>

    <!-- تاریخچه ورود دکتر -->
    <div class="card">
      <div class="card-header">
        <span>📜 تاریخچه ورود دکتر</span>
      </div>
      <div class="card-body" id="doctorLogsContainer">
        @include('dr.panel.profile.partials.doctor_logs')
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')

<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>

<script>
  $(document).ready(function() {
    let dropdownOpen = false;
    let selectedClinic = localStorage.getItem('selectedClinic');
    let selectedClinicId = localStorage.getItem('selectedClinicId');
    if (selectedClinic && selectedClinicId) {
      $('.dropdown-label').text(selectedClinic);
      $('.option-card').each(function() {
        if ($(this).attr('data-id') === selectedClinicId) {
          $('.option-card').removeClass('card-active');
          $(this).addClass('card-active');
        }
      });
    } else {
      localStorage.setItem('selectedClinic', 'مشاوره آنلاین به نوبه');
      localStorage.setItem('selectedClinicId', 'default');
    }

    function checkInactiveClinics() {
      var hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
      if (hasInactiveClinics) {
        $('.dropdown-trigger').addClass('warning');
      } else {
        $('.dropdown-trigger').removeClass('warning');
      }
    }
    checkInactiveClinics();

    $('.dropdown-trigger').on('click', function(event) {
      event.stopPropagation();
      dropdownOpen = !dropdownOpen;
      $(this).toggleClass('border border-primary');
      $('.my-dropdown-menu').toggleClass('d-none');
      setTimeout(() => {
        dropdownOpen = $('.my-dropdown-menu').is(':visible');
      }, 100);
    });

    $(document).on('click', function() {
      if (dropdownOpen) {
        $('.dropdown-trigger').removeClass('border border-primary');
        $('.my-dropdown-menu').addClass('d-none');
        dropdownOpen = false;
      }
    });

    $('.my-dropdown-menu').on('click', function(event) {
      event.stopPropagation();
    });

    $('.option-card').on('click', function() {
      var selectedText = $(this).find('.fw-bold.d-block.fs-15').text().trim();
      var selectedId = $(this).attr('data-id');
      $('.option-card').removeClass('card-active');
      $(this).addClass('card-active');
      $('.dropdown-label').text(selectedText);

      localStorage.setItem('selectedClinic', selectedText);
      localStorage.setItem('selectedClinicId', selectedId);
      checkInactiveClinics();
      $('.dropdown-trigger').removeClass('border border-primary');
      $('.my-dropdown-menu').addClass('d-none');
      dropdownOpen = false;

      window.location.href = window.location.pathname + "?selectedClinicId=" + selectedId;
    });
  });
  $(document).ready(function() {
    function loadLogs(page = 1) {
      $.ajax({
        url: "{{ route('dr-edit-profile-security') }}?page=" + page,
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

      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: "این عمل قابل بازگشت نیست!",
        icon: 'warning',
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
            success: function() {
              row.remove();
              loadLogs();
              toastr.success('تارخچه مورد نظر با موفقیت حذف شد.');
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
        url: "{{ route('dr-get-doctor-logs') }}?page=" + page,
        type: 'GET',
        success: function(response) {
          $('#doctorLogsContainer').html(response.doctorLogsHtml);
        }
      });
    }

    function loadSecretaryLogs(page) {
      $.ajax({
        url: "{{ route('dr-get-secretary-logs') }}?page=" + page,
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
