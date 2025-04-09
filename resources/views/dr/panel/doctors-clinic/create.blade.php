@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />

  <style>
       .myPanelOption {
      display: none;
    }
    .bg-gradient-primary {
      background: linear-gradient(90deg, #6b7280, #374151);
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .form-control,
    .form-select {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 12px 15px;
      font-size: 14px;
      transition: all 0.3s ease;
      height: 48px;
      background: #fafafa;
      width: 100%;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #6b7280;
      box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.2);
      background: #fff;
    }

    .form-label {
      position: absolute;
      top: -25px;
      right: 15px;
      color: #374151;
      font-size: 12px;
      background: #ffffff;
      padding: 0 5px;
      pointer-events: none;
    }

    .btn-primary {
      background: linear-gradient(90deg, #6b7280, #374151);
      border: none;
      color: white;
      font-weight: 600;
    }

    .btn-primary:hover {
      background: linear-gradient(90deg, #4b5563, #1f2937);
      transform: translateY(-2px);
    }

    .btn-outline-light {
      border-color: rgba(255, 255, 255, 0.8);
    }

    .btn-outline-light:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
    }

    .animate-bounce {
      animation: bounce 1s infinite;
    }

    @keyframes bounce {

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-5px);
      }
    }

    .text-shadow {
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .error {
      color: #dc3545;
      font-size: 12px;
      margin-top: 4px;
    }

    @media (max-width: 767px) {
      .card-header {
        flex-direction: column;
        gap: 1rem;
      }

      .btn-outline-light {
        width: 100%;
        justify-content: center;
      }

      .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
      }
    }

    @media (max-width: 575px) {
      .card-body {
        padding: 2rem 1.5rem;
      }

      .btn-primary {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
@endsection

@section('site-header')
  {{ 'به نوبه | افزودن مطب' }}
@endsection

@section('content')
@section('bread-crumb-title', ' افزودن مطب ')

  <div class="container-fluid py-4" dir="rtl">
    <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
      <div
        class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            class="animate-bounce">
            <path d="M5 12h14M12 5l7 7-7 7" />
          </svg>
          <h5 class="mb-0 fw-bold text-shadow">افزودن مطب جدید</h5>
        </div>
        <a href="{{ route('dr-clinic-management') }}"
          class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
          <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          بازگشت
        </a>
      </div>

      <div class="card-body p-4">
        <form id="add-clinic-form" action="{{ route('dr-clinic-store') }}" method="POST">
          @csrf
          <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
              <div class="row g-4">
                <div class="col-md-6 col-sm-12 position-relative mt-5">
                  <input type="text" name="name" class="form-control" id="clinic-name" placeholder=" " required>
                  <label for="clinic-name" class="form-label">نام مطب</label>
                  @error('name')
                    <div class="error">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6 col-sm-12 position-relative mt-5">
                  <input type="text" name="phone_numbers[]" class="form-control" id="clinic-phone" placeholder=" "
                    required>
                  <label for="clinic-phone" class="form-label">شماره موبایل</label>
                  @error('phone_numbers.*')
                    <div class="error">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6 col-sm-12 position-relative mt-5">
                  <select name="province_id" class="form-select" id="clinic-province" required>
                    <option value="">انتخاب استان</option>
                    @foreach ($provinces as $province)
                      <option value="{{ $province->id }}">{{ $province->name }}</option>
                    @endforeach
                  </select>
                  <label for="clinic-province" class="form-label">استان</label>
                  @error('province_id')
                    <div class="error">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6 col-sm-12 position-relative mt-5">
                  <select name="city_id" class="form-select" id="clinic-city" required disabled>
                    <option value="">انتخاب شهر</option>
                  </select>
                  <label for="clinic-city" class="form-label">شهر</label>
                  @error('city_id')
                    <div class="error">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6 col-sm-12 position-relative mt-5">
                  <input type="text" name="postal_code" class="form-control" id="clinic-postal-code" placeholder=" ">
                  <label for="clinic-postal-code" class="form-label">کد پستی (اختیاری)</label>
                  @error('postal_code')
                    <div class="error">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-12 position-relative mt-5">
                  <textarea name="address" class="form-control" id="clinic-address" rows="3" placeholder=" "></textarea>
                  <label for="clinic-address" class="form-label">آدرس (اختیاری)</label>
                  @error('address')
                    <div class="error">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-12 position-relative mt-5">
                  <textarea name="description" class="form-control" id="clinic-description" rows="3" placeholder=" "></textarea>
                  <label for="clinic-description" class="form-label">توضیحات (اختیاری)</label>
                  @error('description')
                    <div class="error">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-12 text-end mt-4 w-100 d-flex justify-content-end">
                  <button type="submit"
                    class="btn btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <path d="M12 5v14M5 12h14" />
                    </svg>
                    ذخیره
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    
    const cities = @json($cities);
    document.addEventListener('DOMContentLoaded', function() {
      $('#clinic-province').on('change', function() {
        const provinceId = $(this).val();
        const citySelect = $('#clinic-city');
        citySelect.empty().append('<option value="">انتخاب شهر</option>');
        if (provinceId && cities[provinceId]) {
          cities[provinceId].forEach(function(city) {
            citySelect.append(`<option value="${city.id}">${city.name}</option>`);
          });
          citySelect.prop('disabled', false);
        } else {
          citySelect.prop('disabled', true);
        }
      });

      $('#add-clinic-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        $.ajax({
          url: form.attr('action'),
          method: 'POST',
          data: form.serialize(),
          success: function(response) {
            toastr.success(response.message);
            window.location.href = "{{ route('dr-clinic-management') }}";
          },
          error: function(xhr) {
            if (xhr.status === 422) {
              toastr.error('لطفاً خطاهای فرم را بررسی کنید.');
            } else {
              toastr.error('خطا در ذخیره اطلاعات!');
            }
          }
        });
      });
    });
  </script>
@endsection
