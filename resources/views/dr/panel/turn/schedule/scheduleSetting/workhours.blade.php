@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/scheduleSetting/workhours.css') }}"
    rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')

@section('bread-crumb-title', ' ساعت کاری من')
@livewire('dr.panel.work-hours')
@endsection

@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
  jalaliDatepicker.startWatch();
  var svgUrl = "{{ asset('dr-assets/icons/copy.svg') }}";
  var trashSvg = "{{ asset('dr-assets/icons/trash.svg') }}";
  $(document).ready(function() {
    let dropdownOpen = false;
    let selectedClinic = localStorage.getItem("selectedClinic");
    let selectedClinicId = localStorage.getItem("selectedClinicId");

    // چک کردن و ست کردن مقدار پیش‌فرض برای localStorage
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
      selectedClinicId = "default";
      $(".dropdown-label").text("مشاوره آنلاین به نوبه");
    }

    // ارسال مقدار اولیه selectedClinicId به Livewire برای بارگذاری داده‌ها
    Livewire.dispatch('setSelectedClinicId', {
      clinicId: selectedClinicId
    });

    // چک کردن کلینیک‌های غیرفعال
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

    // رویداد کلیک برای باز و بسته کردن دراپ‌داون
    $(".dropdown-trigger").on("click", function(event) {
      event.stopPropagation();
      dropdownOpen = !dropdownOpen;
      $(this).toggleClass("border border-primary");
      $(".my-dropdown-menu").toggleClass("d-none");
      setTimeout(() => {
        dropdownOpen = $(".my-dropdown-menu").is(":visible");
      }, 100);
    });

    // بستن دراپ‌داون با کلیک خارج از آن
    $(document).on("click", function() {
      if (dropdownOpen) {
        $(".dropdown-trigger").removeClass("border border-primary");
        $(".my-dropdown-menu").addClass("d-none");
        dropdownOpen = false;
      }
    });

    // جلوگیری از بسته شدن دراپ‌داون با کلیک داخل آن
    $(".my-dropdown-menu").on("click", function(event) {
      event.stopPropagation();
    });

    // انتخاب کلینیک و ریلود صفحه
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

      // ارسال رویداد به Livewire برای به‌روزرسانی
      Livewire.dispatch('setSelectedClinicId', {
        clinicId: selectedId
      });

      // ریلود صفحه با پارامتر جدید
      window.location.href =
        window.location.pathname + "?selectedClinicId=" + selectedId;
    });
  });
</script>

@endsection
