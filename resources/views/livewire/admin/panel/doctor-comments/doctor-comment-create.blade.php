<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between  gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن نظر جدید</h5>
      </div>
      <a href="{{ route('admin.panel.doctor-comments.index') }}"
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
            <div class="col-12 position-relative mt-5" wire:ignore>
              <select wire:model.live="doctor_id" class="form-select select2" id="doctor_id" required>
                <option value="">انتخاب کنید</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}">{{ $doctor->first_name . ' ' . $doctor->last_name }}</option>
                @endforeach
              </select>
              <label for="doctor_id" class="form-label">پزشک</label>
              @error('doctor_id')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="comment" class="form-control" id="comment" rows="4" placeholder=" " required></textarea>
              <label for="comment" class="form-label">متن نظر</label>
              @error('comment')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="status" wire:model="status">
                <label class="form-check-label fw-medium" for="status">
                  وضعیت: <span
                    class="px-2 text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="store"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              ثبت نظر
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>



  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#doctor_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });

        // تنظیم مقدار اولیه
        const doctorId = @json($doctor_id);
        console.log('Initial doctor_id:', doctorId); // دیباگ
        $('#doctor_id').val(doctorId || '').trigger('change');
      }

      // اجرای اولیه Select2
      initializeSelect2();

      // همگام‌سازی با تغییرات کاربر
      $('#doctor_id').on('change', function() {
        @this.set('doctor_id', $(this).val());
      });

      // نمایش توستر
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      // بازسازی Select2 بعد از هر آپدیت Livewire
      document.addEventListener('livewire:updated', function() {
        initializeSelect2();
      });
    });
  </script>
</div>
