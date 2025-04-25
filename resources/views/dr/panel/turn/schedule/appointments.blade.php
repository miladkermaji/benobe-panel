@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/appointments.css') }}" rel="stylesheet" />


@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'لیست نوبت ها')
@livewire('dr.panel.turn.schedule.appointments-list')
@endsection
@section('scripts')
<script>
  $('#rescheduleModal').on('show.bs.modal', function() {
    // Check if stylesheet is already loaded
    if (!$('#rescheduleModalStyles').length) {
      $('<link>', {
        id: 'rescheduleModalStyles',
        rel: 'stylesheet',
        type: 'text/css',
        href: '{{ asset('dr-assets/panel/css/reschedule.css') }}'
      }).appendTo('head');
    }
  });

  $('#rescheduleModal').on('hidden.bs.modal', function() {
    // Optionally remove the stylesheet when modal is closed
    $('#rescheduleModalStyles').remove();
  });
</script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var appointmentsCountUrl = "{{ route('appointments.count') }}";
  var updateStatusAppointmentUrl =
    "{{ route('updateStatusAppointment', ':id') }}";
</script>
<script>
    $(document).ready(function() {
    let dropdownOpen = false;
    let selectedClinic = localStorage.getItem('selectedClinic');
    let selectedClinicId = localStorage.getItem('selectedClinicId');

    // تنظیم مقدار اولیه برای منوی کشویی
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
      $('.dropdown-label').text('مشاوره آنلاین به نوبه');
    }

    // بررسی کلینیک‌های غیرفعال برای نمایش هشدار
    function checkInactiveClinics() {
      var hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
      if (hasInactiveClinics) {
        $('.dropdown-trigger').addClass('warning');
      } else {
        $('.dropdown-trigger').removeClass('warning');
      }
    }
    checkInactiveClinics();

    // مدیریت کلیک روی دکمه منوی کشویی
    $('.dropdown-trigger').on('click', function(event) {
      event.stopPropagation();
      dropdownOpen = !dropdownOpen;
      $(this).toggleClass('border border-primary');
      $('.my-dropdown-menu').toggleClass('d-none');
      setTimeout(() => {
        dropdownOpen = $('.my-dropdown-menu').is(':visible');

      }, 100);
    });

    // بستن منوی کشویی با کلیک خارج از آن
    $(document).on('click', function() {
      if (dropdownOpen) {
        $('.dropdown-trigger').removeClass('border border-primary');
        $('.my-dropdown-menu').addClass('d-none');
        dropdownOpen = false;
      }
    });

    // جلوگیری از بسته شدن منو با کلیک داخل آن
    $('.my-dropdown-menu').on('click', function(event) {
      event.stopPropagation();
    });

    // مدیریت انتخاب گزینه کلینیک
    $('.option-card').on('click', function() {
      let currentDate = moment().format('YYYY-MM-DD');
      let persianDate = moment(currentDate, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');
      var selectedText = $(this).find('.fw-bold.d-block.fs-15').text().trim();
      var selectedId = $(this).attr('data-id');
      $('.option-card').removeClass('card-active');
      $(this).addClass('card-active');
      $('.dropdown-label').text(selectedText);
      localStorage.setItem('selectedClinic', selectedText);
      localStorage.setItem('selectedClinicId', selectedId);
      selectedClinicId = selectedId; // آپدیت متغیر جهانی
      window.location.reload()

      $('.dropdown-trigger').removeClass('border border-primary');
      $('.my-dropdown-menu').addClass('d-none');

      dropdownOpen = false;
    });
  });
</script>
@endsection
