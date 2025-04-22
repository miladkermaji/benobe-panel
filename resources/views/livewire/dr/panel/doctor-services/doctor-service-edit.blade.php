<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div class="card-header text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش خدمت: {{ $name }}</h5>
      </div>
      <a href="{{ route('dr.panel.doctor-services.index') }}"
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
            <div class="col-md-6 col-sm-12 position-relative mt-5" wire:ignore>
              <select wire:model="insurance_id" class="form-select select2" id="insurance_id">
                <option value="" selected>بدون انتخاب </option>
                @foreach ($insurances as $insurance)
                  <option value="{{ $insurance->id }}">{{ $insurance->name }}</option>
                @endforeach
              </select>
              <label for="insurance_id" class="form-label">بیمه </label>
            </div>
            <div class="col-md-6 col-sm-12 position-relative mt-5" wire:ignore>
              <select wire:model="clinic_id" class="form-select select2" id="clinic_id">
                <option value="" selected>بدون انتخاب </option>
                @foreach ($clinics as $clinic)
                  <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                @endforeach
              </select>
              <label for="clinic_id" class="form-label">کلینیک </label>
            </div>
            <div class="col-md-6 col-sm-12 position-relative mt-5">
              <input type="text" wire:model="name" class="form-control" id="name" placeholder=" " required>
              <label for="name" class="form-label">نام خدمت</label>
            </div>
            <div class="col-md-6 col-sm-12 position-relative mt-5">
              <input type="number" wire:model="duration" class="form-control" id="duration" placeholder=" " required>
              <label for="duration" class="form-label">مدت زمان (دقیقه)</label>
            </div>
            <div class="col-md-6 col-sm-12 position-relative mt-5">
              <input type="number" wire:model="price" class="form-control" id="price" placeholder=" " required>
              <label for="price" class="form-label">قیمت (تومان)</label>
            </div>
            <div class="col-md-6 col-sm-12 position-relative mt-5">
              <input type="number" wire:model="discount" wire:click="openDiscountModal"
                class="form-control cursor-pointer" id="discount" placeholder=" " readonly>
              <label for="discount" class="form-label">تخفیف (درصد)</label>
            </div>
            <div class="col-md-6 col-sm-12 position-relative mt-5" wire:ignore>
              <select wire:model="parent_id" class="form-select select2" id="parent_id">
                <option value="">بدون  زیر دسته</option>
                @foreach ($parentServices as $service)
                  <option value="{{ $service->id }}">
                    {{ $service->name }}
                    ({{ $service->clinic_id ? $service->clinic->name ?? 'کلینیک نامشخص' : 'ویزیت آنلاین' }})
                  </option>
                @endforeach
              </select>
              <label for="parent_id" class="form-label">زیر دسته (اختیاری)</label>
            </div>
            <div class="col-md-6 col-sm-12 position-relative mt-5 d-flex align-items-center">
              <div class="form-check w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="status" wire:model="status">
                <label class="form-check-label fw-medium mx-4" for="status">
                  وضعیت: <span
                    class="px-2 text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="description" class="form-control" id="description" rows="3" placeholder=" "></textarea>
              <label for="description" class="form-label">توضیحات (اختیاری)</label>
            </div>
            <div class="col-12 text-end mt-4 w-100 d-flex justify-content-end">
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
        // جلوگیری از مقداردهی مجدد Select2
        $('#insurance_id, #clinic_id, #parent_id').each(function() {
          if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).select2('destroy');
          }
        });

        // مقداردهی اولیه Select2 برای insurance_id
        $('#insurance_id').select2({
          dir: 'rtl',
          placeholder: 'بدون انتخاب ',
          allowClear: true,
          width: '100%',
          dropdownAutoWidth: true,
          minimumResultsForSearch: 5
        });

        // مقداردهی اولیه Select2 برای clinic_id
        $('#clinic_id').select2({
          dir: 'rtl',
          placeholder: 'بدون انتخاب ',
          allowClear: true,
          width: '100%',
          dropdownAutoWidth: true,
          minimumResultsForSearch: 5
        });

        // مقداردهی اولیه Select2 برای parent_id
        $('#parent_id').select2({
          dir: 'rtl',
          placeholder: 'بدون زیر دسته',
          allowClear: true,
          width: '100%',
          dropdownAutoWidth: true,
          minimumResultsForSearch: 5
        });

        // تنظیم مقادیر اولیه هنگام لود صفحه
        const insuranceId = @json($insurance_id);
        const clinicId = @json($clinic_id);
        const parentId = @json($parent_id);

        console.log('Initial Values:', {
          insuranceId,
          clinicId,
          parentId
        });

        $('#insurance_id').val(insuranceId || '').trigger('change');
        $('#clinic_id').val(clinicId || '').trigger('change');
        $('#parent_id').val(parentId || '').trigger('change');
      }

      // اجرای اولیه Select2
      initializeSelect2();

      // همگام‌سازی با تغییرات کاربر
      $('#insurance_id').on('change', function() {
        const value = $(this).val() === '' ? null : $(this).val();
        console.log('Insurance ID Changed:', value);
        @this.set('insurance_id', value);
      });

      $('#clinic_id').on('change', function() {
        const value = $(this).val() === '' ? null : $(this).val();
        console.log('Clinic ID Changed:', value);
        @this.set('clinic_id', value);
      });

      $('#parent_id').on('change', function() {
        const value = $(this).val() === '' ? null : $(this).val();
        console.log('Parent ID Changed:', value);
        @this.set('parent_id', value);
      });

      // به‌روزرسانی Select2 بعد از تغییرات Livewire
      Livewire.on('updateSelect2', () => {
        initializeSelect2();
      });

      // نمایش اعلان‌ها
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      // اطمینان از مقداردهی مجدد Select2 بعد از به‌روزرسانی‌های Livewire
      document.addEventListener('livewire:updated', function() {
        initializeSelect2();
      });
    });
  </script>
</div>
