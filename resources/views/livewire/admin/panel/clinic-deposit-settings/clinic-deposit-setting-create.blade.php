<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between  gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن تنظیم بیعانه جدید</h5>
      </div>
      <a href="{{ route('admin.panel.clinic-deposit-settings.index') }}"
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
              <select wire:model.live="form.doctor_id" class="form-select select2" id="doctor_id">
                <option value="">انتخاب کنید</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}">{{ $doctor->first_name . ' ' . $doctor->last_name }}</option>
                @endforeach
              </select>
              <label for="doctor_id" class="form-label">پزشک</label>
            </div>
            <div class="col-12 position-relative mt-5" wire:ignore>
              <select wire:model.live="form.medical_center_id" class="form-select select2" id="medical_center_id">
                <option value="">بدون مطب (ویزیت آنلاین)</option>
              </select>
              <label for="medical_center_id" class="form-label">مطب</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <input type="number" wire:model.live="form.deposit_amount" class="form-control" id="deposit_amount"
                placeholder=" " {{ $form['no_deposit'] ? 'disabled' : '' }}>
              <label for="deposit_amount" class="form-label">مبلغ بیعانه (تومان)</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="no_deposit" wire:model.live="form.no_deposit">
                <label class="form-check-label fw-medium" for="no_deposit">بدون بیعانه</label>
              </div>
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="form.notes" class="form-control" id="notes" rows="3" placeholder=" "></textarea>
              <label for="notes" class="form-label">یادداشت (اختیاری)</label>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="store"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              بیعانه
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>



  <script>
    document.addEventListener('livewire:initialized', () => {
      // Function to initialize or reinitialize Select2
      function initializeSelect2() {
        // Destroy existing Select2 instances if they exist
        if ($('#doctor_id').hasClass('select2-hidden-accessible')) {
          $('#doctor_id').select2('destroy');
        }
        if ($('#medical_center_id').hasClass('select2-hidden-accessible')) {
          $('#medical_center_id').select2('destroy');
        }

        // Initialize Select2 for doctor
        $('#doctor_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });

        // Initialize Select2 for clinic
        $('#medical_center_id').select2({
          dir: 'rtl',
          placeholder: 'بدون مطب (ویزیت آنلاین)',
          width: '100%'
        });
      }

      // Initial setup
      initializeSelect2();

      // Handle doctor selection change
      $('#doctor_id').on('change', function(e) {
        console.log('Doctor selected:', e.target.value);
        @this.set('form.doctor_id', e.target.value);
      });

      // Handle clinic selection change
      $('#medical_center_id').on('change', function(e) {
        console.log('Clinic selected:', e.target.value);
        @this.set('form.medical_center_id', e.target.value);
      });

      // Listen for clinics update event
      Livewire.on('clinics-updated', (data) => {
        console.log('Received clinics data:', data);

        // Prepare options including the default "no clinic" option
        const options = [{
            id: '',
            text: 'بدون مطب (ویزیت آنلاین)'
          },
          ...(data.clinics || [])
        ];

        // Destroy and reinitialize clinic Select2 with new data
        $('#medical_center_id').select2('destroy');
        $('#medical_center_id').empty().select2({
          dir: 'rtl',
          placeholder: 'بدون مطب (ویزیت آنلاین)',
          width: '100%',
          data: options
        });

        // Reset the selected value
        $('#medical_center_id').val('').trigger('change');
      });

      // Reinitialize Select2 after Livewire updates to prevent reverting
      document.addEventListener('livewire:update', () => {
        initializeSelect2();

        // Restore the current values after reinitialization
        const doctorId = @json($form['doctor_id']);
        const clinicId = @json($form['medical_center_id']);
        $('#doctor_id').val(doctorId || '').trigger('change');
        $('#medical_center_id').val(clinicId || '').trigger('change');
      });

      // Handle alerts
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>
