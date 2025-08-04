<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold">ویرایش آیتم فوتر: {{ $title ?? 'بدون عنوان' }}</h5>
      </div>
      <a href="{{ route('admin.panel.footer-contents.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill d-flex align-items-center gap-2 text-white hover:shadow-md transition-all">
        <svg width="16" style="transform: rotate(180deg)" height="16" viewBox="0 0 24 24" fill="none"
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
              <input type="text" wire:model="section" class="form-control input-shiny" id="section"
                placeholder=" ">
              <label for="section" class="form-label">بخش (مثل about، links)</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <input type="text" wire:model="title" class="form-control input-shiny" id="title" placeholder=" ">
              <label for="title" class="form-label">عنوان (اختیاری)</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="description" class="form-control input-shiny" id="description" rows="3" placeholder=" "></textarea>
              <label for="description" class="form-label">توضیحات (اختیاری)</label>
            </div>
            <div class="col-6 position-relative mt-5">
              <input type="text" wire:model="link_url" class="form-control input-shiny" id="link_url"
                placeholder=" ">
              <label for="link_url" class="form-label">لینک (اختیاری)</label>
            </div>
            <div class="col-6 position-relative mt-5">
              <input type="text" wire:model="link_text" class="form-control input-shiny" id="link_text"
                placeholder=" ">
              <label for="link_text" class="form-label">متن لینک (اختیاری)</label>
            </div>
            <div class="col-6 position-relative mt-5">
              <input type="file" wire:model="icon" class="form-control input-shiny" id="icon" accept="image/*">
              <label for="icon" class="form-label">آیکن (اختیاری)</label>
              @if ($current_icon)
                <img src="{{ $current_icon }}" alt="آیکن فعلی" class="mt-2" style="max-width: 50px; height: auto;">
              @endif
            </div>
            <div class="col-6 position-relative mt-5">
              <input type="file" wire:model="image" class="form-control input-shiny" id="image" accept="image/*">
              <label for="image" class="form-label">تصویر (اختیاری)</label>
              @if ($current_image)
                <img src="{{ $current_image }}" alt="تصویر فعلی" class="mt-2" style="max-width: 50px; height: auto;">
              @endif
            </div>
            <div class="col-6 position-relative mt-5">
              <input type="number" wire:model="order" class="form-control input-shiny" id="order" min="0"
                placeholder=" ">
              <label for="order" class="form-label">ترتیب نمایش</label>
            </div>
            <div class="col-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="is_active" wire:model="is_active">
                <label class="form-check-label fw-medium" for="is_active">
                  وضعیت: <span
                    class="px-2 text-{{ $is_active ? 'success' : 'danger' }}">{{ $is_active ? 'فعال' : 'غیرفعال' }}</span>
                </label>
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
                ذخیره
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>



  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => toastr[event.type](event.message));
    });
  </script>
</div>
