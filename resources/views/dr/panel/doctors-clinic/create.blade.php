@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/doctor-clinics/creaete.css') }}" rel="stylesheet" />

@endsection

@section('site-header')
  {{ 'به نوبه | افزودن مطب' }}
@endsection

@section('content')
@section('bread-crumb-title', 'افزودن مطب')

<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold">افزودن مطب جدید</h5>
      </div>
      <a href="{{ route('dr-clinic-management') }}"
        class="btn btn-outline-light btn-sm px-4 d-flex align-items-center gap-2" aria-label="بازگشت به لیست مطب‌ها">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <div class="card-body">
      <form id="add-clinic-form" action="{{ route('dr-clinic-store') }}" method="POST">
        @csrf
        <div class="row justify-content-center">
          <div class="col-12 col-md-10 col-lg-8">
            <div class="row g-4">
              <div class="col-md-6 col-sm-12 position-relative mt-5">
                <input type="text" name="name" class="form-control" id="clinic-name" placeholder="نام مطب"
                  required>
                <label for="clinic-name" class="form-label">نام مطب</label>
                @error('name')
                  <div class="error">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-5">
                <input type="text" name="phone_numbers[]" class="form-control" id="clinic-phone"
                  placeholder="شماره موبایل" required>
                <label for="clinic-phone" class="form-label">شماره موبایل</label>
                @error('phone_numbers.*')
                  <div class="error">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-5">
                <select name="province_id" class="form-select select2" id="clinic-province" required>
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
                <select name="city_id" class="form-select select2" id="clinic-city" required disabled>
                  <option value="">انتخاب شهر</option>
                </select>
                <label for="clinic-city" class="form-label">شهر</label>
                @error('city_id')
                  <div class="error">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-5">
                <input type="text" name="postal_code" class="form-control" id="clinic-postal-code"
                  placeholder="کد پستی">
                <label for="clinic-postal-code" class="form-label">کد پستی (اختیاری)</label>
                @error('postal_code')
                  <div class="error">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12 position-relative mt-5">
                <textarea name="address" class="form-control" id="clinic-address" rows="3" placeholder="آدرس"></textarea>
                <label for="clinic-address" class="form-label">آدرس (اختیاری)</label>
                @error('address')
                  <div class="error">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12 position-relative mt-5">
                <textarea name="description" class="form-control" id="clinic-description" rows="3" placeholder="توضیحات"></textarea>
                <label for="clinic-description" class="form-label">توضیحات (اختیاری)</label>
                @error('description')
                  <div class="error">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12 text-end mt-4">
                <button type="submit" class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2"
                  aria-label="ذخیره مطب">
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
@endsection

@section('scripts')
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<!-- Select2 JS -->
<script>
  const cities = @json($cities);
  $(document).ready(function() {
    // مقداردهی اولیه Select2


    // تنظیمات تغییر استان
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
      citySelect.trigger('change');
    });

    // ارسال فرم
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
