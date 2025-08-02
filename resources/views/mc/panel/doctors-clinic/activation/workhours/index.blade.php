@extends('mc.panel.doctors-clinic.layouts.master')
@section('styles')
  <link rel="stylesheet" href="{{ asset('mc-assets/panel/css/doctors-clinic/activation/workhours/workhours.css') }}">
  <link type="text/css" href="{{ asset('mc-assets/panel/css/turn/schedule/scheduleSetting/workhours.css') }}"
    rel="stylesheet" />
@endsection


@section('headerTitle')
  ساعت کاری
@endsection

@section('backUrl')
  {{ route('duration.index', $clinicId) }}
@endsection
@section('content')
  <div class="d-flex w-100 justify-content-center align-items-center flex-column">
    <div class="roadmap-container mt-3">
      <div class="step completed">
        <span class="step-title">شروع</span>
        <svg class="icon" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="#0d6efd" stroke-width="2" fill="#0d6efd" />
          <path d="M7 12l3 3l5-5" stroke="#fff" stroke-width="2" fill="none" />
        </svg>
      </div>
      <div class="line completed"></div>
      <div class="step completed">
        <span class="step-title">آدرس</span>
        <svg class="icon" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="#0d6efd" stroke-width="2" fill="#0d6efd" />
          <path d="M7 12l3 3l5-5" stroke="#fff" stroke-width="2" fill="none" />
        </svg>
      </div>
      <div class="line completed"></div>
      <div class="step completed">
        <span class="step-title"> بیعانه</span>
        <svg class="icon" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="#0d6efd" stroke-width="2" fill="#0d6efd" />
          <path d="M7 12l3 3l5-5" stroke="#fff" stroke-width="2" fill="none" />
        </svg>
      </div>
      <div class="line completed"></div>
      <div class="step active">
        <span class="step-title">ساعت کاری</span>
        <svg class="icon" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="#0d6efd" stroke-width="2" fill="#0d6efd" />
          <path d="M7 12l3 3l5-5" stroke="#fff" stroke-width="2" fill="none" />
        </svg>
      </div>
      <div class="line"></div>
      <div class="step active">
        <span class="step-title">پایان</span>
        <svg class="icon" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="#0d6efd" stroke-width="2" fill="#0d6efd" />
          <path d="M7 12l3 3l5-5" stroke="#fff" stroke-width="2" fill="none" />
        </svg>
      </div>
    </div>
  </div>
  @livewire('mc.panel.work-hours', ['clinicId' => $clinicId])
@endsection


@section('scripts')
  <script>
    document.getElementById('startAppointmentBtn').addEventListener('click', function() {
      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'می‌خواهید نوبت‌دهی را شروع کنید؟',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله، شروع کن',
        cancelButtonText: 'لغو'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `{{ route('start.appointment') }}`,
            method: 'POST',
            data: {
              doctor_id: "{{ $doctorId }}",
              medical_center_id: "{{ $clinicId }}",
              _token: "{{ csrf_token() }}"
            },
            beforeSend: function() {
              Swal.fire({
                title: 'لطفاً صبر کنید...',
                text: 'در حال بررسی...',
                didOpen: () => {
                  Swal.showLoading();
                }
              });
            },
            success: function(response) {
              Swal.fire(
                'موفق!',
                response.message,
                'success'
              ).then(() => {
                window.location.href = response.redirect_url; // هدایت به روت پنل دکتر
              });
            },
            error: function(xhr) {
              Swal.fire(
                'خطا!',
                xhr.responseJSON.message || 'مشکلی رخ داد.',
                'error'
              );
            }
          });
        }
      });
    });
  </script>
@endsection
