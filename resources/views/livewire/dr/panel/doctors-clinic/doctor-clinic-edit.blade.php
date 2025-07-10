<div class="container-fluid py-2" dir="rtl">
  <div class="card shadow-lg border-0 rounded-2 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش مطب</h5>
      </div>
      <a href="{{ route('dr-clinic-management') }}"
        class="btn btn-outline-light btn-sm rounded-pill px-3 py-1 d-flex align-items-center gap-1 hover:shadow-lg transition-all">
        <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>
    <div class="card-body p-3">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
          <form wire:submit.prevent="update">
            <div class="row g-3">
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="name" type="text" class="form-control" id="clinic-name"
                  placeholder="نام مطب" required>
                <label for="clinic-name" class="form-label">نام مطب</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="phone_numbers.0" type="text" class="form-control" id="clinic-phone"
                  placeholder="شماره موبایل" required>
                <label for="clinic-phone" class="form-label">شماره موبایل</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4" wire:ignore>
                <select wire:model="province_id" class="form-select select2-province" id="province_id" required>
                  <option value="">انتخاب استان</option>
                  @if (isset($provinces))
                    @foreach ($provinces as $province)
                      <option value="{{ $province->id }}" {{ $province->id == $province_id ? 'selected' : '' }}>
                        {{ $province->name }}</option>
                    @endforeach
                  @endif
                </select>
                <label for="province_id" class="form-label">استان</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4" wire:ignore>
                <select wire:model="city_id" class="form-select select2-city" id="city_id" required>
                  <option value="">انتخاب کنید</option>
                  @foreach ($cities as $city)
                    <option value="{{ $city->id }}" {{ $city->id == $city_id ? 'selected' : '' }}>
                      {{ $city->name }}</option>
                  @endforeach
                </select>
                <label for="city_id" class="form-label">شهر</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="postal_code" type="text" class="form-control" id="clinic-postal-code"
                  placeholder="کد پستی">
                <label for="clinic-postal-code" class="form-label">کد پستی (اختیاری)</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <textarea wire:model.defer="address" class="form-control" id="clinic-address" rows="3" placeholder="آدرس"></textarea>
                <label for="clinic-address" class="form-label">آدرس (اختیاری)</label>
              </div>
              <div class="col-12 position-relative mt-4">
                <textarea wire:model.defer="description" class="form-control" id="clinic-description" rows="3"
                  placeholder="توضیحات"></textarea>
                <label for="clinic-description" class="form-label">توضیحات (اختیاری)</label>
              </div>
              <div class="col-12 text-end mt-3 w-100 d-flex justify-content-end">
                <button type="submit"
                  class="btn my-btn-primary py-2 px-3 d-flex align-items-center gap-1 shadow-lg hover:shadow-xl transition-all">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                    <path d="M17 21v-8H7v8M7 3v5h8" />
                  </svg>
                  ذخیره تغییرات
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('livewire:init', function() {
        function initializeSelect2() {
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
</div>
