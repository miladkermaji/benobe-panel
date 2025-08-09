<div>
  <div>
    <div class="w-100 d-flex justify-content-center mt-4" dir="ltr">
      <div class="auto-scheule-content-top">
        <x-my-toggle-appointment :isChecked="$autoScheduling" id="appointment-toggle"
          day="{{ $autoScheduling ? 'نوبت‌دهی آنلاین + دستی' : 'نوبت‌دهی دستی' }}" class="mt-3 custom-toggle"
          wire:model.live="autoScheduling" wire:change="updateAutoScheduling" />
      </div>
    </div>
    <div class="workhours-content w-100 d-flex justify-content-center mb-3">
      <div class="workhours-wrapper-content p-3 pt-4">
        <!-- تنظیمات نوبت دستی (فقط وقتی autoScheduling غیرفعال باشد) -->
        <div class="card border border-radius-11  mb-2 bg-white shadow-sm">
          <div class="p-2 d-flex align-items-center justify-content-between">
            <h6 class="mb-0">تنظیمات تایید دو مرحله ای نوبت‌های دستی</h6>
            @component('components.custom-tooltip', [
                'title' =>
                    'در فیلد اول می‌توانید مشخص کنید که چند ساعت قبل از زمان نوبت پیامک تأیید نهایی نوبت ارسال شود و در فیلد دوم، می‌توانید مشخص کنید بیمار چند ساعت مهلت دارد نوبت خود را تأیید کند، در غیر این صورت نوبت لغو خواهد شد. در زیر با استفاده از گزینه بلی یا خیر می‌توانید این امکان را فعال یا غیرفعال نمایید.',
                'placement' => 'top',
                'trigger' => 'hover',
            ])
              <span class="toggle-appointment-help" tabindex="0">&#9432;</span>
            @endcomponent
          </div>
          <div class="py-2 px-2">
            <div class="row">
              @if (!$autoScheduling)
                <div class="col-12 mb-1">
                  <div class="d-flex flex-row gap-3 flex-wrap align-items-center justify-content-center">
                    <div class="p-1 rounded bg-white"
                      style="height: 50px; display: flex; align-items: center; justify-content: center; min-width: 200px;">
                      <x-my-toggle-yes-no :isChecked="$manualNobatActive" id="manual-nobat-active" model="manualNobatActive"
                        day="تأیید دو مرحله‌ای نوبت‌های دستی
" />
                    </div>
                    <div class="p-1 rounded bg-white"
                      style="height: 50px; display: flex; align-items: center; justify-content: center; min-width: 200px;">
                      <x-my-toggle-yes-no :isChecked="$holidayAvailability" id="holiday-availability-manual" model="holidayAvailability"
                        day="باز بودن مطب در تعطیلات رسمی" />
                    </div>
                  </div>
                </div>
              @else
                <div class="col-12 mb-3">
                  <div class="p-1 rounded bg-white"
                    style="height: 50px; display: flex; align-items: center; justify-content: center; min-width: 200px;">
                    <x-my-toggle-yes-no :isChecked="$manualNobatActive" id="manual-nobat-active" model="manualNobatActive"
                      day="تأیید دو مرحله‌ای نوبت‌های دستی
" />
                  </div>
                </div>
              @endif
              <!-- زمان ارسال لینک تأیید و مدت اعتبار لینک در یک ردیف (فقط وقتی toggle فعال باشد) -->
              <div class="col-12 {{ $manualNobatActive ? '' : 'd-none' }}">
                <div class="d-flex flex-column flex-md-row gap-2">
                  <div class="flex-fill position-relative">
                    <label class="label-top-input-special-takhasos mb-2">زمان ارسال لینک تأیید:</label>
                    <div class="input-group position-relative">
                      <input class="form-control ltr text-center position-relative" type="number" min="1"
                        wire:model.live.debounce.500ms="manualNobatSendLink"
                        wire:change="autoSaveManualNobatSetting('duration_send_link')" placeholder="مثلا: 72">
                      <span class="input-group-text">ساعت قبل</span>
                    </div>
                  </div>
                  <div class="flex-fill mt-sm-3 mt-md-0 mb-sm-2 mb-md-0 position-relative">
                    <label class="label-top-input-special-takhasos mb-2">مدت زمان اعتبار لینک:</label>
                    <div class="input-group position-relative">
                      <input class="form-control ltr text-center position-relative" type="number" min="1"
                        wire:model.live.debounce.500ms="manualNobatConfirmLink"
                        wire:change="autoSaveManualNobatSetting('duration_confirm_link')" placeholder="مثلا: 48">
                      <span class="input-group-text">ساعت</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- پایان تنظیمات نوبت دستی -->
        <div>
          <div>
            <div>
              <!-- بخش تعداد روزهای باز تقویم و باز بودن مطب در تعطیلات -->
              <div x-data="{ auto: @entangle('autoScheduling') }"
                class="row border border-radius-11 p-3 align-items-center conditional-section" x-show="auto"
                style="display: none;">
                <!-- تعداد روزهای باز تقویم -->
                <div class="col-8">
                  <div class="input-group position-relative  rounded bg-white mt-2">
                    <label class="floating-label bg-white px-2 fw-bold"
                      style="position: absolute; top: -10px; right: -4px; font-size: 0.7rem; color: var(--text-secondary); z-index: 10; transition: all 0.2s ease;">
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
              <div class="mt-3">
                <label class="text-dark fw-bolder">روزهای کاری</label>
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
                <div id="work-hours" class="mt-3">
                  @foreach (['saturday' => 'شنبه', 'sunday' => 'یکشنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $englishDay => $persianDay)
                    <div
                      class="work-hours-{{ $englishDay }} {{ $isWorking[$englishDay] ? '' : 'd-none' }} position-relative">
                      <div class="border-333 p-2 mt-2 border-radius-11">
                        <h6>{{ $persianDay }}</h6>
                        <div id="morning-{{ $englishDay }}-details" class="mt-2">
                          @if (!empty($slots[$englishDay]))
                            @foreach ($slots[$englishDay] as $index => $slot)
                              <div class="mt-2 form-row d-flex w-100 pt-4 bg-active-slot border-radius-11"
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
                                      id="morning-patients-{{ $englishDay }}-{{ $index }}" readonly
                                      value="{{ $slot['max_appointments'] ?? '' }}" x-data="{ day: '{{ $englishDay }}', index: '{{ $index }}' }"
                                      @click="$dispatch('open-modal', { name: 'calculator-modal', day: day, index: index })" />
                                    <!-- مقدار این input فقط با JS بعد از ذخیره مودال کلکیولیتر ست می‌شود و Livewire هیچ sync انجام نمی‌دهد -->
                                  </div>
                                  <!-- دکمه باز شدن نوبت‌ها -->
                                  <div class="form-group position-relative {{ $autoScheduling ? '' : 'd-none' }}">
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
                                  <div class="form-group position-relative {{ $autoScheduling ? '' : 'd-none' }}">
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
                                        data-day="{{ $englishDay }}" data-index="{{ $index }}">
                                        <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                                      </button>
                                    </x-custom-tooltip>
                                  </div>
                                </div>
                              </div>
                            @endforeach
                          @else
                            <div class="mt-2 form-row d-flex w-100 pt-4 bg-active-slot border-radius-11"
                              data-slot-id="">
                              <div class="d-flex justify-content-start align-items-center gap-4">
                                <div class="form-group position-relative timepicker-ui">
                                  <label class="label-top-input-special-takhasos"
                                    for="morning-start-{{ $englishDay }}-0">از</label>
                                  <input type="text"
                                    class="form-control h-50 timepicker-ui-input text-center fw-bold font-size-13 start-time bg-white"
                                    data-timepicker id="morning-start-{{ $englishDay }}-0"
                                    wire:model.live="slots.{{ $englishDay }}.0.start_time"
                                    wire:change="autoSaveTimeSlot('{{ $englishDay }}', 0)" />
                                </div>
                                <div class="form-group position-relative timepicker-ui">
                                  <label class="label-top-input-special-takhasos"
                                    for="morning-end-{{ $englishDay }}-0">تا</label>
                                  <input type="text"
                                    class="form-control h-50 timepicker-ui-input text-center fw-bold font-size-13 end-time bg-white"
                                    data-timepicker id="morning-end-{{ $englishDay }}-0"
                                    wire:model.live="slots.{{ $englishDay }}.0.end_time"
                                    wire:change="autoSaveTimeSlot('{{ $englishDay }}', 0)" />
                                </div>
                                <div class="form-group position-relative">
                                  <label class="label-top-input-special-takhasos"
                                    for="morning-patients-{{ $englishDay }}-0">تعداد نوبت</label>
                                  <input type="text"
                                    class="form-control h-50 text-center max-appointments bg-white"
                                    id="morning-patients-{{ $englishDay }}-0"
                                    wire:model.live="slots.{{ $englishDay }}.0.max_appointments"
                                    wire:change="autoSaveTimeSlot('{{ $englishDay }}', 0)" x-data="{ day: '{{ $englishDay }}', index: '0' }"
                                    @click="$dispatch('open-modal', { name: 'calculator-modal', day: day, index: index })"
                                    readonly />
                                </div>
                                <!-- دکمه‌های غیرفعال برای ردیف خالی -->
                                <div class="form-group position-relative {{ $autoScheduling ? '' : 'd-none' }}">
                                  <x-custom-tooltip title="زمانبندی باز شدن نوبت‌ها" placement="top">
                                    <button type="button" class="btn text-black btn-sm schedule-btn" disabled>
                                      <img src="{{ asset('dr-assets/icons/open-time.svg') }}" alt="">
                                    </button>
                                  </x-custom-tooltip>
                                </div>
                                <div class="form-group position-relative {{ $autoScheduling ? '' : 'd-none' }}">
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
          <!-- دکمه ذخیره (همیشه نمایش داده می‌شود) -->
          @if ($showSaveButton)
            <div class="d-flex w-100 justify-content-end mt-3">
              <button type="button"
                class="btn my-btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
                id="save-work-schedule" wire:click="saveWorkSchedule">
                <span class="button_text">ذخیره</span>
                <div class="loader"></div>
              </button>
            </div>
          @elseif ($isActivationPage)
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
    <!-- مودال برای زمان های مخصوص منشی -->
    <div wire:ignore>
      <x-modal name="emergency-modal" title="زمان های مخصوص منشی" size="medium">
        <x-slot:body>
          <div class="emergency-times-container">
            <div class="d-flex flex-wrap gap-2 justify-content-center" id="emergency-times">
              <!-- زمان‌ها به‌صورت داینامیک با جاوااسکریپت اضافه می‌شن -->
            </div>
          </div>
          <div class="w-100 d-flex justify-content-end mt-3">
            <button type="button" id="saveEmergencyTimesBtn"
              class="btn my-btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
              wire:click="saveEmergencyTimes">
              <span class="button_text">ذخیره</span>
              <div class="loader"></div>
            </button>
          </div>
        </x-slot:body>
      </x-modal>
    </div>
    <!-- مودال تنظیم زمان‌بندی -->
    <div x-data="{
        handleDayToggle(event, day) {
            const checkbox = event.target;
            if (!checkbox.checked) { // User is trying to uncheck
                // Immediately revert the uncheck, pending confirmation
                checkbox.checked = true;
                showConfirmDialog('آیا مطمئن هستید؟', 'تنظیمات این روز حذف خواهد شد!', 'بله، حذف کن!', 'انصراف').then((result) => {
                    if (result.isConfirmed) {
                        @this.set(`selectedScheduleDays.${day}`, false);
                        @this.call('deleteScheduleSettingsForDay', day);
                    }
                });
            } else { // User is checking
                @this.set(`selectedScheduleDays.${day}`, true);
                @this.call('addScheduleSetting', day);
            }
        }
    }"
      @day-setting-deleted.window="
    const day = $event.detail.day;
    const checkbox = document.getElementById(`schedule-day-${day}`);
    if (checkbox) {
        checkbox.checked = false;
        @this.set(`selectedScheduleDays.${day}`, false);
    }
">
      @php
        $mainSlotStart = $slots[$scheduleModalDay][$scheduleModalIndex]['start_time'] ?? null;
        $mainSlotEnd = $slots[$scheduleModalDay][$scheduleModalIndex]['end_time'] ?? null;
        $mainSlotRange = $mainSlotStart && $mainSlotEnd ? "({$mainSlotStart} تا {$mainSlotEnd})" : '';
        $mainSlotDayFa =
            [
                'saturday' => 'شنبه',
                'sunday' => 'یکشنبه',
                'monday' => 'دوشنبه',
                'tuesday' => 'سه‌شنبه',
                'wednesday' => 'چهارشنبه',
                'thursday' => 'پنج‌شنبه',
                'friday' => 'جمعه',
            ][$scheduleModalDay] ?? '';
      @endphp
      <x-modal id="schedule-modal" name="schedule-modal" :show="$modalOpen"
        title="برنامه باز شدن نوبت‌های {{ $mainSlotDayFa }} {{ $mainSlotRange }}" size="md-medium">
        <x-slot:body>
          <div class="position-relative">
            <!-- لودینگ -->
            <div class="loading-overlay d-none" id="scheduleLoading" style="position: absolute;top:70px;right:50%;">
              <div class="spinner-border text-primary" style="border-radius: 50% !important" role="status">
                <span class="sr-only">در حال بارگذاری...</span>
              </div>
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
                        data-day="{{ $day }}" wire:model.live="selectedScheduleDays.{{ $day }}"
                        @click="handleDayToggle($event, '{{ $day }}')">
                      <label class="mb-0 fw-bold px-0 font-size-12"
                        for="schedule-day-{{ $day }}">{{ $label }}</label>
                    </div>
                  @endforeach
                </div>
              </div>
              <!-- بخش تنظیمات زمان‌بندی برای هر روز -->
              <!-- بخش تنظیمات زمان‌بندی برای هر روز -->
              <div class="schedule-settings-section border-section">
                <h6 class="section-title mb-2">باز شدن نوبت‌ها</h6>
                @foreach (['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day)
                  @if ($selectedScheduleDays[$day])
                    <div class="work-hours-{{ $day }} mb-2 border-333 p-2 border-radius-11">
                      <h6 class="mb-1 font-size-13">
                        {{ ['saturday' => 'شنبه', 'sunday' => 'یکشنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'][$day] . ' ' . 'ها' }}
                      </h6>
                      @if (!empty($scheduleSettings[$day]))
                        @foreach ($scheduleSettings[$day] as $index => $setting)
                          <div class="form-row d-flex align-items-center mb-1 bg-active-slot border-radius-11 p-3"
                            wire:key="setting-{{ $day }}-{{ $index }}-{{ $scheduleModalIndex }}">
                            <div class="form-group position-relative timepicker-ui">
                              <label class="label-top-input-special-takhasos font-size-11">از</label>
                              <input type="text"
                                class="form-control h-40 timepicker-ui-input text-center fw-bold font-size-12 start-time bg-white"
                                data-timepicker
                                wire:model.live="scheduleSettings.{{ $day }}.{{ $index }}.start_time"
                                wire:change="autoSaveSchedule('{{ $day }}', {{ $index }})"
                                value="{{ $setting['start_time'] ?? '' }}">
                            </div>
                            <div class="form-group position-relative timepicker-ui">
                              <label class="label-top-input-special-takhasos font-size-11">تا</label>
                              <input type="text"
                                class="form-control h-40 timepicker-ui-input text-center fw-bold font-size-12 end-time bg-white"
                                data-timepicker
                                wire:model.live="scheduleSettings.{{ $day }}.{{ $index }}.end_time"
                                wire:change="autoSaveSchedule('{{ $day }}', {{ $index }})"
                                value="{{ $setting['end_time'] ?? '' }}">
                            </div>
                            <div class="form-group position-relative">
                              <x-custom-tooltip title="کپی تنظیمات" placement="top">
                                <button class="my-btn btn-light btn-sm copy-schedule-setting p-1"
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
                                <button class="my-btn btn-light btn-sm delete-schedule-setting p-1"
                                  x-data="{ day: '{{ $day }}', index: '{{ $index }}' }"
                                  @click="showConfirmDialog('آیا مطمئن هستید؟', 'این تنظیم حذف خواهد شد و قابل بازگشت نیست!').then((result) => {if (result.isConfirmed) {@this.call('deleteScheduleSetting', day, index);}})"
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
                              data-timepicker wire:model.live="scheduleSettings.{{ $day }}.0.start_time"
                              wire:change="autoSaveSchedule('{{ $day }}', 0)">
                          </div>
                          <div class="form-group position-relative timepicker-ui">
                            <label class="label-top-input-special-takhasos font-size-11">تا</label>
                            <input type="text"
                              class="form-control h-40 timepicker-ui-input text-center fw-bold font-size-12 end-time bg-white"
                              data-timepicker wire:model.live="scheduleSettings.{{ $day }}.0.end_time"
                              wire:change="autoSaveSchedule('{{ $day }}', 0)">
                          </div>
                          <div class="form-group position-relative">
                            <x-custom-tooltip title="کپی تنظیمات" placement="top">
                              <button class="my-btn btn-light btn-sm copy-schedule-setting p-1" disabled>
                                <img src="{{ asset('dr-assets/icons/copy.svg') }}" alt="کپی"
                                  style="width: 14px; height: 14px;">
                              </button>
                            </x-custom-tooltip>
                          </div>
                          <div class="form-group position-relative">
                            <x-custom-tooltip title="حذف تنظیمات" placement="top">
                              <button class="my-btn btn-outline-danger btn-sm delete-schedule-setting p-1" disabled>
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
              @if ($day !== ($copySourceDay ?? null))
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
              wire:click="copyScheduleSetting(false)" id="copy-btn">
              ذخیره
              <div class="loader"></div>
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
                    id="appointment-count" style="height: 50px;">
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
                    id="time-count" style="height: 50px;">
                  <span class="input-group-text px-2">دقیقه</span>
                </div>
              </div>
            </div>
          </div>
          <div class="w-100 d-flex justify-content-end p-1 gap-4 mt-3">
            <button type="button" class="btn my-btn-primary w-100 d-flex justify-content-center align-items-center"
              id="saveSelectionCalculator" style="height: 50px;">
              <span class="button_text">ذخیره</span>
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
              @if ($day !== ($copySource['day'] ?? null))
                <div class="form-check d-flex align-items-center" data-day="{{ $day }}">
                  <input type="checkbox" class="form-check-input me-2" id="day-{{ $day }}"
                    wire:model.live="selectedDays.{{ $day }}" data-day="{{ $day }}">
                  <label class="fw-bold mb-0" for="day-{{ $day }}">{{ $label }}</label>
                </div>
              @endif
            @endforeach
          </div>
          <div class="mt-3">
            <button type="button" class="btn my-btn-primary h-50 w-100" wire:click="copySchedule">
              <span class="button_text">ذخیره</span>
              <div class="loader"></div>
            </button>
          </div>
        </x-slot:body>
      </x-modal>
    </div>
    <script>
      // CSS برای مخفی کردن دکمه‌های اضافی SweetAlert
      const style = document.createElement('style');
      style.textContent = `
        .swal2-deny {
          display: none !important;
        }
        .swal2-close {
          display: none !important;
        }
      `;
      document.head.appendChild(style);

      // تابع کمکی برای تنظیمات یکسان SweetAlert
      function showConfirmDialog(title, text, confirmText = 'بله، حذف کن!', cancelText = 'خیر', isHtml = false) {
        const config = {
          title: title,
          showCancelButton: true,
          showConfirmButton: true,
          showDenyButton: false,
          showCloseButton: false,
          allowOutsideClick: false,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: confirmText,
          cancelButtonText: cancelText,
          reverseButtons: true
        };

        if (isHtml) {
          config.html = text;
        } else {
          config.text = text;
        }

        return Swal.fire(config);
      }

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
              // مقداردهی اولیه فقط از اسلات
              $appointmentCount.val(maxAppointments ? parseInt(maxAppointments) : '');
              $timeCount.val(maxAppointments && maxAppointments > 0 ? Math.floor(totalMinutes / maxAppointments) :
                '');
              $appointmentCount.prop('disabled', false);
              $timeCount.prop('disabled', true);
              $countRadio.prop('checked', true);
              let isUpdating = false;
              // فقط مقدار مقابل را برای نمایش محاسبه کن، مقدار اصلی را هرگز تغییر نده
              $appointmentCount.off('input').on('input', function() {
                if (isUpdating) return;
                isUpdating = true;
                const count = parseInt(this.value);
                if (count && !isNaN(count) && count > 0) {
                  const timePerAppointment = Math.floor(totalMinutes / count);
                  if (timePerAppointment < 5) {
                    toastr.error('تعداد نوبت‌ها بیش از حد مجاز است. حداقل زمان هر نوبت باید ۵ دقیقه باشد.');
                    $timeCount.val('');
                  } else {
                    $timeCount.val(timePerAppointment);
                  }
                } else {
                  $timeCount.val('');
                }
                isUpdating = false;
              });
              // مقدار مقابل فقط برای نمایش، مقدار اصلی هرگز تغییر نکند
              $timeCount.off('input').on('input', function() {
                if (isUpdating) return;
                isUpdating = true;
                const time = parseInt(this.value);
                if (time && !isNaN(time) && time > 0) {
                  if (time < 5) {
                    toastr.error('حداقل زمان هر نوبت باید ۵ دقیقه باشد.');
                    $timeCount.val('');
                  } else {
                    // فقط مقدار مقابل را برای نمایش محاسبه کن
                    const appointmentCount = Math.floor(totalMinutes / time);
                    // فقط نمایش، مقدار اصلی تغییر نکند
                  }
                }
                isUpdating = false;
              });
              // جلوگیری از هرگونه تغییر غیرمستقیم مقدار اصلی
              $appointmentCount.on('blur', function() {
                // هیچ کاری نکن، مقدار فقط همان است که کاربر تایپ کرده
              });
              $appointmentCount.on('focus', function() {
                // هیچ کاری نکن
              });
              // ذخیره فقط با مقدار تایپ‌شده کاربر
              $('#saveSelectionCalculator').off('click').on('click', function() {
                // مقدار تایپ‌شده کاربر را دوباره در input قرار بده تا هیچ sync یا event مقدار را تغییر ندهد
                const userTyped = $appointmentCount.data('userTyped') || $appointmentCount.val();
                $appointmentCount.val(userTyped);
                const count = parseInt($appointmentCount.val());
                const time = parseInt($timeCount.val());
                // لاگ برای دیباگ
                if (!count || !time || time < 5) {
                  toastr.error('تعداد نوبت یا زمان هر نوبت نامعتبر است یا کمتر از ۵ دقیقه است.');
                  return;
                }
                // مقدار زمان شروع و پایان را از inputها بگیر
                const startTime = $(`#morning-start-${day}-${index}`).val();
                const endTime = $(`#morning-end-${day}-${index}`).val();
                @this.call('saveCalculator', day, index, count, time, startTime, endTime).then(() => {
                  $(`#morning-patients-${day}-${index}`).val(count);
                });
              });
              // هر بار که کاربر تایپ می‌کند، مقدار را در data-userTyped ذخیره کن
              $appointmentCount.off('input').on('input', function() {
                if (isUpdating) return;
                isUpdating = true;
                $(this).data('userTyped', this.value);
                const count = parseInt(this.value);
                if (count && !isNaN(count) && count > 0) {
                  const timePerAppointment = Math.floor(totalMinutes / count);
                  if (timePerAppointment < 5) {
                    toastr.error('تعداد نوبت‌ها بیش از حد مجاز است. حداقل زمان هر نوبت باید ۵ دقیقه باشد.');
                    $timeCount.val('');
                  } else {
                    $timeCount.val(timePerAppointment);
                  }
                } else {
                  $timeCount.val('');
                }
                isUpdating = false;
              });
              // اطمینان از بسته شدن مودال با event
              Livewire.on('close-modal', (event) => {
                const modalName = event?.name || (event && event[0]?.name) || null;
                if (modalName === 'calculator-modal') {
                  window.dispatchEvent(new CustomEvent('close-modal', {
                    detail: {
                      name: 'calculator-modal'
                    }
                  }));
                  if (window.$ && $('#calculator-modal').length) {
                    $('#calculator-modal').modal('hide');
                  }
                }
              });
            } catch (error) {
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

              // Immediately hide the source day in the modal
              setTimeout(() => {
                $(`#day-checkboxes .form-check[data-day="${day}"]`).hide();
              }, 100);
            } catch (error) {
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
              const autoScheduling = @this.get('autoScheduling');
              const $saveButton = $('#saveEmergencyTimesBtn');
              $saveButton.prop('disabled', !autoScheduling);
              let currentEmergencyTimes = [];
              try {
                const workSchedule = @this.get('workSchedules').find(s => s.day === day);
                currentEmergencyTimes = workSchedule && workSchedule.emergency_times ? workSchedule
                  .emergency_times : [];
              } catch (error) {
                currentEmergencyTimes = [];
              }
              @this.set('emergencyTimes', currentEmergencyTimes);
              const $timesContainer = $('#emergency-times');
              $timesContainer.empty();
              times.forEach(time => {
                const isSaved = currentEmergencyTimes.includes(time);
                const buttonClass = autoScheduling ? (isSaved ? 'btn-primary' : 'btn-outline-primary') :
                  'btn-primary';
                const disabledAttr = !autoScheduling ? 'disabled' : '';
                const $button = $(`
        <button type="button" class="btn btn-sm time-slot-btn ${buttonClass}" data-time="${time}" ${disabledAttr}>
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
            } catch (error) {
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
              @this.call('openCopyScheduleModal', day, index);
              @this.set('copySourceDay', day);
              @this.set('copySourceIndex', index);
              @this.set('selectedCopyScheduleDays', {
                'saturday': false,
                'sunday': false,
                'monday': false,
                'tuesday': false,
                'wednesday': false,
                'thursday': false,
                'friday': false
              });
              @this.set('selectAllCopyScheduleModal', false);
            } catch (error) {
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
            const day = $(this).data('day');
            const index = $(this).data('index');
            if ($(this).is(':disabled')) return;
            showConfirmDialog('آیا مطمئن هستید؟', 'این ساعت کاری حذف خواهد شد و قابل بازگشت نیست!').then((
              result) => {
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
                toastr.error('خطا در ذخیره زمان‌بندی: ' + (error.message || 'خطای ناشناخته'));
              });
            } catch (error) {
              toggleButtonLoading($button, false);
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
              @this.set('selectedCopyScheduleDays', {
                'saturday': false,
                'sunday': false,
                'monday': false,
                'tuesday': false,
                'wednesday': false,
                'thursday': false,
                'friday': false
              });
              @this.set('selectAllCopyScheduleModal', false);

              // بستن مودال و پاک کردن backdrop
              setTimeout(() => {
                if (window.$ && $('#copy-schedule-modal').length) {
                  $('#copy-schedule-modal').modal('hide');
                }
                // حذف backdrop های باقی‌مانده
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
              }, 100);
            }
          });
          Livewire.on('show-conflict-alert', (event) => {
            let conflictsObj = Array.isArray(event) && event[0] && event[0].conflicts ? event[0].conflicts :
              event.conflicts || event;
            if (!conflictsObj || typeof conflictsObj !== 'object' || Object.keys(conflictsObj).length === 0) {
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
                return;
              }
              const conflictDetails = conflictsObj[day];
              if (!conflictDetails || !Array.isArray(conflictDetails)) {
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
              return;
            }
            conflictMessage += '<p>آیا می‌خواهید داده‌های موجود را جایگزین کنید؟</p>';
            showConfirmDialog('تداخل در کپی برنامه کاری', conflictMessage, 'جایگزین کن', 'لغو', true).then((
              result) => {
              if (result.isConfirmed) {
                @this.call('copySchedule', true);
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
                return;
              }
              const conflictDetails = conflictsObj[day];
              if (!conflictDetails || (!conflictDetails.work_hours && !conflictDetails.emergency_times)) {
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
              return;
            }
            conflictMessage += '<p>آیا می‌خواهید داده‌های موجود را جایگزین کنید؟</p>';
            showConfirmDialog('تداخل در کپی برنامه کاری', conflictMessage, 'جایگزین کن', 'لغو', true).then((
              result) => {
              if (result.isConfirmed) {
                @this.call('copySchedule', true);
              } else {
                @this.set('modalMessage', 'عملیات کپی لغو شد');
                @this.set('modalType', 'error');
                @this.set('modalOpen', true);
              }
            });
          });
          // --- Close modals after copy or emergency save ---
          Livewire.on('close-checkbox-modal', () => {
            window.dispatchEvent(new CustomEvent('close-modal', {
              detail: {
                name: 'checkbox-modal'
              }
            }));
            if (window.$ && $('#checkbox-modal').length) {
              $('#checkbox-modal').modal('hide');
            }
          });
          Livewire.on('close-emergency-modal', () => {
            @this.set('isEmergencyModalOpen', false);
            window.dispatchEvent(new CustomEvent('close-modal', {
              detail: {
                name: 'emergency-modal'
              }
            }));
            if (window.$ && $('#emergency-modal').length) {
              $('#emergency-modal').modal('hide');
            }
          });
          Livewire.on('close-modal', (event) => {
            const modalName = event?.name || (event && event[0]?.name) || null;
            if (modalName === 'copy-schedule-modal') {
              @this.set('copySourceDay', null);
              @this.set('copySourceIndex', null);
              @this.set('selectedCopyScheduleDays', {
                'saturday': false,
                'sunday': false,
                'monday': false,
                'tuesday': false,
                'wednesday': false,
                'thursday': false,
                'friday': false
              });
              @this.set('selectAllCopyScheduleModal', false);

              // بستن مودال و پاک کردن backdrop
              setTimeout(() => {
                if (window.$ && $('#copy-schedule-modal').length) {
                  $('#copy-schedule-modal').modal('hide');
                }
                // حذف backdrop های باقی‌مانده
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
              }, 100);
            }
          });

          // اضافه کردن event listener برای refresh-work-hours
          Livewire.on('refresh-work-hours', () => {
            setTimeout(() => {
              // اطمینان از پاک شدن backdrop ها
              $('.modal-backdrop').remove();
              $('body').removeClass('modal-open').css('padding-right', '');

              // رفرش UI بدون reload
              @this.call('refreshWorkSchedules');
              @this.dispatch('refresh-clinic-data');
            }, 300);
          });

          // اضافه کردن event listener برای refresh-schedule-settings
          Livewire.on('refresh-schedule-settings', () => {
            setTimeout(() => {
              // اطمینان از پاک شدن backdrop ها
              $('.modal-backdrop').remove();
              $('body').removeClass('modal-open').css('padding-right', '');
            }, 200);
          });

          // اضافه کردن event listener برای اطمینان از بسته شدن مودال‌ها
          $(document).on('hidden.bs.modal', '.modal', function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
          });

          // بررسی و پاک کردن backdrop های اضافی هر 2 ثانیه
          setInterval(() => {
            if (!$('.modal.show').length) {
              $('.modal-backdrop').remove();
              $('body').removeClass('modal-open').css('padding-right', '');
            }
          }, 2000);
        });
        window.addEventListener('auto-scheduling-changed', event => {
          const isEnabled = event.detail.isEnabled;
          const $modal = $('#emergency-times').closest('.modal');
          if ($modal.is(':visible')) {
            const $timeSlotButtons = $modal.find('.time-slot-btn');
            const $saveButton = $('#saveEmergencyTimesBtn');
            $saveButton.prop('disabled', !isEnabled);
            $timeSlotButtons.each(function() {
              const $btn = $(this);
              $btn.prop('disabled', !isEnabled);
              if (isEnabled) {
                const time = $btn.data('time');
                const isSaved = @this.get('emergencyTimes').includes(time);
                $btn.removeClass('btn-primary btn-outline-primary').addClass(isSaved ? 'btn-primary' :
                  'btn-outline-primary');
              } else {
                $btn.removeClass('btn-outline-primary').addClass('btn-primary');
              }
            });
          }
        });
      });
    </script>
  </div>
</div>
</div>
