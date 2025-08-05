<div>
  <div wire:ignore>
    <x-special-days-calendar />
  </div>

  <!-- مودال تعطیلات -->
  <x-modal name="holiday-modal" title="مدیریت تعطیلات و ساعات کاری" persistent="true"
    size="{{ $selectedDate && in_array($selectedDate, $holidaysData['holidays']) ? 'sm' : 'md' }}"
    wire:key="holiday-modal-{{ $selectedDate ?? 'default' }}" id="holidayModal">
    <x-slot:body>
      <!-- لودینگ -->
      <div class="loading-overlay d-none">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
          <span class="sr-only">در حال بارگذاری...</span>
        </div>
        <p class="mt-2 text-primary">در حال بارگذاری...</p>
      </div>

      <!-- محتوای اصلی مودال -->
      <div class="modal-content-inner">
        @php
          $isPastDate = $selectedDate ? \Carbon\Carbon::parse($selectedDate)->isPast() : false;
          $jalaliDate = $selectedDate
              ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($selectedDate))->format('d F Y')
              : '';
        @endphp
        @if ($selectedDate && in_array($selectedDate, $holidaysData['holidays'] ?? []))
          <div class="alert alert-warning" role="alert">
            <p class="fw-bold text-center">روز {{ $jalaliDate }} تعطیل است. آیا می‌خواهید از تعطیلی خارج کنید؟</p>
          </div>
          <div class="d-flex justify-content-center gap-2 mt-3 w-100">
            <button class="btn btn-primary w-100 h-50" wire:click="removeHoliday"
              {{ $isProcessing || $isPastDate ? 'disabled' : '' }}>
              خروج از تعطیلی
            </button>
          </div>
        @else
          <div class="workhours-content w-100 d-flex justify-content-center mb-3">
            <div class="workhours-wrapper-content p-3">
              @if ($hasWorkHoursMessage)
                <div class="alert alert-info" role="alert">
                  <p class="fw-bold text-center">
                    @if ($isFromSpecialDailySchedule)
                      پزشک گرامی، شما برای این روز تنظیمات خاص دارید.
                    @else
                      شما از قبل برای این روز ساعات کاری تعریف کرده‌اید. در صورت تمایل می‌توانید آن را ویرایش کنید.
                    @endif
                  </p>
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
                                <img src="{{ asset('dr-assets/icons/emergency.svg') }}" alt="نوبت مخصوص منشی">
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
                <div class="alert alert-warning text-center fw-bold">
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
          <div class="d-flex justify-content-center gap-2 mt-3 w-100">
            <button class="btn btn-danger w-100 h-50" wire:click="addHoliday"
              {{ $isProcessing || $isPastDate ? 'disabled' : '' }}>
              تعطیل کردن
            </button>

          </div>
        @endif
      </div>
    </x-slot>
  </x-modal>

  <!-- مودال جابجایی -->
  <x-modal name="transfer-modal" title="جابجایی نوبت‌ها" size="sm"
    wire:key="transfer-modal-{{ $selectedDate ?? 'default' }}">
    <x-slot:body>
      <div class="alert alert-info" role="alert">
        <p class="fw-bold">این روز دارای نوبت است. برای تعطیل کردن باید نوبت‌ها را جابجا یا در صورت تمایل لغو کنید.
          برای انجام عملیات روی جابجایی نوبت‌ها کلیک کنید و بعد از جابجایی مجدد به همین صفحه برگردید و روز مورد نظر را
          تعطیل کنید.</p>
      </div>
      <div class="d-flex justify-content-center gap-2 mt-3 w-100">
        <a href="{{ route('dr-moshavere_waiting', [
            'selected_date' => \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($selectedDate))->format('Y-m-d'),
            'redirect_back' => route('dr-mySpecialDays-counseling'),
        ]) }}"
          class="btn btn-primary w-100 h-50 text-white">
          جابجایی نوبت‌ها
        </a>
      </div>
    </x-slot>
  </x-modal>
  <!-- مودال محاسبه‌گر -->
  <x-modal name="CalculatorModal" name="CalculatorModal" title="انتخاب تعداد نوبت یا زمان ویزیت" size="sm">
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
          <span class="button_text">ذخیره</span>
          <div class="loader" style="display: none;"></div>
        </button>
      </div>
    </x-slot>
  </x-modal>

  <!-- مودال زمان‌های مخصوص منشی -->
  <x-modal name="emergencyModal" title="انتخاب زمان‌های مخصوص منشی" size="md"
    wire:key="emergency-modal-{{ $selectedDate ?? 'default' }}">
    <x-slot:body>
      <div class="emergency-times-container">
        <div class="d-flex flex-wrap gap-2 justify-content-center" id="emergency-times">
          @if (!empty($emergencyTimes['possible']))
            @foreach ($emergencyTimes['possible'] as $time)
              <button type="button"
                class="btn btn-sm time-slot-btn {{ isset($selectedEmergencyTimes[$time]) && $selectedEmergencyTimes[$time] ? 'btn-primary' : 'btn-outline-primary' }}"
                wire:click="$set('selectedEmergencyTimes.{{ $time }}', {{ isset($selectedEmergencyTimes[$time]) && $selectedEmergencyTimes[$time] ? 'false' : 'true' }})"
                data-time="{{ $time }}">
                {{ $time }}
              </button>
            @endforeach
          @else
            <div class="alert alert-warning text-center">
              هیچ زمان مخصوص منشی برای این بازه زمانی در دسترس نیست.
            </div>
          @endif
        </div>
      </div>
      <div class="w-100 d-flex justify-content-end mt-3">
        <button type="button" class="btn my-btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
          wire:click="saveEmergencyTimes" @if ($isProcessing) disabled @endif>
          <span class="button_text">ذخیره</span>
          <div class="loader" style="display: none;"></div>
        </button>
      </div>
    </x-slot>
  </x-modal>

  <!-- مودال تنظیم زمان‌بندی -->
  <x-modal name="scheduleModal" title="تنظیم زمان‌بندی" size="md-medium"
    wire:key="schedule-modal-{{ $selectedDate ?? 'default' }}">
    <x-slot:body>
      <div class="position-relative">
        <div class="loading-overlay d-none" id="scheduleLoading">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">در حال بارگذاری...</span>
          </div>
          <p class="mt-2">در حال بارگذاری...</p>
        </div>
        <div class="modal-content-inner">
          <!-- بخش انتخاب روزها -->
          <div class="schedule-days-section border-section">
            <h6 class="section-title">انتخاب روزها</h6>
            <div class="day-schedule-grid">
              <div class="day-checkbox form-check select-all-checkbox">
                <input type="checkbox" class="form-check-input" id="select-all-schedule-days"
                  wire:model.live="selectAllScheduleModal">
                <label class="form-check-label" for="select-all-schedule-days">انتخاب همه</label>
              </div>
              @foreach (['saturday' => 'شنبه', 'sunday' => 'یکشنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $day => $label)
                <div class="day-checkbox form-check">
                  <input type="checkbox" class="form-check-input schedule-day-checkbox"
                    id="schedule-day-{{ $day }}" wire:model.live="selectedScheduleDays.{{ $day }}"
                    data-day="{{ $day }}">
                  <label class="form-check-label" for="schedule-day-{{ $day }}">{{ $label }}</label>
                </div>
              @endforeach
            </div>
          </div>
          <!-- بخش تنظیم بازه زمانی و دکمه ذخیره -->
          <div class="timepicker-save-section border-section">
            <h6 class="section-title">تنظیم بازه زمانی</h6>
            <div class="timepicker-grid">
              <div class="form-group">
                <label class="label-top-input-special-takhasos">شروع</label>
                <input data-timepicker type="text" class="form-control timepicker-ui-input text-center fw-bold"
                  id="schedule-start"
                  wire:model.live.debounce.300ms="workSchedule.data.work_hours.{{ $scheduleModalIndex }}.start">
              </div>
              <div class="form-group">
                <label class="label-top-input-special-takhasos">پایان</label>
                <input data-timepicker type="text" class="form-control timepicker-ui-input text-center fw-bold"
                  id="schedule-end"
                  wire:model.live.debounce.300ms="workSchedule.data.work_hours.{{ $scheduleModalIndex }}.end">
              </div>
              <button type="button" class="btn my-btn-primary save-schedule-btn" id="saveSchedule"
                wire:click="saveSchedule" @if ($isProcessing) disabled @endif>
                <span class="button_text">ذخیره</span>
                <div class="loader"></div>
              </button>
            </div>
          </div>
          <!-- بخش لیست تنظیمات ذخیره‌شده -->
          <div class="schedule-settings-section border-section">
            <h6 class="section-title">تنظیمات ذخیره‌شده</h6>
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
                      <span class="setting-text">
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
                  <div class="alert alert-info text-center">
                    هیچ تنظیم زمان‌بندی برای این بازه زمانی ذخیره نشده است.
                  </div>
                @endif
              @else
                <div class="alert alert-info text-center">
                  روز یا بازه زمانی انتخاب نشده است.
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </x-slot>
  </x-modal>
  <x-modal name="add-slot-modal" title="تأیید افزودن ردیف جدید" size="sm"
    wire:key="add-slot-modal-{{ $selectedDate ?? 'default' }}">
    <x-slot:body>
      <div class="alert alert-info" role="alert">
        <p class="fw-bold">آیا مایلید ساعات کاری قبلی ذخیره شوند؟</p>
        <p>در صورت انتخاب "ذخیره کن"، ساعات کاری فعلی حفظ شده و یک ردیف جدید اضافه می‌شود. در غیر این صورت، تمام
          ردیف‌های قبلی حذف خواهند شد.</p>
      </div>
      <div class="d-flex justify-content-center gap-2 mt-3">
        <button class="btn btn-primary w-100 h-50" wire:click="confirmAddSlot" wire:loading.attr="disabled">
          ذخیره کن
        </button>
        <button class="btn btn-secondary w-100 h-50" wire:click="confirmAddSlot(false)" wire:loading.attr="disabled">
          بدون ذخیره
        </button>
        <button class="btn btn-outline-secondary w-100 h-50" wire:click="closeAddSlotModal"
          wire:loading.attr="disabled">
          لغو
        </button>
      </div>
    </x-slot>
  </x-modal>
  <script>
    // تعریف متغیرهای جهانی
    window.holidaysData = @json($holidaysData) || {
      status: true,
      holidays: []
    };
    window.appointmentsData = @json($appointmentsData) || {
      status: true,
      data: []
    };

    // ادغام تمام رویدادهای Livewire در یک بلوک
    document.addEventListener("livewire:initialized", () => {
      // به‌روزرسانی تنظیمات زمان‌بندی
      Livewire.on('refresh-schedule-settings', () => {
        const settingsList = document.querySelector('.schedule-settings-list');
        if (!settingsList) return;

        // پاک کردن آیتم‌های قبلی
        settingsList.innerHTML = '';

        const settings = @json($workSchedule['data']['appointment_settings'] ?? []);
        const scheduleModalIndex = @json($scheduleModalIndex);
        const dayTranslations = {
          saturday: 'شنبه',
          sunday: 'یکشنبه',
          monday: 'دوشنبه',
          tuesday: 'سه‌شنبه',
          wednesday: 'چهارشنبه',
          thursday: 'پنج‌شنبه',
          friday: 'جمعه',
        };

        const filteredSettings = settings.filter(
          (setting) => setting.work_hour_key !== undefined && parseInt(setting.work_hour_key) === parseInt(
            scheduleModalIndex)
        );

        if (filteredSettings.length > 0) {
          filteredSettings.forEach((setting, index) => {
            const daysText = (setting.days || []).map((day) => dayTranslations[day] || day).join(', ');
            const div = document.createElement('div');
            div.className = 'schedule-setting-item';
            div.setAttribute('wire:key',
              `setting-${@json($scheduleModalDay)}-${index}-${@json($selectedDate) || 'default'}`
            );
            div.innerHTML = `
                        <span>از ${setting.start_time} تا ${setting.end_time} (روزها: ${daysText})</span>
                        <button class="btn btn-light delete-schedule-setting" data-day="${@json($scheduleModalDay)}" data-index="${index}">
                            <img src="${@json(asset('dr-assets/icons/trash.svg'))}" alt="حذف">
                        </button>
                    `;
            settingsList.appendChild(div);
          });
        } else {
          settingsList.innerHTML = `
                    <div class="alert alert-danger text-center fw-bold">
                        هیچ تنظیم زمان‌بندی برای این بازه زمانی ذخیره نشده است.
                    </div>
                `;
        }

        // اتصال رویدادهای حذف
        document.querySelectorAll('.delete-schedule-setting').forEach((button) => {
          button.removeEventListener('click', handleDeleteClick);
          button.addEventListener('click', handleDeleteClick);
        });

        function handleDeleteClick() {
          const day = this.dataset.day;
          const index = this.dataset.index;
          Swal.fire({
            title: 'آیا مطمئن هستید؟',
            text: 'این تنظیم زمان‌بندی حذف خواهد شد!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'بله، حذف کن',
            cancelButtonText: 'خیر',
            reverseButtons: true
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('deleteScheduleSetting', {
                day,
                index
              });
            }
          });
        }

        // به‌روزرسانی چک‌باکس‌ها
        document.querySelectorAll('.schedule-day-checkbox').forEach((checkbox) => {
          const day = checkbox.dataset.day;
          checkbox.checked = @json($selectedScheduleDays)[day] || false;
        });

        // به‌روزرسانی select all
        document.querySelector('#select-all-schedule-days').checked = @json($selectAllScheduleModal) || false;
      });
      document.querySelector('#saveSchedule')?.addEventListener('click', function() {
        toggleButtonLoading(this, true);
        Livewire.dispatch('saveSchedule');
        setTimeout(() => {
          toggleButtonLoading(this, false);
        }, 1000);
      });
      // مدیریت انتخاب همه
      const selectAllCheckbox = document.querySelector('#select-all-schedule-days');
      if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
          const isChecked = this.checked;
          document.querySelectorAll('.schedule-day-checkbox').forEach((checkbox) => {
            checkbox.checked = isChecked;
            const day = checkbox.dataset.day;
            Livewire.dispatch('set', {
              key: `selectedScheduleDays.${day}`,
              value: isChecked
            });
          });
          Livewire.dispatch('set', {
            key: 'selectAllScheduleModal',
            value: isChecked
          });
        });
      }

      // مدیریت تغییر چک‌باکس‌های روزها
      document.querySelectorAll('.schedule-day-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
          const allChecked = document.querySelectorAll('.schedule-day-checkbox:checked').length === document
            .querySelectorAll('.schedule-day-checkbox').length;
          document.querySelector('#select-all-schedule-days').checked = allChecked;
          Livewire.dispatch('set', {
            key: 'selectAllScheduleModal',
            value: allChecked
          });
          const day = checkbox.dataset.day;
          Livewire.dispatch('set', {
            key: `selectedScheduleDays.${day}`,
            value: checkbox.checked
          });
        });
      });

      // مدیریت لودینگ مودال تعطیلات
      Livewire.on('toggle-loading', ({
        isLoading
      }) => {
        setTimeout(() => {
          const $modal = document.querySelector('#holidayModal');
          if ($modal) {
            const $loadingOverlay = $modal.querySelector('.loading-overlay');
            const $modalContent = $modal.querySelector('.modal-content-inner');
            if (isLoading) {
              $loadingOverlay.classList.remove('d-none');
              $loadingOverlay.classList.add('d-flex');
              $modalContent.classList.remove('d-flex');
              $modalContent.classList.add('d-none');
            } else {
              $loadingOverlay.classList.remove('d-flex');
              $loadingOverlay.classList.add('d-none');
              $modalContent.classList.remove('d-none');
              $modalContent.classList.add('d-flex');
            }
          } else {
            console.warn('Holiday modal not found');
          }
        }, 100);
      });

      // مدیریت باز شدن مودال‌ها
      Livewire.on('open-modal', ({
        id
      }) => {
        window.dispatchEvent(new CustomEvent('open-modal', {
          detail: {
            name: id
          }
        }));
        if (id === 'holiday-modal') {
          setTimeout(() => {
            const $modal = document.querySelector('#holidayModal');
            if ($modal) {
              $modal.querySelector('.loading-overlay').classList.remove('d-none');
              $modal.querySelector('.loading-overlay').classList.add('d-flex');
              $modal.querySelector('.modal-content-inner').classList.remove('d-flex');
              $modal.querySelector('.modal-content-inner').classList.add('d-none');
            } else {
              console.warn('Holiday modal not found');
            }
          }, 100);
          Livewire.dispatch('refreshWorkhours');
        }
        if (id === 'emergencyModal') {
          setTimeout(() => {
            document.querySelectorAll('.time-slot-btn').forEach((button) => {
              button.removeEventListener('click', handleTimeSlotClick);
              button.addEventListener('click', handleTimeSlotClick);
            });

            function handleTimeSlotClick() {
              const time = this.dataset.time;
              const isSelected = this.classList.contains('btn-primary');
              if (isSelected) {
                this.classList.remove('btn-primary');
                this.classList.add('btn-outline-primary');
              } else {
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
              }
              Livewire.dispatch('set', {
                key: `selectedEmergencyTimes.${time}`,
                value: !isSelected
              });
            }
          }, 100);
        }
      });

      // مدیریت بستن مودال‌ها
      Livewire.on('close-modal', ({
        id
      }) => {
        window.dispatchEvent(new CustomEvent('close-modal', {
          detail: {
            name: id
          }
        }));
        if (id === 'holiday-modal') {
          const $modal = document.querySelector('#holidayModal');
          if ($modal) {
            $modal.querySelector('.loading-overlay').classList.remove('d-flex');
            $modal.querySelector('.loading-overlay').classList.add('d-none');
            $modal.querySelector('.modal-content-inner').classList.remove('d-none');
            $modal.querySelector('.modal-content-inner').classList.add('d-flex');
          }
        }
      });

      // تأیید افزودن ردیف جدید
      Livewire.on('confirm-add-slot', () => {
        Swal.fire({
          title: 'آیا مایلید ساعات کاری قبلی ذخیره شوند؟',
          text: 'در صورت تأیید، ساعات کاری فعلی ذخیره شده و یک ردیف جدید اضافه می‌شود.',
          icon: 'question',
          showCancelButton: true,
          showDenyButton: true,
          confirmButtonText: 'بله، ذخیره کن',
          cancelButtonText: 'بدون ذخیره',
          denyButtonText: 'بستن',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('confirmAddSlot', {
              savePrevious: true
            });
          } else if (result.dismiss === Swal.DismissReason.cancel) {
            Livewire.dispatch('confirmAddSlot', {
              savePrevious: false
            });
          }
        }).catch((error) => {
          console.error('SweetAlert error:', error);
        });
      });

      // تأیید حذف بازه زمانی
      Livewire.on('confirm-delete-slot', (event) => {
        const index = event.index;
        Swal.fire({
          title: 'آیا مطمئن هستید؟',
          text: 'این بازه زمانی حذف خواهد شد!',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('confirmDeleteSlot', {
              index: index
            }); // نام رویداد دقیقاً همونه
          } else {}
        }).catch((error) => {
          console.error('SweetAlert error:', error);
        });
      });

      // تأیید حذف تنظیم زمان‌بندی
      Livewire.on('confirm-delete-schedule-setting', ({
        day,
        index
      }) => {
        Swal.fire({
          title: 'آیا مطمئن هستید؟',
          text: 'این تنظیم زمان‌بندی حذف خواهد شد!',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteScheduleSetting', {
              day,
              index
            });
          }
        });
      });

      // به‌روزرسانی UI محاسبه‌گر
      Livewire.on('update-calculator-ui', (data) => {
        const $modal = document.querySelector('#CalculatorModal');
        if (!$modal) {
          console.error('Calculator modal not found during UI update');
          return;
        }

        const $appointmentCountInput = $modal.querySelector('#appointment-count');
        const $timeCountInput = $modal.querySelector('#time-count');
        const $countRadio = $modal.querySelector('#count-radio');
        const $timeRadio = $modal.querySelector('#time-radio');

        $appointmentCountInput.value = data.appointment_count || '';
        $timeCountInput.value = data.time_per_appointment || '';
        if (data.calculation_mode === 'count') {
          $countRadio.checked = true;
        } else {
          $timeRadio.checked = true;
        }
      });

      // به‌روزرسانی تعداد نوبت‌ها
      Livewire.on('update-appointment-count', (data) => {
        const $input = document.querySelector(`#patients-${data.index}`);
        if ($input) {
          $input.value = data.count;
        } else {
          console.warn(`Input #patients-${data.index} not found`);
        }
      });

      // تنظیم اولیه clinicId
      const clinicId = localStorage.getItem("selectedClinicId") || "default";
      if (clinicId !== "default") {
        Livewire.dispatch("setSelectedClinicId", {
          clinicId
        });
      }

      // به‌روزرسانی داده‌های تقویم
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

      // مقداردهی اولیه محاسبه‌گر
      Livewire.on('initialize-calculator', ({
        start_time,
        end_time,
        index,
        day
      }) => {
        initializeCalculatorModal(start_time, end_time, index, day);
      });

      // مقداردهی اولیه تقویم
      try {
        initializeSpecialDaysCalendar();
      } catch (error) {
        console.error('Error initializing special days calendar:', error);
      }
    });

    // تابع برای مدیریت لودینگ دکمه‌ها
    function toggleButtonLoading(button, isLoading) {
      const loader = button.querySelector('.loader');
      const text = button.querySelector('.button_text');
      if (isLoading) {
        loader.style.display = 'block';
        text.style.display = 'none';
        button.disabled = true;
      } else {
        loader.style.display = 'none';
        text.style.display = 'block';
        button.disabled = false;
      }
    }

    // تابع مقداردهی اولیه مودال محاسبه‌گر
    function initializeCalculatorModal(start_time, end_time, index, day) {
      const maxAttempts = 5;
      let attempts = 0;

      function tryInitialize() {
        const $modal = document.querySelector('#CalculatorModal');
        if (!$modal) {
          attempts++;
          if (attempts < maxAttempts) {
            setTimeout(tryInitialize, 100);
            return;
          }
          return;
        }

        const $appointmentCountInput = $modal.querySelector('#appointment-count');
        const $timeCountInput = $modal.querySelector('#time-count');
        const $countRadio = $modal.querySelector('#count-radio');
        const $timeRadio = $modal.querySelector('#time-radio');
        const $saveButton = $modal.querySelector('#saveSelectionCalculator');

        // تنظیم مقادیر اولیه
        $appointmentCountInput.value = '';
        $timeCountInput.value = '';

        // محاسبه زمان کل
        const start = moment(start_time, 'HH:mm');
        const end = moment(end_time, 'HH:mm');
        const totalMinutes = end.diff(start, 'minutes');

        // فوکوس خودکار روی اینپوت تعداد نوبت‌ها
        $modal.addEventListener('shown.bs.modal', function() {
          const appointmentCount = @json($calculator['appointment_count'] ?? null);
          const timePerAppointment = @json($calculator['time_per_appointment'] ?? 0);
          const calculationMode = @json($calculator['calculation_mode'] ?? 'count');

          $appointmentCountInput.value = appointmentCount;
          $timeCountInput.value = timePerAppointment;

          if (calculationMode === 'count') {
            $countRadio.checked = true;
            $appointmentCountInput.focus();
          } else {
            $timeRadio.checked = true;
            $timeCountInput.focus();
          }
        }, {
          once: true
        });

        // تغییر حالت با فوکوس روی اینپوت‌ها
        $appointmentCountInput.addEventListener('focus', () => {
          $countRadio.checked = true;
          Livewire.dispatch('setCalculationMode', {
            mode: 'count'
          });
        });

        $timeCountInput.addEventListener('focus', () => {
          $timeRadio.checked = true;
          Livewire.dispatch('setCalculationMode', {
            mode: 'time'
          });
        });

        // محاسبه زنده هنگام تغییر اینپوت‌ها
        $appointmentCountInput.addEventListener('input', function() {
          const count = parseInt(this.value);
          if (count > 0) {
            const timePerAppointment = Math.floor(totalMinutes / count);
            $timeCountInput.value = timePerAppointment || '';
            Livewire.dispatch('set-calculator-values', [{
              appointment_count: count,
              time_per_appointment: timePerAppointment,
              calculation_mode: 'count'
            }]);
          } else {
            $timeCountInput.value = '';
          }
        });

        $timeCountInput.addEventListener('input', function() {
          const time = parseInt(this.value);
          if (time > 0) {
            const appointmentCount = Math.floor(totalMinutes / time);
            $appointmentCountInput.value = appointmentCount || '';
            Livewire.dispatch('set-calculator-values', [{
              appointment_count: appointmentCount,
              time_per_appointment: time,
              calculation_mode: 'time'
            }]);
          } else {
            $appointmentCountInput.value = '';
          }
        });

        // ذخیره و بستن مودال
        $saveButton.addEventListener('click', function() {
          toggleButtonLoading(this, true);
          const values = {
            appointment_count: parseInt($appointmentCountInput.value) || null,
            time_per_appointment: parseInt($timeCountInput.value) || null,
            calculation_mode: $countRadio.checked ? 'count' : 'time',
          };
          Livewire.dispatch('set-calculator-values', [values]);
          setTimeout(() => {
            toggleButtonLoading(this, false);
            const patientInput = document.querySelector(`#patients-${index}`);
            if (patientInput) patientInput.value = values.appointment_count;
            Livewire.dispatch('close-modal', {
              id: 'CalculatorModal'
            });
          }, 1000);
        });
      }

      tryInitialize();
    }
  </script>

</div>
