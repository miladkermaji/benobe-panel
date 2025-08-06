<div class="specialty-create-container">
  <div class="container py-2 mt-3" dir="rtl">
    <div class="glass-header text-white p-2 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3 mb-2">
            <h1 class="m-0 h4 font-thin text-nowrap mb-md-0">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                class="me-2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              افزودن تخصص
            </h1>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-4">
          <form wire:submit="store">
            <!-- Specialty Selection -->
            <div class="row mb-4">
              <div class="col-12">
                <div class="position-relative" wire:ignore>
                  <select wire:model="selectedSpecialtyIds" class="form-select select2" id="specialty_ids" multiple>
                    @foreach ($availableSpecialties as $specialty)
                      <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                    @endforeach
                  </select>
                  <label for="specialty_ids" class="form-label">انتخاب تخصص‌ها *</label>
                </div>
                @error('selectedSpecialtyIds')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- Selected Count -->
            @if (count($selectedSpecialtyIds) > 0)
              <div class="row mb-4">
                <div class="col-12">
                  <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" class="me-2">
                        <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span>{{ count($selectedSpecialtyIds) }} تخصص انتخاب شده است</span>
                    </div>
                  </div>
                </div>
              </div>
            @endif

            <!-- Form Actions -->
            <div class="row">
              <div class="col-12">
                <div class="d-flex gap-2 justify-content-end">
                  <a href="{{ route('mc.panel.specialties.index') }}" class="btn btn-outline-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="me-1">
                      <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                    بازگشت
                  </a>
                  <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" class="me-1">
                        <path d="M5 13l4 4L19 7" />
                      </svg>
                      ذخیره تخصص‌ها
                    </span>
                    <span wire:loading>
                      <svg class="spinner-border spinner-border-sm me-1" width="16" height="16"
                        viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                          fill="none" stroke-dasharray="31.416" stroke-dashoffset="31.416">
                          <animate attributeName="stroke-dasharray" dur="2s"
                            values="0 31.416;15.708 15.708;0 31.416" repeatCount="indefinite" />
                          <animate attributeName="stroke-dashoffset" dur="2s" values="0;-15.708;-31.416"
                            repeatCount="indefinite" />
                        </circle>
                      </svg>
                      در حال ذخیره...
                    </span>
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('livewire:init', () => {
      // Initialize Select2 for specialties (multiple)
      $('#specialty_ids').select2({
        placeholder: 'تخصص‌ها را انتخاب کنید',
        allowClear: true,
        multiple: true,
        dir: 'rtl'
      });

      // Handle specialty change
      $('#specialty_ids').on('change', function() {
        @this.set('selectedSpecialtyIds', $(this).val());
      });

      // Listen for specialties refresh
      Livewire.on('refresh-specialties', (data) => {
        $('#specialty_ids').empty();
        data.specialties.forEach(specialty => {
          $('#specialty_ids').append(`<option value="${specialty.id}">${specialty.name}</option>`);
        });
        $('#specialty_ids').trigger('change');
      });
    });
  </script>
</div>
