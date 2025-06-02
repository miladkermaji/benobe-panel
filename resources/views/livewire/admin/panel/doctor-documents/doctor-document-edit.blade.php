<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش مدرک: {{ $title ?? 'بدون عنوان' }}</h5>
      </div>
      <a href="{{ route('admin.panel.doctor-documents.index') }}"
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
          <div class="text-center mb-4">
            <div class="position-relative d-inline-block">
              <img src="{{ $filePreview }}" class="rounded shadow border-2 border-white"
                style="max-width: 200px; max-height: 200px; object-fit: cover;" alt="پیش‌نمایش مدرک"
                wire:loading.class="opacity-50" wire:target="file">
              <label for="file"
                class="btn my-btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 p-2 shadow"
                style="transform: translate(10%, 10%);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M4 12h16M12 4v16" />
                </svg>
              </label>
              <input type="file" wire:model="file" id="file" class="d-none" accept="image/*,.pdf,.doc,.docx">
            </div>
          </div>

          <div class="row g-4">
            <div class="col-12 position-relative mt-5" wire:ignore>
              <select wire:model="doctor_id" class="form-select select2" id="doctor_id" required>
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
              <input type="text" wire:model="title" class="form-control" id="title" placeholder=" ">
              <label for="title" class="form-label">عنوان مدرک (اختیاری)</label>
              @error('title')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-12 position-relative mt-5">
              <div class="form-check form-switch d-flex align-items-center gap-3">
                <input class="form-check-input" type="checkbox" id="is_verified" wire:model="is_verified">
                <label class="form-check-label fw-medium mx-4" for="is_verified">
                  وضعیت تأیید: <span
                    class="px-2 text-{{ $is_verified ? 'success' : 'danger' }}">{{ $is_verified ? 'تأیید شده' : 'تأیید نشده' }}</span>
                </label>
              </div>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="update"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
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



  <script>
    document.addEventListener('livewire:init', function() {
      $('#doctor_id').select2({
        dir: 'rtl',
        placeholder: 'انتخاب کنید',
        width: '100%'
      });

      $('#doctor_id').on('select2:select', function() {
        @this.set('doctor_id', $(this).val());
      });

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>
