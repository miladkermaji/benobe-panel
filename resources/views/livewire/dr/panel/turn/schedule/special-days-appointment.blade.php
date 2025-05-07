<div>
  <div wire:ignore>
    <x-special-days-calendar />
  </div>

  <!-- مودال تعطیلات -->
  <x-modal name="holiday-modal" title="مدیریت تعطیلات و ساعات کاری"
    size="{{ $selectedDate && in_array($selectedDate, $holidaysData['holidays']) ? 'sm' : 'lg' }}"
    wire:key="holiday-modal-{{ $selectedDate ?? 'default' }}" wire:ignore.self>
    <x-slot:body>
      @php
        $isPastDate = $selectedDate ? \Carbon\Carbon::parse($selectedDate)->isPast() : false;
        $jalaliDate = $selectedDate
            ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($selectedDate))->format('d F Y')
            : '';
      @endphp
      @if ($selectedDate && in_array($selectedDate, $holidaysData['holidays'] ?? []))
        <div class="alert alert-warning" role="alert">
          <h4 class="alert-heading">تأیید تغییر وضعیت تعطیلات</h4>
          <p>روز {{ $jalaliDate }} تعطیل است. آیا می‌خواهید از تعطیلی خارج کنید؟</p>
          <hr>
          <div class="d-flex justify-content-center gap-2 mt-3">
            <button class="btn btn-primary w-100 h-50" wire:click="removeHoliday"
              {{ $isProcessing || $isPastDate ? 'disabled' : '' }}>
              خروج از تعطیلی
            </button>
            <button class="btn btn-secondary w-100 h-50"
              x-on:click="$dispatch('close-modal', { name: 'holiday-modal' })" {{ $isProcessing ? 'disabled' : '' }}>
              لغو
            </button>
          </div>
        </div>
      @else
        <div class="workhours-content w-100 d-flex justify-content-center mb-3">
          <div class="workhours-wrapper-content p-3">
            @if ($hasWorkHoursMessage)
              <div class="alert alert-info" role="alert">
                <p class="fw-bold text-center">شما از قبل برای این روز ساعات کاری تعریف کرده‌اید. در صورت تمایل
                  می‌توانید آن را ویرایش کنید</p>
              </div>
            @endif
            @if ($workSchedule['status'] && !empty($workSchedule['data']['work_hours']))
              <div class="border-333 p-3 mt-3 border-radius-11">
                <h6>ساعات کاری -
                  {{ $selectedDate ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($selectedDate))->format('d F Y') : '' }}
                </h6>
                <div class="mt-4">
                  @foreach ($workSchedule['data']['work_hours'] as $index => $slot)
                    <div class="form-row d-flex w-100 p-3 bg-active-slot border-radius-11"
                      data-slot-id="{{ $index }}"
                      wire:key="slot-{{ $index }}-{{ $selectedDate ?? 'default' }}">
                      <div class="d-flex justify-content-start align-items-center gap-4">
                        <div class="form-group position-relative timepicker-ui">
                          <label class="label-top-input-special-takhasos" for="start-{{ $index }}">از</label>
                          <input type="text" data-timepicker
                            class="form-control h-50 timepicker-ui-input text-center fw-bold font-size-13 start-time bg-white"
                            id="start-{{ $index }}"
                            wire:model.live.debounce.300ms="workSchedule.data.work_hours.{{ $index }}.start"
                            wire:key="start-{{ $index }}-{{ $selectedDate ?? 'default' }}" />
                        </div>
                        <div class="form-group position-relative timepicker-ui">
                          <label class="label-top-input-special-takhasos" for="end-{{ $index }}">تا</label>
                          <input type="text" data-timepicker
                            class="form-control h-50 timepicker-ui-input text-center fw-bold font-size-13 end-time bg-white"
                            id="end-{{ $index }}"
                            wire:model.live.debounce.300ms="workSchedule.data.work_hours.{{ $index }}.end"
                            wire:key="end-{{ $index }}-{{ $selectedDate ?? 'default' }}" />
                        </div>
                        <div class="form-group position-relative">
                          <label class="label-top-input-special-takhasos" for="patients-{{ $index }}">تعداد
                            نوبت</label>
                          <input type="text" class="form-control h-50 text-center max-appointments bg-white"
                            id="patients-{{ $index }}"
                            wire:model.live.debounce.300ms="workSchedule.data.work_hours.{{ $index }}.max_appointments"
                            wire:click="openCalculatorModal('{{ $workSchedule['data']['day'] }}', {{ $index }})"
                            wire:key="patients-{{ $index }}-{{ $selectedDate ?? 'default' }}"
                            data-index="{{ $index }}" readonly />
                        </div>
                        <div class="form-group position-relative">
                          <x-custom-tooltip
                            title="زمان‌های مخصوص منشی که می‌تواند برای شرایط خاص نگه دارد. این زمان‌ها غیرفعال می‌شوند تا زمانی که منشی یا پزشک آن‌ها را مجدداً فعال کند."
                            placement="top">
                            <button class="btn btn-light btn-sm emergency-slot-btn"
                              data-day="{{ $workSchedule['data']['day'] }}"
                              wire:click="openEmergencyModal('{{ $workSchedule['data']['day'] }}', {{ $index }})"
                              data-index="{{ $index }}" @if (empty($slot['start']) || empty($slot['end']) || empty($slot['max_appointments'])) disabled @endif>
                              <img src="{{ asset('dr-assets/icons/emergency.svg') }}" alt="نوبت اورژانسی">
                            </button>
                          </x-custom-tooltip>
                        </div>
                        <div class="form-group position-relative">
                          <x-custom-tooltip title="حذف برنامه کاری" placement="top">
                            <button class="btn btn-light btn-sm remove-row-btn"
                              wire:click="removeSlot({{ $index }})"
                              @if (empty($slot['start']) || empty($slot['end']) || empty($slot['max_appointments'])) disabled @endif>
                              <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                            </button>
                          </x-custom-tooltip>
                        </div>
                      </div>
                      <div class="d-flex align-items-center">
                        <x-custom-tooltip title="زمان‌بندی باز شدن نوبت‌ها" placement="top">
                          <button type="button" class="btn text-black btn-sm btn-outline-primary schedule-btn"
                            wire:click="openScheduleModal('{{ $workSchedule['data']['day'] }}', {{ $index }})"
                            data-day="{{ $workSchedule['data']['day'] }}" data-index="{{ $index }}"
                            @if (empty($slot['start']) || empty($slot['end']) || empty($slot['max_appointments'])) disabled @endif>
                            <img src="{{ asset('dr-assets/icons/open-time.svg') }}" alt="">
                          </button>
                        </x-custom-tooltip>
                      </div>
                    </div>
                  @endforeach
                </div>
                <div class="add-new-row mt-3">
                  <button class="add-row-btn btn btn-sm btn-light" data-tooltip="true" data-placement="bottom"
                    data-original-title="اضافه کردن ساعت کاری جدید" wire:click="addSlot"
                    @if ($isProcessing) disabled @endif>
                    <img src="{{ asset('dr-assets/icons/plus2.svg') }}" alt="" srcset="">
                    <span>افزودن ردیف جدید</span>
                  </button>
                </div>
              </div>
            @else
              <div class="alert alert-warning text-center">
                هیچ ساعت کاری برای این روز تعریف نشده است.
                <div class="mt-3">
                  <button class="btn btn-primary w-100 h-50" wire:click="addSlot"
                    @if ($isProcessing) disabled @endif>
                    افزودن بازه زمانی
                  </button>
                </div>
              </div>
            @endif
          </div>
        </div>
        <div class="d-flex justify-content-center gap-2 mt-3">
          <button class="btn btn-danger w-100 h-50" wire:click="addHoliday"
            {{ $isProcessing || $isPastDate ? 'disabled' : '' }}>
            تعطیل کردن
          </button>
          <button class="btn btn-secondary w-100 h-50" x-on:click="$dispatch('close-modal', { name: 'holiday-modal' })"
            {{ $isProcessing ? 'disabled' : '' }}>
            لغو
          </button>
        </div>
      @endif
    </x-slot>
  </x-modal>

  <!-- مودال جابجایی -->
  <x-modal name="transfer-modal" title="جابجایی نوبت‌ها" size="lg"
    wire:key="transfer-modal-{{ $selectedDate ?? 'default' }}" wire:ignore.self>
    <x-slot:body>
      <div class="alert alert-info" role="alert">
        <p class="fw-bold">این روز دارای نوبت است. برای تعطیل کردن باید نوبت‌ها را جابجا کنید</p>
      </div>
      <div class="d-flex justify-content-center gap-2 mt-3">
        <button class="btn btn-secondary w-100 h-50"
          x-on:click="$dispatch('close-modal', { name: 'transfer-modal' })">
          بستن
        </button>
      </div>
    </x-slot>
  </x-modal>

  <!-- مودال محاسبه‌گر -->
  <x-modal name="CalculatorModal" title="انتخاب تعداد نوبت یا زمان ویزیت" size="sm"
    wire:key="calculator-modal-{{ $selectedDate ?? 'default' }}" wire:ignore.self>
    <x-slot:body>
      <div class="d-flex align-items-center">
        <div class="d-flex flex-wrap flex-column align-items-start gap-4 w-100">
          <div class="d-flex align-items-center w-100">
            <div class="d-flex align-items-center">
              <input type="radio" id="count-radio" name="calculation-mode" class="form-check-input"
                wire:model.live="calculator.calculation_mode" value="count">
              <label class="form-check-label" for="count-radio"></label>
            </div>
            <div class="input-group position-relative mx-2">
              <label class="label-top-input-special-takhasos">تعداد نوبت‌ها</label>
              <input type="number" class="form-control text-center h-50 rounded-0 border-radius-0"
                id="appointment-count" wire:model.live.debounce.300ms="calculator.appointment_count"
                wire:focus="setCalculationMode('count')" style="height: 50px;">
              <span class="input-group-text px-2 count-span-prepand-style">نوبت</span>
            </div>
          </div>
          <div class="d-flex align-items-center mt-4 w-100">
            <div class="d-flex align-items-center">
              <input type="radio" id="time-radio" name="calculation-mode" class="form-check-input"
                wire:model.live="calculator.calculation_mode" value="time">
              <label class="form-check-label" for="time-radio"></label>
            </div>
            <div class="input-group position-relative mx-2">
              <label class="label-top-input-special-takhasos">زمان هر نوبت</label>
              <input type="number" class="form-control text-center h-50 rounded-0 border-radius-0" id="time-count"
                wire:model.live.debounce.300ms="calculator.time_per_appointment"
                wire:focus="setCalculationMode('time')" style="height: 50px;">
              <span class="input-group-text px-2">دقیقه</span>
            </div>
          </div>
        </div>
      </div>
      <div class="w-100 d-flex justify-content-end p-1 gap-4 mt-3">
        <button type="button" class="btn my-btn-primary w-100 d-flex justify-content-center align-items-center"
          wire:click="saveCalculator" id="saveSelectionCalculator" style="height: 50px;"
          @if ($isProcessing) disabled @endif>
          <span class="button_text">ذخیره تغییرات</span>
          <div class="loader" style="display: none;"></div>
        </button>
      </div>
    </x-slot>
  </x-modal>

  <!-- مودال زمان‌های اورژانسی -->
  <x-modal name="emergencyModal" title="انتخاب زمان‌های اورژانسی" size="md"
    wire:key="emergency-modal-{{ $selectedDate ?? 'default' }}" wire:ignore.self>
    <x-slot:body>
      <div class="emergency-times-container">
        <div class="d-flex flex-wrap gap-2 justify-content-center" id="emergency-times">
          @if (!empty($emergencyTimes['possible']))
            @foreach ($emergencyTimes['possible'] as $time)
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="emergency-time-{{ $time }}"
                  wire:model.live="selectedEmergencyTimes.{{ $time }}" value="{{ $time }}">
                <label class="form-check-label" for="emergency-time-{{ $time }}">{{ $time }}</label>
              </div>
            @endforeach
          @else
            <div class="alert alert-warning text-center">
              هیچ زمان اورژانسی برای این بازه زمانی در دسترس نیست.
            </div>
          @endif
        </div>
      </div>
      <div class="w-100 d-flex justify-content-end mt-3">
        <button type="button" class="btn my-btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
          wire:click="saveEmergencyTimes" @if ($isProcessing) disabled @endif>
          <span class="button_text">ذخیره تغییرات</span>
          <div class="loader" style="display: none;"></div>
        </button>
      </div>
    </x-slot>
  </x-modal>

  <!-- مودال تنظیم زمان‌بندی -->
  <x-modal name="scheduleModal" title="تنظیم زمان‌بندی" size="lg"
    wire:key="schedule-modal-{{ $selectedDate ?? 'default' }}" wire:ignore.self>
    <x-slot:body>
      <div class="position-relative">
        <div class="loading-overlay d-none" id="scheduleLoading">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">در حال بارگذاری...</span>
          </div>
          <p class="mt-2">در حال بارگذاری...</p>
        </div>
        <div class="modal-content-inner">
          <div class="schedule-days-section">
            <div class="day-schdule-wrapper">
              <div class="day-checkbox">
                <x-my-check-box id="select-all-schedule-days" is-checked="{{ $selectAllScheduleModal }}"
                  day="انتخاب همه" wire:model.live="selectAllScheduleModal" />
              </div>
              @foreach (['saturday' => 'شنبه', 'sunday' => 'یکشنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $day => $label)
                <div class="day-checkbox">
                  <x-my-check-box id="schedule-day-{{ $day }}"
                    is-checked="{{ isset($selectedScheduleDays[$day]) && $selectedScheduleDays[$day] }}"
                    day="{{ $label }}" wire:model.live="selectedScheduleDays.{{ $day }}"
                    data-day="{{ $day }}" class="schedule-day-checkbox" />
                </div>
              @endforeach
            </div>
          </div>
          <div class="timepicker-save-section">
            <div class="form-group position-relative timepicker-ui">
              <label class="label-top-input-special-takhasos">شروع</label>
              <input data-timepicker type="text" class="form-control timepicker-ui-input text-center fw-bold"
                id="schedule-start" wire:model="workSchedule.data.work_hours.{{ $scheduleModalIndex }}.start">
            </div>
            <div class="form-group position-relative timepicker-ui">
              <label class="label-top-input-special-takhasos">پایان</label>
              <input data-timepicker type="text" class="form-control timepicker-ui-input text-center fw-bold"
                id="schedule-end" wire:model="workSchedule.data.work_hours.{{ $scheduleModalIndex }}.end">
            </div>
            <button type="button" class="btn my-btn-primary d-flex justify-content-center align-items-center"
              id="saveSchedule" wire:click="saveSchedule" @if ($isProcessing) disabled @endif>
              <span class="button_text">ذخیره تغییرات</span>
              <div class="loader" style="display: none;"></div>
            </button>
          </div>
          <div class="schedule-settings-section">
            <div class="schedule-settings-list">
              @if ($scheduleModalDay && $scheduleModalIndex !== null)
                @php
                  $settings = $workSchedule['data']['appointment_settings'] ?? [];
                  $filteredSettings = array_values(
                      array_filter(
                          $settings,
                          fn($setting) => isset($setting['work_hour_key']) &&
                              (int) $setting['work_hour_key'] === (int) $scheduleModalIndex,
                      ),
                  );
                  $dayTranslations = [
                      'saturday' => 'شنبه',
                      'sunday' => 'یکشنبه',
                      'monday' => 'دوشنبه',
                      'tuesday' => 'سه‌شنبه',
                      'wednesday' => 'چهارشنبه',
                      'thursday' => 'پنج‌شنبه',
                      'friday' => 'جمعه',
                  ];
                @endphp
                @if (!empty($filteredSettings))
                  @foreach ($filteredSettings as $index => $setting)
                    <div class="schedule-setting-item"
                      wire:key="setting-{{ $scheduleModalDay }}-{{ $index }}-{{ $selectedDate ?? 'default' }}">
                      <span>
                        از {{ $setting['start_time'] }} تا {{ $setting['end_time'] }} (روزها:
                        {{ implode(', ', array_map(fn($day) => $dayTranslations[$day] ?? $day, $setting['days'] ?? [])) }})
                      </span>
                      <button class="btn btn-light delete-schedule-setting" data-day="{{ $scheduleModalDay }}"
                        data-index="{{ $index }}"
                        wire:click="deleteScheduleSetting('{{ $scheduleModalDay }}', {{ $index }})">
                        <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                      </button>
                    </div>
                  @endforeach
                @else
                  <div class="alert alert-danger text-center fw-bold">
                    هیچ تنظیم زمان‌بندی برای این بازه زمانی ذخیره نشده است.
                  </div>
                @endif
              @else
                <div class="alert alert-danger text-center fw-bold">
                  روز یا بازه زمانی انتخاب نشده است.
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </x-slot>
  </x-modal>

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

      Livewire.on('open-modal', ({
        id
      }) => {
        console.log('Dispatching open-modal for:', id);
        window.dispatchEvent(new CustomEvent('open-modal', {
          detail: {
            name: id
          }
        }));
        if (id === 'holiday-modal') {
          Livewire.dispatch('refreshWorkhours');
        }
      });

      Livewire.on('close-modal', ({
        id
      }) => {
        console.log('Dispatching close-modal for:', id);
        window.dispatchEvent(new CustomEvent('close-modal', {
          detail: {
            name: id
          }
        }));
      });

      Livewire.on('openTransferModal', ({
        modalId,
        gregorianDate
      }) => {
        if (modalId === 'transfer-modal' && gregorianDate) {
          window.dispatchEvent(new CustomEvent('open-modal', {
            detail: {
              name: modalId
            }
          }));
        }
      });

      Livewire.on('calendarDataUpdated', ({
        holidaysData,
        appointmentsData,
        calendarYear,
        calendarMonth
      }) => {
        window.holidaysData = holidaysData || {
          status: true,
          holidays: []
        };
        window.appointmentsData = appointmentsData || {
          status: true,
          data: []
        };
        try {
          initializeSpecialDaysCalendar();
        } catch (error) {
          console.error('Error reinitializing special days calendar:', error);
        }
      });

  

      Livewire.on('initialize-calculator', ({
        start_time,
        end_time,
        index,
        day
      }) => {
        initializeCalculatorModal(start_time, end_time, index, day);
      });

      try {
        initializeSpecialDaysCalendar();
      } catch (error) {
        console.error('Error initializing special days calendar:', error);
      }
    });

    function toggleButtonLoading($button, isLoading) {
      const $loader = $button.find('.loader');
      const $text = $button.find('.button_text');
      if (isLoading) {
        $loader.show();
        $text.hide();
        $button.prop('disabled', true);
      } else {
        $loader.hide();
        $text.show();
        $button.prop('disabled', false);
      }
    }

    function initializeCalculatorModal(start_time, end_time, index, day) {
      const $modal = $('#CalculatorModal');
      if (!$modal.length) {
        console.error('Calculator modal not found');
        return;
      }

      const $appointmentCountInput = $modal.find('#appointment-count');
      const $timeCountInput = $modal.find('#time-count');
      const $countRadio = $modal.find('#count-radio');
      const $timeRadio = $modal.find('#time-radio');
      const $saveButton = $modal.find('#saveSelectionCalculator');

      $appointmentCountInput.val('');
      $timeCountInput.val('');

      $countRadio.on('change', function() {
        if ($(this).is(':checked')) {
          $appointmentCountInput.focus();
          Livewire.dispatch('setCalculationMode', {
            mode: 'count'
          });
        }
      });

      $timeRadio.on('change', function() {
        if ($(this).is(':checked')) {
          $timeCountInput.focus();
          Livewire.dispatch('setCalculationMode', {
            mode: 'time'
          });
        }
      });

      $saveButton.on('click', function() {
        toggleButtonLoading($(this), true);
        const values = {
          appointment_count: parseInt($appointmentCountInput.val()) || null,
          time_per_appointment: parseInt($timeCountInput.val()) || null,
          calculation_mode: $countRadio.is(':checked') ? 'count' : 'time',
        };
        Livewire.dispatch('set-calculator-values', [values]);
        setTimeout(() => {
          toggleButtonLoading($(this), false);
        }, 1000);
      });

      $modal.on('shown.bs.modal', function() {
        const appointmentCount = @json($calculator['appointment_count'] ?? null);
        const timePerAppointment = @json($calculator['time_per_appointment'] ?? null);
        const calculationMode = @json($calculator['calculation_mode'] ?? 'count');

        $appointmentCountInput.val(appointmentCount);
        $timeCountInput.val(timePerAppointment);
        if (calculationMode === 'count') {
          $countRadio.prop('checked', true);
          $appointmentCountInput.focus();
        } else {
          $timeRadio.prop('checked', true);
          $timeCountInput.focus();
        }
      });
    }

  </script>
</div>
