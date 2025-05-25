<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold">گالری تصاویر درمانگاه: {{ $treatmentCenter->name }}</h5>
      </div>
      <a href="{{ route('admin.panel.treatment-centers.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill d-flex align-items-center gap-2 text-white hover:shadow-md transition-all">
        <svg width="16" style="transform: rotate(180deg)" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <div class="card-body p-4">
      <div class="row g-4">
        <div class="col-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label class="form-label fw-bold text-dark mb-2">آپلود تصاویر</label>
            <input type="file" wire:model="images" multiple class="form-control input-shiny" accept="image/*">
            @foreach ($images as $index => $image)
              <div class="mt-2">
                <input type="text" wire:model="captions.{{ $index }}" class="form-control input-shiny mt-1"
                  placeholder="توضیح تصویر {{ $index + 1 }}">
              </div>
            @endforeach
            <button wire:click="uploadImages"
              class="btn my-btn-primary rounded-pill mt-3 px-4 d-flex align-items-center gap-2">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              آپلود
            </button>
          </div>
        </div>

        <div class="col-12 mt-3">
          <h6 class="fw-bold mb-3">تصاویر گالری</h6>
          <div class="row g-3">
            @forelse ($galleries as $gallery)
              <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm position-relative">
                  <img src="{{ Storage::url($gallery->image_path) }}" class="card-img-top"
                    alt="{{ $gallery->caption ?? 'تصویر درمانگاه' }}" style="height: 150px; object-fit: cover;">
                  <div class="card-body p-2 text-center">
                    <p class="text-muted small">{{ $gallery->caption ?? 'بدون توضیح' }}</p>
                    <div class="d-flex justify-content-center gap-2">
                      <button wire:click="setPrimary({{ $gallery->id }})"
                        class="btn btn-sm btn-outline-success rounded-pill {{ $gallery->is_primary ? 'active' : '' }}">
                        {{ $gallery->is_primary ? 'اصلی' : 'تنظیم به‌عنوان اصلی' }}
                      </button>
                      <button wire:click="deleteImage({{ $gallery->id }})"
                        class="btn btn-sm btn-outline-danger rounded-pill">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <div class="col-12 text-center py-5">
                <p class="text-muted">هیچ تصویری در گالری وجود ندارد.</p>
              </div>
            @endforelse
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

    .form-control {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 12px 15px;
      font-size: 14px;
      transition: all 0.3s ease;
      background: #fafafa;
    }

    .form-control:focus {
      border-color: #6b7280;
      box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.2);
      background: #fff;
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

    .btn-outline-success.active {
      background-color: #28a745;
      color: white;
    }
  </style>

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => toastr[event.type](event.message));
    });
  </script>
</div>
