<div>
  <div>
    <div class="w-100 d-flex justify-content-center mt-3" dir="ltr">
      <div class="auto-scheule-content-top">
        <x-my-toggle-appointment :isChecked="$autoScheduling" id="appointment-toggle" day="نوبت دهی خودکار" class="mt-3"
          wire:model="autoScheduling" wire:change="updateAutoScheduling" />
      </div>
    </div>
    <div class="workhours-content w-100 d-flex justify-content-center mt-4">
      <div class="workhours-wrapper-content p-3 pt-4 {{ $autoScheduling ? '' : 'd-none' }}">
        <div>
          <div>
            <div>
              <div>
                <div class="input-group position-relative">
                  <label class="label-top-input-special-takhasos">تعداد روز‌های باز تقویم</label>
                  <input type="number" class="form-control text-center h-50 border-radius-0" name="calendar_days"
                    placeholder="تعداد روز مورد نظر خود را وارد کنید" wire:model.live="calendarDays">
                  <div class="input-group-append count-span-prepand-style border-radius-0">
                    <span class="input-group-text px-2">روز</span>
                  </div>
                </div>
                <div class="mt-3">
                  <x-my-check :isChecked="$holidayAvailability" id="posible-appointments-inholiday" day="باز بودن مطب در تعطیلات رسمی"
                    model="holidayAvailability" />
                </div>
              </div>
              <div class="mt-4">
                <label class="text-dark font-weight-bold">روزهای کاری</label>
                <div
                  class="d-flex flex-wrap justify-content-start mt-3 gap-40 bg-light p-3 border-radius-4 day-contents align-items-center h-55"
                  id="day-contents-outside">
                  @foreach (['saturday' => 'شنبه', 'sunday' => 'یکشنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $englishDay => $persianDay)
                    <x-my-check :isChecked="$isWorking[$englishDay]" id="{{ $englishDay }}" day="{{ $persianDay }}" />
                  @endforeach
                </div>
                <div id="work-hours" class="mt-4">
                  @foreach (['saturday' => 'شنبه', 'sunday' => 'یکشنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $englishDay => $persianDay)
                    <div
                      class="work-hours-{{ $englishDay }} {{ $isWorking[$englishDay] ? '' : 'd-none' }} position-relative">
                      <div class="border-333 p-3 mt-3 border-radius-4">
                        <h6>{{ $persianDay }}</h6>
                        <div id="morning-{{ $englishDay }}-details" class="mt-4">
                          @if (!empty($slots[$englishDay]))
                            @foreach ($slots[$englishDay] as $index => $slot)
                              <div
                                class="mt-3 form-row d-flex justify-content-between w-100 pt-4 bg-active-slot border-radius-4"
                                data-slot-id="{{ $slot['id'] ?? '' }}">
                                <div class="d-flex justify-content-start align-items-center gap-4">
                                  <div class="form-group position-relative timepicker-ui">
                                    <label class="label-top-input-special-takhasos"
                                      for="morning-start-{{ $englishDay }}-{{ $index }}">از</label>
                                    <input type="text"
                                      class="form-control h-50 timepicker-ui-input text-center font-weight-bold font-size-13 start-time bg-white"
                                      id="morning-start-{{ $englishDay }}-{{ $index }}"
                                      wire:model.live="slots.{{ $englishDay }}.{{ $index }}.start_time" />
                                  </div>
                                  <div class="form-group position-relative timepicker-ui">
                                    <label class="label-top-input-special-takhasos"
                                      for="morning-end-{{ $englishDay }}-{{ $index }}">تا</label>
                                    <input type="text"
                                      class="form-control h-50 timepicker-ui-input text-center font-weight-bold font-size-13 end-time bg-white"
                                      id="morning-end-{{ $englishDay }}-{{ $index }}"
                                      wire:model.live="slots.{{ $englishDay }}.{{ $index }}.end_time" />
                                  </div>
                                  <div class="form-group position-relative">
                                    <label class="label-top-input-special-takhasos"
                                      for="morning-patients-{{ $englishDay }}-{{ $index }}">تعداد
                                      نوبت</label>
                                    <input type="text"
                                      class="form-control h-50 text-center max-appointments bg-white"
                                      id="morning-patients-{{ $englishDay }}-{{ $index }}"
                                      wire:model.live="slots.{{ $englishDay }}.{{ $index }}.max_appointments"
                                      data-toggle="modal" data-target="#CalculatorModal" data-day="{{ $englishDay }}"
                                      data-index="{{ $index }}" readonly />
                                  </div>
                                  <!-- دکمه جدید برای نوبت‌های اورژانسی -->
                                  <div class="form-group position-relative">
                                    <button data-tooltip="true" data-placement="bottom"
                                      data-original-title="زمان های اورژانسی که میتوانید برای شرایط خاص نگهدارید"
                                      class="btn btn-light btn-sm emergency-slot-btn" data-toggle="modal"
                                      data-target="#emergencyModal" data-day="{{ $englishDay }}"
                                      data-index="{{ $index }}"
                                      {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                      <img src="{{ asset('dr-assets/icons/emergency.svg') }}" alt="نوبت اورژانسی">
                                    </button>
                                  </div>
                                  <!-- دکمه کپی -->
                                  <div class="form-group position-relative">
                                    <button data-tooltip="true" data-placement="top"
                                      data-original-title="کپی ساعات کاری"
                                      class="btn btn-light btn-sm copy-single-slot-btn" data-toggle="modal"
                                      data-target="#checkboxModal" data-day="{{ $englishDay }}"
                                      data-index="{{ $index }}"
                                      {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                      <img src="{{ asset('dr-assets/icons/copy.svg') }}" alt="کپی">
                                    </button>
                                  </div>
                                  <!-- دکمه حذف -->
                                  <div class="form-group position-relative">
                                    <button data-tooltip="true" data-placement="bottom"
                                      data-original-title="حذف برنامه کاری" class="btn btn-light btn-sm remove-row-btn"
                                      {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                      <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                                    </button>
                                  </div>
                                </div>
                                <div class="d-flex align-items-center">
                                  <button data-tooltip="true" data-placement="top"
                                    data-original-title="زمانبندی باز شدن نوبت ها" type="button"
                                    class="btn text-black btn-sm btn-outline-primary schedule-btn" data-toggle="modal"
                                    data-target="#scheduleModal" data-day="{{ $englishDay }}"
                                    data-index="{{ $index }}"
                                    {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                    <img src="{{ asset('dr-assets/icons/open-time.svg') }}" alt="">
                                  </button>
                                </div>
                              </div>
                            @endforeach
                          @else
                            <div
                              class="mt-3 form-row d-flex justify-content-between w-100 pt-4 bg-active-slot border-radius-4"
                              data-slot-id="{{ $slot['id'] ?? '' }}">
                              <div class="d-flex justify-content-start align-items-center gap-4">
                                <div class="form-group position-relative timepicker-ui">
                                  <label class="label-top-input-special-takhasos"
                                    for="morning-start-{{ $englishDay }}-{{ $index }}">از</label>
                                  <input type="text"
                                    class="form-control h-50 timepicker-ui-input text-center font-weight-bold font-size-13 start-time bg-white"
                                    id="morning-start-{{ $englishDay }}-{{ $index }}"
                                    wire:model.live="slots.{{ $englishDay }}.{{ $index }}.start_time" />
                                </div>
                                <div class="form-group position-relative timepicker-ui">
                                  <label class="label-top-input-special-takhasos"
                                    for="morning-end-{{ $englishDay }}-{{ $index }}">تا</label>
                                  <input type="text"
                                    class="form-control h-50 timepicker-ui-input text-center font-weight-bold font-size-13 end-time bg-white"
                                    id="morning-end-{{ $englishDay }}-{{ $index }}"
                                    wire:model.live="slots.{{ $englishDay }}.{{ $index }}.end_time" />
                                </div>
                                <div class="form-group position-relative">
                                  <label class="label-top-input-special-takhasos"
                                    for="morning-patients-{{ $englishDay }}-{{ $index }}">تعداد نوبت</label>
                                  <input type="text"
                                    class="form-control h-50 text-center max-appointments bg-white"
                                    id="morning-patients-{{ $englishDay }}-{{ $index }}"
                                    wire:model.live="slots.{{ $englishDay }}.{{ $index }}.max_appointments"
                                    data-toggle="modal" data-target="#CalculatorModal"
                                    data-day="{{ $englishDay }}" data-index="{{ $index }}" readonly />
                                </div>
                                <!-- دکمه جدید برای نوبت‌های اورژانسی -->
                                <div class="form-group position-relative">
                                  <button data-tooltip="true" data-placement="bottom"
                                    data-original-title="زمان های اورژانسی که میتوانید برای شرایط خاص نگهدارید"
                                    class="btn btn-light btn-sm emergency-slot-btn" data-toggle="modal"
                                    data-target="#emergencyModal" data-day="{{ $englishDay }}"
                                    data-index="{{ $index }}"
                                    {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                    <img src="{{ asset('dr-assets/icons/emergency.svg') }}" alt="نوبت اورژانسی">
                                  </button>
                                </div>
                                <!-- دکمه کپی -->
                                <div class="form-group position-relative">
                                  <button data-tooltip="true" data-placement="top"
                                    data-original-title="کپی ساعات کاری"
                                    class="btn btn-light btn-sm copy-single-slot-btn" data-toggle="modal"
                                    data-target="#checkboxModal" data-day="{{ $englishDay }}"
                                    data-index="{{ $index }}"
                                    {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                    <img src="{{ asset('dr-assets/icons/copy.svg') }}" alt="کپی">
                                  </button>
                                </div>
                                <!-- دکمه حذف -->
                                <div class="form-group position-relative">
                                  <button data-tooltip="true" data-placement="bottom"
                                    data-original-title="حذف برنامه کاری" class="btn btn-light btn-sm remove-row-btn"
                                    {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                    <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                                  </button>
                                </div>
                              </div>
                              <div class="d-flex align-items-center">
                                <button data-tooltip="true" data-placement="top"
                                  data-original-title="زمانبندی باز شدن نوبت ها" type="button"
                                  class="btn text-black btn-sm btn-outline-primary schedule-btn" data-toggle="modal"
                                  data-target="#scheduleModal" data-day="{{ $englishDay }}"
                                  data-index="{{ $index }}"
                                  {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                  <img src="{{ asset('dr-assets/icons/open-time.svg') }}" alt="">

                                </button>
                              </div>
                            </div>
                          @endif
                          <div class="add-new-row mt-3">
                            <button class="add-row-btn btn btn-sm btn-light"
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
          <div class="d-flex w-100 justify-content-end mt-3">
            <button type="button"
              class="btn my-btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
              id="save-work-schedule" wire:click="saveWorkSchedule">
              <span class="button_text">ذخیره تغییرات</span>
              <div class="loader"></div>
            </button>
          </div>
          <hr>
          @if (isset($_GET['activation-path']) && $_GET['activation-path'] == true)
            <div class="w-100 mt-3">
              <button class="btn btn-success w-100 h-50" tabindex="0" type="button" id=":rs:"
                data-toggle="modal" data-target="#activation-modal">
                <span class="button_text"> پایان فعالسازی</span>
                <div class="loader"></div>
              </button>

            </div>
            <div class="modal fade" id="activation-modal" tabindex="-1" role="dialog"
              aria-labelledby="activation-modal-label" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-radius-6">
                  <div class="modal-header border-radius-6">
                    <h5 class="modal-title" id="activation-modal-label">فعالسازی نوبت دهی</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <div>
                      <p>اطلاعات شما ثبت شد و ویزیت آنلاین شما تا ساعاتی دیگر فعال می‌شود. بیماران می‌توانند مستقیماً از
                        طریق
                        پروفایل شما ویزیت آنلاین رزرو کنند.</p>
                      <p>به دلیل محدودیت ظرفیت فعلی، نمایه شما در ابتدا در لیست پزشکان موجود برای ویزیت آنلاین در رتبه
                        پایین‌تری
                        قرار می‌گیرد.</p>
                      <p>برای هر گونه سوال یا توضیح بیشتر، لطفا با ما <a style="color: blue"
                          href="https://emr-benobe.ir/about">ارتباط</a> بگیرید.
                        تیم ما
                        اینجاست تا از شما در هر مرحله حمایت کند.</p>
                    </div>
                  </div>
                  <div class="p-3">
                    <a href="{{ route('dr-panel', ['showModal' => 'true']) }}"
                      class="btn my-btn-primary w-100 h-50 d-flex align-items-center text-white justify-content-center">شروع
                      نوبت
                      دهی</a>
                  </div>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
  <!-- مودال برای انتخاب زمان‌های اورژانسی -->
  <div class="modal fade" id="emergencyModal" tabindex="-1" role="dialog" aria-labelledby="emergencyModalLabel"
    aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content border-radius-6">
        <div class="modal-header border-radius-6">
          <h6 class="modal-title font-weight-bold" id="emergencyModalLabel">انتخاب زمان‌های اورژانسی</h6>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
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
      </div>
    </div>
  </div>
  <div class="modal fade" id="scheduleModal" tabindex="-1" role="dialog" aria-labelledby="scheduleModalLabel"
    aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content border-radius-6">
        <div class="modal-header border-radius-6">
          <h5 class="modal-title font-weight-bold" id="scheduleModalLabel">تنظیم زمان‌بندی</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
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
            <div>
              <label class="font-weight-bold text-dark">روزهای کاری</label>
              <div class="form-check mb-3">
                <x-my-check-box :is-checked="$selectAllScheduleModal" id="select-all-schedule-days" day="انتخاب همه"
                  wire:model.live="selectAllScheduleModal" />
              </div>
              <div class="mt-2 d-flex gap-2 flex-wrap" id="day-schdule-wrapper">
                @foreach (['saturday' => 'شنبه', 'sunday' => 'یکشنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $day => $label)
                  <div class="form-check">
                    <x-my-check-box :is-checked="isset($selectedScheduleDays[$day]) && $selectedScheduleDays[$day]" id="schedule-day-{{ $day }}"
                      day="{{ $label }}" wire:model.live="selectedScheduleDays.{{ $day }}"
                      data-day="{{ $day }}" class="schedule-day-checkbox" />
                  </div>
                @endforeach
              </div>
            </div>
            <div class="w-100 d-flex mt-4 gap-4 justify-content-center">
              <div class="form-group position-relative timepicker-ui">
                <label class="label-top-input-special-takhasos">شروع</label>
                <input type="text"
                  class="form-control h-50 timepicker-ui-input text-center font-weight-bold font-size-13 col-6"
                  id="schedule-start" value="00:00">
              </div>
              <div class="form-group position-relative timepicker-ui">
                <label class="label-top-input-special-takhasos">پایان</label>
                <input type="text"
                  class="form-control h-50 timepicker-ui-input text-center font-weight-bold font-size-13 col-6"
                  id="schedule-end" value="23:59">
              </div>
            </div>
            <div class="mt-4">
              <div class="d-flex flex-column gap-2" id="schedule-settings-list">
                @if ($scheduleModalDay && $scheduleModalIndex !== null)
                  @php
                    $schedule = collect($this->workSchedules)->firstWhere('day', $scheduleModalDay);
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
                      <div class="d-flex justify-content-between align-items-center p-2 border rounded"
                        wire:key="setting-{{ $scheduleModalDay }}-{{ $index }}">
                        <span>
                          از {{ $setting['start_time'] }} تا {{ $setting['end_time'] }} (روزها:
                          {{ implode(', ', array_map(fn($day) => $dayTranslations[$day] ?? $day, $setting['days'] ?? [])) }})
                        </span>
                        <button class="btn btn-danger btn-sm delete-schedule-setting"
                          data-day="{{ $scheduleModalDay }}" data-index="{{ $index }}">
                          <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                        </button>
                      </div>
                    @endforeach
                  @else
                    <p class="text-muted text-center text-danger">هیچ تنظیم زمان‌بندی برای این بازه زمانی ذخیره نشده
                      است.</p>
                  @endif
                @else
                  <p class="text-muted text-center text-danger">روز یا بازه زمانی انتخاب نشده است.</p>
                @endif
              </div>
            </div>
            <div class="w-100 d-flex justify-content-end mt-3">
              <button type="button"
                class="btn my-btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
                id="saveSchedule">
                <span class="button_text">ذخیره تغییرات</span>
                <div class="loader"></div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="CalculatorModal" tabindex="-1" role="dialog" aria-labelledby="CalculatorModalLabel"
    aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content border-radius-6" id="calculate-modal">
        <div class="modal-header border-radius-6">
          <h6 class="modal-title font-weight-bold" id="CalculatorModalLabel">انتخاب تعداد نوبت یا زمان ویزیت:</h6>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="d-flex align-items-center">
            <div class="d-flex flex-wrap flex-column align-items-start gap-4 w-100">
              <!-- حالت انتخاب تعداد نوبت -->
              <div class="d-flex align-items-center w-100">
                <div class="custom-control custom-radio mr-2">
                  <input type="radio" id="count-radio" name="calculation-mode" class="custom-control-input"
                    wire:model.live="calculationMode" value="count">
                  <label class="custom-control-label" for="count-radio"></label>
                </div>
                <div class="input-group position-relative mx-2">
                  <label class="label-top-input-special-takhasos">تعداد نوبت‌ها</label>
                  <input type="number" class="form-control text-center h-50 border-radius-0" id="appointment-count"
                    wire:model.live="calculator.appointment_count"
                    {{ $calculationMode !== 'count' ? 'disabled' : '' }}>
                  <div class="input-group-append count-span-prepand-style">
                    <span class="input-group-text px-2">نوبت</span>
                  </div>
                </div>
              </div>
              <!-- حالت انتخاب زمان هر نوبت -->
              <div class="d-flex align-items-center mt-4 w-100">
                <div class="custom-control custom-radio mr-2">
                  <input type="radio" id="time-radio" name="calculation-mode" class="custom-control-input"
                    wire:model.live="calculationMode" value="time">
                  <label class="custom-control-label" for="time-radio"></label>
                </div>
                <div class="input-group position-relative mx-2">
                  <label class="label-top-input-special-takhasos">زمان هر نوبت</label>
                  <input type="number" class="form-control text-center h-50 border-radius-0" id="time-count"
                    wire:model.live="calculator.time_per_appointment"
                    {{ $calculationMode !== 'time' ? 'disabled' : '' }}>
                  <div class="input-group-append">
                    <span class="input-group-text px-2">دقیقه</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="w-100 d-flex justify-content-end p-1 gap-4 mt-3">
            <button type="button" class="btn my-btn-primary h-50 w-100" wire:click="saveCalculator"
              id="saveSelectionCalculator">
              <span class="button_text">ذخیره تغییرات</span>
              <div class="loader"></div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="checkboxModal" tabindex="-1" aria-labelledby="checkboxModalLabel" aria-hidden="true"
    wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-radius-6">
        <div class="modal-header border-radius-6">
          <h5 class="modal-title font-weight-bold" id="checkboxModalLabel">کپی برنامه کاری</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="بستن">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <p>روزهایی که می‌خواهید برنامه کاری به آن‌ها کپی شود را انتخاب کنید:</p>
          <div class="form-check mb-3">
            <x-my-check-box :is-checked="$selectAllCopyModal" id="select-all-days" day="انتخاب همه"
              wire:model.live="selectAllCopyModal" />
          </div>
          <div class="d-flex flex-column gap-2" id="day-checkboxes">
            @foreach (['saturday' => 'شنبه', 'sunday' => 'یک‌شنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $day => $label)
              @if ($day !== $sourceDay)
                <!-- فقط روزهایی که منبع نیستند نمایش داده شوند -->
                <div class="form-check d-flex align-items-center" data-day="{{ $day }}">
                  <x-my-check-box :is-checked="isset($selectedDays[$day]) && $selectedDays[$day]" id="day-{{ $day }}" day="{{ $label }}"
                    wire:model.live="selectedDays.{{ $day }}" data-day="{{ $day }}" />
                </div>
              @endif
            @endforeach
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary h-50 w-100" wire:click="copySchedule">ذخیره</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('livewire:initialized', () => {
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
        // لیست دکمه‌هایی که نباید لودینگ داشته باشند
        const excludedButtons = [
          '.copy-single-slot-btn',
          '.delete-schedule-setting',
          '.emergency-slot-btn'
        ];
        // افزودن لودینگ به همه دکمه‌های غیرمستثنی
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

        // متوقف کردن لودینگ هنگام بستن مودال‌ها
        ['#CalculatorModal', '#scheduleModal', '#emergencyModal', '#checkboxModal'].forEach(modalId => {
          $(document).on('hidden.bs.modal', modalId, function() {
            $('.btn').each(function() {
              const $button = $(this);
              if ($button.find('.loader').length && $button.find('.button_text').length) {
                toggleButtonLoading($button, false);
              }
            });
          });
        });

        function initializeTooltips() {
          $('[data-tooltip="true"]').tooltip('dispose');
          $('[data-tooltip="true"]').tooltip({
            trigger: 'hover focus',
            container: 'body',
            boundary: 'window',
            delay: {
              show: 100,
              hide: 200
            },
          });
        }

        function initializeTimepicker() {
          $('.timepicker-ui').each(function() {
            if (!$(this).data('timepicker-initialized')) {
              try {
                const options = {
                  clockType: '24h',
                  theme: 'basic',
                  mobile: true,
                  enableScrollbar: true,
                  disableTimeRangeValidation: false,
                  autoClose: true,
                };
                const timepicker = new window.tui.TimepickerUI(this, options);
                timepicker.create();
                $(this).data('timepicker-initialized', true);
              } catch (e) {
                console.error('Error initializing timepicker:', e);
              }
            }
          });
        }

        initializeTooltips();
        initializeTimepicker();

        Livewire.on('refresh-work-hours', () => {
          initializeTimepicker();
          initializeTooltips();
        });

        Livewire.on('refresh-timepicker', () => {
          setTimeout(() => {
            initializeTimepicker();
            initializeTooltips();
          }, 100);
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

        $(document).on('show.bs.modal', '#CalculatorModal', function(e) {
          const $modal = $(this);
          const button = $(e.relatedTarget);
          const day = button.data('day');
          const index = button.data('index');

          try {
            @this.set('calculator.day', day);
            @this.set('calculator.index', index);

            const startTime = $(`#morning-start-${day}-${index}`).val();
            const endTime = $(`#morning-end-${day}-${index}`).val();

            if (!startTime || !endTime) {
              @this.set('modalMessage', 'لطفاً ابتدا زمان شروع و پایان را وارد کنید');
              @this.set('modalType', 'error');
              @this.set('modalOpen', true);
              $modal.modal('hide');
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
              $modal.modal('hide');
              return;
            }

            const currentCount = @this.get('calculator.appointment_count');
            const currentTime = @this.get('calculator.time_per_appointment');
            if (currentCount) {
              $appointmentCount.val(currentCount);
              $timeCount.val(Math.round(totalMinutes / currentCount));
            } else if (currentTime) {
              $timeCount.val(currentTime);
              $appointmentCount.val(Math.round(totalMinutes / currentTime));
            } else {
              $appointmentCount.val('');
              $timeCount.val('');
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

            const calculationMode = @this.get('calculationMode');
            if (calculationMode === 'count') {
              $countRadio.prop('checked', true);
              $appointmentCount.prop('disabled', false);
              $timeCount.prop('disabled', true);
            } else {
              $timeRadio.prop('checked', true);
              $timeCount.prop('disabled', false);
              $appointmentCount.prop('disabled', true);
            }
          } catch (error) {
            console.error('Error in CalculatorModal:', error);
            $modal.modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
          }
        });

        $(document).on('hidden.bs.modal', '#CalculatorModal', function() {
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open').css('padding-right', '');
          $('#appointment-count').off('input focus');
          $('#time-count').off('input focus');
          $('#count-radio').off('change');
          $('#time-radio').off('change');
        });

        $(document).on('show.bs.modal', '#checkboxModal', function(e) {
          const $modal = $(this);
          const button = $(e.relatedTarget);
          const day = button.data('day');
          const index = button.data('index');

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
              initializeTooltips();
            }, 100);
          } catch (error) {
            console.error('Error setting copySource:', error);
            $modal.modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
          }
        });

        $(document).on('hidden.bs.modal', '#checkboxModal', function() {
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open').css('padding-right', '');
          $('#day-checkboxes .form-check').show();
          @this.set('selectedDays', []);
          @this.set('copySource', {
            day: null,
            index: null
          });
          @this.set('selectAllCopyModal', false);
          initializeTooltips();
        });

        $(document).on('show.bs.modal', '#emergencyModal', function(e) {
          const $modal = $(this);
          const button = $(e.relatedTarget);
          const day = button.data('day');
          const index = button.data('index');

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
              $modal.modal('hide');
              return;
            }

            const startTime = $startTimeInput.val();
            const endTime = $endTimeInput.val();
            const maxAppointments = $maxAppointmentsInput.val();

            if (!startTime || !endTime || !maxAppointments) {
              @this.set('modalMessage', 'لطفاً ابتدا زمان شروع، پایان و تعداد نوبت را وارد کنید');
              @this.set('modalType', 'error');
              @this.set('modalOpen', true);
              $modal.modal('hide');
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

            setTimeout(() => {
              initializeTooltips();
            }, 100);
          } catch (error) {
            console.error('Error in emergencyModal:', error);
            $modal.modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
          }
        });

        $(document).on('hidden.bs.modal', '#emergencyModal', function() {
          @this.set('isEmergencyModalOpen', false);
          $('#emergency-times').empty();
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open').css('padding-right', '');
          initializeTooltips();
        });

        Livewire.on('close-emergency-modal', () => {
          @this.set('isEmergencyModalOpen', false);
          $('#emergencyModal').modal('hide');
          $('#emergency-times').empty();
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open').css('padding-right', '');
          initializeTooltips();
        });

        function cleanupModal() {
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open').css('padding-right', '');
        }

        $(document).on('show.bs.modal', '#scheduleModal', function(e) {
          const $modal = $(this);
          const button = $(e.relatedTarget);
          const day = button.data('day');
          const index = button.data('index');

          try {
            if (!day || index === undefined) {
              throw new Error('Invalid day or index');
            }

            @this.call('openScheduleModal', day, index);

            $('#scheduleLoading').removeClass('d-none');
            $('.modal-content-inner').hide();

            const startTimeInput = $(`#morning-start-${day}-${index}`);
            const endTimeInput = $(`#morning-end-${day}-${index}`);
            const startTime = startTimeInput.length ? startTimeInput.val() : '00:00';
            const endTime = endTimeInput.length ? endTimeInput.val() : '23:59';

            if (!startTime || !endTime) {
              throw new Error('Start or end time is missing');
            }

         

            $('#schedule-start').val(startTime);
            $('#schedule-end').val(endTime);

            setTimeout(() => {
              $('#scheduleLoading').addClass('d-none');
              $('.modal-content-inner').show();
              initializeTooltips();
              initializeTimepicker();
              const selectAllCheckbox = $('#select-all-schedule-days');
              const dayCheckboxes = $('.schedule-day-checkbox');
              selectAllCheckbox.prop('checked', false);
              selectAllCheckbox.off('change').on('change', function() {
                const isChecked = $(this).is(':checked');
                dayCheckboxes.prop('checked', isChecked);
                dayCheckboxes.each(function() {
                  @this.set(`selectedScheduleDays.${$(this).data('day')}`, isChecked);
                });
              });
              const allChecked = dayCheckboxes.length === dayCheckboxes.filter(':checked').length;
              selectAllCheckbox.prop('checked', allChecked);
            }, 300);
          } catch (error) {
            console.error('Error in scheduleModal:', error);
            toastr.error('خطا در بارگذاری مودال: ' + error.message);
            $modal.modal('hide');
            cleanupModal();
          }
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
            // اعتبارسنجی سمت کلاینت
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

            // همگام‌سازی selectedScheduleDays با سرور
            selectedDays.forEach(day => {
              @this.set(`selectedScheduleDays.${day}`, true);
            });

            // فعال کردن لودینگ
            toggleButtonLoading($button, true);

            // فراخوانی متد saveSchedule
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

        $(document).on('hidden.bs.modal', '#scheduleModal', function() {
          cleanupModal();
          @this.set('scheduleModalDay', null);
          @this.set('scheduleModalIndex', null);
          @this.set('selectedScheduleDays', []);
          @this.set('selectAllScheduleModal', false);
          $('#schedule-settings-list').empty();
          $('.form-check-input').prop('disabled', false);
          initializeTooltips();
          initializeTimepicker();
        });

        Livewire.on('refresh-schedule-settings', () => {
          initializeTooltips();
          initializeTimepicker();
        });

        Livewire.on('close-schedule-modal', () => {
          $('#scheduleModal').modal('hide');
          cleanupModal();
          initializeTooltips();
        });

        $(document).on('change', '#select-all-days', function() {
          const isChecked = $(this).is(':checked');
          $('#day-checkboxes .form-check:visible input[type="checkbox"]').prop('checked', isChecked);
        });

        $('#checkboxModal').on('hidden.bs.modal', function() {
          $('#day-checkboxes .form-check').css('display', 'flex');
          $('#day-checkboxes input[type="checkbox"]').prop('checked', false);
          initializeTooltips();
        });

        Livewire.on('show-conflict-alert', (event) => {

          // استخراج شیء conflicts از داده‌های دریافتی
          let conflictsObj = Array.isArray(event) && event[0] && event[0].conflicts ? event[0].conflicts :
            event.conflicts || event;

          // بررسی معتبر بودن conflictsObj
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
            friday: 'جمعه'
          };

          let conflictMessage = '<p>تداخل در روزهای زیر یافت شد:</p><ul>';
          let hasConflicts = false;

          // پردازش کلیدهای معتبر (روزهای هفته)
          Object.keys(conflictsObj).forEach(day => {
            // فقط روزهایی که در persianDayMap وجود دارند پردازش شوند
            if (!persianDayMap[day]) {
              console.warn(`Day ${day} not found in persianDayMap`);
              return;
            }

            const conflictDetails = conflictsObj[day];
            // بررسی وجود داده‌های تداخل
            if (!conflictDetails || (!conflictDetails.work_hours && !conflictDetails.emergency_times)) {
              console.warn(`No conflict details for day ${day}`);
              return;
            }

            hasConflicts = true;
            conflictMessage += `<li>${persianDayMap[day]}:<ul>`;

            // نمایش تداخل‌های ساعت کاری
            if (conflictDetails.work_hours && Array.isArray(conflictDetails.work_hours) && conflictDetails
              .work_hours.length > 0) {
              conflictDetails.work_hours.forEach(slot => {
                const start = slot.start || 'نامشخص';
                const end = slot.end || 'نامشخص';
                conflictMessage += `<li>ساعت کاری: از ${start} تا ${end}</li>`;
              });
            }

            // نمایش تداخل‌های زمان‌های اورژانسی
            if (conflictDetails.emergency_times && Array.isArray(conflictDetails.emergency_times) &&
              conflictDetails.emergency_times.length > 0) {
              conflictMessage +=
                `<li>زمان‌های اورژانسی: ${conflictDetails.emergency_times.join(', ')}</li>`;
            }

            conflictMessage += '</ul></li>';
          });

          conflictMessage += '</ul>';

          // اگر هیچ تداخل معتبری وجود نداشت، عملیات کپی ادامه یابد
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
