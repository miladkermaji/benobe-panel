<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش تعطیلات پزشک</h5>
      </div>
      <a href="{{ route('admin.panel.doctor-holidays.index') }}"
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
                  <option value="{{ $doctor->id }}" {{ $doctor_id == $doctor->id ? 'selected' : '' }}>
                    {{ $doctor->full_name }}
                  </option>
                @endforeach
              </select>
              <label for="doctor_id" class="form-label">پزشک</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model="clinic_id" class="form-select select2" id="clinic_id">
                <option value="">انتخاب کنید</option>
                @foreach ($clinics as $clinic)
                  <option value="{{ $clinic->id }}" {{ $clinic_id == $clinic->id ? 'selected' : '' }}>
                    {{ $clinic->name }}
                  </option>
                @endforeach
              </select>
              <label for="clinic_id" class="form-label">کلینیک (اختیاری)</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <input type="text" class="form-control jalali-datepicker text-end" id="holiday_dates"
                wire:model="holiday_dates.0" placeholder="تاریخ را انتخاب کنید" data-jdp data-jdp-single-date="true">
              <label for="holiday_dates" class="form-label">تاریخ تعطیل</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="status" wire:model.live="status"
                  {{ $status === 'active' ? 'checked' : '' }}>
                <label class="form-check-label fw-medium" for="status">
                  وضعیت: <span
                    class="px-2 text-{{ $status === 'active' ? 'success' : 'danger' }}">{{ $status === 'active' ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="update"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center justify-content-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path
                  d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
              </svg>
              به‌روزرسانی 
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>



  <script>
    document.addEventListener('livewire:init', function() {
      // تنظیم Select2
      function initializeSelect2() {
        const doctorId = "{{ $doctor_id ?? '' }}";
        const clinicId = "{{ $clinic_id ?? '' }}";

        $('#doctor_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        if (doctorId) $('#doctor_id').val(doctorId).trigger('change');

        $('#clinic_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        if (clinicId) $('#clinic_id').val(clinicId).trigger('change');
      }
      initializeSelect2();

      // رویداد تغییر سلکت‌ها
      $('#doctor_id').on('change', function() {
        @this.set('doctor_id', $(this).val());
      });
      $('#clinic_id').on('change', function() {
        @this.set('clinic_id', $(this).val());
      });

      // تنظیم jalaliDatepicker
      if (typeof jalaliDatepicker !== 'undefined') {
        const holidayDatesInput = document.getElementById('holiday_dates');
        if (holidayDatesInput) {
          jalaliDatepicker.startWatch({
            minDate: "attr",
            maxDate: "attr",
            showTodayBtn: false,
            showEmptyBtn: false,
            time: false,
            autoClose: true
          });

          // تنظیم مقدار اولیه از Livewire
          const initialDate = "{{ $holiday_dates[0] ?? '' }}";
          if (initialDate) {
            holidayDatesInput.value = initialDate;
          }

          // به‌روزرسانی با تغییرات Livewire
          Livewire.on('updateHolidayDates', (dates) => {
            if (dates && dates[0]) {
              holidayDatesInput.value = dates[0];
            }
          });
        }
      }
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>
