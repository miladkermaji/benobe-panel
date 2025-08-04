<div class="doctor-faqs-container">
  <div class="container py-2 mt-3" dir="rtl">
    <div class="glass-header text-white p-2  shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3 mb-2">
            <h1 class="m-0 h4 font-thin text-nowrap  mb-md-0">افزودن سوال متداول جدید</h1>
          </div>
          <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
            <div class="d-flex gap-2 flex-shrink-0 justify-content-center">
              <a href="{{ route('dr.panel.doctor-faqs.index') }}"
                class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center gap-1">
                <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2">
                  <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>بازگشت</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-3 p-md-4">
          <div class="row g-3 g-md-4">
            <!-- سوال -->
            <div class="col-12">
              <div class="form-group position-relative">
                <input type="text" wire:model.live="form.question" class="form-control h-50" id="question"
                  placeholder="سوال متداول خود را وارد کنید..." required>
                <label for="question" class="form-label">سوال متداول</label>
                @error('form.question')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- پاسخ -->
            <div class="col-12">
              <div class="form-group position-relative">
                <textarea wire:model.live="form.answer" class="form-control" id="answer" rows="4"
                  placeholder="پاسخ سوال را وارد کنید..." required></textarea>
                <label for="answer" class="form-label">پاسخ</label>
                @error('form.answer')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- وضعیت و ترتیب -->
            <div class="col-12 col-md-6">
              <div class="form-group">
                <div
                  class="form-check form-switch d-flex align-items-center justify-content-between p-3 border rounded-3">
                  <div class="d-flex align-items-center gap-2">
                    <input class="form-check-input" type="checkbox" id="is_active" wire:model.live="form.is_active">
                    <label class="form-check-label fw-medium mb-0" for="is_active">
                      وضعیت سوال
                    </label>
                  </div>
                  <span class="badge {{ $form['is_active'] ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                    {{ $form['is_active'] ? 'فعال' : 'غیرفعال' }}
                  </span>
                </div>
                @error('form.is_active')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-12 col-md-6">
              <div class="form-group position-relative">
                <input type="number" wire:model.live="form.order" class="form-control h-50" id="order"
                  placeholder="ترتیب نمایش (مثلاً: 1)" min="0" required>
                <label for="order" class="form-label">ترتیب نمایش</label>
                @error('form.order')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- دکمه ذخیره -->
            <div class="col-12">
              <div class="d-flex justify-content-end mt-4">
                <button wire:click="store" wire:loading.attr="disabled"
                  class="btn btn-gradient-success px-4 py-2 d-flex align-items-center gap-2 rounded-2">
                  <span wire:loading.remove>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <path d="M12 5v14M5 12h14" />
                    </svg>
                    افزودن سوال متداول
                  </span>
                  <span wire:loading>
                    <div class="spinner-border spinner-border-sm" role="status">
                      <span class="visually-hidden">در حال بارگذاری...</span>
                    </div>
                    در حال ذخیره...
                  </span>
                </button>
              </div>
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
    });
  </script>
</div>
