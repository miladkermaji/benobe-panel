<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header glass-header text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن بهترین پزشک جدید</h5>
      </div>
      <a href="{{ route('admin.panel.best-doctors.index') }}"
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
            <!-- پزشک -->
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.live="doctor_id" class="form-select select2" id="doctor_id" required>
                <option value="">انتخاب کنید</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}">{{ $doctor->display_name ?? $doctor->full_name }}</option>
                @endforeach
              </select>
              <label for="doctor_id" class="form-label">پزشک</label>
              @error('doctor_id')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>

            <!-- کلینیک -->
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.live="medical_center_id" class="form-select select2" id="medical_center_id">
                <option value="">انتخاب کنید</option>
                @foreach ($clinics as $clinic)
                  <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                @endforeach
              </select>
              <label for="medical_center_id" class="form-label">کلینیک (اختیاری)</label>
              @error('medical_center_id')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>

            <!-- بهترین پزشک -->
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="best_doctor" wire:model="best_doctor">
                <label class="form-check-label fw-medium" for="best_doctor">
                  بهترین پزشک: <span
                    class="px-2 text-{{ $best_doctor ? 'success' : 'danger' }}">{{ $best_doctor ? 'بله' : 'خیر' }}</span>
                </label>
              </div>
            </div>

            <!-- بهترین مشاور -->
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="best_consultant" wire:model="best_consultant">
                <label class="form-check-label fw-medium" for="best_consultant">
                  بهترین مشاور: <span
                    class="px-2 text-{{ $best_consultant ? 'success' : 'danger' }}">{{ $best_consultant ? 'بله' : 'خیر' }}</span>
                </label>
              </div>
            </div>

            <!-- وضعیت -->
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="status" wire:model="status">
                <label class="form-check-label fw-medium" for="status">
                  وضعیت: <span
                    class="px-2 text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
          </div>

          <!-- دکمه افزودن -->
          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="store"
              class="btn btn-gradient-success px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              افزودن بهترین پزشک
            </button>
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

    .form-control,
    .form-select {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 12px 15px;
      font-size: 14px;
      transition: all 0.3s ease;
      height: 48px;
      background: #fafafa;
      width: 100%;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #6b7280;
      box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.2);
      background: #fff;
    }

    .form-label {
      position: absolute;
      top: -25px;
      right: 15px;
      color: #374151;
      font-size: 12px;
      background: #ffffff;
      padding: 0 5px;
      pointer-events: none;
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

    .btn-outline-light {
      border-color: rgba(255, 255, 255, 0.8);
    }

    .btn-outline-light:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
    }

    .form-check-input {
      margin-top: 0;
      height: 20px;
      width: 20px;
      vertical-align: middle;
    }

    .form-check-label {
      margin-right: 25px;
      line-height: 1.5;
      vertical-align: middle;
    }

    .form-check-input:checked {
      background-color: #6b7280;
      border-color: #6b7280;
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

    .select2-container {
      width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
      height: 48px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      background: #fafafa;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 46px;
      padding-right: 15px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 46px;
    }

    .select2-dropdown {
      z-index: 1050 !important;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
    }
  </style>

  <script>
    document.addEventListener('livewire:initialized', () => {
      window.Livewire.on('show-alert', function(event) {
        if (typeof toastr !== 'undefined') {
          toastr.options = {
            "positionClass": "toast-top-right",
            "rtl": true
          };
          toastr[event.type](event.message);
        } else {
          alert(event.message);
        }
      });
      // Initialize doctor select2
      $('#doctor_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'انتخاب پزشک',
        allowClear: true
      });

      // Initialize clinic select2
      $('#medical_center_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'انتخاب کلینیک',
        allowClear: true
      });

      // Handle doctor selection
      $('#doctor_id').on('select2:select', function(e) {
        @this.set('doctor_id', e.target.value);
      });

      // Handle doctor clearing
      $('#doctor_id').on('select2:clear', function() {
        @this.set('doctor_id', null);
      });

      // Handle clinic selection
      $('#medical_center_id').on('select2:select', function(e) {
        @this.set('medical_center_id', e.target.value);
      });

      // Handle clinic clearing
      $('#medical_center_id').on('select2:clear', function() {
        @this.set('medical_center_id', null);
      });

      // Listen for clinics update
      Livewire.on('clinics-updated', (data) => {
        const clinicSelect = $('#medical_center_id');
        clinicSelect.empty();

        // Add placeholder option
        clinicSelect.append(new Option('انتخاب کلینیک', '', true, true));

        // Add clinic options
        data.clinics.forEach(clinic => {
          clinicSelect.append(new Option(clinic.text, clinic.id, false, false));
        });

        // Trigger change to update Select2
        clinicSelect.trigger('change');
      });
    });

    // Re-initialize Select2 after Livewire updates
    document.addEventListener('livewire:update', () => {
      $('#doctor_id, #medical_center_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'انتخاب پزشک',
        allowClear: true
      });
    });
  </script>



</div>
