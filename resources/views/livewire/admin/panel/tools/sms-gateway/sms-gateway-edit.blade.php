<div>
  <div class="container-fluid py-4">
    <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
      <div class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 10h18M3 14h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" />
          </svg>
          <h5 class="mb-0 fw-bold">ویرایش پنل: {{ $gateway->title }}</h5>
        </div>
        <a href="{{ route('admin.panel.tools.sms-gateways.index') }}"
          class="btn btn-outline-light btn-sm rounded-pill d-flex align-items-center gap-2 text-white">
          <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          بازگشت
        </a>
      </div>

      <div class="card-body p-4">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="bg-light rounded-3 p-3 shadow-sm">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div
                  class="gateway-logo rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center"
                  style="width: 40px; height: 40px; border: 2px solid #e5e7eb;">
                  @if ($gateway->name === 'pishgamrayan')
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b"
                      stroke-width="2">
                      <path d="M12 2a10 10 0 110 20 10 10 0 010-20zm0 4v6l5 5" />
                    </svg>
                  @elseif($gateway->name === 'farazsms')
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#16a34a"
                      stroke-width="2">
                      <path d="M4 4h16v16H4zM8 8l8 8M8 16L16 8" />
                    </svg>
                  @elseif($gateway->name === 'mellipayamak')
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626"
                      stroke-width="2">
                      <path d="M4 12a8 8 0 018-8 8 8 0 018 8H4z" />
                    </svg>
                  @elseif($gateway->name === 'kavenegar')
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563eb"
                      stroke-width="2">
                      <path d="M12 2L2 12l10 10 10-10L12 2zm0 4v12" />
                    </svg>
                  @elseif($gateway->name === 'payamito')
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#9333ea"
                      stroke-width="2">
                      <path d="M3 6l9 9 9-9M5 4h14v16H5z" />
                    </svg>
                  @endif
                </div>
                <input type="text" wire:model="title" class="form-control fw-bold" placeholder="عنوان پنل" required>
              </div>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="isActive" wire:model="is_active">
                <label class="form-check-label fw-medium" for="isActive">
                  وضعیت: <span
                    class="text-{{ $is_active ? 'success' : 'danger' }}">{{ $is_active ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="bg-light rounded-3 p-3 shadow-sm">
              <label for="settings" class="form-label fw-bold text-dark mb-2">تنظیمات (JSON)</label>
              <textarea dir="ltr" wire:model="settings" class="form-control rounded-2" id="settings" rows="6"
                placeholder="تنظیمات را به‌صورت JSON وارد کنید"
                style="resize: vertical; font-family: 'Courier New', monospace; font-size: 0.9rem; background: #f9fafb; border-color: #d1d5db;"></textarea>
              <small class="text-muted mt-1 d-block">مثال: {"auth_key": "xxxx", "otp_id": "96"}</small>
            </div>
          </div>

          <div class="col-12 text-end mt-3">
            <button wire:click="update"
              class="btn my-btn-primary rounded-pill px-4 py-2 d-flex align-items-center gap-2">
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
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>
