<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن طرح عضویت جدید</h5>
      </div>
      <a href="{{ route('admin.panel.user-membership-plans.index') }}"
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
            <div class="col-12 col-md-6 position-relative mt-5">
              <input type="text" wire:model="name" class="form-control text-end" id="name" placeholder=" ">
              <label for="name" class="form-label">نام طرح</label>
            </div>
            <div class="col-12 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model="type" class="form-select select2" id="type">
                <option value="gold">طلایی</option>
                <option value="silver">نقره‌ای</option>
                <option value="bronze">برنزی</option>
              </select>
              <label for="type" class="form-label">نوع طرح</label>
            </div>
            <div class="col-12 col-md-6 position-relative mt-5">
              <input type="number" wire:model="price" class="form-control text-end" id="price" placeholder=" ">
              <label for="price" class="form-label">قیمت (تومان)</label>
            </div>
            <div class="col-12 col-md-6 position-relative mt-5">
              <input type="number" wire:model="discount" class="form-control text-end" id="discount" placeholder=" ">
              <label for="discount" class="form-label">تخفیف (درصد)</label>
            </div>
            <div class="col-12 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model="duration_type" class="form-select select2" id="duration_type">
                <option value="day">روز</option>
                <option value="week">هفته</option>
                <option value="month">ماه</option>
                <option value="year">سال</option>
              </select>
              <label for="duration_type" class="form-label">نوع مدت‌زمان</label>
            </div>
            <div class="col-12 col-md-6 position-relative mt-5">
              <input type="number" wire:model="duration_days" class="form-control text-end" id="duration_days"
                placeholder=" ">
              <label for="duration_days" class="form-label">مدت‌زمان</label>
            </div>
            <div class="col-12 col-md-6 position-relative mt-5">
              <input type="number" wire:model="appointment_count" class="form-control text-end" id="appointment_count"
                placeholder=" ">
              <label for="appointment_count" class="form-label">تعداد نوبت</label>
            </div>
            <div class="col-12 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="status" wire:model="status">
                <label class="form-check-label fw-medium" for="status">
                  وضعیت: <span
                    class="px-2 text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="description" class="form-control" id="description" rows="4" placeholder=" "></textarea>
              <label for="description" class="form-label">توضیحات (اختیاری)</label>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="save"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              افزودن طرح عضویت
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#duration_type, #type').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
      }
      initializeSelect2();

      $('#duration_type, #type').on('change', function() {
        @this.set($(this).attr('id'), $(this).val());
      });

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>
