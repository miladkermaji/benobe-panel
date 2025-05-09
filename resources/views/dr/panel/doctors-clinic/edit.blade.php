@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
 <link type="text/css" href="{{ asset('dr-assets/panel/css/doctor-clinics/creaete.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | ویرایش مطب' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش مطب')

<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold">ویرایش مطب: {{ $clinic->name }}</h5>
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
      <form id="edit-clinic-form" action="{{ route('dr-clinic-update', $clinic->id) }}" method="POST">
        @csrf
        @method('POST')
        <div class="row justify-content-center">
          <div class="col-12 col-md-10 col-lg-8">
            <div class="row g-4">
              <div class="col-md-6 col-sm-12 position-relative mt-5">
                <input type="text" name="name" class="form-control" id="clinic-name" value="{{ $clinic->name }}"
                  placeholder="نام مطب" required>
                <label for="clinic-name" class="form-label">نام مطب</label>
                @error('name')
                  <div class="error">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-5">
                <input type="text" name="phone_numbers[]" class="form-control" id="clinic-phone"
                  value="{{ json_decode($clinic->phone_numbers, true)[0] ?? '' }}" placeholder="شماره موبایل" required>
                <label for="clinic-phone" class="form-label">شماره موبایل</label>
                @error('phone_numbers.*')
                  <div class="error">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-5">
                <select name="province_id" class="form-select select2" id="clinic-province" required>
                  <option value="">انتخاب استان</option>
                  @foreach ($provinces as $province)
                    <option value="{{ $province->id }}" {{ $clinic->province_id == $province->id ? 'selected' : '' }}>
                      {{ $province->name }}
                    </option>
                  @endforeach
                </select>
                <label for="clinic-province" class="form-label">استان</label>
                @error('province_id')
                  <div class="error">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-5">
                <select name="city_id" class="form-select select2" id="clinic-city" required>
                  <option value="">انتخاب شهر</option>
                  @if ($clinic->province_id && isset($cities[$clinic->province_id]))
                    @foreach ($cities[$clinic->province_id] as $city)
                      <option value="{{ $city->id }}" {{ $clinic->city_id == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                      </option>
                    @endforeach
                  @endif
                </select>
                <label for="clinic-city" class="form-label">شهر</label>
                @error('city_id')
                  <div class="error">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-5">
                <input type="text" name="postal_code" class="form-control" id="clinic-postal-code"
                  value="{{ $clinic->postal_code }}" placeholder="کد پستی">
                <label for="clinic-postal-code" class="form-label">کد پستی (اختیاری)</label>
                @error('postal_code')
                  <div class="error">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12 position-relative mt-5">
                <textarea name="address" class="form-control" id="clinic-address" rows="3" placeholder="آدرس">{{ $clinic->address }}</textarea>
                <label for="clinic-address" class="form-label">آدرس (اختیاری)</label>
                @error('address')
                  <div class="error">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12 position-relative mt-5">
                <textarea name="description" class="form-control" id="clinic-description" rows="3" placeholder="توضیحات">{{ $clinic->description }}</textarea>
                <label for="clinic-description" class="form-label">توضیحات (اختیاری)</label>
                @error('description')
                  <div class="error">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12 text-end mt-4">
                <button type="submit" class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2"
                  aria-label="ذخیره تغییرات مطب">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                    <path d="M17 21v-8H7v8M7 3v5h8" />
                  </svg>
                  ذخیره تغییرات
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
    $('#edit-clinic-form').on('submit', function(e) {
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

    // مقداردهی اولیه شهرها برای فرم ویرایش
    @if ($clinic->province_id)
      $('#clinic-province').trigger('change');
      $('#clinic-city').val('{{ $clinic->city_id }}').trigger('change');
    @endif
  });
</script>
@endsection
