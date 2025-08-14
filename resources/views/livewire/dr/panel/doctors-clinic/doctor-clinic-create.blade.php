<div class="container-fluid py-2 mt-3" dir="rtl">
  <div class="card shadow-lg border-0 rounded-2 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن مطب جدید</h5>
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
          <form wire:submit.prevent="store">
            <div class="row g-3">
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="name" type="text" class="form-control" id="clinic-name"
                  placeholder="نام مطب" required>
                <label for="clinic-name" class="form-label">نام مطب</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="title" type="text" class="form-control" id="clinic-title"
                  placeholder="عنوان مطب">
                <label for="clinic-title" class="form-label">عنوان مطب (اختیاری)</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="phone_numbers.0" type="text" class="form-control" id="clinic-phone"
                  placeholder="شماره موبایل" required>
                <label for="clinic-phone" class="form-label">شماره موبایل</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="secretary_phone" type="text" class="form-control"
                  id="clinic-secretary-phone" placeholder="شماره منشی">
                <label for="clinic-secretary-phone" class="form-label">شماره منشی (اختیاری)</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="phone_number" type="text" class="form-control" id="clinic-phone-number"
                  placeholder="شماره تلفن">
                <label for="clinic-phone-number" class="form-label">شماره تلفن (اختیاری)</label>
              </div>
              <div class="col-md-6 col-sm-12 position-relative mt-4">
                <input wire:model.defer="prescription_tariff" type="number" min="0" step="0.01"
                  class="form-control" id="clinic-prescription-tariff" placeholder="تعرفه نسخه (تومان)">
                <label for="clinic-prescription-tariff" class="form-label">تعرفه نسخه (تومان)</label>
                @error('prescription_tariff')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
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
                  <option value="">انتخاب کنید</option>
                  @foreach ($cities as $city)
                    <option value="{{ $city->id }}">{{ $city->name }}</option>
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
                    <path d="M12 5v14M5 12h14" />
                  </svg>
                  @if ($createdClinicId)
                    به‌روزرسانی مطب
                  @else
                    ذخیره
                  @endif
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- کامپوننت تخصیص ساعات کاری -->
    @if ($showWorkHoursAssignment)
      <div class="card shadow-lg border-0 rounded-2 overflow-hidden mt-3" style="background: #ffffff;">
        <div class="card-header bg-gradient-success text-white p-3">
          <h6 class="mb-0 fw-bold">تخصیص ساعات کاری برای مطب: {{ $name }}</h6>
        </div>
        <div class="card-body p-3">
          <div class="row">
            <div class="col-12">
              <p class="text-muted mb-3">مطب با موفقیت ایجاد شد! حالا می‌توانید ساعات کاری را تخصیص دهید.</p>

              @if ($hasWorkHoursWithoutClinic)
                <div class="alert alert-info mb-3">
                  <strong>توجه:</strong> شما {{ $this->getWorkHoursWithoutClinicCount() }} ساعات کاری بدون مطب دارید که
                  می‌توانید به این مطب تخصیص دهید.
                </div>
              @endif

              <div class="d-flex gap-2 flex-wrap">
                <button wire:click="assignWorkHours" class="btn btn-success btn-lg">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="me-2">
                    <path
                      d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                  </svg>
                  تخصیص ساعات کاری
                </button>
                <button wire:click="skipWorkHoursAssignment" class="btn btn-outline-secondary">
                  رد کردن
                </button>
                <a href="{{ route('dr-clinic-management') }}" class="btn btn-primary">
                  رفتن به مدیریت مطب
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endif

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
