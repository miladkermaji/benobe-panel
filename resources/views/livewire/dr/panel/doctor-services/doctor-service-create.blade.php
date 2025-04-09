<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن سرویس جدید</h5>
      </div>
      <a href="{{ route('dr.panel.doctor-services.index') }}"
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
            <div class="col-md-6 col-sm-12 position-relative mt-5">
              <input type="text" wire:model="name" class="form-control" id="name" placeholder=" " required>
              <label for="name" class="form-label">نام سرویس</label>
            </div>
            <div class="col-md-6 col-sm-12 position-relative mt-5">
              <input type="number" wire:model="duration" class="form-control" id="duration" placeholder=" " required>
              <label for="duration" class="form-label">مدت زمان (دقیقه)</label>
            </div>
            <div class="col-md-6 col-sm-12 position-relative mt-5">
              <input type="number" wire:model="price" class="form-control" id="price" placeholder=" " required>
              <label for="price" class="form-label">قیمت (تومان)</label>
            </div>
            <div class="col-md-6 col-sm-12 position-relative mt-5">
              <input type="number" wire:model="discount" wire:click="openDiscountModal"
                class="form-control cursor-pointer" id="discount" placeholder=" " readonly>
              <label for="discount" class="form-label">تخفیف (درصد)</label>
            </div>
            <div class="col-md-6 col-sm-12 position-relative mt-5">
              <select wire:model="parent_id" class="form-select" id="parent_id">
                <option value="">بدون سرویس مادر</option>
                @foreach ($parentServices as $service)
                  <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
              </select>
              <label for="parent_id" class="form-label">سرویس مادر (اختیاری)</label>
            </div>
            <div class="col-md-6 col-sm-12 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="status" wire:model="status">
                <label class="form-check-label fw-medium mx-4" for="status">
                  وضعیت: <span
                    class="px-2 text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="description" class="form-control" id="description" rows="3" placeholder=" "></textarea>
              <label for="description" class="form-label">توضیحات (اختیاری)</label>
            </div>
            <div class="col-12 text-end mt-4 w-100 d-flex justify-content-end">
              <button wire:click="store"
                class="btn btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                افزودن سرویس
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if ($showDiscountModal)
    <div class="modal fade show d-block" id="discountModal" tabindex="-1" role="dialog"
      aria-labelledby="discountModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h5 class="modal-title" id="discountModalLabel">محاسبه تخفیف</h5>
            <button type="button" class="btn-close" wire:click="closeDiscountModal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-4">
              <label for="discountPercent" class="form-label">درصد تخفیف</label>
              <input type="number" wire:model.live="discountPercent" class="form-control" id="discountPercent"
                placeholder="درصد را وارد کنید">
            </div>
            <div class="mb-4">
              <label for="discountAmount" class="form-label">مبلغ تخفیف (تومان)</label>
              <input type="number" wire:model.live="discountAmount" class="form-control" id="discountAmount"
                placeholder="مبلغ را وارد کنید">
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" wire:click="closeDiscountModal">لغو</button>
            <button type="button" class="btn btn-primary"
              style="background: linear-gradient(to right, #4B5EAA, #8B5CF6); border: none;"
              wire:click="applyDiscount">تأیید</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>
  @endif

  <style>
    /* Same CSS as previous examples */
    .bg-gradient-primary {
      background: linear-gradient(90deg, #6b7280, #374151);
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .form-control,
    .form-select {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 12px 15px;
      font-size: 14px;
      transition: all 0.3s ease;
      height: 48px;
      background: #fafafa;
      width: 100%;
    }

    .form-control:focus,
    .form-select:focus {
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

    .btn-primary {
      background: linear-gradient(90deg, #6b7280, #374151);
      border: none;
      color: white;
      font-weight: 600;
    }

    .btn-primary:hover {
      background: linear-gradient(90deg, #4b5563, #1f2937);
      transform: translateY(-2px);
    }

    .btn-outline-light {
      border-color: rgba(255, 255, 255, 0.8);
    }

    .btn-outline-light:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
    }

    .form-check-input {
      margin-top: 0;
      height: 20px;
      width: 20px;
    }

    .form-check-input:checked {
      background-color: #6b7280;
      border-color: #6b7280;
    }

    .animate-bounce {
      animation: bounce 1s infinite;
    }

    @keyframes bounce {

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-5px);
      }
    }

    .text-shadow {
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 767px) {
      .card-header {
        flex-direction: column;
        gap: 1rem;
      }

      .btn-outline-light {
        width: 100%;
        justify-content: center;
      }

      .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
      }
    }

    @media (max-width: 575px) {
      .card-body {
        padding: 2rem 1.5rem;
      }

      .btn-primary {
        width: 100%;
        justify-content: center;
      }
    }
  </style>

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>
