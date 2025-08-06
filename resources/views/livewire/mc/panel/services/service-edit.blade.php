<div class="container-fluid py-2 mt-3" dir="rtl">
  <div class="card shadow-lg border-0 rounded-2 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
          <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش خدمت</h5>
      </div>
      <a href="{{ route('mc.panel.services.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill px-3 py-1 d-flex align-items-center gap-1 hover:shadow-lg transition-all">
        <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <div class="card-body p-3">
      @if ($currentService)
        <div class="row justify-content-center">
          <div class="col-12">
            <!-- Current Service Info -->
            <div class="alert alert-info mb-4">
              <div class="d-flex align-items-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" class="me-2">
                  <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                  <strong>خدمت فعلی:</strong> {{ $currentService->name }}
                  @if ($currentService->description)
                    <br><small class="text-muted">{{ $currentService->description }}</small>
                  @endif
                </div>
              </div>
            </div>

            <form wire:submit="update">
              <div class="row g-3">
                <!-- Service Selection -->
                <div class="col-12 position-relative mt-4" wire:ignore>
                  <select wire:model="selectedServiceIds" class="form-select select2" id="service_ids" multiple>
                    @foreach ($availableServices as $service)
                      <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                  </select>
                  <label for="service_ids" class="form-label">انتخاب خدمت جدید *</label>
                </div>
                @error('selectedServiceIds')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror

                <!-- Selected Count -->
                @if (count($selectedServiceIds) > 0)
                  <div class="col-12">
                    <div class="alert alert-success">
                      <div class="d-flex align-items-center">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2" class="me-2">
                          <path d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ count($selectedServiceIds) }} خدمت انتخاب شده است</span>
                      </div>
                    </div>
                  </div>
                @endif

                <!-- Form Actions -->
                <div class="col-12 text-end mt-3 w-100 d-flex justify-content-end">
                  <button type="submit"
                    class="btn my-btn-primary py-2 px-3 d-flex align-items-center gap-1 shadow-lg hover:shadow-xl transition-all"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove>
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                        <path d="M17 21v-8H7v8M7 3v5h8" />
                      </svg>
                      به‌روزرسانی خدمت‌ها
                    </span>
                    <span wire:loading>
                      <div class="spinner-border spinner-border-sm me-1" role="status">
                        <span class="visually-hidden">در حال به‌روزرسانی...</span>
                      </div>
                      در حال به‌روزرسانی...
                    </span>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      @else
        <div class="text-center py-4">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="1">
            <path
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <p class="text-muted mt-2 mb-0">خدمت مورد نظر یافت نشد</p>
          <a href="{{ route('mc.panel.services.index') }}" class="btn btn-outline-secondary mt-3">
            بازگشت به لیست
          </a>
        </div>
      @endif
    </div>

    <script>
      document.addEventListener('livewire:init', function() {
        Livewire.on('show-alert', (event) => {
          toastr[event.type](event.message);
        });

        function initializeSelect2() {
          // Initialize Select2 for specialties (multiple)
          $('#service_ids').select2({
            placeholder: 'خدمت‌ها را انتخاب کنید',
            allowClear: true,
            multiple: true,
            dir: 'rtl'
          });

          // Handle specialty change
          $('#service_ids').on('change', function() {
            @this.set('selectedServiceIds', $(this).val());
          });
        }

        initializeSelect2();

        // Listen for specialties refresh
        Livewire.on('refresh-specialties', (data) => {
          $('#service_ids').empty();
          data.specialties.forEach(specialty => {
            $('#service_ids').append(`<option value="${specialty.id}">${specialty.name}</option>`);
          });
          $('#service_ids').trigger('change');
        });

        Livewire.hook('element.updated', (el, component) => {
          if (el.id === 'service_ids') {
            initializeSelect2();
          }
        });
      });
    </script>
  </div>
</div>
