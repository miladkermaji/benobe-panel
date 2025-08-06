<div class="container-fluid py-2 mt-3" dir="rtl">
  <div class="card shadow-lg border-0 rounded-2 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن خدمت جدید</h5>
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
      <div class="row justify-content-center">
        <div class="col-12">
          <form wire:submit="store">
            <div class="row g-3">
              <!-- Insurance Selection -->
              <div class="col-12 position-relative mt-4" wire:ignore>
                <select wire:model="selectedInsuranceIds" class="form-select select2" id="insurance_ids" multiple>
                  @foreach ($availableInsurances as $insurance)
                    <option value="{{ $insurance->id }}">{{ $insurance->name }}</option>
                  @endforeach
                </select>
                <label for="insurance_ids" class="form-label">انتخاب بیمه‌ها *</label>
              </div>
              @error('selectedInsuranceIds')
                <span class="text-danger small">{{ $message }}</span>
              @enderror

              <!-- Selected Count -->
              @if (count($selectedInsuranceIds) > 0)
                <div class="col-12">
                  <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" class="me-2">
                        <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span>{{ count($selectedInsuranceIds) }} بیمه انتخاب شده است</span>
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
                      <path d="M12 5v14M5 12h14" />
                    </svg>
                    ذخیره بیمه‌ها
                  </span>
                  <span wire:loading>
                    <div class="spinner-border spinner-border-sm me-1" role="status">
                      <span class="visually-hidden">در حال ذخیره...</span>
                    </div>
                    در حال ذخیره...
                  </span>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
      document.addEventListener('livewire:init', function() {
        Livewire.on('show-alert', (event) => {
          toastr[event.type](event.message);
        });

        // Initialize Select2 for insurances (multiple)
        $('#insurance_ids').select2({
          placeholder: 'بیمه‌ها را انتخاب کنید',
          allowClear: true,
          multiple: true,
          dir: 'rtl'
        });

        // Handle insurance change
        $('#insurance_ids').on('change', function() {
          @this.set('selectedInsuranceIds', $(this).val());
        });

        // Listen for insurances refresh
        Livewire.on('refresh-insurances', (data) => {
          $('#insurance_ids').empty();
          data.insurances.forEach(insurance => {
            $('#insurance_ids').append(`<option value="${insurance.id}">${insurance.name}</option>`);
          });
          $('#insurance_ids').trigger('change');
        });
      });
    </script>
  </div>
</div>
