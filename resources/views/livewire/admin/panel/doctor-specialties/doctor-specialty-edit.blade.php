<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش تخصص: {{ $specialty->specialty->name ?? 'نامشخص' }}</h5>
      </div>
      <a href="{{ route('admin.panel.doctor-specialties.index') }}"
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
            <div class="col-12 position-relative mt-5" wire:ignore>
              <select wire:model="doctor_id" class="form-select select2" id="doctor_id" required>
                <option value="">انتخاب کنید</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}">{{ $doctor->first_name . ' ' . $doctor->last_name }}</option>
                @endforeach
              </select>
              <label for="doctor_id" class="form-label">پزشک</label>
              @error('doctor_id')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-12 position-relative mt-5" wire:ignore>
              <select wire:model="specialty_id" class="form-select select2" id="specialty_id" required>
                <option value="">انتخاب کنید</option>
                @foreach ($specialties as $specialty)
                  <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                @endforeach
              </select>
              <label for="specialty_id" class="form-label">تخصص</label>
              @error('specialty_id')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-12 position-relative mt-5" wire:ignore>
              <select wire:model="academic_degree_id" class="form-select select2" id="academic_degree_id">
                <option value="">انتخاب کنید</option>
                @foreach ($academicDegrees as $degree)
                  <option value="{{ $degree->id }}">{{ $degree->title }}</option>
                @endforeach
              </select>
              <label for="academic_degree_id" class="form-label">درجه علمی (اختیاری)</label>
              @error('academic_degree_id')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-12 position-relative mt-5">
              <input type="text" wire:model="specialty_title" class="form-control" id="specialty_title"
                placeholder=" ">
              <label for="specialty_title" class="form-label">عنوان تخصص (اختیاری)</label>
              @error('specialty_title')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-12 position-relative mt-5">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="is_main" wire:model="is_main">
                <label class="form-check-label fw-medium" for="is_main">
                  تخصص اصلی: <span
                    class="px-2 text-{{ $is_main ? 'success' : 'danger' }}">{{ $is_main ? 'بله' : 'خیر' }}</span>
                </label>
              </div>
              @error('is_main')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="update"
              class="btn btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
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

    .btn-primary {
      background: linear-gradient(90deg, #6b7280, #374151);
      border: none;
      color: white;
      font-weight: 600;
    }

    .btn-primary:hover {
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

    .text-shadow {
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .select2-container {
      width: 100% !important;
    }

    .select2-selection {
      border: 1px solid #e5e7eb !important;
      border-radius: 8px !important;
      height: 48px !important;
      background: #fafafa !important;
    }

    .select2-selection__rendered {
      line-height: 46px !important;
      padding-right: 15px !important;
    }

    .select2-selection__arrow {
      height: 46px !important;
    }

    .select2-dropdown {
      z-index: 1050 !important;
      border: 1px solid #e5e7eb !important;
      border-radius: 8px !important;
    }
  </style>

  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#doctor_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        $('#specialty_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        $('#academic_degree_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%',
        });

        // تنظیم مقادیر اولیه هنگام لود صفحه
        $('#doctor_id').val(@json($doctor_id)).trigger('change');
        $('#specialty_id').val(@json($specialty_id)).trigger('change');
        $('#academic_degree_id').val(@json($academic_degree_id)).trigger('change');
      }

      // اجرای اولیه Select2
      initializeSelect2();

      // همگام‌سازی با تغییرات کاربر
      $('#doctor_id').on('change', function() {
        @this.set('doctor_id', $(this).val());
      });
      $('#specialty_id').on('change', function() {
        @this.set('specialty_id', $(this).val());
      });
      $('#academic_degree_id').on('change', function() {
        @this.set('academic_degree_id', $(this).val());
      });

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      // بازسازی Select2 بعد از هر آپدیت Livewire
      document.addEventListener('livewire:updated', function() {
        initializeSelect2();
        $('#doctor_id').val(@this.doctor_id).trigger('change');
        $('#specialty_id').val(@this.specialty_id).trigger('change');
        $('#academic_degree_id').val(@this.academic_degree_id).trigger('change');
      });
    });
  </script>
</div>
