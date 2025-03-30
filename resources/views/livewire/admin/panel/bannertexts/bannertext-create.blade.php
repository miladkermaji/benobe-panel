<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold">افزودن بنر جدید</h5>
      </div>
      <a href="{{ route('admin.panel.bannertexts.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill d-flex align-items-center gap-2 hover:shadow-md transition-all">
        <svg width="16" style="transform: rotate(180deg)" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <div class="card-body p-4">
      <div class="row g-4">
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
            <label for="main_text" class="form-label fw-bold text-dark mb-2">متن اصلی</label>
            <input type="text" wire:model="main_text" class="form-control input-shiny" id="main_text"
              placeholder="متن اصلی بنر" required>
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
            <label class="form-label fw-bold text-dark mb-2">کلمات متغیر (اختیاری)</label>
            @foreach ($switch_words as $index => $word)
              <div class="d-flex align-items-center gap-2 mb-2">
                <input type="text" wire:model="switch_words.{{ $index }}" class="form-control input-shiny"
                  placeholder="کلمه متغیر">
                <button wire:click="removeSwitchWord({{ $index }})" class="btn btn-danger btn-sm rounded-pill">
                  <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12" />
                  </svg>
                </button>
              </div>
            @endforeach
            <button wire:click="addSwitchWord" class="btn btn-outline-success btn-sm rounded-pill">
              <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>

            </button>
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
            <label for="switch_interval" class="form-label fw-bold text-dark mb-2">فاصله تعویض (ثانیه)</label>
            <input type="number" wire:model="switch_interval" class="form-control input-shiny" id="switch_interval"
              placeholder="مثال: 3">
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
            <label for="image" class="form-label fw-bold text-dark mb-2">تصویر بنر (اختیاری)</label>
            <input type="file" wire:model="image" class="form-control input-shiny" id="image" accept="image/*">
            @if ($image)
              <img src="{{ $image->temporaryUrl() }}" alt="پیش‌نمایش" class="mt-2 rounded" style="max-width: 100px;">
            @endif
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
            <div class="col-md-6 col-sm-12">
              <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
                <div class="form-check form-switch d-flex align-items-center gap-2">
                  <input class="form-check-input" type="checkbox" id="isActive" wire:model="status">
                  <label class="form-check-label fw-medium mb-0 mx-3" for="isActive">
                    وضعیت: <span
                      class="text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 text-end mt-3">
          <button wire:click="store"
            class="btn btn-primary rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-md hover:shadow-lg transition-all">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            افزودن بنر
          </button>
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
