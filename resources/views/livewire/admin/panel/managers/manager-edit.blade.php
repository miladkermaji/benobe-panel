@push('styles')
  <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/doctor/doctor.css') }}">
@endpush

<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-white">ویرایش مدیر</h5>
      </div>
      <a href="{{ route('admin.panel.managers.index') }}"
        class="btn btn-outline-light btn-sm px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <div class="card-body p-4">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
          <!-- فرم -->
          <form wire:submit="save">
            <!-- اطلاعات شخصی -->
            <div class="card mb-4 border-0 shadow-sm">
              <div class="card-header bg-primary text-white">
                <h6 class="mb-0 fw-bold">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="me-2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                  اطلاعات شخصی
                </h6>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-group position-relative">
                      <label class="form-label">نام <span class="text-danger">*</span></label>
                      <input type="text" wire:model="first_name" class="form-control" placeholder="نام مدیر">
                      @error('first_name')
                        <span class="text-danger small">{{ $message }}</span>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group position-relative">
                      <label class="form-label">نام خانوادگی <span class="text-danger">*</span></label>
                      <input type="text" wire:model="last_name" class="form-control" placeholder="نام خانوادگی مدیر">
                      @error('last_name')
                        <span class="text-danger small">{{ $message }}</span>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group position-relative">
                      <label class="form-label">کد ملی</label>
                      <input type="text" wire:model="national_code" class="form-control"
                        placeholder="کد ملی (۱۰ رقم)" maxlength="10">
                      @error('national_code')
                        <span class="text-danger small">{{ $message }}</span>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group position-relative">
                      <label class="form-label">تاریخ تولد</label>
                      <input data-jdp type="text" wire:model="date_of_birth" class="form-control"
                        placeholder="انتخاب تاریخ تولد" readonly>
                      @error('date_of_birth')
                        <span class="text-danger small">{{ $message }}</span>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group position-relative">
                      <label class="form-label">جنسیت</label>
                      <select wire:model="gender" class="form-select">
                        <option value="">انتخاب کنید</option>
                        <option value="male">مرد</option>
                        <option value="female">زن</option>
                        <option value="other">سایر</option>
                      </select>
                      @error('gender')
                        <span class="text-danger small">{{ $message }}</span>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group position-relative">
                      <label class="form-label">سطح دسترسی <span class="text-danger">*</span></label>
                      <select wire:model="permission_level" class="form-select" required>
                        <option value="1">مدیر عادی</option>
                        <option value="2">مدیر ارشد</option>
                      </select>
                      @error('permission_level')
                        <span class="text-danger small">{{ $message }}</span>
                      @enderror
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-group position-relative">
                      <label class="form-label">آدرس</label>
                      <textarea wire:model="address" class="form-control" rows="3" placeholder="آدرس کامل"></textarea>
                      @error('address')
                        <span class="text-danger small">{{ $message }}</span>
                      @enderror
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-group position-relative">
                      <label class="form-label">بیوگرافی</label>
                      <textarea wire:model="bio" class="form-control" rows="3" placeholder="توضیحات مختصر"></textarea>
                      @error('bio')
                        <span class="text-danger small">{{ $message }}</span>
                      @enderror
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- اطلاعات ورود -->
            <div class="card mb-4 border-0 shadow-sm">
              <div class="card-header bg-primary text-white">
                <h6 class="mb-0 fw-bold">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="me-2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="m22 21-2-2" />
                    <path d="M16 16h6" />
                  </svg>
                  اطلاعات ورود
                </h6>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-group position-relative">
                      <label class="form-label">ایمیل <span class="text-danger">*</span></label>
                      <input type="email" wire:model="email" class="form-control"
                        placeholder="example@domain.com">
                      @error('email')
                        <span class="text-danger small">{{ $message }}</span>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group position-relative">
                      <label class="form-label">شماره موبایل</label>
                      <input type="text" wire:model="mobile" class="form-control" placeholder="شماره موبایل">
                      @error('mobile')
                        <span class="text-danger small">{{ $message }}</span>
                      @enderror
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- تنظیمات امنیتی -->
            <div class="card mb-4 border-0 shadow-sm">
              <div class="card-header bg-primary text-white">
                <h6 class="mb-0 fw-bold">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="me-2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                  </svg>
                  تنظیمات امنیتی
                </h6>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-check form-switch">
                      <input wire:model="two_factor_enabled" class="form-check-input" type="checkbox"
                        id="two_factor_enabled">
                      <label class="form-check-label" for="two_factor_enabled">
                        احراز هویت دو مرحله‌ای
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-check form-switch">
                      <input wire:model="static_password_enabled" class="form-check-input" type="checkbox"
                        id="static_password_enabled">
                      <label class="form-check-label" for="static_password_enabled">
                        رمز عبور ثابت
                      </label>
                    </div>
                  </div>

                  <!-- فیلدهای رمز عبور ثابت -->
                  @if ($static_password_enabled)
                    <div class="col-12">
                      <div class="alert alert-info">
                        <strong>توجه:</strong> با فعال کردن رمز عبور ثابت، مدیر می‌تواند از رمز عبور ثابت برای ورود
                        استفاده کند.
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group position-relative">
                        <label class="form-label">رمز عبور ثابت <span class="text-danger">*</span></label>
                        <input type="text" wire:model="static_password" class="form-control"
                          placeholder="رمز عبور ثابت (حداقل ۶ کاراکتر)">
                        @error('static_password')
                          <span class="text-danger small">{{ $message }}</span>
                        @enderror
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group position-relative">
                        <label class="form-label">تکرار رمز عبور ثابت <span class="text-danger">*</span></label>
                        <input type="text" wire:model="static_password_confirmation" class="form-control"
                          placeholder="تکرار رمز عبور ثابت">
                        @error('static_password_confirmation')
                          <span class="text-danger small">{{ $message }}</span>
                        @enderror
                      </div>
                    </div>
                  @endif
                </div>
              </div>
            </div>


            <!-- دکمه‌های عملیات -->
            <div class="d-flex justify-content-end gap-3">
              <a href="{{ route('admin.panel.managers.index') }}" class="btn btn-outline-secondary px-4">
                انصراف
              </a>
              <button type="submit" class="btn btn-gradient-primary px-4 d-flex align-items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M5 13l4 4L19 7" />
                </svg>
                بروزرسانی مدیر
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Jalali Date Picker
    if (typeof jalaliDatepicker !== 'undefined') {
      jalaliDatepicker.startWatch();
    }
  });
</script>
