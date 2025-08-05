<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div class="card-header text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش خدمت</h5>
        @if ($isSaving)
          <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
          <span class="text-light ms-2">در حال ذخیره...</span>
        @endif
      </div>
      <div class="d-flex gap-2">
        <button wire:click="saveAndRedirect"
          class="btn btn-success btn-sm rounded-pill px-4 d-flex align-items-center hover:shadow-lg transition-all">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
            <path d="M17 21v-8H7v8M7 3v5h8" />
          </svg>
          ذخیره تغییرات
        </button>
        <a href="{{ route('dr.panel.doctor-services.index') }}"
          class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center hover:shadow-lg transition-all">
          <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          بازگشت
        </a>
      </div>
    </div>
    <div class="card-body p-4">
      <div class="row justify-content-center">
        <div class="col-lg-12">
          <div class="row g-4">
            <!-- خدمت -->
            <div class="col-lg-4 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.debounce.500ms="selected_service" class="form-select select2" id="selected_service">
                <option value="" selected>انتخاب خدمت</option>
                <optgroup label="خدمات قبلی شما">
                  @foreach ($doctorServices as $doctorService)
                    <option value="doctor_service_{{ $doctorService->id }}">
                      {{ $doctorService->service->name ?? 'خدمت نامشخص' }}
                      ({{ $doctorService->medicalCenter->name ?? 'کلینیک نامشخص' }} -
                      {{ $doctorService->insurance->name ?? 'بیمه نامشخص' }})
                    </option>
                  @endforeach
                </optgroup>
                <optgroup label="خدمات موجود">
                  @foreach ($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                  @endforeach
                </optgroup>
              </select>
              <label for="selected_service" class="form-label">خدمت</label>
            </div>
            <!-- کلینیک -->
            <div class="col-lg-4 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.debounce.500ms="medical_center_id" class="form-select select2" id="medical_center_id">
                <option value="" selected>انتخاب کلینیک</option>
                @foreach ($clinics as $clinic)
                  <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                @endforeach
              </select>
              <label for="medical_center_id" class="form-label">کلینیک</label>
            </div>
            <!-- مدت زمان -->
            <div class="col-lg-4 col-md-6 position-relative mt-5">
              <input type="number" wire:model.debounce.500ms="duration" class="form-control position-relative"
                id="duration" placeholder=" " required>
              <label for="duration" class="form-label">مدت زمان (دقیقه)</label>
            </div>
            <!-- توضیحات -->
            <div class="position-relative mt-5">
              <textarea wire:model.debounce.500ms="description" class="form-control position-relative" id="description" rows="3"
                placeholder=" "></textarea>
              <label for="description" class="form-label">توضیحات (اختیاری)</label>
            </div>
            <!-- بخش قیمت‌گذاری -->
            <div class="mt-5">
              <h6 class="fw-bold">قیمت‌گذاری</h6>
              <hr class="my-2">
            </div>
            @foreach ($pricing as $index => $price)
              <div class="row g-4 align-items-end">
                <!-- بیمه -->
                <div class="col-lg-3 col-md-6 position-relative mt-5" wire:ignore>
                  <select wire:model.debounce.500ms="pricing.{{ $index }}.insurance_id"
                    class="form-select select2" id="insurance_id_{{ $index }}">
                    <option value="" selected>انتخاب بیمه</option>
                    @foreach ($insurances as $insurance)
                      <option value="{{ $insurance->id }}">{{ $insurance->name }}</option>
                    @endforeach
                  </select>
                  <label for="insurance_id_{{ $index }}" class="form-label">بیمه</label>
                </div>
                <!-- قیمت -->
                <div class="col-lg-3 col-md-6 position-relative mt-5">
                  <input type="text" wire:model.debounce.500ms="pricing.{{ $index }}.price"
                    class="form-control price-input" id="price_{{ $index }}" placeholder=" " required
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                    onblur="this.value = this.value.replace(/,/g, '')">
                  <label for="price_{{ $index }}" class="form-label">قیمت (تومان)</label>
                </div>
                <!-- تخفیف -->
                <div class="col-lg-3 col-md-6 position-relative mt-5">
                  <input type="number" wire:model="pricing.{{ $index }}.discount"
                    wire:click="openDiscountModal({{ $index }})" class="form-control cursor-pointer"
                    id="discount_{{ $index }}" placeholder=" " readonly>
                  <label for="discount_{{ $index }}" class="form-label">تخفیف (درصد)</label>
                </div>
                <!-- قیمت نهایی -->
                <div class="col-lg-2 col-md-6 position-relative mt-5">
                  <input type="number" wire:model="pricing.{{ $index }}.final_price"
                    class="form-control position-relative" id="final_price_{{ $index }}" placeholder=" "
                    readonly>
                  <label for="final_price_{{ $index }}" class="form-label">قیمت نهایی (تومان)</label>
                </div>
                <!-- دکمه حذف ردیف -->
                <div class="col-lg-1 col-md-6 position-relative mt-5">
                  <button wire:click="removePricingRow({{ $index }})" class="btn btn-danger btn-sm"
                    @if (count($pricing) == 1) disabled @endif>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                    </svg>
                  </button>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- مودال تخفیف -->
  <x-custom-modal id="discountModal" title="محاسبه تخفیف" size="md">
    <div class="mb-4 position-relative mt-2">
      <label for="discountPercent" class="label-top-input-special-takhasos fw-bold mb-2">درصد تخفیف:</label>
      <input type="number" wire:model="discountPercent" class="form-control position-relative" id="discountPercent"
        placeholder="درصد را وارد کنید" min="0" max="100" step="0.01">
    </div>
    <div class="mb-4 position-relative mt-2">
      <label for="discountAmount" class="label-top-input-special-takhasos fw-bold mb-2">مبلغ تخفیف (تومان):</label>
      <input type="number" wire:model="discountAmount" class="form-control position-relative" id="discountAmount"
        placeholder="مبلغ را وارد کنید" min="0">
    </div>
    <div class="mt-3 d-flex gap-2">
      <button type="button" class="btn btn-secondary flex-grow-1" wire:click="closeDiscountModal">لغو</button>
      <button type="button" class="btn btn-primary flex-grow-1" wire:click="applyDiscount">تأیید</button>
    </div>
  </x-custom-modal>

  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        // تخریب Select2های قبلی برای جلوگیری از تداخل
        $('#selected_service, #medical_center_id, [id^="insurance_id_"]').each(function() {
          if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).select2('destroy');
          }
        });

        // مقداردهی اولیه برای خدمت
        $('#selected_service').select2({
          dir: 'rtl',
          placeholder: 'انتخاب خدمت',
          allowClear: true,
          width: '100%',
          dropdownAutoWidth: true,
          minimumResultsForSearch: 5
        }).val(@json($selected_service) || '').trigger('change');

        // مقداردهی اولیه برای کلینیک
        $('#medical_center_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کلینیک',
          allowClear: true,
          width: '100%',
          dropdownAutoWidth: true,
          minimumResultsForSearch: 5
        }).val(@json($medical_center_id) || '').trigger('change');

        // مقداردهی اولیه برای بیمه‌ها
        $('[id^="insurance_id_"]').each(function() {
          const index = $(this).attr('id').replace('insurance_id_', '');
          const insuranceId = @json($pricing) && @json($pricing)[index] ?
            @json($pricing)[index].insurance_id : '';
          $(this).select2({
            dir: 'rtl',
            placeholder: 'انتخاب بیمه',
            allowClear: true,
            width: '100%',
            dropdownAutoWidth: true,
            minimumResultsForSearch: 5
          }).val(insuranceId || '').trigger('change');
        });
      }

      // اولیه‌سازی Select2 در بارگذاری اولیه
      initializeSelect2();

      // رویداد تغییر خدمت
      $('#selected_service').on('select2:select', function(e) {
        const value = e.target.value === '' ? null : e.target.value;
        @this.set('selected_service', value);
      });

      $('#selected_service').on('select2:clear', function() {
        @this.set('selected_service', null);
      });

      // رویداد تغییر کلینیک
      $('#medical_center_id').on('select2:select', function(e) {
        const value = e.target.value === '' ? null : e.target.value;
        @this.set('medical_center_id', value);
      });

      $('#medical_center_id').on('select2:clear', function() {
        @this.set('medical_center_id', null);
      });

      // رویداد تغییر بیمه
      $(document).on('select2:select', '[id^="insurance_id_"]', function(e) {
        const index = $(this).attr('id').replace('insurance_id_', '');
        const value = e.target.value === '' ? null : e.target.value;
        @this.set(`pricing.${index}.insurance_id`, value);
      });

      $(document).on('select2:clear', '[id^="insurance_id_"]', function() {
        const index = $(this).attr('id').replace('insurance_id_', '');
        @this.set(`pricing.${index}.insurance_id`, null);
      });

      // رویداد تغییر قیمت برای به‌روزرسانی تخفیف
      $(document).on('input change', '[id^="price_"]', function() {
        const index = $(this).attr('id').replace('price_', '');
        const value = $(this).val().replace(/,/g, '');
        @this.set(`pricing.${index}.price`, value);

        // اگر مودال تخفیف باز است و این همان ردیف است، تخفیف را به‌روزرسانی کن
        if (@this.showDiscountModal && @this.currentPricingIndex == index) {
          const cleanPrice = parseFloat(value) || 0;
          const discountPercent = @this.discountPercent || 0;
          if (cleanPrice > 0 && discountPercent > 0) {
            const discountAmount = Math.round(cleanPrice * discountPercent / 100);
            @this.set('discountAmount', discountAmount);
            $('#discountAmount').val(discountAmount);
          } else {
            @this.set('discountAmount', 0);
            $('#discountAmount').val(0);
          }
        }
      });

      // به‌روزرسانی Select2 هنگام تغییر کلینیک
      Livewire.on('update-select2', ({
        clinicId
      }) => {
        if (clinicId) {
          $('#medical_center_id').val(clinicId).trigger('change');
        }
      });

      // نمایش پیام‌های toastr
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      // به‌روزرسانی مقادیر تخفیف در مودال
      Livewire.on('updateDiscountValues', (data) => {
        if (data.discountPercent !== undefined) {
          $('#discountPercent').val(data.discountPercent);
        }
        if (data.discountAmount !== undefined) {
          $('#discountAmount').val(data.discountAmount);
        }
      });

      // به‌روزرسانی مقادیر تخفیف بعد از محاسبه
      Livewire.on('discountCalculated', (data) => {
        if (data.discountPercent !== undefined) {
          $('#discountPercent').val(data.discountPercent);
        }
        if (data.discountAmount !== undefined) {
          $('#discountAmount').val(data.discountAmount);
        }
      });

      // باز کردن مودال تخفیف
      Livewire.on('openDiscountModal', () => {
        setTimeout(() => {
          openXModal('discountModal');

          // تنظیم مقادیر اولیه بعد از باز شدن مودال
          setTimeout(() => {
            $('#discountPercent').val(@this.discountPercent || 0);
            $('#discountAmount').val(@this.discountAmount || 0);
          }, 100);

          // اضافه کردن event handlers برای محاسبه real-time
          $('#discountPercent').off('input keyup change').on('input keyup change', function() {
            const value = parseFloat($(this).val()) || 0;
            @this.set('discountPercent', value);
            @this.call('calculateDiscountPercent', value);

            // به‌روزرسانی مستقیم input مبلغ تخفیف
            const cleanPrice = parseFloat(@this.pricing[@this.currentPricingIndex].price.toString()
              .replace(/,/g, '')) || 0;
            if (cleanPrice > 0 && value > 0) {
              const discountAmount = Math.round(cleanPrice * value / 100);
              $('#discountAmount').val(discountAmount);
            } else {
              $('#discountAmount').val(0);
            }
          });

          $('#discountAmount').off('input keyup change').on('input keyup change', function() {
            const value = parseFloat($(this).val()) || 0;
            @this.set('discountAmount', value);
            @this.call('calculateDiscountAmount', value);

            // به‌روزرسانی مستقیم input درصد تخفیف
            const cleanPrice = parseFloat(@this.pricing[@this.currentPricingIndex].price.toString()
              .replace(/,/g, '')) || 0;
            if (cleanPrice > 0 && value > 0) {
              const discountPercent = Math.round((value / cleanPrice) * 100 * 100) / 100;
              $('#discountPercent').val(discountPercent);
            } else {
              $('#discountPercent').val(0);
            }
          });
        }, 200);
      });

      // بستن مودال تخفیف
      Livewire.on('closeDiscountModal', () => {
        setTimeout(() => {
          closeXModal('discountModal');

          // پاک کردن event handlers
          $('#discountPercent').off('input keyup change');
          $('#discountAmount').off('input keyup change');
        }, 50);
      });

      // بازسازی Select2 بعد از به‌روزرسانی Livewire
      document.addEventListener('livewire:updated', function() {
        initializeSelect2();
      });
    });

    // --- AutoSave Debounce ---
    let autoSaveTimeout = null;

    function triggerAutoSave() {
      if (autoSaveTimeout) clearTimeout(autoSaveTimeout)
      autoSaveTimeout = setTimeout(function() {
        window.Livewire && window.Livewire.dispatch('autoSave');
      }, 1200); // 1.2 ثانیه تاخیر
    }

    // روی همه input و select های قابل ویرایش (به جز readonly و دکمه‌ها و مودال تخفیف)
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll(
          'input:not([readonly]):not([type=button]):not([type=submit]):not(#discountPercent):not(#discountAmount), textarea, select'
        )
        .forEach(function(el) {
          el.addEventListener('input', triggerAutoSave);
          el.addEventListener('change', triggerAutoSave);
        });
    });
  </script>
</div>
