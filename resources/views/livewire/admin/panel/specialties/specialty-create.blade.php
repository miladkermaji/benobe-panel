<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن تخصص جدید</h5>
      </div>
      <a href="{{ route('admin.panel.specialties.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
        <svg width="16" style="transform: rotate(180deg)" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <div class="card-body p-4">
      <div class="row g-4 justify-content-center">
        <div class="col-md-12 col-lg-12">
          <!-- نام -->
          <div class="rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label for="name" class="form-label fw-bold text-dark mb-2">نام تخصص</label>
            <input type="text" wire:model="name" class="form-control input-shiny" id="name"
              placeholder="نام تخصص را وارد کنید" required>
            @error('name')
              <span class="text-danger small d-block mt-1">{{ $message }}</span>
            @enderror
          </div>

          <!-- توضیحات -->
          <div class="rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative mt-4">
            <label for="description" class="form-label fw-bold text-dark mb-2">توضیحات (اختیاری)</label>
            <textarea wire:model="description" class="form-control input-shiny" id="description" rows="3"
              placeholder="توضیحات تخصص"></textarea>
            @error('description')
              <span class="text-danger small d-block mt-1">{{ $message }}</span>
            @enderror
          </div>

          <!-- وضعیت -->
          <div class="rounded-3 p-3 shadow-sm hover:shadow-md transition-all mt-4">
            <div class="d-flex align-items-center">
              <input class="form-check-input flex-shrink-0" type="checkbox" id="isActive" wire:model="status"
                style="width: 20px; height: 20px;">
              <label class="form-check-label fw-medium flex-grow-1 mb-0 mx-4" for="isActive">
                وضعیت: <span class="text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
              </label>
            </div>
            @error('status')
              <span class="text-danger small d-block mt-1">{{ $message }}</span>
            @enderror
          </div>

          <!-- دکمه ذخیره -->
          <div class="text-end mt-4">
            <button wire:click="store"
              class="btn my-btn-primary rounded-pill px-5 py-2 d-flex align-items-center gap-2 shadow-md hover:shadow-lg transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              افزودن
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
    });
  </script>
</div>
