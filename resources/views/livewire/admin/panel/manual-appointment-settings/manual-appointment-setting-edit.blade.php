<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between  gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش تنظیمات نوبت دستی</h5>
      </div>
      <a href="{{ route('admin.panel.manual-appointment-settings.index') }}"
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
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.live="doctor_id" class="form-select select2" id="doctor_id">
                <option value="">انتخاب کنید</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}" {{ $doctor->id == $doctor_id ? 'selected' : '' }}>
                    {{ $doctor->first_name . ' ' . $doctor->last_name }}
                    ({{ $doctor->specialty->name ?? 'نامشخص' }})
                  </option>
                @endforeach
              </select>
              <label for="doctor_id" class="form-label">پزشک</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.live="medical_center_id" class="form-select select2" id="medical_center_id">
                <option value="">انتخاب کنید</option>
                @foreach ($clinics as $clinic)
                  <option value="{{ is_array($clinic) ? $clinic['id'] : $clinic->id }}">
                    {{ is_array($clinic) ? $clinic['name'] : $clinic->name }}
                  </option>
                @endforeach
              </select>
              <label for="medical_center_id" class="form-label">کلینیک (اختیاری)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="is_active" class="form-select" id="is_active">
                <option value="1">فعال</option>
                <option value="0">غیرفعال</option>
              </select>
              <label for="is_active" class="form-label">تأیید دو مرحله‌ای</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model="duration_send_link" class="form-control" id="duration_send_link"
                min="1" required>
              <label for="duration_send_link" class="form-label">زمان ارسال لینک (ساعت)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model="duration_confirm_link" class="form-control" id="duration_confirm_link"
                min="1" required>
              <label for="duration_confirm_link" class="form-label">مدت اعتبار لینک (ساعت)</label>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="update"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
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

      $('#doctor_id').select2({
        dir: 'rtl',
        placeholder: 'انتخاب کنید',
        width: '100%'
      }).val('{{ $doctor_id }}').trigger('change');
      $('#medical_center_id').select2({
        dir: 'rtl',
        placeholder: 'انتخاب کنید',
        width: '100%'
      }).val('{{ $medical_center_id }}').trigger('change');

      $('#doctor_id').on('change', function() {
        @this.set('doctor_id', $(this).val());
      });
      $('#medical_center_id').on('change', function() {
        @this.set('medical_center_id', $(this).val());
      });
    });
  </script>
</div>
