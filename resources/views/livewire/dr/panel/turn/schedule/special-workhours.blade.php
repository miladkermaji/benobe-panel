<div>
  <div class="workhours-content w-100 d-flex justify-content-center mb-3">
    <div class="workhours-wrapper-content p-3">
      @if ($workSchedule['status'] && !empty($workSchedule['data']['work_hours']))
        <div class="border-333 p-3 mt-3 border-radius-11">
          <h6>ساعات کاری -
            {{ $selectedDate ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($selectedDate))->format('d F Y') : '' }}
          </h6>
          <div class="mt-4">
            @foreach ($workSchedule['data']['work_hours'] as $index => $slot)
              <div class="form-row d-flex w-100 p-3 bg-active-slot border-radius-11" data-slot-id="{{ $index }}">
                <div class="d-flex justify-content-start align-items-center gap-4">
                  <div class="form-group position-relative timepicker-ui">
                    <label class="label-top-input-special-takhasos" for="start-{{ $index }}">از</label>
                    <input type="text" data-timepicker
                      class="form-control h-50 timepicker-ui-input text-center fw-bold font-size-13 start-time bg-white"
                      id="start-{{ $index }}"
                      wire:model.live="workSchedule.data.work_hours.{{ $index }}.start" />
                  </div>
                  <div class="form-group position-relative timepicker-ui">
                    <label class="label-top-input-special-takhasos" for="end-{{ $index }}">تا</label>
                    <input type="text" data-timepicker
                      class="form-control h-50 timepicker-ui-input text-center fw-bold font-size-13 end-time bg-white"
                      id="end-{{ $index }}"
                      wire:model.live="workSchedule.data.work_hours.{{ $index }}.end" />
                  </div>
                  <div class="form-group position-relative">
                    <label class="label-top-input-special-takhasos" for="patients-{{ $index }}">تعداد
                      نوبت</label>
                    <input type="text" class="form-control h-50 text-center max-appointments bg-white"
                      id="patients-{{ $index }}"
                      wire:model.live="workSchedule.data.work_hours.{{ $index }}.max_appointments"
                      wire:click="openCalculatorModal('{{ $workSchedule['data']['day'] }}', {{ $index }})"
                      readonly />
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
                      <button class="btn btn-light btn-sm remove-row-btn" wire:click="removeSlot({{ $index }})"
                        @if (empty($slot['start']) || empty($slot['end']) || empty($slot['max_appointments'])) disabled @endif>
                        <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                      </button>
                    </x-custom-tooltip>
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  <x-custom-tooltip title="زمانبندی باز شدن نوبت‌ها" placement="top">
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
              data-original-title="اضافه کردن ساعت کاری جدید" wire:click="addSlot">
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

  <div>
    <x-custom-modal id="CalculatorModal" title="انتخاب تعداد نوبت یا زمان ویزیت" size="sm" :show="false"
      wire:key="calculator-modal-{{ $selectedDate ?? 'default' }}">
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
                id="appointment-count" wire:model.live="calculator.appointment_count"
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
                wire:model.live="calculator.time_per_appointment" wire:focus="setCalculationMode('time')"
                style="height: 50px;">
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
      </div>
    </x-custom-modal>
  </div>

  <div>
    <x-custom-modal id="emergencyModal" title="انتخاب زمان‌های اورژانسی" size="md" :show="$isEmergencyModalOpen"
      wire:key="emergency-modal-{{ $selectedDate ?? 'default' }}">
      <div class="modal-body">
        <div class="emergency-times-container">
          <div class="d-flex flex-wrap gap-2 justify-content-center" id="emergency-times">
          </div>
        </div>
        <div class="w-100 d-flex justify-content-end mt-3">
          <button type="button"
            class="btn my-btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
            wire:click="saveEmergencyTimes">
            <span class="button_text">ذخیره تغییرات</span>
            <div class="loader"></div>
          </button>
        </div>
      </div>
    </x-custom-modal>
  </div>

  <div>
    <x-custom-modal id="scheduleModal" title="تنظیم زمان‌بندی" size="lg" :show="false"
      wire:key="schedule-modal-{{ $selectedDate ?? 'default' }}">
      <div class="modal-body position-relative">
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
              <input data-timepicker type="text" class="form-control timepicker-ui-input text-center fw-bold" id="schedule-start"
                value="00:00">
            </div>
            <div class="form-group position-relative timepicker-ui">
              <label class="label-top-input-special-takhasos">پایان</label>
              <input data-timepicker type="text" class="form-control timepicker-ui-input text-center fw-bold" id="schedule-end"
                value="23:59">
            </div>
            <button type="button" class="btn my-btn-primary d-flex justify-content-center align-items-center"
              id="saveSchedule">
              <span class="button_text">ذخیره تغییرات</span>
              <div class="loader"></div>
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
                      wire:key="setting-{{ $scheduleModalDay }}-{{ $index }}">
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
    </x-custom-modal>
  </div>

  <script>
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



    document.addEventListener('livewire:initialized', () => {



     

      Livewire.on('show-toastr', () => {
        $('.btn').each(function() {
          const $button = $(this);
          if ($button.find('.loader').length && $button.find('.button_text').length) {
            toggleButtonLoading($button, false);
          }
        });
      });

      $(document).on('click', '#saveSchedule', function() {
        const $button = $(this);
        const startTime = $('#schedule-start').val();
        const endTime = $('#schedule-end').val();

        console.log('Save schedule clicked. Start:', startTime, 'End:', endTime);

        try {
          if (!startTime) {
            toastr.error('لطفاً زمان شروع را وارد کنید');
            return;
          }
          if (!endTime) {
            toastr.error('لطفاً زمان پایان را وارد کنید');
            return;
          }

          const timeToMinutes = (time) => {
            const [hours, minutes] = time.split(':').map(Number);
            return hours * 60 + minutes;
          };

          if (timeToMinutes(endTime) <= timeToMinutes(startTime)) {
            toastr.error('زمان پایان باید بعد از زمان شروع باشد');
            return;
          }

          toggleButtonLoading($button, true);

          @this.call('saveSchedule', startTime, endTime).then(() => {
            toggleButtonLoading($button, false);
            toastr.success('تنظیمات زمان‌بندی ذخیره شد.');
          }).catch((error) => {
            toggleButtonLoading($button, false);
            console.error('Error saving schedule:', error);
            toastr.error('خطا در ذخیره زمان‌بندی: ' + (error.message || 'خطای ناشناخته'));
          });
        } catch (error) {
          toggleButtonLoading($button, false);
          console.error('Error in saveSchedule click:', error);
          toastr.error('خطا در ذخیره زمان‌بندی: ' + error.message);
        }
      });

      $(document).on('click', '.delete-schedule-setting', function() {
        const day = $(this).data('day');
        const index = $(this).data('index');

        console.log('Delete schedule setting clicked. Day:', day, 'Index:', index);

        Swal.fire({
          title: 'آیا مطمئن هستید؟',
          text: 'این تنظیم زمان‌بندی حذف خواهد شد و قابل بازگشت نیست!',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'بله، حذف کن!',
          cancelButtonText: 'خیر',
          reverseButtons: true,
        }).then((result) => {
          if (result.isConfirmed) {
            @this.call('deleteScheduleSetting', day, index).then(() => {
              @this.dispatch('refresh-schedule-settings');
              toastr.success('تنظیم زمان‌بندی حذف شد.');
            }).catch((error) => {
              console.error('Error deleting schedule setting:', error);
              toastr.error('خطا در حذف زمان‌بندی: ' + (error.message || 'خطای ناشناخته'));
            });
          }
        });
      });

      function initializeCalculatorModal() {
        console.log('Initializing CalculatorModal...');

        const $modal = $('#CalculatorModal');
        const $appointmentCount = $('#appointment-count');
        const $timeCount = $('#time-count');
        const $countRadio = $('#count-radio');
        const $timeRadio = $('#time-radio');

        const startTime = @json($this->calculator['start_time']);
        const endTime = @json($this->calculator['end_time']);

        console.log('CalculatorModal data:', {
          startTime,
          endTime,
          appointmentCount: @json($this->calculator['appointment_count']),
          timePerAppointment: @json($this->calculator['time_per_appointment']),
          calculationMode: @json($this->calculator['calculation_mode']),
        });

        // بررسی معتبر بودن مقادیر زمان
        if (!startTime || !endTime || !startTime.match(/^\d{2}:\d{2}$/) || !endTime.match(/^\d{2}:\d{2}$/)) {
          console.error('Invalid start or end time:', {
            startTime,
            endTime
          });
          toastr.error('مقادیر زمان شروع یا پایان نامعتبر هستند');
          return;
        }

        const timeToMinutes = (time) => {
          if (!time) return 0;
          const [hours, minutes] = time.split(':').map(Number);
          return hours * 60 + minutes;
        };

        const startMinutes = timeToMinutes(startTime);
        const endMinutes = timeToMinutes(endTime);
        const totalMinutes = endMinutes - startMinutes;

        console.log('Time calculations:', {
          startMinutes,
          endMinutes,
          totalMinutes,
        });

        if (totalMinutes <= 0) {
          console.error('End time is not after start time:', {
            startTime,
            endTime
          });
          toastr.error('زمان پایان باید بعد از زمان شروع باشد');
          return;
        }

        const currentCount = @json($this->calculator['appointment_count']);
        const currentTime = @json($this->calculator['time_per_appointment']);
        const calculationMode = @json($this->calculator['calculation_mode']);

        console.log('Setting initial values:', {
          currentCount,
          currentTime,
          calculationMode,
        });

        if (calculationMode === 'count') {
          $countRadio.prop('checked', true);
          $appointmentCount.prop('disabled', false);
          $timeCount.prop('disabled', true);
        } else {
          $timeRadio.prop('checked', true);
          $timeCount.prop('disabled', false);
          $appointmentCount.prop('disabled', true);
        }

        if (currentCount) {
          $appointmentCount.val(currentCount);
          $timeCount.val(currentTime || Math.round(totalMinutes / currentCount));
        } else if (currentTime) {
          $timeCount.val(currentTime);
          $appointmentCount.val(currentCount || Math.round(totalMinutes / currentTime));
        } else {
          $appointmentCount.val('');
          $timeCount.val('');
        }

        $appointmentCount.off('input').on('input', function() {
          const count = parseInt($(this).val());
          console.log('Appointment count input:', {
            count
          });

          if (count && !isNaN(count) && count > 0) {
            const timePerAppointment = Math.round(totalMinutes / count);
            console.log('Calculated time per appointment:', timePerAppointment);
            $timeCount.val(timePerAppointment);
            @this.set('calculator.appointment_count', count);
            @this.set('calculator.time_per_appointment', timePerAppointment);
          } else {
            console.log('Invalid appointment count, resetting fields');
            $timeCount.val('');
            @this.set('calculator.appointment_count', null);
            @this.set('calculator.time_per_appointment', null);
          }
        });

        $timeCount.off('input').on('input', function() {
          const time = parseInt($(this).val());
          console.log('Time per appointment input:', {
            time
          });

          if (time && !isNaN(time) && time > 0) {
            const appointmentCount = Math.round(totalMinutes / time);
            console.log('Calculated appointment count:', appointmentCount);
            $appointmentCount.val(appointmentCount);
            @this.set('calculator.time_per_appointment', time);
            @this.set('calculator.appointment_count', appointmentCount);
          } else {
            console.log('Invalid time per appointment, resetting fields');
            $appointmentCount.val('');
            @this.set('calculator.appointment_count', null);
            @this.set('calculator.time_per_appointment', null);
          }
        });

        $countRadio.off('change').on('change', function() {
          if ($(this).is(':checked')) {
            console.log('Calculation mode changed to count');
            $appointmentCount.prop('disabled', false);
            $timeCount.prop('disabled', true);
            @this.set('calculator.calculation_mode', 'count');
          }
        });

        $timeRadio.off('change').on('change', function() {
          if ($(this).is(':checked')) {
            console.log('Calculation mode changed to time');
            $timeCount.prop('disabled', false);
            $appointmentCount.prop('disabled', true);
            @this.set('calculator.calculation_mode', 'time');
          }
        });
      }

      Livewire.on('openXModal', ({
        id
      }) => {
        console.log('Opening modal:', id);
        if (id === 'CalculatorModal') {
          initializeCalculatorModal();
        }
        window.openXModal(id);
      });

      Livewire.on('closeXModal', ({
        id
      }) => {
        console.log('Closing modal:', id);
        window.closeXModal(id);
      });
    });
  </script>
</div>
