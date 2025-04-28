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
    let selectedClinic = localStorage.getItem("selectedClinic");
    let selectedClinicId = localStorage.getItem("selectedClinicId");
    if (selectedClinic && selectedClinicId) {
      $(".dropdown-label").text(selectedClinic);
      $(".option-card").each(function() {
        if ($(this).attr("data-id") === selectedClinicId) {
          $(".option-card").removeClass("card-active");
          $(this).addClass("card-active");
        }
      });
    } else {
      localStorage.setItem("selectedClinic", "مشاوره آنلاین به نوبه");
      localStorage.setItem("selectedClinicId", "default");
    }

    function checkInactiveClinics() {
      var hasInactiveClinics =
        $('.option-card[data-active="0"]').length > 0;
      if (hasInactiveClinics) {
        $(".dropdown-trigger").addClass("warning");
      } else {
        $(".dropdown-trigger").removeClass("warning");
      }
    }
    checkInactiveClinics();

    $(".dropdown-trigger").on("click", function(event) {
      event.stopPropagation();
      dropdownOpen = !dropdownOpen;
      $(this).toggleClass("border border-primary");
      $(".my-dropdown-menu").toggleClass("d-none");
      setTimeout(() => {
        dropdownOpen = $(".my-dropdown-menu").is(":visible");
      }, 100);
    });

    $(document).on("click", function() {
      if (dropdownOpen) {
        $(".dropdown-trigger").removeClass("border border-primary");
        $(".my-dropdown-menu").addClass("d-none");
        dropdownOpen = false;
      }
    });

    $(".my-dropdown-menu").on("click", function(event) {
      event.stopPropagation();
    });

    $(".option-card").on("click", function() {
      var selectedText = $(this)
        .find(".fw-bold.d-block.fs-15")
        .text()
        .trim();
      var selectedId = $(this).attr("data-id");
      $(".option-card").removeClass("card-active");
      $(this).addClass("card-active");
      $(".dropdown-label").text(selectedText);

      localStorage.setItem("selectedClinic", selectedText);
      localStorage.setItem("selectedClinicId", selectedId);
      checkInactiveClinics();
      $(".dropdown-trigger").removeClass("border border-primary");
      $(".my-dropdown-menu").addClass("d-none");
      dropdownOpen = false;

      // ریلود صفحه با پارامتر جدید
      window.location.href =
        window.location.pathname + "?selectedClinicId=" + selectedId;
    });
  });
</script>
@endsection
