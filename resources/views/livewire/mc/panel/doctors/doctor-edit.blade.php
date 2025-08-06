<div>
  <div class="container-fluid py-4" dir="rtl">
    <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
      <div
        class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between  gap-3">
        <div class="d-flex align-items-center gap-3 mb-2">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            class="custom-animate-bounce">
            <path d="M5 12h14M12 5l7 7-7 7" />
          </svg>
          <h5 class="mb-0 fw-bold text-shadow">ویرایش پزشک</h5>
        </div>
        <a href="{{ route('mc.panel.doctors.index') }}"
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
            <!-- آپلود عکس -->
            <div class="text-center mb-4">
              <div class="position-relative d-inline-block">
                <img src="{{ $this->photoPreview }}" class="rounded-circle shadow border-2 border-white"
                  style="width: 100px; height: 100px; object-fit: cover;" alt="پروفایل" wire:loading.class="opacity-50"
                  wire:target="photo">
                <label for="photo"
                  class="btn my-btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 p-2 shadow"
                  style="transform: translate(10%, 10%);">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M4 12h16M12 4v16" />
                  </svg>
                </label>
                <input type="file" wire:model="photo" id="photo" class="d-none" accept="image/*">
              </div>
            </div>
            <!-- فرم -->
            <form wire:submit="update">
              <div class="row g-4">
                <div class="col-6 col-md-6 position-relative mt-5">
                  <input type="text" wire:model="first_name" class="form-control" id="first_name" placeholder=" "
                    required>
                  <label for="first_name" class="form-label">نام *</label>
                  @error('first_name')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-6 col-md-6 position-relative mt-5">
                  <input type="text" wire:model="last_name" class="form-control" id="last_name" placeholder=" "
                    required>
                  <label for="last_name" class="form-label">نام خانوادگی *</label>
                  @error('last_name')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-6 col-md-6 position-relative mt-5">
                  <input type="email" wire:model="email" class="form-control" id="email" placeholder=" " required>
                  <label for="email" class="form-label">ایمیل *</label>
                  @error('email')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-6 col-md-6 position-relative mt-5">
                  <input type="text" wire:model="mobile" class="form-control" id="mobile" placeholder=" "
                    required>
                  <label for="mobile" class="form-label">موبایل *</label>
                  @error('mobile')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-6 col-md-6 position-relative mt-5">
                  <input type="password" wire:model="password" class="form-control" id="password" placeholder=" ">
                  <label for="password" class="form-label">رمز عبور (خالی بگذارید برای عدم تغییر)</label>
                  @error('password')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-6 col-md-6 position-relative mt-5">
                  <input type="text" wire:model="national_code" class="form-control" id="national_code"
                    placeholder=" " required>
                  <label for="national_code" class="form-label">کد ملی *</label>
                  @error('national_code')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-6 col-md-6 position-relative mt-5">
                  <input type="text" wire:model="date_of_birth" class="form-control jalali-datepicker text-end"
                    id="date_of_birth" placeholder=" " required data-jdp>
                  <label for="date_of_birth" class="form-label">تاریخ تولد *</label>
                  @error('date_of_birth')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
                  <select wire:model="sex" class="form-select select2" id="sex" required>
                    <option value="">انتخاب کنید</option>
                    <option value="male">مرد</option>
                    <option value="female">زن</option>
                  </select>
                  <label for="sex" class="form-label">جنسیت *</label>
                  @error('sex')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
                  <select wire:model.live="province_id" class="form-select select2" id="province_id" required>
                    <option value="">انتخاب کنید</option>
                    @foreach ($provinces as $province)
                      <option value="{{ $province->id }}" {{ $province_id == $province->id ? 'selected' : '' }}>
                        {{ $province->name }}</option>
                    @endforeach
                  </select>
                  <label for="province_id" class="form-label">استان *</label>
                  @error('province_id')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
                  <select wire:model="city_id" class="form-select select2" id="city_id" required>
                    <option value="">انتخاب کنید</option>
                    @foreach ($cities as $city)
                      <option value="{{ $city->id }}" {{ $city_id == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}</option>
                    @endforeach
                  </select>
                  <label for="city_id" class="form-label">شهر *</label>
                  @error('city_id')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-6 col-md-6 position-relative mt-5">
                  <input type="text" wire:model="license_number" class="form-control" id="license_number"
                    placeholder=" " required>
                  <label for="license_number" class="form-label">کد نظام پزشکی *</label>
                  @error('license_number')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
                  <select wire:model="specialty_id" class="form-select select2" id="specialty_id" required multiple>
                    <option value="">انتخاب کنید</option>
                    @foreach ($specialties as $specialty)
                      <option value="{{ $specialty->id }}"
                        {{ in_array($specialty->id, $specialty_id) ? 'selected' : '' }}>
                        {{ $specialty->name }}</option>
                    @endforeach
                  </select>
                  <label for="specialty_id" class="form-label">تخصص *</label>
                  @error('specialty_id')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-12 position-relative mt-5">
                  <input type="text" wire:model="address" class="form-control" id="address" placeholder=" ">
                  <label for="address" class="form-label">آدرس</label>
                  @error('address')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-6 col-md-6 position-relative mt-5">
                  <input type="text" wire:model="postal_code" class="form-control" id="postal_code"
                    placeholder=" ">
                  <label for="postal_code" class="form-label">کد پستی</label>
                  @error('postal_code')
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
                <div class="col-12 position-relative mt-5">
                  <textarea wire:model="description" class="form-control" id="description" rows="4" placeholder=" "></textarea>
                  <label for="description" class="form-label">توضیحات</label>
                  @error('description')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <!-- دکمه‌های عملیات -->
              <div class="d-flex justify-content-end gap-3 mt-5">
                <a href="{{ route('mc.panel.doctors.index') }}" class="btn btn-secondary px-4">
                  انصراف
                </a>
                <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled">
                  <span wire:loading.remove wire:target="update">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                    به‌روزرسانی
                  </span>
                  <span wire:loading wire:target="update">
                    <div class="spinner-border spinner-border-sm" role="status">
                      <span class="visually-hidden">در حال به‌روزرسانی...</span>
                    </div>
                    در حال به‌روزرسانی...
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('livewire:init', () => {
      // Initialize Jalali Date Picker
      jalaliDatepicker.startWatch({
        separatorChar: "/",
        minDate: "1900-01-01",
        maxDate: "2100-01-01",
        initDate: false,
        format: 'YYYY/MM/DD'
      });

      // Initialize Select2 for sex
      $('#sex').select2({
        placeholder: 'جنسیت را انتخاب کنید',
        allowClear: true
      });

      // Initialize Select2 for provinces
      $('#province_id').select2({
        placeholder: 'استان را انتخاب کنید',
        allowClear: true
      });

      // Initialize Select2 for cities
      $('#city_id').select2({
        placeholder: 'شهر را انتخاب کنید',
        allowClear: true
      });

      // Initialize Select2 for specialties (multiple)
      $('#specialty_id').select2({
        placeholder: 'تخصص را انتخاب کنید',
        allowClear: true,
        multiple: true
      });

      // Set selected values after initialization
      setTimeout(() => {
        // Set sex value
        if ($('#sex').val()) {
          $('#sex').val($('#sex').val()).trigger('change');
        }

        // Set province value
        if ($('#province_id').val()) {
          $('#province_id').val($('#province_id').val()).trigger('change');
        }

        // Set city value
        if ($('#city_id').val()) {
          $('#city_id').val($('#city_id').val()).trigger('change');
        }

        // Set specialty values (multiple)
        if ($('#specialty_id').val()) {
          $('#specialty_id').val($('#specialty_id').val()).trigger('change');
        }
      }, 100);

      // Handle sex change
      $('#sex').on('change', function() {
        @this.set('sex', $(this).val());
      });

      // Handle province change
      $('#province_id').on('change', function() {
        @this.set('province_id', $(this).val());
      });

      // Handle city change
      $('#city_id').on('change', function() {
        @this.set('city_id', $(this).val());
      });

      // Handle specialty change
      $('#specialty_id').on('change', function() {
        @this.set('specialty_id', $(this).val());
      });

      // Listen for cities refresh
      Livewire.on('refresh-select2', (data) => {
        $('#city_id').empty().append('<option value="">شهر را انتخاب کنید</option>');
        data.cities.forEach(city => {
          $('#city_id').append(`<option value="${city.id}">${city.name}</option>`);
        });
        $('#city_id').trigger('change');
      });

      // Listen for component updates to refresh select2 values
      Livewire.on('update-select2-values', () => {
        setTimeout(() => {
          // Set sex value
          if ($('#sex').val()) {
            $('#sex').val($('#sex').val()).trigger('change');
          }

          // Set province value
          if ($('#province_id').val()) {
            $('#province_id').val($('#province_id').val()).trigger('change');
          }

          // Set city value
          if ($('#city_id').val()) {
            $('#city_id').val($('#city_id').val()).trigger('change');
          }

          // Set specialty values (multiple)
          if ($('#specialty_id').val()) {
            $('#specialty_id').val($('#specialty_id').val()).trigger('change');
          }
        }, 100);
      });
    });
  </script>
</div>
