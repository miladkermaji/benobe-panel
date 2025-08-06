@push('styles')
  <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/doctor/doctor.css') }}">
@endpush

<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between  gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش مدیر</h5>
      </div>
      <a href="{{ route('admin.panel.managers.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
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
            <div class="row g-4">
              <div class="col-6 col-md-6 position-relative mt-5">
                <input type="text" wire:model="first_name" class="form-control" id="first_name" placeholder=" "
                  required>
                <label for="first_name" class="form-label">نام <span class="text-danger">*</span></label>
                @error('first_name')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-6 col-md-6 position-relative mt-5">
                <input type="text" wire:model="last_name" class="form-control" id="last_name" placeholder=" "
                  required>
                <label for="last_name" class="form-label">نام خانوادگی <span class="text-danger">*</span></label>
                @error('last_name')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-6 col-md-6 position-relative mt-5">
                <input type="email" wire:model="email" class="form-control" id="email" placeholder=" " required>
                <label for="email" class="form-label">ایمیل <span class="text-danger">*</span></label>
                @error('email')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-6 col-md-6 position-relative mt-5">
                <input type="text" wire:model="mobile" class="form-control" id="mobile" placeholder=" ">
                <label for="mobile" class="form-label">موبایل</label>
                @error('mobile')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-6 col-md-6 position-relative mt-5">
                <input type="text" wire:model="national_code" class="form-control" id="national_code"
                  placeholder=" ">
                <label for="national_code" class="form-label">کد ملی</label>
                @error('national_code')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-6 col-md-6 position-relative mt-5">
                <input type="date" wire:model="date_of_birth" class="form-control" id="date_of_birth" placeholder="">
                <label for="date_of_birth" class="form-label">تاریخ تولد</label>
                @error('date_of_birth')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-6 col-md-6 position-relative mt-5">
                <select wire:model="gender" class="form-select" id="gender">
                  <option value="">انتخاب کنید</option>
                  <option value="male">مرد</option>
                  <option value="female">زن</option>
                  <option value="other">سایر</option>
                </select>
                <label for="gender" class="form-label">جنسیت</label>
                @error('gender')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-6 col-md-6 position-relative mt-5">
                <select wire:model="permission_level" class="form-select" id="permission_level" required>
                  <option value="1">مدیر عادی</option>
                  <option value="2">مدیر ارشد</option>
                </select>
                <label for="permission_level" class="form-label">سطح دسترسی <span
                    class="text-danger">*</span></label>
                @error('permission_level')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-12 position-relative mt-5">
                <textarea wire:model="address" class="form-control" id="address" rows="3" placeholder=" "></textarea>
                <label for="address" class="form-label">آدرس</label>
                @error('address')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-12 position-relative mt-5">
                <textarea wire:model="bio" class="form-control" id="bio" rows="3" placeholder=" "></textarea>
                <label for="bio" class="form-label">بیوگرافی</label>
                @error('bio')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>

              <!-- تغییر رمز عبور -->
              <div class="col-12 mt-4">
                <div class="form-check">
                  <input wire:model="change_password" class="form-check-input" type="checkbox" id="change_password">
                  <label class="form-check-label" for="change_password">
                    تغییر رمز عبور
                  </label>
                </div>
              </div>

              @if ($change_password)
                <div class="col-6 col-md-6 position-relative mt-5">
                  <input type="password" wire:model="password" class="form-control" id="password" placeholder=" ">
                  <label for="password" class="form-label">رمز عبور جدید</label>
                  @error('password')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-6 col-md-6 position-relative mt-5">
                  <input type="password" wire:model="password_confirmation" class="form-control"
                    id="password_confirmation" placeholder=" ">
                  <label for="password_confirmation" class="form-label">تکرار رمز عبور جدید</label>
                </div>
              @endif

              <!-- تنظیمات امنیتی -->
              <div class="col-12 mt-4">
                <h6 class="fw-bold mb-3">تنظیمات امنیتی</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-check">
                      <input wire:model="two_factor_enabled" class="form-check-input" type="checkbox"
                        id="two_factor_enabled">
                      <label class="form-check-label" for="two_factor_enabled">
                        احراز هویت دو مرحله‌ای
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-check">
                      <input wire:model="static_password_enabled" class="form-check-input" type="checkbox"
                        id="static_password_enabled">
                      <label class="form-check-label" for="static_password_enabled">
                        رمز عبور ثابت
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- وضعیت -->
              <div class="col-12 mt-4">
                <h6 class="fw-bold mb-3">وضعیت</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-check">
                      <input wire:model="is_active" class="form-check-input" type="checkbox" id="is_active">
                      <label class="form-check-label" for="is_active">
                        فعال
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-check">
                      <input wire:model="is_verified" class="form-check-input" type="checkbox" id="is_verified">
                      <label class="form-check-label" for="is_verified">
                        تایید شده
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- دکمه‌های عملیات -->
              <div class="col-12 mt-5">
                <div class="d-flex justify-content-end gap-3">
                  <a href="{{ route('admin.panel.managers.index') }}"
                    class="btn btn-outline-secondary rounded-pill px-4">
                    انصراف
                  </a>
                  <button type="submit"
                    class="btn btn-gradient-primary rounded-pill px-4 d-flex align-items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <path d="M5 13l4 4L19 7" />
                    </svg>
                    بروزرسانی مدیر
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
