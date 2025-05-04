<div>
  <div class="workhours-content w-100 d-flex justify-content-center mb-3">
    <div class="workhours-wrapper-content p-3">
      @if ($workSchedule['status'] && !empty($workSchedule['data']['work_hours']))
        <div class="border-333 p-3 mt-3 border-radius-11">
          <h6>ساعات کاری - {{ \Carbon\Carbon::parse($selectedDate)->locale('fa')->translatedFormat('l j F Y') }}</h6>
          <div class="mt-4">
            @foreach ($workSchedule['data']['work_hours'] as $index => $slot)
              <div class="form-row d-flex w-100 p-3 bg-active-slot border-radius-11" data-slot-id="{{ $index }}">
                <div class="d-flex justify-content-start align-items-center gap-4">
                  <div class="form-group position-relative timepicker-ui">
                    <label class="label-top-input-special-takhasos" for="start-{{ $index }}">از</label>
                    <input type="text"
                      class="form-control h-50 timepicker-ui-input text-center fw-bold font-size-13 start-time bg-white"
                      id="start-{{ $index }}"
                      wire:model.live="workSchedule.data.work_hours.{{ $index }}.start" />
                  </div>
                  <div class="form-group position-relative timepicker-ui">
                    <label class="label-top-input-special-takhasos" for="end-{{ $index }}">تا</label>
                    <input type="text"
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
                      wire:click="$dispatch('openXModal', { id: 'CalculatorModal' })"
                      data-day="{{ $workSchedule['data']['day'] }}" data-index="{{ $index }}" readonly />
                  </div>
                  <!-- دکمه زمان‌های اورژانسی -->
                  <div class="form-group position-relative">
                    <x-custom-tooltip
                      title="زمان‌های مخصوص منشی که می‌تواند برای شرایط خاص نگه دارد. این زمان‌ها غیرفعال می‌شوند تا زمانی که منشی یا پزشک آن‌ها را مجدداً فعال کند."
                      placement="top">
                      <button class="btn btn-light btn-sm emergency-slot-btn"
                        data-day="{{ $workSchedule['data']['day'] }}"
                        wire:click="$dispatch('openXModal', { id: 'emergencyModal' })" data-index="{{ $index }}"
                        {{ empty($slot['start']) || empty($slot['end']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                        <img src="{{ asset('dr-assets/icons/emergency.svg') }}" alt="نوبت اورژانسی">
                      </button>
                    </x-custom-tooltip>
                  </div>
                  <!-- دکمه حذف -->
                  <div class="form-group position-relative">
                    <x-custom-tooltip title="حذف برنامه کاری" placement="top">
                      <button class="btn btn-light btn-sm remove-row-btn" wire:click="removeSlot({{ $index }})"
                        {{ empty($slot['start']) || empty($slot['end']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                        <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                      </button>
                    </x-custom-tooltip>
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  <x-custom-tooltip title="زمانبندی باز شدن نوبت‌ها" placement="top">
                    <button type="button" class="btn text-black btn-sm btn-outline-primary schedule-btn"
                      wire:click="$dispatch('openXModal', { id: 'scheduleModal' })"
                      data-day="{{ $workSchedule['data']['day'] }}" data-index="{{ $index }}"
                      {{ empty($slot['start']) || empty($slot['end']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
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
            <button class="btn btn-primary w-100 h-50" wire:click="addSlot" {{ $isProcessing ? 'disabled' : '' }}>
              افزودن بازه زمانی
            </button>
          </div>
        </div>
      @endif
    </div>
  </div>

  <!-- مودال محاسبه‌گر -->
  <div wire:ignore>
    <x-custom-modal id="CalculatorModal" title="انتخاب تعداد نوبت یا زمان ویزیت" size="sm" :show="false"
      wire:key="calculator-modal-{{ $selectedDate ?? 'default' }}">
      <div class="">
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
        </div>
      </div>
    </x-custom-modal>
  </div>

  <!-- مودال زمان‌های اورژانسی -->
  <div wire:ignore>
    <x-custom-modal id="emergencyModal" title="انتخاب زمان‌های اورژانسی" size="md" :show="$isEmergencyModalOpen"
      wire:key="emergency-modal-{{ $selectedDate ?? 'default' }}">
      <div class="modal-body">
        <div class="emergency-times-container">
          <div class="d-flex flex-wrap gap-2 justify-content-center" id="emergency-times" wire:ignore>
            <!-- زمان‌ها به‌صورت داینامیک با جاوااسکریپت اضافه می‌شن -->
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

  <!-- مودال زمان‌بندی -->
  <div wire:ignore>
    <x-custom-modal id="scheduleModal" title="تنظیم زمان‌بندی" size="lg" :show="false"
      wire:key="schedule-modal-{{ $selectedDate ?? 'default' }}">
      <div class="modal-body position-relative">
        <!-- لودینگ -->
        <div class="loading-overlay d-none" id="scheduleLoading">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">در حال بارگذاری...</span>
          </div>
          <p class="mt-2">در حال بارگذاری...</p>
        </div>
        <!-- محتوای اصلی -->
        <div class="modal-content-inner">
          <div class="schedule-days-section">
            <div class="day-schdule-wrapper">
              <div class="day-checkbox">
                <x-my-check-box :is-checked="$selectAllScheduleModal" id="select-all-schedule-days" day="انتخاب همه"
                  wire:model.live="selectAllScheduleModal" />
              </div>
              @foreach (['saturday' => 'شنبه', 'sunday' => 'یکشنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $day => $label)
                <div class="day-checkbox">
                  <x-my-check-box :is-checked="isset($selectedScheduleDays[$day]) && $selectedScheduleDays[$day]" id="schedule-day-{{ $day }}" day="{{ $label }}"
                    wire:model.live="selectedScheduleDays.{{ $day }}" data-day="{{ $day }}"
                    class="schedule-day-checkbox" />
                </div>
              @endforeach
            </div>
          </div>
          <div class="timepicker-save-section">
            <div class="form-group position-relative timepicker-ui">
              <label class="label-top-input-special-takhasos">شروع</label>
              <input type="text" class="form-control timepicker-ui-input text-center fw-bold" id="schedule-start"
                value="00:00">
            </div>
            <div class="form-group position-relative timepicker-ui">
              <label class="label-top-input-special-takhasos">پایان</label>
              <input type="text" class="form-control timepicker-ui-input text-center fw-bold" id="schedule-end"
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
                        data-index="{{ $index }}">
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
      $(document).ready(function() {
        // افزودن لودینگ به دکمه‌ها
        $(document).on('click',
          '.btn:not(.copy-single-slot-btn, .delete-schedule-setting, .emergency-slot-btn, .remove-row-btn)',
          function(e) {
            const $button = $(this);
            if ($button.find('.loader').length && $button.find('.button_text').length) {
              toggleButtonLoading($button, true);
            }
          });

        // متوقف کردن لودینگ هنگام نمایش toastr
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
          const selectedDays = $('.schedule-day-checkbox:checked')
            .map(function() {
              return $(this).data('day');
            })
            .get();

          try {
            if (!selectedDays.length) {
              toastr.error('لطفاً حداقل یک روز انتخاب کنید');
              return;
            }
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

            selectedDays.forEach(day => {
              @this.set(`selectedScheduleDays.${day}`, true);
            });

            toggleButtonLoading($button, true);

            @this.call('saveSchedule', startTime, endTime).catch((error) => {
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
              @this.call('deleteScheduleSetting', day, index);
              setTimeout(() => {
                @this.dispatch('refresh-schedule-settings');
              }, 300);
            }
          });
        });
      });
    });
  </script>
</div>
