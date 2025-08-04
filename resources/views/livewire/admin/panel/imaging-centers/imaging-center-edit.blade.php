<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش مرکز تصویر برداری : {{ $name }}</h5>
      </div>
      <a href="{{ route('admin.panel.imaging-centers.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
        <svg width="16" style="transform: rotate(180deg)" height="16" viewBox="0 0 24 24" fill="none"
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
              <select wire:model.live="doctor_ids" class="form-select select2" id="doctor_ids" multiple>
                <option value="">انتخاب کنید</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}">{{ $doctor->first_name . ' ' . $doctor->last_name }}</option>
                @endforeach
              </select>
              <label for="doctor_ids" class="form-label">پزشکان</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="name" class="form-control" id="name" placeholder=" " required>
              <label for="name" class="form-label">نام مرکز تصویر برداری </label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="title" class="form-control" id="title" placeholder=" ">
              <label for="title" class="form-label">عنوان مرکز تصویر برداری </label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.live="specialty_ids" class="form-select select2" id="specialty_ids" multiple>
                <option value="">انتخاب کنید</option>
                @foreach ($specialties as $specialty)
                  <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                @endforeach
              </select>
              <label for="specialty_ids" class="form-label">تخصص‌های مرکز تصویر برداری </label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.live="insurance_ids" class="form-select select2" id="insurance_ids" multiple>
                <option value="">انتخاب کنید</option>
                @foreach ($insurances as $insurance)
                  <option value="{{ $insurance->id }}">{{ $insurance->name }}</option>
                @endforeach
              </select>
              <label for="insurance_ids" class="form-label">بیمه‌های مرکز تصویر برداری </label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.live="service_ids" class="form-select select2" id="service_ids" multiple>
                <option value="">انتخاب کنید</option>
                @foreach ($services as $service)
                  <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
              </select>
              <label for="service_ids" class="form-label">خدمات</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="address" class="form-control" id="address" placeholder=" ">
              <label for="address" class="form-label">آدرس</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="phone_number" class="form-control" id="phone_number" placeholder=" ">
              <label for="phone_number" class="form-label">شماره تماس اصلی</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="secretary_phone" class="form-control" id="secretary_phone"
                placeholder=" ">
              <label for="secretary_phone" class="form-label">شماره منشی</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="postal_code" class="form-control" id="postal_code" placeholder=" ">
              <label for="postal_code" class="form-label">کد پستی</label>
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
              <input type="number" wire:model="latitude" class="form-control" id="latitude" placeholder=" "
                step="0.0000001">
              <label for="latitude" class="form-label">عرض جغرافیایی</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model="longitude" class="form-control" id="longitude" placeholder=" "
                step="0.0000001">
              <label for="longitude" class="form-label">طول جغرافیایی</label>
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
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model="payment_methods" class="form-select select2" id="payment_methods">
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
                <input class="form-check-input" type="checkbox" id="is_main_center" wire:model="is_main_center">
                <label class="form-check-label fw-medium" for="is_main_center">مرکز تصویر برداری اصلی</label>
              </div>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="location_confirmed"
                  wire:model="location_confirmed">
                <label class="form-check-label fw-medium" for="location_confirmed">مکان تأیید شده</label>
              </div>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="Center_tariff_type" class="form-select select2" id="Center_tariff_type">
                <option value="">انتخاب کنید</option>
                <option value="governmental">دولتی</option>
                <option value="special">ویژه</option>
                <option value="else">سایر</option>
              </select>
              <label for="Center_tariff_type" class="form-label">نوع تعرفه مرکز</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="Daycare_centers" class="form-select select2" id="Daycare_centers">
                <option value="">انتخاب کنید</option>
                <option value="yes">بله</option>
                <option value="no">خیر</option>
              </select>
              <label for="Daycare_centers" class="form-label">مرکز شبانه‌روزی</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <label class="form-label fw-bold text-dark mb-3">انتخاب روزهای کاری</label>
              <div class="d-flex flex-wrap gap-3 mt-4 border rounded-lg px-2">
                @foreach (['saturday' => 'شنبه', 'sunday' => 'یک‌شنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $day => $label)
                  <div class="form-check d-flex align-items-center">
                    <input class="form-check-input" type="checkbox"
                      wire:model.live="working_days.{{ $day }}" id="working_days_{{ $day }}"
                      value="1">
                    <label class="text-black px-2"
                      for="working_days_{{ $day }}">{{ $label }}</label>
                  </div>
                @endforeach
              </div>
            </div>
            <div class="col-12 position-relative mt-5">
              <label class="form-label">تصویر اصلی</label>
              <input type="file" wire:model="avatar" class="form-control" accept="image/*">
              @if ($imagingCenter->avatar)
                <img src="{{ Storage::url($imagingCenter->avatar) }}" alt="تصویر اصلی" class="mt-2"
                  style="max-width: 100px;">
              @endif
            </div>
            <div class="col-12 position-relative mt-5">
              <label class="form-label">مدارک</label>
              <input type="file" wire:model="documents" class="form-control" multiple accept=".pdf,.doc,.docx">
              @if ($imagingCenter->documents)
                <div class="mt-2">
                  @foreach ($imagingCenter->documents as $document)
                    <a href="{{ Storage::url($document) }}" target="_blank"
                      class="d-block">{{ basename($document) }}</a>
                  @endforeach
                </div>
              @endif
            </div>
            <div class="col-12 position-relative mt-5">
              <label class="form-label fw-bold text-dark mb-3" style="z-index: 1;">شماره‌های تماس اضافی</label>
              <div class="phone-numbers">
                @foreach ($phone_numbers as $index => $phone)
                  <div class="input-group mb-2" wire:ignore.self>
                    <input type="text" wire:model="phone_numbers.{{ $index }}" class="form-control"
                      placeholder="شماره تماس {{ $index + 1 }}">
                    <button class="btn btn-outline-danger" type="button"
                      wire:click="removePhoneNumber({{ $index }})">حذف</button>
                  </div>
                @endforeach
                <button type="button" wire:click="addPhoneNumber" class="btn btn-outline-primary mt-2">افزودن شماره
                  تماس</button>
              </div>
            </div>
            <div class="col-12 position-relative mt-5">
              <label class="form-label">توضیحات</label>
              <textarea wire:model="description" class="form-control" id="description" rows="3" placeholder=" "></textarea>
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
  </div>

  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#doctor_ids').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        $('#specialty_ids').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        $('#insurance_ids').select2({
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
        $('#payment_methods').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        $('#service_ids').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
      }

      setTimeout(() => {
        initializeSelect2();
      }, 100);

      Livewire.on('set-select2-initial', (event) => {
        setTimeout(() => {
          const data = event || {};
          if (data.doctor_ids && Array.isArray(data.doctor_ids)) $('#doctor_ids').val(data.doctor_ids)
            .trigger('change');
          if (data.specialty_ids && Array.isArray(data.specialty_ids)) $('#specialty_ids').val(data
            .specialty_ids).trigger('change');
          if (data.insurance_ids && Array.isArray(data.insurance_ids)) $('#insurance_ids').val(data
            .insurance_ids).trigger('change');
          if (data.province_id) $('#province_id').val(data.province_id).trigger('change');
          if (data.city_id) $('#city_id').val(data.city_id).trigger('change');
          if (data.payment_methods) $('#payment_methods').val(data.payment_methods).trigger('change');
          if (data.Center_tariff_type) $('#Center_tariff_type').val(data.Center_tariff_type).trigger(
            'change');
          if (data.service_ids && Array.isArray(data.service_ids)) $('#service_ids').val(data.service_ids)
            .trigger('change');
          if (data.Daycare_centers) $('#Daycare_centers').val(data.Daycare_centers).trigger('change');
        }, 200);
      });

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
        const cityId = @json($this->city_id);
        if (cityId) $('#city_id').val(cityId).trigger('change');
      });

      $('#doctor_ids').on('change', function() {
        @this.set('doctor_ids', $(this).val());
      });
      $('#specialty_ids').on('change', function() {
        @this.set('specialty_ids', $(this).val());
      });
      $('#insurance_ids').on('change', function() {
        @this.set('insurance_ids', $(this).val());
      });
      $('#province_id').on('change', function() {
        @this.set('province_id', $(this).val());
      });
      $('#city_id').on('change', function() {
        @this.set('city_id', $(this).val());
      });
      $('#payment_methods').on('change', function() {
        @this.set('payment_methods', $(this).val());
      });
      $('#service_ids').on('change', function() {
        @this.set('service_ids', $(this).val());
      });
      Livewire.on('show-alert', (event) => toastr[event.type](event.message));
    });
  </script>
</div>
