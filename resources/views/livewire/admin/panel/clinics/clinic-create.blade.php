<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن کلینیک جدید</h5>
      </div>
      <a href="{{ route('admin.panel.clinics.index') }}"
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
          <div class="row g-4">
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.live="doctor_id" class="form-select select2" id="doctor_id">
                <option value="">انتخاب کنید</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}">{{ $doctor->first_name . ' ' . $doctor->last_name }}</option>
                @endforeach
              </select>
              <label for="doctor_id" class="form-label">پزشک</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="name" class="form-control" id="name" placeholder=" " required>
              <label for="name" class="form-label">نام کلینیک</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="address" class="form-control" id="address" placeholder=" ">
              <label for="address" class="form-label">آدرس</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="phone_number" class="form-control" id="phone_number" placeholder=" ">
              <label for="phone_number" class="form-label">شماره تماس</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.live="province_id" class="form-select select2" id="province_id">
                <option value="">انتخاب کنید</option>
                @foreach ($provinces as $province)
                  <option value="{{ $province->id }}">{{ $province->name }}</option>
                @endforeach
              </select>
              <label for="province_id" class="form-label">استان</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model="city_id" class="form-select select2" id="city_id">
                <option value="">انتخاب کنید</option>
                @foreach ($cities as $city)
                  <option value="{{ $city->id }}">{{ $city->name }}</option>
                @endforeach
              </select>
              <label for="city_id" class="form-label">شهر</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input data-timepicker type="text" wire:model="start_time" class="form-control" id="start_time"
                placeholder=" ">
              <label for="start_time" class="form-label">ساعت شروع</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input data-timepicker type="text" wire:model="end_time" class="form-control" id="end_time"
                placeholder=" ">
              <label for="end_time" class="form-label">ساعت پایان</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model="consultation_fee" class="form-control" id="consultation_fee"
                placeholder=" " step="0.01">
              <label for="consultation_fee" class="form-label">هزینه خدمات</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="payment_methods" class="form-select" id="payment_methods">
                <option value="">انتخاب کنید</option>
                <option value="cash">نقدی</option>
                <option value="card">کارت</option>
                <option value="online">آنلاین</option>
              </select>
              <label for="payment_methods" class="form-label">روش پرداخت</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="is_active" wire:model="is_active">
                <label class="form-check-label fw-medium" for="is_active">
                  وضعیت: <span
                    class="px-2 text-{{ $is_active ? 'success' : 'danger' }}">{{ $is_active ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="is_main_clinic" wire:model="is_main_clinic">
                <label class="form-check-label fw-medium" for="is_main_clinic">کلینیک اصلی</label>
              </div>
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="description" class="form-control" id="description" rows="3" placeholder=" "></textarea>
              <label for="description" class="form-label">توضیحات (اختیاری)</label>
            </div>
            <div class="text-end mt-4 w-100 d-flex justify-content-end">
              <button wire:click="store"
                class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                افزودن
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>



  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#doctor_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        $('#province_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        $('#city_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
      }
      initializeSelect2();

      Livewire.on('refresh-select2', (event) => {
        const cities = event.cities || [];
        $('#city_id').select2('destroy');
        $('#city_id').empty().select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%',
          data: [{
            id: '',
            text: 'انتخاب کنید'
          }, ...cities.map(city => ({
            id: city.id,
            text: city.name
          }))]
        });
      });

      $('#doctor_id').on('change', function() {
        @this.set('doctor_id', $(this).val());
      });
      $('#province_id').on('change', function() {
        @this.set('province_id', $(this).val());
      });
      $('#city_id').on('change', function() {
        @this.set('city_id', $(this).val());
      });

      Livewire.on('show-alert', (event) => toastr[event.type](event.message));
    });
  </script>
</div>
