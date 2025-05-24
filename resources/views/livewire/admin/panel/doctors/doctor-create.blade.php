@push('styles')
  <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/doctor/doctor.css') }}">
@endpush

<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن پزشک جدید</h5>
      </div>
      <a href="{{ route('admin.panel.doctors.index') }}"
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
          <div class="row g-4">
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="first_name" class="form-control" id="first_name" placeholder=" "
                required>
              <label for="first_name" class="form-label">نام</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="last_name" class="form-control" id="last_name" placeholder=" " required>
              <label for="last_name" class="form-label">نام خانوادگی</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="email" wire:model="email" class="form-control" id="email" placeholder=" ">
              <label for="email" class="form-label">ایمیل (اختیاری)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="mobile" class="form-control" id="mobile" placeholder=" " required>
              <label for="mobile" class="form-label">موبایل</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="password" wire:model="password" class="form-control" id="password" placeholder=" " required>
              <label for="password" class="form-label">رمز عبور</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="national_code" class="form-control" id="national_code" placeholder=" ">
              <label for="national_code" class="form-label">کد ملی (اختیاری)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="date_of_birth" class="form-control jalali-datepicker text-end"
                id="date_of_birth" placeholder="" data-jdp>
              <label for="date_of_birth" class="form-label">تاریخ تولد (اختیاری)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="sex" class="form-select" id="sex">
                <option value="">انتخاب کنید</option>
                <option value="male">مرد</option>
                <option value="female">زن</option>
              </select>
              <label for="sex" class="form-label">جنسیت</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.live="zone_province_id" class="form-select select2" id="zone_province_id">
                <option value="">انتخاب کنید</option>
                @foreach ($provinces as $province)
                  <option value="{{ $province->id }}">{{ $province->name }}</option>
                @endforeach
              </select>
              <label for="zone_province_id" class="form-label">استان</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model="zone_city_id" class="form-select select2" id="zone_city_id">
                <option value="">انتخاب کنید</option>
                @foreach ($cities as $city)
                  <option value="{{ $city->id }}">{{ $city->name }}</option>
                @endforeach
              </select>
              <label for="zone_city_id" class="form-label">شهر</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model="appointment_fee" class="form-control" id="appointment_fee"
                placeholder=" ">
              <label for="appointment_fee" class="form-label">تعرفه نوبت (تومان)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model="visit_fee" class="form-control" id="visit_fee" placeholder=" ">
              <label for="visit_fee" class="form-label">تعرفه ویزیت (تومان)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="status" wire:model="status">
                <label class="form-check-label fw-medium" for="status">
                  وضعیت: <span
                    class="px-2 text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="store"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              افزودن پزشک
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#zone_province_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        $('#zone_city_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%',
          data: [{
            id: '',
            text: 'انتخاب کنید'
          }]
        });
      }
      initializeSelect2();

      Livewire.on('refresh-select2', (event) => {
        const cities = event.cities || [];
        $('#zone_city_id').select2('destroy');
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
      });

      $('#zone_province_id').on('change', function() {
        @this.set('zone_province_id', $(this).val());
      });
      $('#zone_city_id').on('change', function() {
        @this.set('zone_city_id', $(this).val());
      });

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

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>
