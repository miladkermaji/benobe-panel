<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش سوال متداول: {{ Str::limit($form['question'], 30) }}</h5>
      </div>
      <a href="{{ route('dr.panel.doctor-faqs.index') }}"
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
            <div class="col-12 position-relative mt-5">
              <input type="text" wire:model.live="form.question" class="form-control" id="question" placeholder=" "
                required>
              <label for="question" class="form-label">سوال</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model.live="form.answer" class="form-control" id="answer" rows="5" placeholder=" " required></textarea>
              <label for="answer" class="form-label">پاسخ</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="is_active" wire:model.live="form.is_active">
                <label class="form-check-label fw-medium" for="is_active">
                  وضعیت: <span
                    class="px-2 text-{{ $form['is_active'] ? 'success' : 'danger' }}">{{ $form['is_active'] ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model.live="form.order" class="form-control" id="order" placeholder=" "
                min="0" required>
              <label for="order" class="form-label">ترتیب</label>
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
    document.addEventListener('livewire:initialized', function() {
      window.Livewire.on('show-alert', ({
        type,
        message
      }) => {
        toastr[type](message);
      });
    });
  </script>
</div>
