<div class="container-fluid py-5" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <!-- هدر اصلی -->
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="background: linear-gradient(135deg, #1e3a8a, #60a5fa);">
      <div class="d-flex align-items-center gap-3">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="3" y1="9" x2="21" y2="9"></line>
          <line x1="9" y1="21" x2="9" y2="9"></line>
        </svg>
        <h5 class="mb-0 fw-bold">تغییر لوگوی سایت</h5>
      </div>
      <a href="{{ route('admin.panel.setting.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill d-flex align-items-center gap-2 text-white hover:shadow-md transition-all text-white">
        <svg width="16" style="transform: rotate(180deg)" height="16" viewBox="0 0 24 24" fill="#fff"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <!-- بدنه اصلی -->
    <div class="card-body p-5 bg-light" style="background: linear-gradient(145deg, #ffffff, #f1f5f9);">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="bg-white rounded-3 p-4 shadow-sm hover:shadow-md transition-all">
            <label class="form-label fw-bold text-dark mb-3" style="font-size: 1.15rem; color: #1e3a8a;">انتخاب تصویر
              لوگو</label>
            <div class="input-group mb-3">
              <input type="file" class="form-control input-shiny" wire:model="logo"
                accept="image/png,image/jpg,image/jpeg">
              <span class="input-group-text bg-gradient text-white"
                style="background: linear-gradient(135deg, #1e3a8a, #60a5fa); border-radius: 0 12px 12px 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="17 8 12 3 7 8"></polyline>
                  <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
              </span>
            </div>
            <small class="text-muted d-block mb-4" style="font-size: 0.9rem;">ابعاد فایل باید 200x53 پیکسل باشد.
              فرمت‌های مجاز: PNG, JPG, JPEG.</small>

            @if ($currentLogo)
              <div class="mt-4 text-center logo-preview">
                <img src="{{ Storage::url($currentLogo->path) }}" class="img-thumbnail rounded shadow-sm" width="200"
                  alt="لوگوی فعلی"
                  style="height: 53px; object-fit: contain; transition: transform 0.4s ease, box-shadow 0.4s ease;">
                <p class="text-muted mt-2" style="font-size: 0.95rem;">لوگوی فعلی</p>
              </div>
            @endif

            <div class="mt-5 text-end">
              <button wire:click="saveLogo"
                class="btn btn-primary rounded-pill px-5 py-2 d-flex align-items-center gap-2 shadow-md transition-all"
                style="background: linear-gradient(135deg, #1e3a8a, #60a5fa);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="17 8 12 3 7 8"></polyline>
                  <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                ذخیره لوگو
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- استایل‌ها -->
  <style>
    .card {
      transition: all 0.3s ease;
    }

    .card:hover {
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .card-header {
      border-bottom: 3px solid rgba(255, 255, 255, 0.15);
    }

    .input-shiny {
      border-radius: 12px 0 0 12px;
      border: 2px solid #e5e7eb;
      padding: 12px;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .input-shiny:focus {
      border-color: #60a5fa;
      box-shadow: 0 0 12px rgba(96, 165, 250, 0.3);
      outline: none;
    }

    .btn-primary {
      border: none;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #1e40af, #3b82f6);
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
    }

    .btn-outline-light {
      border: 2px solid rgba(255, 255, 255, 0.8);
      transition: all 0.3s ease;
    }

    .btn-outline-light:hover {
      background: rgba(255, 255, 255, 0.25);
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
    }

    .logo-preview img {
      border: 1px solid #e5e7eb;
    }

    .logo-preview img:hover {
      transform: scale(1.1);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .shadow-sm {
      box-shadow: 0 4px 15 Yoshidapx rgba(0, 0, 0, 0.05);
    }

    .hover\:shadow-md:hover {
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
    }

    .bg-gradient {
      transition: all 0.3s ease;
    }

    .bg-gradient:hover {
      background: linear-gradient(135deg, #2563eb, #93c5fd);
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
  </style>

  <!-- اسکریپت Toastr -->
  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => toastr[event.type](event.message));
    });
  </script>
</div>
