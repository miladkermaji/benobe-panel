<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش مسدودیت</h5>
      </div>
      <a href="{{ route('admin.panel.userblockings.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <div class="card-body p-4">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
          <!-- فرم -->
          <div class="row g-4">
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model="user_id" class="form-select select2" id="user_id">
                <option value="">انتخاب کنید</option>
                @foreach ($users as $user)
                  <option value="{{ $user->id }}">{{ $user->first_name . ' ' . $user->last_name . ' (' . $user->mobile . ')' }}</option>
                @endforeach
              </select>
              <label for="user_id" class="form-label">کاربر (اختیاری)</label>
              @error('user_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model="doctor_id" class="form-select select2" id="doctor_id">
                <option value="">انتخاب کنید</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}">{{ $doctor->first_name . ' ' . $doctor->last_name . ' (' . $doctor->mobile . ')' }}</option>
                @endforeach
              </select>
              <label for="doctor_id" class="form-label">دکتر (اختیاری)</label>
              @error('doctor_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="blocked_at" class="form-control jalali-datepicker text-end" id="blocked_at" placeholder="" data-jdp required>
              <label for="blocked_at" class="form-label">تاریخ شروع مسدودیت</label>
              @error('blocked_at') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="unblocked_at" class="form-control jalali-datepicker text-end" id="unblocked_at" placeholder="" data-jdp>
              <label for="unblocked_at" class="form-label">تاریخ پایان مسدودیت (اختیاری)</label>
              @error('unblocked_at') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model="clinic_id" class="form-select select2" id="clinic_id">
                <option value="">انتخاب کنید</option>
                @foreach ($clinics as $clinic)
                  <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                @endforeach
              </select>
              <label for="clinic_id" class="form-label">کلینیک (اختیاری)</label>
              @error('clinic_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="reason" class="form-control" id="reason" rows="3" placeholder=" "></textarea>
              <label for="reason" class="form-label">دلیل مسدودیت (اختیاری)</label>
              @error('reason') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="status" wire:model="status">
                <label class="form-check-label fw-medium" for="status">
                  وضعیت: <span class="px-2 text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'مسدود' : 'آزاد' }}</span>
                </label>
              </div>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="update"
              class="btn btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
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
    .jalali-datepicker {
      z-index: 1050 !important;
    }
    @media (max-width: 767px) {
      .card-header { flex-direction: column; gap: 1rem; }
      .btn-outline-light { width: 100%; justify-content: center; }
      .col-md-6 { flex: 0 0 100%; max-width: 100%; }
    }
    @media (max-width: 575px) {
      .card-body { padding: 2rem 1.5rem; }
      .btn-primary { width: 100%; justify-content: center; }
    }
  </style>

  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#user_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        $('#doctor_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        $('#clinic_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
      }
      initializeSelect2();

      $('#user_id').on('change', function() {
        @this.set('user_id', $(this).val());
      });
      $('#doctor_id').on('change', function() {
        @this.set('doctor_id', $(this).val());
      });
      $('#clinic_id').on('change', function() {
        @this.set('clinic_id', $(this).val());
      });

      jalaliDatepicker.startWatch({
        minDate: "attr",
        maxDate: "attr",
        showTodayBtn: true,
        showEmptyBtn: true,
        time: false,
        dateFormatter: function(unix) {
          return new Date(unix).toLocaleDateString('fa-IR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
          });
        }
      });

      document.getElementById('blocked_at').addEventListener('change', function() {
        @this.set('blocked_at', this.value);
      });
      document.getElementById('unblocked_at').addEventListener('change', function() {
        @this.set('unblocked_at', this.value);
      });

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>