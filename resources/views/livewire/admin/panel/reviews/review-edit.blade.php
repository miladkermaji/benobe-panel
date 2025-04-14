<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold">ویرایش نظر: {{ $name ?? 'بدون نام' }}</h5>
      </div>
      <a href="{{ route('admin.panel.reviews.index') }}"
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
              <input type="text" wire:model="name" class="form-control input-shiny" id="name" placeholder=" ">
              <label for="name" class="form-label">نام (اختیاری)</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="comment" class="form-control input-shiny" id="comment" rows="3" placeholder=" "></textarea>
              <label for="comment" class="form-label">نظر (اختیاری)</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <input type="file" wire:model="image" class="form-control input-shiny" id="image" accept="image/*">
              <label for="image" class="form-label">تصویر (اختیاری)</label>
              @if ($current_image)
                <img src="{{ $current_image }}" alt="تصویر فعلی" class="mt-2" style="max-width: 100px; height: auto;">
              @endif
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model="rating" class="form-control input-shiny" id="rating" min="0"
                max="5" step="1" placeholder=" ">
              <label for="rating" class="form-label">امتیاز (0-5)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="is_approved" wire:model="is_approved">
                <label class="form-check-label fw-medium" for="is_approved">
                  وضعیت: <span
                    class="px-2 text-{{ $is_approved ? 'success' : 'danger' }}">{{ $is_approved ? 'تأیید شده' : 'تأیید نشده' }}</span>
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
                ذخیره تغییرات
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <style>
    .bg-gradient-primary {
      background: linear-gradient(90deg, #6b7280, #374151);
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .input-shiny {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 12px 15px;
      font-size: 14px;
      transition: all 0.3s ease;
      height: 48px;
      background: #fafafa;
    }

    .input-shiny:focus {
      border-color: #6b7280;
      box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.2);
      background: #fff;
    }

    .form-label {
      position: absolute;
      top: -25px;
      right: 15px;
      color: #374151;
      font-size: 12px;
      background: #ffffff;
      padding: 0 5px;
      pointer-events: none;
    }

    .my-btn-primary {
      background: linear-gradient(90deg, #6b7280, #374151);
      border: none;
      color: white;
      font-weight: 600;
    }

    .my-btn-primary:hover {
      background: linear-gradient(90deg, #4b5563, #1f2937);
      transform: translateY(-2px);
    }

    .form-check-input {
      margin-top: 0;
      height: 20px;
      width: 20px;
      vertical-align: middle;
    }

    .form-check-label {
      margin-right: 25px;
      line-height: 1.5;
      vertical-align: middle;
    }

    .form-check-input:checked {
      background-color: #6b7280;
      border-color: #6b7280;
    }
  </style>

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => toastr[event.type](event.message));
    });
  </script>
</div>
