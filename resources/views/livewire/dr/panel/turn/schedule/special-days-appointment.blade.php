<div>
  <div wire:ignore>
    <x-special-days-calendar />
  </div>

  <!-- مودال تعطیلات -->
  <div>
    <x-custom-modal id="holiday-modal" title="مدیریت تعطیلات و ساعات کاری" size="md" :show="$showModal"
      wire:key="holiday-modal-{{ $selectedDate ?? 'default' }}">
      <div class="">
        @if ($selectedDate && in_array($selectedDate, $holidaysData['holidays'] ?? []))
          <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">تأیید تغییر وضعیت تعطیلات</h4>
            <p>این روز تعطیل است. آیا می‌خواهید آن را از تعطیلی خارج کنید؟</p>
            <hr>
            <div class="d-flex justify-content-center gap-2 mt-3">
              <button class="btn btn-primary w-100 h-50" wire:click="removeHoliday"
                {{ $isProcessing ? 'disabled' : '' }}>خروج از تعطیلی</button>
              <button class="btn btn-secondary w-100 h-50" wire:click="closeModal"
                {{ $isProcessing ? 'disabled' : '' }}>لغو</button>
            </div>
          </div>
        @else
          <div class="alert alert-info" role="alert">
            <p class="fw-bold text-center">این روز تعطیل نیست در صورت تمایل میتوانید این روز را تعطیل کنید.</p>
            <hr>
            @if ($workSchedule['status'] && !empty($workSchedule['data']['work_hours']))
              <div class="work-hours-section">
                <h5>ساعات کاری</h5>
                <div class="workhours-content-special">
                  @foreach ($workSchedule['data']['work_hours'] as $index => $slot)
                    <div class="mt-3 form-row d-flex w-100 p-3 bg-active-slot border-radius-11 align-items-center"
                      data-slot-id="{{ $index }}">
                      <div class="d-flex justify-content-start align-items-center gap-4">
                        <div class="form-group position-relative timepicker-ui">
                          <label class="label-top-input-special-takhasos">از</label>
                          <input type="text"
                            class="form-control h-50 timepicker-ui-input text-center fw-bold font-size-13 start-time bg-white"
                            value="{{ $slot['start'] }}" readonly />
                        </div>
                        <div class="form-group position-relative timepicker-ui">
                          <label class="label-top-input-special-takhasos">تا</label>
                          <input type="text"
                            class="form-control h-50 timepicker-ui-input text-center fw-bold font-size-13 end-time bg-white"
                            value="{{ $slot['end'] }}" readonly />
                        </div>
                        <div class="form-group position-relative">
                          <label class="label-top-input-special-takhasos">تعداد نوبت</label>
                          <input type="text" class="form-control h-50 text-center max-appointments bg-white"
                            value="{{ $workSchedule['data']['appointment_settings'][$index]['max_appointments'] ?? ($slot['max_appointments'] ?? 0) }}"
                            wire:click="$dispatch('openXModal', { id: 'calculator-modal', day: '{{ $workSchedule['data']['day'] }}', index: {{ $index }} })"
                            readonly />
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>

              </div>
            @else
              <div class="alert alert-warning text-center">
                هیچ ساعت کاری برای این روز تعریف نشده است.
              </div>
            @endif
            <div class="d-flex justify-content-center gap-2 mt-3">
              <button class="btn btn-danger w-100 h-50" wire:click="addHoliday"
                {{ $isProcessing ? 'disabled' : '' }}>تعطیل کردن</button>
              <button class="btn btn-secondary w-100 h-50" wire:click="closeModal"
                {{ $isProcessing ? 'disabled' : '' }}>لغو</button>
            </div>
          </div>
        @endif
      </div>
    </x-custom-modal>
  </div>
  <div wire:ignore>
    <x-custom-modal id="calculator-modal" title="انتخاب تعداد نوبت یا زمان ویزیت" size="md" :show="false"
      wire:key="calculator-modal-{{ $selectedDate ?? 'default' }}">
      <div class="p-3">
        <div class="d-flex align-items-center">
          <div class="d-flex flex-wrap flex-column align-items-start gap-4 w-100">
            <!-- حالت انتخاب تعداد نوبت -->
            <div class="d-flex align-items-center w-100">
              <div class="d-flex align-items-center">
                <input type="radio" id="count-radio" name="calculation-mode" class="form-check-input"
                  wire:model.live="calculator.calculation_mode" value="count">
                <label class="form-check-label" for="count-radio"></label>
              </div>
              <div class="input-group position-relative mx-2">
                <label class="label-top-input-special-takhasos">تعداد نوبت‌ها</label>
                <input type="number" class="form-control text-center h-50 rounded-0 border-radius-0"
                  id="appointment-count" wire:model.live="calculator.appointment_count" style="height: 50px;">
                <span class="input-group-text px-2 count-span-prepand-style">نوبت</span>
              </div>
            </div>
            <!-- حالت انتخاب زمان هر نوبت -->
            <div class="d-flex align-items-center mt-4 w-100">
              <div class="d-flex align-items-center">
                <input type="radio" id="time-radio" name="calculation-mode" class="form-check-input"
                  wire:model.live="calculator.calculation_mode" value="time">
                <label class="form-check-label" for="time-radio"></label>
              </div>
              <div class="input-group position-relative mx-2">
                <label class="label-top-input-special-takhasos">زمان هر نوبت</label>
                <input type="number" class="form-control text-center h-50 rounded-0 border-radius-0" id="time-count"
                  wire:model.live="calculator.time_per_appointment" style="height: 50px;">
                <span class="input-group-text px-2">دقیقه</span>
              </div>
            </div>
          </div>
        </div>
        <div class="w-100 d-flex justify-content-end p-1 gap-4 mt-3">
          <button type="button" class="btn my-btn-primary w-100 d-flex justify-content-center align-items-center"
            wire:click="saveCalculator" id="saveSelectionCalculator" style="height: 50px;">
            <span class="button_text">ذخیره تغییرات</span>
            <div class="loader"></div>
          </button>
          <button type="button" class="btn btn-secondary w-100 h-50"
            wire:click="$dispatch('closeXModal', { id: 'calculator-modal' })">لغو</button>
        </div>
      </div>
    </x-custom-modal>
  </div>
  <!-- مودال جابجایی -->
  <div wire:ignore>
    <x-custom-modal id="transfer-modal" title="جابجایی نوبت‌ها" size="lg" :show="false"
      wire:key="transfer-modal-{{ $selectedDate ?? 'default' }}">
      <div class="alert alert-info" role="alert">
        <p class="fw-bold">
          این روز دارای نوبت است. برای تعطیل کردن باید نوبت هارا جابجا کنید
        </p>
      </div>
      <div class="d-flex justify-content-center gap-2 mt-3">
        <button class="btn btn-secondary w-100 h-50" onclick="window.closeXModal('transfer-modal')">بستن</button>
      </div>
    </x-custom-modal>
  </div>

  <script>
    window.holidaysData = @json($holidaysData) || {
      status: true,
      holidays: []
    };
    window.appointmentsData = @json($appointmentsData) || {
      status: true,
      data: []
    };

    document.addEventListener("livewire:initialized", () => {
      console.log('Livewire initialized for SpecialDaysAppointment');

      window.holidaysData = @json($holidaysData) || {
        status: true,
        holidays: []
      };
      window.appointmentsData = @json($appointmentsData) || {
        status: true,
        data: []
      };

      const clinicId = localStorage.getItem("selectedClinicId") || "default";
      if (clinicId !== "default") {
        Livewire.dispatch("setSelectedClinicId", {
          clinicId
        });
      }

      window.addEventListener('openXModal', event => {
        console.log('openXModal event received:', event.detail);
        const modalId = event.detail.id;
        if (modalId) {
          window.openXModal(modalId);
          console.log(`Modal ${modalId} opened`);
        } else {
          console.error('Modal ID not found in openXModal event:', event.detail);
        }
      });

      window.addEventListener('closeXModal', event => {
        console.log('closeXModal event received:', event.detail);
        const modalId = event.detail.id;
        if (modalId) {
          window.closeXModal(modalId);
          console.log(`Modal ${modalId} closed`);
        } else {
          console.error('Modal ID not found in closeXModal event:', event.detail);
        }
      });

      // اضافه کردن مدیریت مودال جابجایی
      Livewire.on('openTransferModal', (event) => {
        console.log('openTransferModal event received:', event);
        const {
          modalId,
          gregorianDate
        } = event;
        if (modalId === 'transfer-modal' && gregorianDate) {
          window.openXModal(modalId);
          console.log(`Transfer modal opened for date: ${gregorianDate}`);
        }
      });

      try {
        initializeSpecialDaysCalendar();
      } catch (error) {
        console.error('Error initializing special days calendar:', error);
      }
    });
  </script>
</div>
