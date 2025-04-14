<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش خدمت پزشک</h5>
      </div>
      <a href="{{ route('admin.panel.doctor-services.index') }}"
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
              <select wire:model="doctor_id" class="form-select select2" id="doctor_id">
                <option value="">انتخاب کنید</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}">{{ $doctor->first_name . ' ' . $doctor->last_name }}</option>
                @endforeach
              </select>
              <label for="doctor_id" class="form-label">پزشک</label>
              @error('doctor_id')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>

            <!-- کلینیک -->
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model="clinic_id" class="form-select select2" id="clinic_id">
                <option value="">انتخاب کنید</option>
                @foreach ($clinics as $clinic)
                  <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                @endforeach
              </select>
              <label for="clinic_id" class="form-label">کلینیک (اختیاری)</label>
              @error('clinic_id')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>

            <!-- نام -->
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="name" class="form-control" id="name"
                placeholder="نام خدمت را وارد کنید" required>
              <label for="name" class="form-label">نام خدمت</label>
              @error('name')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>

            <!-- مدت زمان -->
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model="duration" class="form-control" id="duration"
                placeholder="مدت زمان خدمت">
              <label for="duration" class="form-label">مدت زمان (دقیقه)</label>
              @error('duration')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>

            <!-- قیمت -->
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model="price" class="form-control" id="price" placeholder="قیمت خدمت"
                step="0.01">
              <label for="price" class="form-label">قیمت</label>
              @error('price')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>

            <!-- تخفیف -->
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model="discount" class="form-control" id="discount" placeholder="درصد تخفیف"
                step="0.01">
              <label for="discount" class="form-label">تخفیف (درصد، اختیاری)</label>
              @error('discount')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>

            <!-- توضیحات -->
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="description" class="form-control" id="description" rows="3" placeholder="توضیحات خدمت"></textarea>
              <label for="description" class="form-label">توضیحات (اختیاری)</label>
              @error('description')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
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
              @error('status')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>

            <!-- خدمت مادر -->
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model="parent_id" class="form-select select2" id="parent_id">
                <option value="">بدون زیرگروه</option>
                @foreach ($parentServices as $service)
                  <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
              </select>
              <label for="parent_id" class="form-label">زیر گروه (اختیاری)</label>
              @error('parent_id')
                <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <!-- دکمه ویرایش -->
          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="update"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              ذخیره تغییرات
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <style>
    /* استایل‌ها بدون تغییر باقی می‌مانند */
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
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#doctor_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%',
          dropdownAutoWidth: true,
        });
        $('#clinic_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        $('#parent_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });

        // تنظیم مقادیر اولیه هنگام لود صفحه
        const doctorId = @json($this->doctor_id);
        const clinicId = @json($this->clinic_id);
        const parentId = @json($this->parent_id);

        $('#doctor_id').val(doctorId || '').trigger('change');
        $('#clinic_id').val(clinicId || '').trigger('change');
        $('#parent_id').val(parentId || '').trigger('change');
      }

      // اجرای اولیه Select2
      initializeSelect2();

      // همگام‌سازی با تغییرات کاربر
      $('#doctor_id').on('change', function() {
        const value = $(this).val() === '' || $(this).val() === null ? null : $(this).val();
        @this.set('doctor_id', value);
      });
      $('#clinic_id').on('change', function() {
        const value = $(this).val() === '' || $(this).val() === null ? null : $(this).val();
        @this.set('clinic_id', value);
      });
      $('#parent_id').on('change', function() {
        const value = $(this).val() === '' || $(this).val() === null ? null : $(this).val();
        @this.set('parent_id', value);
      });

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('redirect-after-delay', () => {
        setTimeout(() => {
          window.location.href = "{{ route('admin.panel.doctor-services.index') }}";
        }, 3000);
      });

      // رفرش Select2 بعد از هر آپدیت Livewire
      document.addEventListener('livewire:updated', function() {
        // فقط در صورتی که Select2 قبلاً initialize شده باشد
        if ($('#doctor_id').hasClass('select2-hidden-accessible')) {
          const doctorId = @json($this->doctor_id);
          const clinicId = @json($this->clinic_id);
          const parentId = @json($this->parent_id);

          $('#doctor_id').val(doctorId || '').trigger('change');
          $('#clinic_id').val(clinicId || '').trigger('change');
          $('#parent_id').val(parentId || '').trigger('change');
        }
      });
    });
  </script>
</div>
