<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن گروه کاربری جدید</h5>
      </div>
      <a href="{{ route('admin.panel.user-groups.index') }}"
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
              <input type="text" wire:model="name" class="form-control" id="name" placeholder=" " required>
              <label for="name" class="form-label">نام گروه کاربری</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="description" class="form-control" id="description" rows="3" placeholder=" "></textarea>
              <label for="description" class="form-label">توضیحات (اختیاری)</label>
            </div>
            <div class="col-12 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <label class="status-toggle">
                  <input type="checkbox" wire:model="is_active">
                  <span class="slider"></span>
                </label>
                <label class="form-check-label fw-medium ms-3" for="is_active">
                  وضعیت: <span
                    class="px-2 text-{{ $is_active ? 'success' : 'danger' }}">{{ $is_active ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="store"
              class="btn btn-gradient-success px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              افزودن گروه کاربری
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <style>
    .bg-gradient-primary {
      background: var(--gradient-primary);
    }

    .card {
      border-radius: var(--radius-card);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .form-control {
      border: 1px solid var(--border-neutral);
      border-radius: var(--radius-button);
      padding: 12px 15px;
      font-size: 14px;
      transition: all 0.3s ease;
      background: var(--background-light);
      width: 100%;
    }

    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(46, 134, 193, 0.2);
      background: var(--background-card);
    }

    .form-label {
      position: absolute;
      top: -25px;
      right: 15px;
      color: var(--text-primary);
      font-size: 12px;
      background: var(--background-card);
      padding: 0 5px;
      pointer-events: none;
    }

    .btn-gradient-success {
      background: linear-gradient(90deg, var(--secondary), var(--secondary-hover));
      border: none;
      color: var(--background-card);
      font-weight: 600;
    }

    .btn-gradient-success:hover {
      background: linear-gradient(90deg, var(--secondary-hover), var(--secondary));
      transform: translateY(-2px);
    }

    .btn-outline-light {
      border-color: rgba(255, 255, 255, 0.8);
    }

    .btn-outline-light:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
    }

    .status-toggle {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 24px;
    }

    .status-toggle input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .status-toggle .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: .4s;
      border-radius: 24px;
    }

    .status-toggle .slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }

    .status-toggle input:checked+.slider {
      background-color: var(--secondary);
    }

    .status-toggle input:checked+.slider:before {
      transform: translateX(26px);
    }

    .custom-animate-bounce {
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
    }

    @media (max-width: 575px) {
      .card-body {
        padding: 1.5rem;
      }

      .btn-gradient-success {
        width: 100%;
        justify-content: center;
      }

      .form-control {
        font-size: 13px;
        padding: 10px 12px;
      }

      .form-label {
        font-size: 11px;
        top: -20px;
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
