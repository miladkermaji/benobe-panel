@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/turn/schedule/my-appointments.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'نوبت های من')

<div class="appointments-content w-100 d-flex align-items-center flex-column">
  <div class="appointments-content-wrapper">
    <div class="top-appointment d-flex justify-content-start p-4 align-items-center">
      <div>
        <span class="text-dark fw-bold">نوبت های من</span>
      </div>
    </div>
  </div>
  @if (count($appointments) > 0)
    @foreach ($appointments as $appointment)
      <div class="appointments-content-wrapper mt-3">
        <div class="top-appointment d-flex justify-content-start p-4">
          <div class="d-flex w-100 justify-content-between align-items-center">
            <div>
              <div class="d-flex align-items-center">
                <div>
                  <img width="70" height="70" alt="avatar" class="prof-img rounded-circle bg-light"
                    src="{{ asset('mc-assets/panel/img/pro.jpg') }}">
                </div>
                <div class="mx-2">
                  <h6 class="d-block fw-bold"> {{ $appointment->doctor->first_name }}
                    {{ $appointment->doctor->last_name }}</h6>
                  <span class="font-size-13"> {{ $appointment->doctor->specialty_name }}</span>
                </div>
              </div>
            </div>
            <div class="d-flex align-items-center position-relative">
              @php
                $statusConfig = [
                    'attended' => ['label' => 'ویزیت شده', 'class' => 'text-success'],
                    'scheduled' => ['label' => 'در انتظار ', 'class' => 'text-primary'],
                    'cancelled' => ['label' => 'لغو شده', 'class' => 'text-danger'],
                    'missed' => ['label' => 'از دست رفته', 'class' => 'text-danger'],
                    'pending_review' => ['label' => 'در انتظارث', 'class' => 'text-info'],
                    'default' => ['label' => 'نامشخص', 'class' => 'text-muted'],
                ];
                $statusInfo = $statusConfig[$appointment->status] ?? $statusConfig['default'];
              @endphp
              <span class="font-size-13 my-sm-badge {{ $statusInfo['class'] }}">
                {{ $statusInfo['label'] }}
              </span>
              <span class="mx-2 btn-details">
                <img class="btn-show-details cursor-pointer"
                  src="{{ asset('mc-assets/icons/dots-vertical-svgrepo-com.svg') }}" alt="جزئیات">
              </span>
              <div class="drop-side-details-content d-none">
                <div class="d-flex flex-column p-2">
                  <a href="https://emr-benobe.ir/profile/doctor/{{ $appointment->doctor->slug }}" target="_blank"
                    rel="noreferrer" class="dropdown-item d-flex align-items-center p-2 cursor-pointer space-s-2">
                    <img class="btn-show-details" src="{{ asset('mc-assets/icons/qabz.svg') }}" alt="قبض">
                    <span class="text-sm font-medium">قبض نوبت</span>
                  </a>
                  <div class="dropdown-item d-flex align-items-center p-2 cursor-pointer space-s-2 share-appointment"
                    data-share-url="https://emr-benobe.ir/profile/doctor/{{ $appointment->doctor->slug }}"
                    data-share-title="نوبت پزشکی با {{ $appointment->doctor->first_name }} {{ $appointment->doctor->last_name }}"
                    data-share-text="جزئیات نوبت من در به نوبه">
                    <img class="btn-show-details" src="{{ asset('mc-assets/icons/share.svg') }}" alt="اشتراک">
                    <span class="text-sm font-medium">اشتراک‌گذاری</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="p-3">
          <div class="bg-light w-100 border-radius-6">
            <div class="d-flex flex-column w-100 bg-light p-3 px-4 cursor-pointer">
              <div class="d-flex align-items-center justify-content-between">
                <span class="font-size-13 mt-2 fw-bold">تاریخ نوبت:</span>
                <span class="text-sm fw-bold font-size-13 mt-2">
                  {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($appointment->appointment_date))->format('Y/m/d') }}
                </span>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <span class="font-size-13 mt-2 fw-bold">زمان نوبت:</span>
                <span class="text-sm fw-bold font-size-13 mt-2">
                  {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($appointment->appointment_time))->format('H:i') }}
                </span>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <span class="font-size-13 mt-2 fw-bold">مراجعه‌کننده:</span>
                <span class="text-sm fw-bold font-size-13 mt-2">
                  {{ $appointment->patientable?->first_name }} {{ $appointment->patientable?->last_name }}
                </span>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <span class="font-size-13 mt-2 fw-bold">کد پیگیری:</span>
                <span class="text-sm fw-bold font-size-13 mt-2">{{ $appointment->tracking_code }}</span>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <span class="font-size-13 mt-2 fw-bold">میانگین زمان انتظار در مطب:</span>
                <span class="text-sm fw-bold font-size-13 mt-2">30 دقیقه</span>
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex">
          <a href="#" rel="noreferrer" class="d-flex align-items-center w-100 p-3 px-0">
            <div class="d-flex align-items-center justify-content-center w-12">
              <img class="btn-show-details" src="{{ asset('mc-assets/icons/location.svg') }}" alt="موقعیت">
            </div>
            <span class="text-sm line-clamp-2" data-testid="location__address">
              {{ $appointment->doctor->city->name ?? 'نامشخص' }}،
              {{ $appointment->doctor->province->name ?? 'نامشخص' }}
            </span>
          </a>
        </div>
        <div class="p-3">
          <div class="d-flex align-items-center justify-content-between w-100 bg-light p-3 px-4 border-radius-6">
            <span class="text-sm">هنوز به این پزشک امتیازی نداده‌اید.</span>
            <a href="https://emr-benobe.ir/profile/doctor/{{ $appointment->doctor->slug }}" target="_blank"
              rel="noreferrer" class="d-flex align-items-center text-info">
              <span class="text-sm fw-bold ml-2 text-info mx-2">ثبت نظر</span>
              <img class="btn-show-details" src="{{ asset('mc-assets/icons/caret-left.svg') }}" alt="ثبت نظر">
            </a>
          </div>
        </div>
        <div class="p-3 w-100 border-radius-6">
          @php
            $activeStatuses = ['scheduled', 'pending_review'];
            $isButtonDisabled = in_array($appointment->status, $activeStatuses);
          @endphp
          <button class="w-100 btn btn-outline-primary h-50 border-radius-4" {{ $isButtonDisabled ? 'disabled' : '' }}
            onclick="window.location.href='https://emr-benobe.ir/profile/doctor/{{ $appointment->doctor->slug }}'">
            دریافت نوبت مجدد
          </button>
        </div>
      </div>
    @endforeach
  @else
    <div class="container mt-2">
      <div class="alert alert-info w-100 text-center">
        <p class="fw-bold">نوبتی یافت نشد.</p>
      </div>
    </div>
  @endif
  <div class="pagination-links w-100 d-flex justify-content-center">
    {{ $appointments->links('pagination::bootstrap-4') }}
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('mc-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
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

    // Toggle details on button click
    $(document).on('click', '.btn-show-details', function(event) {
      event.stopPropagation();
      const $dropdown = $(this).closest('.d-flex').find('.drop-side-details-content');
      $('.drop-side-details-content').not($dropdown).addClass('d-none');
      $dropdown.toggleClass('d-none');
    });

    // Hide details when clicking outside
    $(document).on('click', function(event) {
      if (!$(event.target).closest('.drop-side-details-content').length && !$(event.target).hasClass(
          'btn-show-details')) {
        $('.drop-side-details-content').addClass('d-none');
        $('.share-options').remove();
      }
    });

    // Share functionality
    $(document).on('click', '.share-appointment', function(event) {
      event.preventDefault();
      event.stopPropagation();

      $('.share-options').remove(); // حذف باکس‌های اشتراک‌گذاری قبلی

      const shareUrl = $(this).data('share-url');
      const shareTitle = $(this).data('share-title');
      const shareText = $(this).data('share-text');
      const shareData = {
        title: shareTitle,
        text: shareText,
        url: shareUrl
      };

      if (navigator.share) {
        navigator.share(shareData)
          .then(() => console.log('اشتراک‌گذاری موفق'))
          .catch((error) => console.log('خطا در اشتراک‌گذاری:', error));
      } else {
        const shareOptions = `
          <div class="share-options p-3">
            <a href="https://telegram.me/share/url?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}" target="_blank" class="share-option d-flex align-items-center p-2">
              <img src="{{ asset('mc-assets/icons/telegram.svg') }}" alt="تلگرام" class="me-2" style="width: 24px;">
              <span>تلگرام</span>
            </a>
            <a href="https://api.whatsapp.com/send?text=${encodeURIComponent(shareText + ' ' + shareUrl)}" target="_blank" class="share-option d-flex align-items-center p-2">
              <img src="{{ asset('mc-assets/icons/whatsapp-svgrepo-com.svg') }}" alt="واتساپ" class="me-2" style="width: 24px;">
              <span>واتساپ</span>
            </a>
            <a href="mailto:?subject=${encodeURIComponent(shareTitle)}&body=${encodeURIComponent(shareText + ' ' + shareUrl)}" class="share-option d-flex align-items-center p-2">
              <img src="{{ asset('mc-assets/icons/email.svg') }}" alt="ایمیل" class="me-2" style="width: 24px;">
              <span>ایمیل</span>
            </a>
            <div class="share-option d-flex align-items-center p-2 cursor-pointer copy-link" data-url="${shareUrl}">
              <img src="{{ asset('mc-assets/icons/copy.svg') }}" alt="کپی" class="me-2" style="width: 24px;">
              <span>کپی لینک</span>
            </div>
          </div>
        `;
        const $dropdown = $(this).closest('.drop-side-details-content');
        $dropdown.append(shareOptions);
        $('.copy-link').on('click', function() {
          navigator.clipboard.writeText($(this).data('url')).then(() => {
            Swal.fire({
              icon: 'success',
              title: 'کپی شد!',
              text: 'لینک در کلیپ‌بورد کپی شد.',
              timer: 1500,
              showConfirmButton: false
            });
            $('.share-options').remove();
          });
        });
      }
    });
  });
</script>
@endsection
