<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش تنظیم بیعانه</h5>
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
              <select wire:model.live="form.clinic_id" class="form-select select2" id="clinic_id">
                <option value="">بدون مطب (ویزیت آنلاین)</option>
              </select>
              <label for="clinic_id" class="form-label">مطب</label>
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
            <button wire:click="update"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                <path d="M17 21v-8H7v8M7 3v5h8" />
              </svg>
              ذخیره
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
        if ($('#clinic_id').hasClass('select2-hidden-accessible')) {
          $('#clinic_id').select2('destroy');
        }

        // Initialize Select2 for doctor
        $('#doctor_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });

        // Initialize Select2 for clinic
        $('#clinic_id').select2({
          dir: 'rtl',
          placeholder: 'بدون مطب (ویزیت آنلاین)',
          width: '100%'
        });
      }

      // Initial setup
      initializeSelect2();

      // Set initial values
      const initialDoctorId = @json($form['doctor_id']);
      const initialClinicId = @json($form['clinic_id']);
      $('#doctor_id').val(initialDoctorId || '').trigger('change');
      $('#clinic_id').val(initialClinicId || '').trigger('change');

      // Handle doctor selection change
      $('#doctor_id').on('change', function(e) {
        console.log('Doctor selected:', e.target.value);
        @this.set('form.doctor_id', e.target.value);
      });

      // Handle clinic selection change
      $('#clinic_id').on('change', function(e) {
        console.log('Clinic selected:', e.target.value);
        @this.set('form.clinic_id', e.target.value);
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
        $('#clinic_id').select2('destroy');
        $('#clinic_id').empty().select2({
          dir: 'rtl',
          placeholder: 'بدون مطب (ویزیت آنلاین)',
          width: '100%',
          data: options
        });

        // Set the selected clinic_id from the dispatched event
        const selectedClinicId = data.selectedClinicId || '';
        $('#clinic_id').val(selectedClinicId).trigger('change');
      });

      // Reinitialize Select2 after Livewire updates to prevent reverting
      document.addEventListener('livewire:update', () => {
        initializeSelect2();

        // Restore the current values after reinitialization
        const doctorId = @json($form['doctor_id']);
        const clinicId = @json($form['clinic_id']);
        $('#doctor_id').val(doctorId || '').trigger('change');
        $('#clinic_id').val(clinicId || '').trigger('change');
      });

      // Handle alerts
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>
