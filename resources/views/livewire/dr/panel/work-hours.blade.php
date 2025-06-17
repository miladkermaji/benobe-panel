<div>
  <div>


    <div class="w-100 d-flex justify-content-center mt-3" dir="ltr">
      <div class="auto-scheule-content-top">
        <x-my-toggle-appointment :isChecked="$autoScheduling" id="appointment-toggle"
          day="{{ $autoScheduling ? 'نوبت‌دهی آنلاین + دستی' : 'نوبت‌دهی دستی' }}" class="mt-3 custom-toggle"
          wire:model.live="autoScheduling" wire:change="updateAutoScheduling" />
      </div>
    </div>

    <div class="workhours-content w-100 d-flex justify-content-center mt-4 mb-3">
      <div class="workhours-wrapper-content p-3 pt-4">
        <div>
          <div>
            <div>
              <!-- بخش تعداد روزهای باز تقویم و باز بودن مطب در تعطیلات -->
              <div x-data="{ auto: @entangle('autoScheduling') }"
                class="row border border-radius-11 p-3 align-items-center conditional-section" x-show="auto"
                style="display: none;">
                <!-- تعداد روزهای باز تقویم -->
                <div class="col-8">
                  <div class="input-group position-relative p-1 rounded bg-white">
                    <label class="floating-label bg-white px-2 fw-bold"
                      style="position: absolute; top: -10px; right: -4px; font-size: 0.85rem; color: var(--text-secondary); z-index: 10; transition: all 0.2s ease;">
                      تعداد روز‌های باز تقویم
                    </label>
                    <input type="text" inputmode="numeric" pattern="[0-9]*"
                      class="form-control text-center calendar-days-input" name="calendar_days"
                      placeholder="تعداد روز مورد نظر خود را وارد کنید" wire:model.live.debounce.500ms="calendarDays"
                      wire:change="autoSaveCalendarDays" style="height: 50px; z-index: 1;">
                    <span class="input-group-text" style="height: 50px; z-index: 1;">روز</span>
                  </div>
                </div>
                <!-- باز بودن مطب در تعطیلات رسمی -->
                <div class="col-4">
                  <div class="p-1 rounded bg-white"
                    style="height: 50px; display: flex; align-items: center; justify-content: center; min-width: 200px;">
                    <x-my-toggle-yes-no :isChecked="$holidayAvailability" id="holiday-availability" model="holidayAvailability"
                      day="باز بودن مطب در تعطیلات رسمی" />
                  </div>
                </div>
              </div>

              <!-- بخش روزهای کاری و تنظیمات ساعت کاری (همیشه نمایش داده می‌شود) -->
              <div class="mt-4">
                <label class="text-dark fw-bold">روزهای کاری</label>
                <div
                  class="d-flex justify-content-start mt-3 gap-40 bg-light p-3 border-radius-11 day-contents align-items-center h-55"
                  id="day-contents-outside">
                  @foreach (['saturday' => 'شنبه', 'sunday' => 'یکشنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $englishDay => $persianDay)
                    <div class="d-flex align-items-center">
                      <input type="checkbox" class="form-check-input me-2" id="{{ $englishDay }}"
                        wire:model.live="isWorking.{{ $englishDay }}">
                      <label class="mb-0 fw-bold px-0" for="{{ $englishDay }}">{{ $persianDay }}</label>
                    </div>
                  @endforeach
                </div>
                <div id="work-hours" class="mt-4">
                  @foreach (['saturday' => 'شنبه', 'sunday' => 'یکشنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $englishDay => $persianDay)
                    <div
                      class="work-hours-{{ $englishDay }} {{ $isWorking[$englishDay] ? '' : 'd-none' }} position-relative">
                      <div class="border-333 p-3 mt-3 border-radius-11">
                        <h6>{{ $persianDay }}</h6>
                        <div id="morning-{{ $englishDay }}-details" class="mt-4">
                          @if (!empty($slots[$englishDay]))
                            @foreach ($slots[$englishDay] as $index => $slot)
                              <div class="mt-3 form-row d-flex w-100 pt-4 bg-active-slot border-radius-11"
                                data-slot-id="{{ $slot['id'] ?? '' }}">
                                <div class="d-flex justify-content-start align-items-center gap-4">
                                  <div class="form-group position-relative timepicker-ui">
                                    <label class="label-top-input-special-takhasos"
                                      for="morning-start-{{ $englishDay }}-{{ $index }}">از</label>
                                    <input type="text"
                                      class="form-control h-50 timepicker-ui-input text-center fw-bold font-size-13 start-time bg-white"
                                      data-timepicker id="morning-start-{{ $englishDay }}-{{ $index }}"
                                      wire:model.live="slots.{{ $englishDay }}.{{ $index }}.start_time"
                                      wire:change="autoSaveTimeSlot('{{ $englishDay }}', {{ $index }})"
                                      value="{{ $slot['start_time'] ?? '' }}" />
                                  </div>
                                  <div class="form-group position-relative timepicker-ui">
                                    <label class="label-top-input-special-takhasos"
                                      for="morning-end-{{ $englishDay }}-{{ $index }}">تا</label>
                                    <input type="text"
                                      class="form-control h-50 timepicker-ui-input text-center fw-bold font-size-13 end-time bg-white"
                                      data-timepicker id="morning-end-{{ $englishDay }}-{{ $index }}"
                                      wire:model.live="slots.{{ $englishDay }}.{{ $index }}.end_time"
                                      wire:change="autoSaveTimeSlot('{{ $englishDay }}', {{ $index }})"
                                      value="{{ $slot['end_time'] ?? '' }}" />
                                  </div>
                                  <div class="form-group position-relative">
                                    <label class="label-top-input-special-takhasos"
                                      for="morning-patients-{{ $englishDay }}-{{ $index }}">تعداد
                                      نوبت</label>
                                    <input type="text"
                                      class="form-control h-50 text-center max-appointments bg-white"
                                      id="morning-patients-{{ $englishDay }}-{{ $index }}"
                                      wire:model.live="slots.{{ $englishDay }}.{{ $index }}.max_appointments"
                                      wire:change="autoSaveTimeSlot('{{ $englishDay }}', {{ $index }})"
                                      x-data="{ day: '{{ $englishDay }}', index: '{{ $index }}' }"
                                      @click="$dispatch('open-modal', { name: 'calculator-modal', day: day, index: index })"
                                      readonly value="{{ $slot['max_appointments'] ?? '' }}" />
                                  </div>
                                  <!-- دکمه باز شدن نوبت‌ها -->
                                  <div class="form-group position-relative">
                                    <x-custom-tooltip title="زمانبندی باز شدن نوبت‌ها" placement="top">
                                      <button type="button" class="btn text-black btn-sm schedule-btn"
                                        x-data="{ day: '{{ $englishDay }}', index: '{{ $index }}' }"
                                        @click="$dispatch('open-modal', { name: 'schedule-modal', day: day, index: index })"
                                        {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                        <img src="{{ asset('dr-assets/icons/open-time.svg') }}" alt="">
                                      </button>
                                    </x-custom-tooltip>
                                  </div>
                                  <!-- دکمه نوبت‌های اورژانسی -->
                                  <div class="form-group position-relative">
                                    <x-custom-tooltip
                                      title="زمان‌های مخصوص منشی که می‌تواند برای شرایط خاص نگهدارد. این زمان‌ها غیرفعال می‌شوند تا زمانی که منشی یا پزشک آن‌ها را مجدداً فعال کند."
                                      placement="top">
                                      <button class="btn btn-light btn-sm emergency-slot-btn" x-data="{ day: '{{ $englishDay }}', index: '{{ $index }}' }"
                                        @click="$dispatch('open-modal', { name: 'emergency-modal', day: day, index: index })"
                                        {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                        <img src="{{ asset('dr-assets/icons/emergency.svg') }}" alt="نوبت اورژانسی">
                                      </button>
                                    </x-custom-tooltip>
                                  </div>
                                  <!-- دکمه کپی -->
                                  <div class="form-group position-relative">
                                    <x-custom-tooltip title="کپی ساعات کاری" placement="top">
                                      <button class="btn btn-light btn-sm copy-single-slot-btn"
                                        x-data="{ day: '{{ $englishDay }}', index: '{{ $index }}' }"
                                        @click="$dispatch('open-modal', { name: 'checkbox-modal', day: day, index: index })"
                                        {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                        <img src="{{ asset('dr-assets/icons/copy.svg') }}" alt="کپی">
                                      </button>
                                    </x-custom-tooltip>
                                  </div>
                                  <!-- دکمه حذف -->
                                  <div class="form-group position-relative">
                                    <x-custom-tooltip title="حذف برنامه کاری" placement="top">
                                      <button class="btn btn-light btn-sm remove-row-btn"
                                        {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}
                                        x-data="{ day: '{{ $englishDay }}', index: '{{ $index }}' }"
                                        @click="Swal.fire({
                                    title: 'آیا مطمئن هستید؟',
                                    text: 'این اسلات حذف خواهد شد و قابل بازگشت نیست!',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'بله، حذف کن!',
                                    cancelButtonText: 'خیر',
                                    reverseButtons: true
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        @this.call('removeSlot', day, index);
                                    }
                                })">
                                        <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                                      </button>
                                    </x-custom-tooltip>
                                  </div>
                                </div>
                              </div>
                            @endforeach
                          @else
                            <div class="mt-3 form-row d-flex w-100 pt-4 bg-active-slot border-radius-11"
                              data-slot-id="">
                              <div class="d-flex justify-content-start align-items-center gap-4">
                                <div class="form-group position-relative timepicker-ui">
                                  <label class="label-top-input-special-takhasos"
                                    for="morning-start-{{ $englishDay }}-0">از</label>
                                  <input type="text"
                                    class="form-control h-50 timepicker-ui-input text-center fw-bold font-size-13 start-time bg-white"
                                    data-timepicker id="morning-start-{{ $englishDay }}-0"
                                    wire:model.live.debounce.500ms="slots.{{ $englishDay }}.0.start_time"
                                    wire:change="autoSaveTimeSlot('{{ $englishDay }}', 0)" />
                                </div>
                                <div class="form-group position-relative timepicker-ui">
                                  <label class="label-top-input-special-takhasos"
                                    for="morning-end-{{ $englishDay }}-0">تا</label>
                                  <input type="text"
                                    class="form-control h-50 timepicker-ui-input text-center fw-bold font-size-13 end-time bg-white"
                                    data-timepicker id="morning-end-{{ $englishDay }}-0"
                                    wire:model.live.debounce.500ms="slots.{{ $englishDay }}.0.end_time"
                                    wire:change="autoSaveTimeSlot('{{ $englishDay }}', 0)" />
                                </div>
                                <div class="form-group position-relative">
                                  <label class="label-top-input-special-takhasos"
                                    for="morning-patients-{{ $englishDay }}-0">تعداد نوبت</label>
                                  <input type="text"
                                    class="form-control h-50 text-center max-appointments bg-white"
                                    id="morning-patients-{{ $englishDay }}-0"
                                    wire:model.live.debounce.500ms="slots.{{ $englishDay }}.0.max_appointments"
                                    wire:change="autoSaveTimeSlot('{{ $englishDay }}', 0)" x-data="{ day: '{{ $englishDay }}', index: '0' }"
                                    @click="$dispatch('open-modal', { name: 'calculator-modal', day: day, index: index })"
                                    readonly />
                                </div>
                                <!-- دکمه‌های غیرفعال برای ردیف خالی -->
                                <div class="form-group position-relative">
                                  <x-custom-tooltip title="زمانبندی باز شدن نوبت‌ها" placement="top">
                                    <button type="button" class="btn text-black btn-sm schedule-btn" disabled>
                                      <img src="{{ asset('dr-assets/icons/open-time.svg') }}" alt="">
                                    </button>
                                  </x-custom-tooltip>
                                </div>
                                <div class="form-group position-relative">
                                  <x-custom-tooltip
                                    title="زمان‌های مخصوص منشی که می‌تواند برای شرایط خاص نگهدارد. این زمان‌ها غیرفعال می‌شوند تا زمانی که منشی یا پزشک آن‌ها را مجدداً فعال کند."
                                    placement="top">
                                    <button class="btn btn-light btn-sm emergency-slot-btn" disabled>
                                      <img src="{{ asset('dr-assets/icons/emergency.svg') }}" alt="نوبت اورژانسی">
                                    </button>
                                  </x-custom-tooltip>
                                </div>
                                <div class="form-group position-relative">
                                  <x-custom-tooltip title="کپی ساعات کاری" placement="top">
                                    <button class="btn btn-light btn-sm copy-single-slot-btn" disabled>
                                      <img src="{{ asset('dr-assets/icons/copy.svg') }}" alt="کپی">
                                    </button>
                                  </x-custom-tooltip>
                                </div>
                                <div class="form-group position-relative">
                                  <x-custom-tooltip title="حذف برنامه کاری" placement="top">
                                    <button class="btn btn-light btn-sm remove-row-btn" disabled>
                                      <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                                    </button>
                                  </x-custom-tooltip>
                                </div>
                              </div>
                            </div>
                          @endif
                          <div class="add-new-row mt-3">
                            <button class="add-row-btn btn btn-sm btn-light" data-tooltip="true"
                              data-placement="bottom" data-original-title="اضافه کردن ساعت کاری جدید"
                              wire:click="addSlot('{{ $englishDay }}')">
                              <img src="{{ asset('dr-assets/icons/plus2.svg') }}" alt="" srcset="">
                              <span>افزودن ردیف جدید</span>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>

          <!-- دکمه ذخیره تغییرات (همیشه نمایش داده می‌شود) -->
          @if ($showSaveButton)
            <div class="d-flex w-100 justify-content-end mt-3">
              <button type="button"
                class="btn my-btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
                id="save-work-schedule" wire:click="saveWorkSchedule">
                <span class="button_text">ذخیره تغییرات</span>
                <div class="loader"></div>
              </button>
            </div>
          @elseif (Request::is('dr/panel/doctors-clinic/activation/workhours/*'))
            <div class="d-flex w-100 justify-content-end mt-3">
              <button type="button" id="startAppointmentBtn" data-tooltip="true" data-placement="right"
                data-original-title="پایان ثبت ساعات کاری و شروع نوبت‌دهی! حالا می‌توانید از امکانات سایت استفاده کنید."
                class="btn my-btn-primary h-50 col-12 d-flex justify-content-center align-items-center">
                <span class="button_text"> پایان فعالسازی کلینیک</span>
              </button>
            </div>
          @endif




          <hr>

          @if (isset($_GET['activation-path']) && $_GET['activation-path'] == true)
            <div class="w-100 mt-3">
              <button class="btn btn-success w-100 h-50" tabindex="0" type="button" id=":rs:"
                data-bs-toggle="modal" data-bs-target="#activation-modal">
                <span class="button_text">پایان فعالسازی</span>
                <div class="loader"></div>
              </button>
            </div>
            <div class="modal fade" id="activation-modal" tabindex="-1" role="dialog"
              aria-labelledby="activation-modal-label" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-radius-6">
                  <div class="modal-header border-radius-6">
                    <h5 class="modal-title" id="activation-modal-label">فعالسازی نوبت‌دهی</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <div>
                      <p>اطلاعات شما ثبت شد و ویزیت آنلاین شما تا ساعاتی دیگر فعال می‌شود. بیماران می‌توانند مستقیماً از
                        طریق پروفایل شما ویزیت آنلاین رزرو کنند.</p>
                      <p>به دلیل محدودیت ظرفیت فعلی، نمایه شما در ابتدا در لیست پزشکان موجود برای ویزیت آنلاین در رتبه
                        پایین‌تری قرار می‌گیرد.</p>
                      <p>برای هر گونه سوال یا توضیح بیشتر، لطفاً با ما <a style="color: var(--primary)"
                          href="https://emr-benobe.ir/about">ارتباط</a> بگیرید. تیم ما
                        اینجاست تا از شما در هر مرحله حمایت کند.</p>
                    </div>
                  </div>
                  <div class="p-3">
                    <a href="{{ route('dr-panel', ['showModal' => 'true']) }}"
                      class="btn my-btn-primary w-100 h-50 d-flex align-items-center text-white justify-content-center">شروع
                      نوبت‌دهی</a>
                  </div>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- مودال برای انتخاب زمان‌های اورژانسی -->
    <div wire:ignore>
      <x-modal name="emergency-modal" title="انتخاب زمان‌های اورژانسی" size="medium">
        <x-slot:body>
          <div class="emergency-times-container">
            <div class="d-flex flex-wrap gap-2 justify-content-center" id="emergency-times">
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
        </x-slot:body>
      </x-modal>
    </div>

    <!-- مودال تنظیم زمان‌بندی -->
    <div>
      <x-modal id="scheduleModal" name="schedule-modal" :title="'برنامه باز شدن نوبت‌های ' .
          ([
              'saturday' => 'شنبه',
              'sunday' => 'یکشنبه',
              'monday' => 'دوشنبه',
              'tuesday' => 'سه‌شنبه',
              'wednesday' => 'چهارشنبه',
              'thursday' => 'پنج‌شنبه',
              'friday' => 'جمعه',
          ][$scheduleModalDay] ??
              'نامشخص')" size="md-medium">
        <x-slot:body>
          <div class="position-relative">
            <!-- لودینگ -->
            <div class="loading-overlay d-none" id="scheduleLoading">
              <div class="spinner-border text-primary" role="status">
                <span class="sr-only">در حال بارگذاری...</span>
              </div>
              <p class="mt-2">در حال بارگذاری...</p>
            </div>
            <!-- محتوای اصلی -->
            <div class="modal-content-inner">
              <!-- بخش انتخاب روزها -->
              <div class="schedule-days-section border-section mb-2">
                <h6 class="section-title mb-2">انتخاب روزها</h6>
                <div class="d-flex justify-content-start gap-3 bg-light p-2 border-radius-11 align-items-center"
                  style="overflow-x: scroll;overflow-y: hidden">
                  @foreach (['saturday' => 'شنبه', 'sunday' => 'یکشنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $day => $label)
                    <div class="d-flex align-items-center">
                      <input type="checkbox" class="form-check-input me-1" id="schedule-day-{{ $day }}"
                        wire:model.live="selectedScheduleDays.{{ $day }}" data-day="{{ $day }}">
                      <label class="mb-0 fw-bold px-0 font-size-12"
                        for="schedule-day-{{ $day }}">{{ $label }}</label>
                    </div>
                  @endforeach
                </div>
              </div>
              <!-- بخش تنظیمات زمان‌بندی برای هر روز -->
              <div class="schedule-settings-section border-section">
                <h6 class="section-title mb-2"> باز شدن نوبت ها</h6>
                @foreach (['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day)
                  @if ($selectedScheduleDays[$day])
                    <div class="work-hours-{{ $day }} mb-2 border-333 p-2 border-radius-11">
                      <h6 class="mb-1 font-size-13">
                        {{ ['saturday' => 'شنبه', 'sunday' => 'یکشنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'][$day] . ' ' . 'ها' }}
                      </h6>
                      @php
                        $schedule = collect($this->workSchedules)->firstWhere('day', $day);
                        $settings =
                            $schedule && isset($schedule['appointment_settings'])
                                ? (is_array($schedule['appointment_settings'])
                                    ? $schedule['appointment_settings']
                                    : json_decode($schedule['appointment_settings'], true) ?? [])
                                : [];
                        $filteredSettings = array_values(
                            array_filter(
                                $settings,
                                fn($setting) => isset($setting['work_hour_key']) &&
                                    (int) $setting['work_hour_key'] === (int) $this->scheduleModalIndex,
                            ),
                        );
                        // ترکیب تنظیمات ذخیره‌شده و تنظیمات جدید
                        $combinedSettings = !empty($this->scheduleSettings[$day])
                            ? $this->scheduleSettings[$day]
                            : $filteredSettings;
                      @endphp
                      @if (!empty($combinedSettings))
                        @foreach ($combinedSettings as $index => $setting)
                          <div class="form-row d-flex align-items-center  mb-1 bg-active-slot border-radius-11 p-3"
                            wire:key="setting-{{ $day }}-{{ $index }}-{{ $scheduleModalIndex }}">
                            <div class="form-group position-relative timepicker-ui ">
                              <label class="label-top-input-special-takhasos font-size-11">از</label>
                              <input type="text"
                                class="form-control h-40 timepicker-ui-input text-center fw-bold font-size-12 start-time bg-white"
                                data-timepicker
                                wire:model.live="scheduleSettings.{{ $day }}.{{ $index }}.start_time"
                                wire:change="autoSaveSchedule('{{ $day }}', {{ $index }})"
                                value="{{ $setting['start_time'] ?? '' }}">
                            </div>
                            <div class="form-group position-relative timepicker-ui ">
                              <label class="label-top-input-special-takhasos font-size-11">تا</label>
                              <input type="text"
                                class="form-control h-40 timepicker-ui-input text-center fw-bold font-size-12 end-time bg-white"
                                data-timepicker
                                wire:model.live="scheduleSettings.{{ $day }}.{{ $index }}.end_time"
                                wire:change="autoSaveSchedule('{{ $day }}', {{ $index }})"
                                value="{{ $setting['end_time'] ?? '' }}">
                            </div>
                            <!-- دکمه‌های کپی و حذف -->
                            <div class="form-group position-relative">
                              <x-custom-tooltip title="کپی تنظیمات" placement="top">
                                <button class="btn btn-outline-primary btn-sm copy-schedule-setting p-1"
                                  x-data="{ day: '{{ $day }}', index: '{{ $index }}' }"
                                  @click="$dispatch('open-modal', { name: 'copy-schedule-modal', day: day, index: index })"
                                  {{ empty($setting['start_time']) || empty($setting['end_time']) ? 'disabled' : '' }}>
                                  <img src="{{ asset('dr-assets/icons/copy.svg') }}" alt="کپی"
                                    style="width: 14px; height: 14px;">
                                </button>
                              </x-custom-tooltip>
                            </div>
                            <div class="form-group position-relative">
                              <x-custom-tooltip title="حذف تنظیمات" placement="top">
                                <button class="btn btn-outline-danger btn-sm delete-schedule-setting p-1"
                                  x-data="{ day: '{{ $day }}', index: '{{ $index }}' }"
                                  @click="Swal.fire({title: 'آیا مطمئن هستید؟', text: 'این تنظیم حذف خواهد شد و قابل بازگشت نیست!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'بله، حذف کن!', cancelButtonText: 'خیر', reverseButtons: true}).then((result) => {if (result.isConfirmed) {@this.call('deleteScheduleSetting', day, index);}})"
                                  {{ empty($setting['start_time']) || empty($setting['end_time']) ? 'disabled' : '' }}>
                                  <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف"
                                    style="width: 14px; height: 14px;">
                                </button>
                              </x-custom-tooltip>
                            </div>
                          </div>
                        @endforeach
                      @else
                        <div
                          class="form-row d-flex w-100 align-items-center gap-2 mb-1 bg-active-slot border-radius-11 p-2">
                          <div class="form-group position-relative timepicker-ui">
                            <label class="label-top-input-special-takhasos font-size-11">از</label>
                            <input type="text"
                              class="form-control h-40 timepicker-ui-input text-center fw-bold font-size-12 start-time bg-white"
                              data-timepicker
                              wire:model.live.debounce.500ms="scheduleSettings.{{ $day }}.0.start_time"
                              wire:change="autoSaveSchedule('{{ $day }}', 0)">
                          </div>
                          <div class="form-group position-relative timepicker-ui">
                            <label class="label-top-input-special-takhasos font-size-11">تا</label>
                            <input type="text"
                              class="form-control h-40 timepicker-ui-input text-center fw-bold font-size-12 end-time bg-white"
                              data-timepicker
                              wire:model.live.debounce.500ms="scheduleSettings.{{ $day }}.0.end_time"
                              wire:change="autoSaveSchedule('{{ $day }}', 0)">
                          </div>
                          <!-- دکمه‌های غیرفعال برای ردیف خالی -->
                          <div class="form-group position-relative">
                            <x-custom-tooltip title="کپی تنظیمات" placement="top">
                              <button class="btn btn-outline-primary btn-sm copy-schedule-setting p-1" disabled>
                                <img src="{{ asset('dr-assets/icons/copy.svg') }}" alt="کپی"
                                  style="width: 14px; height: 14px;">
                              </button>
                            </x-custom-tooltip>
                          </div>
                          <div class="form-group position-relative">
                            <x-custom-tooltip title="حذف تنظیمات" placement="top">
                              <button class="btn btn-outline-danger btn-sm delete-schedule-setting p-1" disabled>
                                <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف"
                                  style="width: 14px; height: 14px;">
                              </button>
                            </x-custom-tooltip>
                          </div>
                        </div>
                      @endif
                      <div class="add-new-row mt-1">
                        <button class="add-row-btn btn btn-sm btn-light p-1 font-size-12" data-tooltip="true"
                          data-placement="bottom" data-original-title="اضافه کردن تنظیم جدید"
                          wire:click="addScheduleSetting('{{ $day }}')">
                          <img src="{{ asset('dr-assets/icons/plus2.svg') }}" alt=""
                            style="width: 14px; height: 14px;">
                          <span>افزودن</span>
                        </button>
                      </div>
                    </div>
                  @endif
                @endforeach
              </div>
            </div>
          </div>
        </x-slot:body>
      </x-modal>

      <!-- مودال کپی تنظیمات زمان‌بندی -->
      <x-modal name="copy-schedule-modal" title="کپی تنظیم زمان‌بندی" size="sm">
        <x-slot:body>
          <p class="font-size-12 mb-2">روزهایی که می‌خواهید تنظیمات به آن‌ها کپی شود:</p>
          <div class="mb-2">
            <input type="checkbox" class="form-check-input me-1" id="select-all-copy-schedule-days"
              wire:model.live="selectAllCopyScheduleModal">
            <label class="fw-bold mb-0 font-size-12" for="select-all-copy-schedule-days">انتخاب همه</label>
          </div>
          <div class="d-flex flex-column gap-1" id="copy-schedule-day-checkboxes">
            @foreach (['saturday' => 'شنبه', 'sunday' => 'یک‌شنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $day => $label)
              @if ($day !== $copySourceDay)
                <div class="form-check d-flex align-items-center" data-day="{{ $day }}">
                  <input type="checkbox" class="form-check-input me-1" id="copy-schedule-day-{{ $day }}"
                    wire:model.live="selectedCopyScheduleDays.{{ $day }}" data-day="{{ $day }}">
                  <label class="fw-bold mb-0 font-size-12"
                    for="copy-schedule-day-{{ $day }}">{{ $label }}</label>
                </div>
              @endif
            @endforeach
          </div>
          <div class="mt-2">
            <button type="button" class="btn my-btn-primary h-40 w-100 font-size-12"
              wire:click="copyScheduleSetting">
              ذخیره
            </button>
          </div>
        </x-slot:body>
      </x-modal>
    </div>
    <!-- مودال ماشین‌حساب -->
    <div>
      <x-modal name="calculator-modal" title="انتخاب تعداد نوبت یا زمان ویزیت" size="sm">
        <x-slot:body>
          <div class="d-flex align-items-center">
            <div class="d-flex flex-wrap flex-column align-items-start gap-4 w-100">
              <!-- حالت انتخاب تعداد نوبت -->
              <div class="d-flex align-items-center w-100">
                <div class="d-flex align-items-center">
                  <input type="radio" id="count-radio" name="calculation-mode" class="form-check-input"
                    wire:model.live="calculationMode" value="count">
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
                    wire:model.live="calculationMode" value="time">
                  <label class="form-check-label" for="time-radio"></label>
                </div>
                <div class="input-group position-relative mx-2">
                  <label class="label-top-input-special-takhasos">زمان هر نوبت</label>
                  <input type="number" class="form-control text-center h-50 rounded-0 border-radius-0"
                    id="time-count" wire:model.live="calculator.time_per_appointment" style="height: 50px;">
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
        </x-slot:body>
      </x-modal>
    </div>

    <!-- مودال کپی برنامه کاری -->
    <div>
      <x-modal name="checkbox-modal" title="کپی برنامه کاری" size="sm">
        <x-slot:body>
          <p>روزهایی که می‌خواهید برنامه کاری به آن‌ها کپی شود را انتخاب کنید:</p>
          <div class="mb-3">
            <input type="checkbox" class="form-check-input me-2" id="select-all-days"
              wire:model.live="selectAllCopyModal">
            <label class="fw-bold mb-0" for="select-all-days">انتخاب همه</label>
          </div>
          <div class="d-flex flex-column gap-2" id="day-checkboxes">
            @foreach (['saturday' => 'شنبه', 'sunday' => 'یک‌شنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $day => $label)
              @if ($day !== $sourceDay)
                <div class="form-check d-flex align-items-center" data-day="{{ $day }}">
                  <input type="checkbox" class="form-check-input me-2" id="day-{{ $day }}"
                    wire:model.live="selectedDays.{{ $day }}" data-day="{{ $day }}">
                  <label class="fw-bold mb-0" for="day-{{ $day }}">{{ $label }}</label>
                </div>
              @endif
            @endforeach
          </div>
          <div class="mt-3">
            <button type="button" class="btn my-btn-primary h-50 w-100" wire:click="copySchedule">ذخیره</button>
          </div>
        </x-slot:body>
      </x-modal>
    </div>

    <script>
      document.addEventListener('livewire:initialized', () => {
        window.addEventListener('open-modal', event => {

          const modalName = event.detail.name;
          const day = event.detail.day;
          const index = event.detail.index;

          if (modalName === 'calculator-modal') {
            try {
              @this.set('calculator.day', day);
              @this.set('calculator.index', index);
              const startTime = $(`#morning-start-${day}-${index}`).val();
              const endTime = $(`#morning-end-${day}-${index}`).val();
              const maxAppointments = $(`#morning-patients-${day}-${index}`).val();

              if (!startTime || !endTime) {
                @this.set('modalMessage', 'لطفاً ابتدا زمان شروع و پایان را وارد کنید');
                @this.set('modalType', 'error');
                @this.set('modalOpen', true);
                return;
              }

              @this.set('calculator.start_time', startTime);
              @this.set('calculator.end_time', endTime);

              const $appointmentCount = $('#appointment-count');
              const $timeCount = $('#time-count');
              const $countRadio = $('#count-radio');
              const $timeRadio = $('#time-radio');

              const timeToMinutes = (time) => {
                const [hours, minutes] = time.split(':').map(Number);
                return hours * 60 + minutes;
              };

              const totalMinutes = timeToMinutes(endTime) - timeToMinutes(startTime);
              if (totalMinutes <= 0) {
                @this.set('modalMessage', 'زمان پایان باید بعد از زمان شروع باشد');
                @this.set('modalType', 'error');
                @this.set('modalOpen', true);
                return;
              }

              // بازیابی مقادیر قبلی از slots
              const currentCount = maxAppointments ? parseInt(maxAppointments) : @this.get(
                'calculator.appointment_count');
              const currentMode = @this.get('calculationMode');

              if (currentCount && currentMode === 'count') {
                $appointmentCount.val(currentCount);
                const timePerAppointment = Math.round(totalMinutes / currentCount);
                $timeCount.val(timePerAppointment);
                @this.set('calculator.appointment_count', currentCount);
                @this.set('calculator.time_per_appointment', timePerAppointment);
                $countRadio.prop('checked', true);
                $appointmentCount.prop('disabled', false);
                $timeCount.prop('disabled', true);
              } else if (currentMode === 'time' && @this.get('calculator.time_per_appointment')) {
                const timePerAppointment = @this.get('calculator.time_per_appointment');
                $timeCount.val(timePerAppointment);
                const appointmentCount = Math.round(totalMinutes / timePerAppointment);
                $appointmentCount.val(appointmentCount);
                @this.set('calculator.time_per_appointment', timePerAppointment);
                @this.set('calculator.appointment_count', appointmentCount);
                $timeRadio.prop('checked', true);
                $timeCount.prop('disabled', false);
                $appointmentCount.prop('disabled', true);
              } else {
                $appointmentCount.val('');
                $timeCount.val('');
                @this.set('calculator.appointment_count', null);
                @this.set('calculator.time_per_appointment', null);
                $countRadio.prop('checked', true);
                $appointmentCount.prop('disabled', false);
                $timeCount.prop('disabled', true);
              }

              $appointmentCount.on('focus', function() {
                $countRadio.prop('checked', true).trigger('change');
                $timeRadio.prop('checked', false);
                $appointmentCount.prop('disabled', false);
                $timeCount.prop('disabled', true);
                @this.set('calculationMode', 'count');
              });

              $timeCount.on('focus', function() {
                $timeRadio.prop('checked', true).trigger('change');
                $countRadio.prop('checked', false);
                $timeCount.prop('disabled', false);
                $appointmentCount.prop('disabled', true);
                @this.set('calculationMode', 'time');
              });

              $appointmentCount.on('input', function() {
                const count = parseInt($(this).val());
                if (count && !isNaN(count) && count > 0) {
                  const timePerAppointment = Math.round(totalMinutes / count);
                  $timeCount.val(timePerAppointment);
                  @this.set('calculator.appointment_count', count);
                  @this.set('calculator.time_per_appointment', timePerAppointment);
                } else {
                  $timeCount.val('');
                  @this.set('calculator.appointment_count', null);
                  @this.set('calculator.time_per_appointment', null);
                }
              });

              $timeCount.on('input', function() {
                const time = parseInt($(this).val());
                if (time && !isNaN(time) && time > 0) {
                  const appointmentCount = Math.round(totalMinutes / time);
                  $appointmentCount.val(appointmentCount);
                  @this.set('calculator.time_per_appointment', time);
                  @this.set('calculator.appointment_count', appointmentCount);
                } else {
                  $appointmentCount.val('');
                  @this.set('calculator.appointment_count', null);
                  @this.set('calculator.time_per_appointment', null);
                }
              });

              $countRadio.on('change', function() {
                if ($(this).is(':checked')) {
                  $appointmentCount.prop('disabled', false);
                  $timeCount.prop('disabled', true);
                  @this.set('calculationMode', 'count');
                }
              });

              $timeRadio.on('change', function() {
                if ($(this).is(':checked')) {
                  $timeCount.prop('disabled', false);
                  $appointmentCount.prop('disabled', true);
                  @this.set('calculationMode', 'time');
                }
              });

            } catch (error) {
              console.error('Error in CalculatorModal:', error);
              window.dispatchEvent(new CustomEvent('close-modal', {
                detail: {
                  name: 'calculator-modal'
                }
              }));
            }
          }

          if (modalName === 'checkbox-modal') {
            try {
              @this.set('copySource.day', day);
              @this.set('copySource.index', index);
              @this.set('selectedDays', []);
              @this.set('selectAllCopyModal', false);
              setTimeout(() => {
                const selector = `#day-checkboxes .form-check[data-day="${day}"]`;
                const $element = $(selector);
                if ($element.length > 0) {
                  $element.hide();
                }
              }, 100);
            } catch (error) {
              console.error('Error setting copySource:', error);
              window.dispatchEvent(new CustomEvent('close-modal', {
                detail: {
                  name: 'checkbox-modal'
                }
              }));
            }
          }

          if (modalName === 'emergency-modal') {
            try {
              @this.set('isEmergencyModalOpen', true);
              @this.set('emergencyModalDay', day);
              @this.set('emergencyModalIndex', index);
              const $startTimeInput = $(`#morning-start-${day}-${index}`);
              const $endTimeInput = $(`#morning-end-${day}-${index}`);
              const $maxAppointmentsInput = $(`#morning-patients-${day}-${index}`);
              if (!$startTimeInput.length || !$endTimeInput.length || !$maxAppointmentsInput.length) {
                @this.set('modalMessage', 'خطا: ورودی‌های زمان یا تعداد نوبت یافت نشدند');
                @this.set('modalType', 'error');
                @this.set('modalOpen', true);
                window.dispatchEvent(new CustomEvent('close-modal', {
                  detail: {
                    name: 'emergency-modal'
                  }
                }));
                return;
              }
              const startTime = $startTimeInput.val();
              const endTime = $endTimeInput.val();
              const maxAppointments = $maxAppointmentsInput.val();
              if (!startTime || !endTime || !maxAppointments) {
                @this.set('modalMessage', 'لطفاً ابتدا زمان شروع، پایان و تعداد نوبت را وارد کنید');
                @this.set('modalType', 'error');
                @this.set('modalOpen', true);
                window.dispatchEvent(new CustomEvent('close-modal', {
                  detail: {
                    name: 'emergency-modal'
                  }
                }));
                return;
              }
              const timeToMinutes = (time) => {
                const [hours, minutes] = time.split(':').map(Number);
                return hours * 60 + minutes;
              };
              const minutesToTime = (minutes) => {
                const hours = Math.floor(minutes / 60).toString().padStart(2, '0');
                const mins = (minutes % 60).toString().padStart(2, '0');
                return `${hours}:${mins}`;
              };
              const totalMinutes = timeToMinutes(endTime) - timeToMinutes(startTime);
              const slotDuration = Math.floor(totalMinutes / maxAppointments);
              const times = [];
              for (let i = 0; i < maxAppointments; i++) {
                const start = timeToMinutes(startTime) + (i * slotDuration);
                times.push(minutesToTime(start));
              }
              let currentEmergencyTimes = [];
              try {
                const workSchedule = @this.workSchedules.find(s => s.day === day);
                currentEmergencyTimes = workSchedule && workSchedule.emergency_times ? workSchedule
                  .emergency_times : [];
              } catch (error) {
                console.error('Error accessing emergency_times:', error);
                currentEmergencyTimes = [];
              }
              @this.set('emergencyTimes', currentEmergencyTimes);
              const $timesContainer = $('#emergency-times');
              $timesContainer.empty();
              times.forEach(time => {
                const isSaved = currentEmergencyTimes.includes(time);
                const $button = $(`
                <button type="button" class="btn btn-sm time-slot-btn ${isSaved ? 'btn-primary' : 'btn-outline-primary'}" data-time="${time}">
                  ${time}
                </button>
              `);
                $timesContainer.append($button);
              });
              $timesContainer.show();
              $timesContainer.off('click', '.time-slot-btn').on('click', '.time-slot-btn', function() {
                const $btn = $(this);
                const time = $btn.data('time');
                const isSelected = $btn.hasClass('btn-primary');
                if (isSelected) {
                  $btn.removeClass('btn-primary').addClass('btn-outline-primary');
                  @this.emergencyTimes = @this.emergencyTimes.filter(t => t !== time);
                } else {
                  $btn.removeClass('btn-outline-primary').addClass('btn-primary');
                  @this.emergencyTimes = [...@this.emergencyTimes, time];
                }
              });
              setTimeout(() => {}, 100);
            } catch (error) {
              console.error('Error in emergencyModal:', error);
              window.dispatchEvent(new CustomEvent('close-modal', {
                detail: {
                  name: 'emergency-modal'
                }
              }));
            }
          }

          if (modalName === 'schedule-modal') {
            try {
              @this.call('openScheduleModal', day, index);
              $('#scheduleLoading').removeClass('d-none');
              $('.modal-content-inner').hide();

              setTimeout(() => {
                $('#scheduleLoading').addClass('d-none');
                $('.modal-content-inner').show();
              }, 300);
            } catch (error) {
              console.error('Error in scheduleModal:', error);
              toastr.error('خطا در بارگذاری مودال: ' + error.message);
              window.dispatchEvent(new CustomEvent('close-modal', {
                detail: {
                  name: 'schedule-modal'
                }
              }));
            }
          }

          if (modalName === 'copy-schedule-modal') {
            try {
              @this.set('copySourceDay', day);
              @this.set('copySourceIndex', index);
              @this.set('selectedCopyScheduleDays', []);
              @this.set('selectAllCopyScheduleModal', false);
              setTimeout(() => {
                const selector = `#copy-schedule-day-checkboxes .form-check[data-day="${day}"]`;
                const $element = $(selector);
                if ($element.length > 0) {
                  $element.hide();
                }
              }, 100);
            } catch (error) {
              console.error('Error setting copyScheduleSource:', error);
              window.dispatchEvent(new CustomEvent('close-modal', {
                detail: {
                  name: 'copy-schedule-modal'
                }
              }));
            }
          }
        });


        $(document).ready(function() {
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

          const excludedButtons = [
            '.copy-single-slot-btn',
            '.delete-schedule-setting',
            '.emergency-slot-btn'
          ];

          $(document).on('click', '.btn:not(' + excludedButtons.join(',') + ')', function(e) {
            const $button = $(this);
            if ($button.find('.loader').length && $button.find('.button_text').length) {
              toggleButtonLoading($button, true);
            }
          });

          Livewire.on('show-toastr', () => {
            $('.btn').each(function() {
              const $button = $(this);
              if ($button.find('.loader').length && $button.find('.button_text').length) {
                toggleButtonLoading($button, false);
              }
            });
          });

          $(document).on('click', '.remove-row-btn', function(e) {
            e.preventDefault();
            const day = $(this).closest('[data-slot-id]').find('.schedule-btn').data('day');
            const index = $(this).closest('[data-slot-id]').find('.schedule-btn').data('index');
            if ($(this).is(':disabled')) return;
            Swal.fire({
              title: 'آیا مطمئن هستید؟',
              text: 'این اسلات حذف خواهد شد و قابل بازگشت نیست!',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'بله، حذف کن!',
              cancelButtonText: 'خیر',
              reverseButtons: true
            }).then((result) => {
              if (result.isConfirmed) {
                toggleButtonLoading($(this), true);
                @this.call('removeSlot', day, index);
              }
            });
          });



          Livewire.on('set-schedule-times', (event) => {
            const startTime = event.startTime || '00:00';
            const endTime = event.endTime || '23:59';
            $('#schedule-start').val(startTime);
            $('#schedule-end').val(endTime);
            $('#timepicker-save-section').removeClass('d-none');
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
              @this.call('saveSchedule', startTime, endTime).then(() => {
                $('#timepicker-save-section').addClass('d-none');
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

          Livewire.on('close-modal', (event) => {
            const modalName = event?.name || (event && event[0]?.name) || null;
            if (modalName === 'schedule-modal') {
              @this.set('scheduleModalDay', null);
              @this.set('scheduleModalIndex', null);
              @this.set('selectedScheduleDays', []);
              @this.set('scheduleSettings', []);
            }
            if (modalName === 'copy-schedule-modal') {
              @this.set('copySourceDay', null);
              @this.set('copySourceIndex', null);
              @this.set('selectedCopyScheduleDays', []);
              @this.set('selectAllCopyScheduleModal', false);
            }
          });



          Livewire.on('show-conflict-alert', (event) => {
            let conflictsObj = Array.isArray(event) && event[0] && event[0].conflicts ? event[0].conflicts :
              event.conflicts || event;
            if (!conflictsObj || typeof conflictsObj !== 'object' || Object.keys(conflictsObj).length === 0) {
              console.warn('No valid conflicts data, proceeding with copyScheduleSetting');
              @this.call('copyScheduleSetting');
              return;
            }

            const persianDayMap = {
              saturday: 'شنبه',
              sunday: 'یک‌شنبه',
              monday: 'دوشنبه',
              tuesday: 'سه‌شنبه',
              wednesday: 'چهارشنبه',
              thursday: 'پنج‌شنبه',
              friday: 'جمعه'
            };

            let conflictMessage = '<p>تداخل در روزهای زیر یافت شد:</p><ul>';
            let hasConflicts = false;
            Object.keys(conflictsObj).forEach(day => {
              if (!persianDayMap[day]) {
                console.warn(`Day ${day} not found in persianDayMap`);
                return;
              }
              const conflictDetails = conflictsObj[day];
              if (!conflictDetails || !Array.isArray(conflictDetails)) {
                console.warn(`No conflict details for day ${day}`);
                return;
              }
              hasConflicts = true;
              conflictMessage += `<li>${persianDayMap[day]}:</li><ul>`;
              conflictDetails.forEach(slot => {
                const start = slot.start_time || 'نامشخص';
                const end = slot.end_time || 'نامشخص';
                conflictMessage += `<li>از ${start} تا ${end}</li>`;
              });
              conflictMessage += '</ul></li>';
            });

            if (!hasConflicts) {
              console.warn('No valid conflicts found, proceeding with copyScheduleSetting');
              @this.call('copyScheduleSetting');
              return;
            }

            conflictMessage += '<p>آیا می‌خواهید داده‌های موجود را جایگزین کنید؟</p>';
            Swal.fire({
              title: 'تداخل در کپی تنظیمات زمان‌بندی',
              html: conflictMessage,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'جایگزین کن',
              cancelButtonText: 'لغو',
              reverseButtons: true
            }).then((result) => {
              if (result.isConfirmed) {
                @this.call('copyScheduleSetting', true);
              } else {
                @this.set('modalMessage', 'عملیات کپی لغو شد');
                @this.set('modalType', 'error');
                @this.set('modalOpen', true);
              }
            });
          });



          Livewire.on('set-schedule-times', (event) => {
            const startTime = event.startTime || '00:00';
            const endTime = event.endTime || '23:59';
            $(`input[wire\\:model="scheduleSettings.${event.day}.${event.index}.start_time"]`).val(startTime);
            $(`input[wire\\:model="scheduleSettings.${event.day}.${event.index}.end_time"]`).val(endTime);
          });

          $(document).on('change', '#select-all-days', function() {
            const isChecked = $(this).is(':checked');
            $('#day-checkboxes .form-check:visible input[type="checkbox"]').prop('checked', isChecked);
          });

          Livewire.on('show-conflict-alert', (event) => {
            let conflictsObj = Array.isArray(event) && event[0] && event[0].conflicts ? event[0].conflicts :
              event.conflicts || event;
            if (!conflictsObj || typeof conflictsObj !== 'object' || Object.keys(conflictsObj).length === 0) {
              console.warn('No valid conflicts data, proceeding with copySchedule');
              @this.call('copySchedule', false);
              return;
            }
            const persianDayMap = {
              saturday: 'شنبه',
              sunday: 'یک‌شنبه',
              monday: 'دوشنبه',
              tuesday: 'سه‌شنبه',
              wednesday: 'چهارشنبه',
              thursday: 'پنج‌شنبه',
              Friday: 'جمعه'
            };
            let conflictMessage = '<p>تداخل در روزهای زیر یافت شد:</p><ul>';
            let hasConflicts = false;
            Object.keys(conflictsObj).forEach(day => {
              if (!persianDayMap[day]) {
                console.warn(`Day ${day} not found in persianDayMap`);
                return;
              }
              const conflictDetails = conflictsObj[day];
              if (!conflictDetails || (!conflictDetails.work_hours && !conflictDetails.emergency_times)) {
                console.warn(`No conflict details for day ${day}`);
                return;
              }
              hasConflicts = true;
              conflictMessage += `<li>${persianDayMap[day]}:</li><ul>`;
              if (conflictDetails.work_hours && Array.isArray(conflictDetails.work_hours) && conflictDetails
                .work_hours.length > 0) {
                conflictDetails.work_hours.forEach(slot => {
                  const start = slot.start || 'نامشخص';
                  const end = slot.end || 'نامشخص';
                  conflictMessage += `<li>ساعت کاری: از ${start} تا ${end}</li>`;
                });
              }
              if (conflictDetails.emergency_times && Array.isArray(conflictDetails.emergency_times) &&
                conflictDetails.emergency_times.length > 0) {
                conflictMessage +=
                  `<li>زمان‌های اورژانسی: ${conflictDetails.emergency_times.join(', ')}</li>`;
              }
              conflictMessage += '</ul></li>';
            });
            if (!hasConflicts) {
              console.warn('No valid conflicts found, proceeding with copySchedule');
              @this.call('copySchedule', false);
              return;
            }
            conflictMessage += '<p>آیا می‌خواهید داده‌های موجود را جایگزین کنید؟</p>';
            Swal.fire({
              title: 'تداخل در کپی برنامه کاری',
              html: conflictMessage,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'جایگزین کن',
              cancelButtonText: 'لغو',
              reverseButtons: true
            }).then((result) => {
              if (result.isConfirmed) {
                @this.call('copySchedule', true);
              } else {
                @this.set('modalMessage', 'عملیات کپی لغو شد');
                @this.set('modalType', 'error');
                @this.set('modalOpen', true);
              }
            });
          });
        });
      });
    </script>
  </div>
</div>
