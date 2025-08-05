<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between  gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش کاربر: {{ $email }}</h5>
      </div>
      <a href="{{ route('admin.panel.users.index') }}"
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
              <img src="{{ $photo ? $photo->temporaryUrl() : $user->profile_photo_url }}"
                class="rounded-circle shadow border-2 border-white"
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
          <div class="row g-4">
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="first_name" class="form-control" id="first_name" placeholder=" "
                required>
              <label for="first_name" class="form-label">نام</label>
              @error('first_name')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="last_name" class="form-control" id="last_name" placeholder=" " required>
              <label for="last_name" class="form-label">نام خانوادگی</label>
              @error('last_name')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="email" wire:model="email" class="form-control" id="email" placeholder=" " required>
              <label for="email" class="form-label">ایمیل</label>
              @error('email')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="mobile" class="form-control" id="mobile" placeholder=" " required>
              <label for="mobile" class="form-label">موبایل</label>
              @error('mobile')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="password" wire:model="password" class="form-control" id="password" placeholder=" ">
              <label for="password" class="form-label">رمز عبور (اختیاری)</label>
              @error('password')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="national_code" class="form-control" id="national_code" placeholder=" ">
              <label for="national_code" class="form-label">کد ملی (اختیاری)</label>
              @error('national_code')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="date_of_birth" class="form-control jalali-datepicker text-end"
                id="date_of_birth" placeholder="" data-jdp>
              <label for="date_of_birth" class="form-label">تاریخ تولد (اختیاری)</label>
              @error('date_of_birth')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="sex" class="form-select" id="sex">
                <option value="">انتخاب کنید</option>
                <option value="male">مرد</option>
                <option value="female">زن</option>
              </select>
              <label for="sex" class="form-label">جنسیت</label>
              @error('sex')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select class="form-select select2" id="zone_province_id" wire:model.live="zone_province_id">
                <option value="">انتخاب کنید</option>
                @foreach ($provinces as $province)
                  <option value="{{ $province->id }}">{{ $province->name }}</option>
                @endforeach
              </select>
              <label for="zone_province_id" class="form-label">استان</label>
              @error('zone_province_id')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select class="form-select select2" id="zone_city_id" wire:model="zone_city_id">
                <option value="">انتخاب کنید</option>
                @foreach ($cities as $city)
                  <option value="{{ $city->id }}">{{ $city->name }}</option>
                @endforeach
              </select>
              <label for="zone_city_id" class="form-label">شهر</label>
              @error('zone_city_id')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="activation" wire:model="activation">
                <label class="form-check-label fw-medium" for="activation">
                  وضعیت: <span
                    class="px-2 text-{{ $activation ?? false ? 'success' : 'danger' }}">{{ $activation ?? false ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="update"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                <path d="M17 21v-8H7v8M7 3v5h8" />
              </svg>
              ذخیره
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>


  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // تابع برای مقداردهی اولیه یا به‌روزرسانی Select2
      function initializeSelect2() {
        $('#zone_province_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });

        $('#zone_city_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });

        // تنظیم مقادیر اولیه هنگام لود صفحه
        $('#zone_province_id').val(@json($zone_province_id)).trigger('change');
        $('#zone_city_id').val(@json($zone_city_id)).trigger('change');
      }

      // بارگذاری اولیه
      initializeSelect2();

      // رفرش Select2 وقتی شهرها تغییر می‌کنند
      Livewire.on('refresh-select2', (event) => {
        const cities = event.cities || [];
        $('#zone_city_id').select2('destroy'); // حذف Select2 قبلی
        $('#zone_city_id').empty().select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%',
          data: [{
              id: '',
              text: 'انتخاب کنید'
            },
            ...cities.map(city => ({
              id: city.id,
              text: city.name
            }))
          ]
        });
        // تنظیم مقدار انتخاب‌شده شهر بعد از رفرش
        $('#zone_city_id').val(@json($zone_city_id)).trigger('change');
      });

      // همگام‌سازی با Livewire
      $('#zone_province_id').on('change', function() {
        @this.set('zone_province_id', $(this).val());
      });

      $('#zone_city_id').on('change', function() {
        @this.set('zone_city_id', $(this).val());
      });

      // دیت‌پیکر JalaliDatePicker
      jalaliDatepicker.startWatch({
        minDate: "attr",
        maxDate: "attr",
        showTodayBtn: true,
        showEmptyBtn: true,
        time: false,
        dateFormatter: function(unix) {
          return new Date(unix).toLocaleDateString('fa-IR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
          });
        }
      });

      document.getElementById('date_of_birth').addEventListener('change', function() {
        @this.set('date_of_birth', this.value);
      });

      // نمایش توستر
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      // بازسازی Select2 بعد از هر آپدیت Livewire
      document.addEventListener('livewire:updated', function() {
        initializeSelect2();
      });
    });
  </script>
</div>
