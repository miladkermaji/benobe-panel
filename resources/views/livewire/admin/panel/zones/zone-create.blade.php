<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold">افزودن استان جدید</h5>
      </div>
      <a href="{{ route('admin.panel.zones.index') }}"
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
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label for="name" class="form-label fw-bold text-dark mb-2">نام استان</label>
            <input type="text" wire:model="name" class="form-control input-shiny" id="name"
              placeholder="نام استان" required>
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label for="sort" class="form-label fw-bold text-dark mb-2">ترتیب</label>
            <input type="number" wire:model="sort" class="form-control input-shiny" id="sort" placeholder="ترتیب"
              required>
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label for="latitude" class="form-label fw-bold text-dark mb-2">عرض جغرافیایی (اختیاری)</label>
            <input type="number" step="any" wire:model="latitude" class="form-control input-shiny" id="latitude"
              placeholder="مثال: 35.6892">
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label for="longitude" class="form-label fw-bold text-dark mb-2">طول جغرافیایی (اختیاری)</label>
            <input type="number" step="any" wire:model="longitude" class="form-control input-shiny" id="longitude"
              placeholder="مثال: 51.3890">
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label for="population" class="form-label fw-bold text-dark mb-2">جمعیت (اختیاری)</label>
            <input type="number" wire:model="population" class="form-control input-shiny" id="population"
              placeholder="مثال: 1000000">
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label for="area" class="form-label fw-bold text-dark mb-2">مساحت (km²) (اختیاری)</label>
            <input type="number" step="any" wire:model="area" class="form-control input-shiny" id="area"
              placeholder="مثال: 183">
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label for="postal_code" class="form-label fw-bold text-dark mb-2">کد پستی (اختیاری)</label>
            <input type="text" wire:model="postal_code" class="form-control input-shiny" id="postal_code"
              placeholder="مثال: 12345">
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <label for="price_shipping" class="form-label fw-bold text-dark mb-2">هزینه ارسال (تومان) (اختیاری)</label>
            <input type="number" wire:model="price_shipping" class="form-control input-shiny" id="price_shipping"
              placeholder="مثال: 50000">
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
            <div class="col-md-6 col-sm-12">
              <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
                <div class="form-check form-switch d-flex align-items-center gap-2">
                  <input class="form-check-input" type="checkbox" id="isActive" wire:model="status">
                  <label class="form-check-label fw-medium mb-0 mx-3" for="isActive">
                    وضعیت: <span
                      class="text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 text-end mt-3">
          <button wire:click="store"
            class="btn my-btn-primary rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-md hover:shadow-lg transition-all">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            افزودن استان
          </button>
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
