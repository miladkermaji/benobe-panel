<div class="container-fluid py-2 mt-3" dir="rtl">
  <div class="card shadow-lg border-0 rounded-2 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش یادداشت</h5>
      </div>
      <a href="{{ route('mc.panel.doctornotes.index') }}"
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
        <div class="col-12">
          <div class="row g-3">
            <div class="col-12 position-relative mt-4" wire:ignore>
              <select wire:model="appointment_type" class="form-select select2-type" id="appointment_type" required>
                <option value="in_person">حضوری</option>
                <option value="online_phone">تلفنی</option>
                <option value="online_text">متنی</option>
                <option value="online_video">ویدیویی</option>
              </select>
              <label for="appointment_type" class="form-label">نوع نوبت</label>
            </div>
            <div class="col-12 position-relative mt-4">
              <textarea wire:model="notes" class="form-control" id="notes" rows="3" placeholder=" "></textarea>
              <label for="notes" class="form-label">یادداشت (اختیاری)</label>
            </div>
            <div class="col-12 text-end mt-3 w-100 d-flex justify-content-end">
              <button wire:click="update"
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
        </div>
      </div>
    </div>

    <script>
      document.addEventListener('livewire:init', function() {
        Livewire.on('show-alert', (event) => {
          toastr[event.type](event.message);
        });

        function initializeSelect2() {
          $('#appointment_type').select2({
            dir: 'rtl',
            placeholder: 'نوع نوبت',
            allowClear: true,
            width: '100%',
          });

          $('#appointment_type').on('change.select2', function() {
            @this.set('appointment_type', this.value);
          });
        }

        initializeSelect2();

        Livewire.hook('element.updated', (el, component) => {
          if (el.id === 'appointment_type') {
            initializeSelect2();
          }
        });
      });
    </script>
  </div>
</div>
