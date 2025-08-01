<div class="container-fluid py-2 mt-3" dir="rtl">
  <div class="card shadow-lg border-0 rounded-2 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن منشی جدید</h5>
      </div>
      <a href="{{ route('dr-secretary-management') }}"
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
          <form wire:submit.prevent="store">
            <div class="row g-3">
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="first_name" type="text" class="form-control" id="secretary-first-name"
                  placeholder="نام" required>
                <label for="secretary-first-name" class="form-label">نام</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="last_name" type="text" class="form-control" id="secretary-last-name"
                  placeholder="نام خانوادگی" required>
                <label for="secretary-last-name" class="form-label">نام خانوادگی</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="mobile" type="text" class="form-control" id="secretary-mobile"
                  placeholder="شماره موبایل" required>
                <label for="secretary-mobile" class="form-label">شماره موبایل</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="national_code" type="text" class="form-control" id="secretary-national-code"
                  placeholder="کدملی" required>
                <label for="secretary-national-code" class="form-label">کدملی</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <select wire:model="gender" class="form-select" id="secretary-gender" required>
                  <option value="">انتخاب جنسیت</option>
                  <option value="male">مرد</option>
                  <option value="female">زن</option>
                </select>
                <label for="secretary-gender" class="form-label">جنسیت</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="password" type="password" class="form-control" id="secretary-password"
                  placeholder="کلمه عبور (اختیاری)">
                <label for="secretary-password" class="form-label">کلمه عبور (اختیاری)</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4" wire:ignore>
                <select wire:model="province_id" class="form-select select2-province" id="province_id" required>
                  <option value="">انتخاب استان</option>
                  @if (isset($provinces))
                    @foreach ($provinces as $province)
                      <option value="{{ $province->id }}">{{ $province->name }}</option>
                    @endforeach
                  @endif
                </select>
                <label for="province_id" class="form-label">استان</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4" wire:ignore>
                <select wire:model="city_id" class="form-select select2-city" id="city_id" required>
                  <option value="">انتخاب شهر</option>
                  @foreach ($cities as $city)
                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                  @endforeach
                </select>
                <label for="city_id" class="form-label">شهر</label>
              </div>
              <div class="col-12 text-end mt-3 w-100 d-flex justify-content-end">
                <button type="submit"
                  class="btn my-btn-primary py-2 px-3 d-flex align-items-center gap-1 shadow-lg hover:shadow-xl transition-all">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M12 5v14M5 12h14" />
                  </svg>
                  ذخیره
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
          setTimeout(function() {
            @this.set('province_id', $('#province_id').val());
            @this.set('city_id', $('#city_id').val());
          }, 200);
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
          setTimeout(function() {
            @this.set('city_id', $('#city_id').val());
          }, 200);
        });

        $('#province_id').on('change', function() {
          @this.set('province_id', $(this).val());
        });
        $('#city_id').on('change', function() {
          @this.set('city_id', $(this).val());
        });

        Livewire.on('show-alert', (event) => {
          if (typeof toastr !== 'undefined' && typeof toastr[event.type] === 'function') {
            toastr[event.type](event.message);
          } else {
            alert(event.message);
          }
        });
      });
    </script>
  </div>
</div>
