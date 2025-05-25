<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold">ویرایش ریدایرکت: {{ $source_url }}</h5>
      </div>
      <a href="{{ route('admin.panel.tools.redirects.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill d-flex align-items-center gap-2 text-white hover:shadow-md transition-all">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <div class="card-body p-4">
      <div class="row g-4">
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label for="source_url" class="form-label fw-bold text-dark mb-2">URL مبدا</label>
            <input type="text" wire:model="source_url" class="form-control input-shiny" id="source_url"
              placeholder="https://example.com/old-path" required>
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label for="target_url" class="form-label fw-bold text-dark mb-2">URL مقصد</label>
            <input type="text" wire:model="target_url" class="form-control input-shiny" id="target_url"
              placeholder="https://example.com/new-path" required>
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label for="status_code" class="form-label fw-bold text-dark mb-2">کد وضعیت</label>
            <select wire:model="status_code" class="form-select input-shiny" id="status_code">
              <option value="301">301 - دائمی</option>
              <option value="302">302 - موقت</option>
            </select>
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="isActive" wire:model="is_active">
              <label class="form-check-label fw-medium" for="isActive">
                وضعیت: <span
                  class="text-{{ $is_active ? 'success' : 'danger' }}">{{ $is_active ? 'فعال' : 'غیرفعال' }}</span>
              </label>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label for="description" class="form-label fw-bold text-dark mb-2">توضیحات (اختیاری)</label>
            <textarea wire:model="description" class="form-control input-shiny" id="description" rows="3"
              placeholder="توضیحات ریدایرکت"></textarea>
          </div>
        </div>
        <div class="col-12 text-end mt-3">
          <button wire:click="update"
            class="btn my-btn-primary rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-md hover:shadow-lg transition-all">
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

  <style>
    .bg-gradient-primary {
      background: linear-gradient(90deg, #4f46e5, #7c3aed);
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .card {
      transition: all 0.3s ease;
    }

    .card:hover {
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .form-control,
    .form-select {
      border: 1px solid #d1d5db;
      border-radius: 6px;
      padding: 10px 15px;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .input-shiny {
      box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #4f46e5;
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.25);
      outline: none;
    }

    .my-btn-primary {
      background: linear-gradient(90deg, #4f46e5, #7c3aed);
      border: none;
      color: white;
    }

    .my-btn-primary:hover {
      background: linear-gradient(90deg, #4338ca, #6b21a8);
    }

    .btn-outline-light {
      border-color: rgba(255, 255, 255, 0.8);
    }

    .btn-outline-light:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    .form-check-input:checked {
      background-color: #4f46e5;
      border-color: #4f46e5;
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
        transform: translateY(-4px);
      }
    }

    /* ریسپانسیو */
    @media (max-width: 991px) {

      /* تبلت */
      .card-body {
        padding: 2rem;
      }

      .card-header {
        padding: 2rem;
      }

      .btn {
        padding: 0.5rem 1.5rem;
      }
    }

    @media (max-width: 767px) {

      /* موبایل */
      .card-body {
        padding: 1.5rem;
      }

      .card-header {
        padding: 1.5rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }

      .btn {
        width: 100%;
        text-align: center;
        justify-content: center;
      }

      .form-label {
        font-size: 0.9rem;
      }

      .form-control,
      .form-select,
      textarea {
        font-size: 0.875rem;
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
