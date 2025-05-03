<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش یادداشت</h5>
      </div>
      <a href="{{ route('dr.panel.doctornotes.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
              <select wire:model="appointment_type" class="form-select select2-type" id="appointment_type" required>
                <option value="in_person">حضوری</option>
                <option value="online_phone">تلفنی</option>
                <option value="online_text">متنی</option>
                <option value="online_video">ویدیویی</option>
              </select>
              <label for="appointment_type" class="form-label">نوع نوبت</label>
            </div>
            <div class="col-md-6 col-sm-12 position-relative mt-5" wire:ignore>
              <select wire:model="clinic_id" class="form-select select2-clinic" id="clinic_id">
                <option value="">بدون کلینیک</option>
                @foreach ($clinics as $clinic)
                  <option value="{{ $clinic->id }}" {{ $clinic->id == $clinic_id ? 'selected' : '' }}>{{ $clinic->name }}</option>
                @endforeach
              </select>
              <label for="clinic_id" class="form-label">کلینیک (اختیاری)</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="notes" class="form-control" id="notes" rows="3" placeholder=" "></textarea>
              <label for="notes" class="form-label">یادداشت (اختیاری)</label>
            </div>
            <div class="col-12 text-end mt-4 w-100 d-flex justify-content-end">
              <button wire:click="update"
                class="btn my-btn-primary  py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      // Initialize Select2
      function initializeSelect2() {
        $('#clinic_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کلینیک',
          allowClear: true,
          width: '100%',
        });
         $('#appointment_type').select2({
          dir: 'rtl',
          placeholder: 'نوع نوبت',
          allowClear: true,
          width: '100%',
        });

        // Sync Select2 with Livewire
        $('#clinic_id').on('change.select2', function() {
          @this.set('clinic_id', this.value);
        });
        $('#appointment_type').on('change.select2', function() {
          @this.set('appointment_type', this.value);
        });
      }

      // Initial Select2 setup
      initializeSelect2();

      // Reinitialize Select2 after Livewire updates
      Livewire.hook('element.updated', (el, component) => {
        if (el.id === 'clinic_id') {
          initializeSelect2();
        }
      });
    });
  </script>
</div>