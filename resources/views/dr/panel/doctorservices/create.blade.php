@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('Dr-assets/css/panel/doctorservice/doctorservice.css') }}" rel="stylesheet" />
   <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'افزودن خدمات جدید')
@livewire('dr.panel.doctorservices.doctorservice-create')
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
      localStorage.setItem('selectedClinic', 'ویزیت آنلاین به نوبه');
      localStorage.setItem('selectedClinicId', 'default');
    }

    // **بررسی کلینیک‌های غیرفعال و اضافه کردن افکت هشدار**
    function checkInactiveClinics() {
      var hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
      if (hasInactiveClinics) {
        $('.dropdown-trigger').addClass('warning');
      } else {
        $('.dropdown-trigger').removeClass('warning');
      }
    }

    checkInactiveClinics(); // اجرای بررسی هنگام بارگذاری صفحه

    // باز و بسته کردن دراپ‌داون
    $('.dropdown-trigger').on('click', function(event) {

      event.stopPropagation();
      dropdownOpen = !dropdownOpen;
      $(this).toggleClass('border border-primary');
      $('.my-dropdown-menu').toggleClass('d-none');

      setTimeout(() => {
        dropdownOpen = $('.my-dropdown-menu').is(':visible');
      }, 100);
    });

    // بستن دراپ‌داون هنگام کلیک بیرون
    $(document).on('click', function() {
      if (dropdownOpen) {
        $('.dropdown-trigger').removeClass('border border-primary');
        $('.my-dropdown-menu').addClass('d-none');
        dropdownOpen = false;
      }
    });

    // جلوگیری از بسته شدن هنگام کلیک روی منوی دراپ‌داون
    $('.my-dropdown-menu').on('click', function(event) {
      event.stopPropagation();
    });

    $('.option-card').on('click', function() {
      let currentDate = moment().format('YYYY-MM-DD');
      let persianDate = moment(currentDate, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');


      var selectedText = $(this).find('.font-weight-bold.d-block.fs-15').text().trim();
      var selectedId = $(this).attr('data-id');

      $('.option-card').removeClass('card-active');
      $(this).addClass('card-active');

      $('.dropdown-label').text(selectedText);
      // Update local storage
      localStorage.setItem('selectedClinic', selectedText);
      localStorage.setItem('selectedClinicId', selectedId);

      checkInactiveClinics();
      handleDateSelection(persianDate, selectedId);
      loadAppointments(persianDate, selectedId)
      $('.dropdown-trigger').removeClass('border border-primary');
      $('.my-dropdown-menu').addClass('d-none');
      dropdownOpen = false;
    });
  });
</script>
@endsection
@endsection
