<div class="doctor-prescriptions-container">
  <div class="py-1" dir="rtl">
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-2">
          <!-- Toggle Section -->
          <div class="text-center mb-4 p-4 bg-light rounded-3">
            <div
              class="d-flex justify-content-center align-items-center p-3 gap-2 bg-white rounded-3 shadow-sm flex-wrap"
              style="min-height: 60px;">
              <span class="fw-bold fs-5 text-dark mb-0">درخواست نسخه</span>
              <div class="form-check form-switch m-0 position-relative" style="display: flex; align-items: center;">
                <input class="form-check-input" type="checkbox" id="requestEnabledSwitch"
                  wire:model.live="request_enabled">
                <label class="form-check-label" for="requestEnabledSwitch"></label>
                <span class="position-absolute fw-bold text-white"
                  style="top: 50%; transform: translateY(-50%); z-index: 2; pointer-events: none; font-size: 0.7rem; {{ $request_enabled ? 'left: 17px;' : 'right: 8px;' }}">
                  {{ $request_enabled ? 'فعال' : 'غیرفعال' }}
                </span>
              </div>
            </div>
          </div>
          <!-- Types Section - Only show when enabled -->
          @if ($request_enabled)
            <div class="types-section">
              <div class="text-center mb-4 border-bottom pb-3">
                <h5 class="fw-bold text-dark mb-2">انتخاب نوع نسخه‌های مجاز برای درخواست</h5>
                <p class="text-muted mb-0">نوع‌های نسخه‌ای که بیماران می‌توانند درخواست دهند را انتخاب کنید</p>
              </div>
              <div class="row g-3">
                @foreach ($all_types as $type => $label)
                  <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="form-check p-3 border rounded-3 h-100 d-flex align-items-center">
                      <input class="form-check-input me-3" type="checkbox" id="type-{{ $type }}"
                        value="{{ $type }}" wire:model.live="enabled_types">
                      <label class="form-check-label fw-semibold text-dark w-100" for="type-{{ $type }}">
                        {{ $label }}
                      </label>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @else
            <!-- Disabled Message -->
            <div class="text-center py-5">
              <div class="text-muted">
                <i class="bi bi-toggle-off fs-1 mb-3 d-block"></i>
                <h6 class="mb-2">درخواست نسخه غیرفعال است</h6>
                <p class="mb-0">برای انتخاب نوع نسخه‌های مجاز، ابتدا درخواست نسخه را فعال کنید</p>
              </div>
            </div>
          @endif
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
