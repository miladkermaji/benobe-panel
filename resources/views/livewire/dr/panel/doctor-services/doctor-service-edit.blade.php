<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div class="card-header text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش خدمت</h5>
      </div>
      <a href="{{ route('dr.panel.doctor-services.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill  d-flex align-items-center  hover:shadow-lg transition-all">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <div class="card-body p-4">
      <div class="row justify-content-center">
        <div class=" col-lg-12">
          <div class="row g-4">
            <!-- خدمت -->
            <div class="col-lg-4 col-md-6  position-relative mt-5" wire:ignore>
              <select wire:model.live="selected_service" class="form-select select2" id="selected_service">
                <option value="" selected>انتخاب خدمت</option>
                <optgroup label="خدمات قبلی شما">
                  @foreach ($doctorServices as $doctorService)
                    <option value="doctor_service_{{ $doctorService->id }}">
                      {{ $doctorService->service->name ?? 'خدمت نامشخص' }}
                      ({{ $doctorService->clinic->name ?? 'کلینیک نامشخص' }} -
                      {{ $doctorService->insurance->name ?? 'بیمه نامشخص' }})
                    </option>
                  @endforeach
                </optgroup>
                <optgroup label="خدمات موجود">
                  @foreach ($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</ spaceroption>
                  @endforeach
                </optgroup>
              </select>
              <label for="selected_service" class="form-label">خدمت</label>
            </div>
            <!-- کلینیک -->
            <div class="col-lg-4 col-md-6  position-relative mt-5" wire:ignore>
              <select wire:model.live="clinic_id" class="form-select select2" id="clinic_id">
                <option value="" selected>انتخاب کلینیک</option>
                @foreach ($clinics as $clinic)
                  <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                @endforeach
              </select>
              <label for="clinic_id" class="form-label">کلینیک</label>
            </div>
            <!-- مدت زمان -->
            <div class="col-lg-4 col-md-6  position-relative mt-5">
              <input type="number" wire:model="duration" class="form-control" id="duration" placeholder=" " required>
              <label for="duration" class="form-label">مدت زمان (دقیقه)</label>
            </div>
            <!-- توضیحات -->
            <div class=" position-relative mt-5">
              <textarea wire:model="description" class="form-control" id="description" rows="3" placeholder=" "></textarea>
              <label for="description" class="form-label">توضیحات (اختیاری)</label>
            </div>
            <!-- بخش قیمت‌گذاری -->
            <div class=" mt-5">
              <h6 class="fw-bold">قیمت‌گذاری</h6>
              <hr class="my-2">
            </div>
            <!-- بیمه -->
            <div class="col-lg-3 col-md-6  position-relative mt-5" wire:ignore>
              <select wire:model.live="insurance_id" class="form-select select2" id="insurance_id">
                <option value="" selected>انتخاب بیمه</option>
                @foreach ($insurances as $insurance)
                  <option value="{{ $insurance->id }}">{{ $insurance->name }}</option>
                @endforeach
              </select>
              <label for="insurance_id" class="form-label">بیمه</label>
            </div>
            <!-- قیمت -->
            <div class="col-lg-3 col-md-6  position-relative mt-5">
              <input type="number" wire:model="price" class="form-control" id="price" placeholder=" " required>
              <label for="price" class="form-label">قیمت (تومان)</label>
            </div>
            <!-- تخفیف -->
            <div class="col-lg-3 col-md-6  position-relative mt-5">
              <input type="number" wire:model="discount" wire:click="openDiscountModal"
                class="form-control cursor-pointer" id="discount" placeholder=" " readonly>
              <label for="discount" class="form-label">تخفیف (درصد)</label>
            </div>
            <!-- قیمت نهایی -->
            <div class="col-lg-3 col-md-6  position-relative mt-5">
              <input type="number" wire:model="final_price" class="form-control" id="final_price" placeholder=" "
                readonly>
              <label for="final_price" class="form-label">قیمت نهایی (تومان)</label>
            </div>
            <!-- دکمه ذخیره -->
            <div class=" text-end mt-4 d-flex justify-content-end">
              <button wire:click="update"
                class="btn btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                  <path d="M17 21v-8H7v8M7 3v5h8" />
                </svg>
                ذخیره تغییرات
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- مودال تخفیف -->
  @if ($showDiscountModal)
    <div class="modal fade show d-block" id="discountModal" tabindex="-1" role="dialog"
      aria-labelledby="discountModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h5 class="modal-title" id="discountModalLabel">محاسبه تخفیف</h5>
            <button type="button" class="btn-close" wire:click="closeDiscountModal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-4">
              <label for="discountPercent" class="form-label">درصد تخفیف</label>
              <input type="number" wire:model.live="discountPercent" class="form-control" id="discountPercent"
                placeholder="درصد را وارد کنید">
            </div>
            <div class="mb-4">
              <label for="discountAmount" class="form-label">مبلغ تخفیف (تومان)</label>
              <input type="number" wire:model.live="discountAmount" class="form-control" id="discountAmount"
                placeholder="مبلغ را وارد کنید">
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" wire:click="closeDiscountModal">لغو</button>
            <button type="button" class="btn btn-primary" wire:click="applyDiscount">تأیید</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>
  @endif

  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#selected_service, #insurance_id, #clinic_id').each(function() {
          if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).select2('destroy');
          }
        });

        $('#selected_service').select2({
          dir: 'rtl',
          placeholder: 'انتخاب خدمت',
          allowClear: true,
          width: '100%',
          dropdownAutoWidth: true,
          minimumResultsForSearch: 5
        });

        $('#insurance_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب بیمه',
          allowClear: true,
          width: '100%',
          dropdownAutoWidth: true,
          minimumResultsForSearch: 5
        });

        $('#clinic_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کلینیک',
          allowClear: true,
          width: '100%',
          dropdownAutoWidth: true,
          minimumResultsForSearch: 5
        });

        const selectedService = @json($selected_service);
        const insuranceId = @json($insurance_id);
        const clinicId = @json($clinic_id);

        $('#selected_service').val(selectedService || '').trigger('change');
        $('#insurance_id').val(insuranceId || '').trigger('change');
        $('#clinic_id').val(clinicId || '').trigger('change');
      }

      initializeSelect2();

      $('#selected_service').on('change', function() {
        const value = $(this).val() === '' ? null : $(this).val();
        @this.set('selected_service', value);
      });

      $('#insurance_id').on('change', function() {
        const value = $(this).val() === '' ? null : $(this).val();
        @this.set('insurance_id', value);
      });

      $('#clinic_id').on('change', function() {
        const value = $(this).val() === '' ? null : $(this).val();
        @this.set('clinic_id', value);
      });

      Livewire.on('updateSelect2', () => {
        initializeSelect2();
      });

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      document.addEventListener('livewire:updated', function() {
        initializeSelect2();
      });
    });
  </script>
</div>
