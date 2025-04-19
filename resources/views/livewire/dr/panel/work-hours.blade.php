<div>
  <div>
    <div class="w-100 d-flex justify-content-center" dir="ltr">
      <div class="auto-scheule-content-top">
        <x-my-toggle-appointment :isChecked="$autoScheduling" id="appointment-toggle" day="نوبت دهی خودکار" class="mt-3"
          wire:model="autoScheduling" wire:change="updateAutoScheduling" />
      </div>
    </div>
    <div class="workhours-content w-100 d-flex justify-content-center mt-4 ">
      <div class="workhours-wrapper-content p-3 {{ $autoScheduling ? '' : 'd-none' }}">
        <div>
          <div>
            <div>
              <div>
                <div class="input-group position-relative">
                  <label class="label-top-input-special-takhasos"> تعداد روز های باز تقویم </label>
                  <input type="number" value="{{ $calendarDays ?? '' }}"
                    class="form-control text-center h-50 border-radius-0" name="calendar_days"
                    placeholder="تعداد روز مورد نظر خود را وارد کنید">
                  <div class="input-group-append count-span-prepand-style border-radius-0"><span
                      class="input-group-text px-2">روز</span>
                  </div>
                </div>
                <div class="mt-3">
                  <x-my-check :isChecked="$holidayAvailability" id="posible-appointments-inholiday"
                    day="باز بودن مطب در تعطیلات رسمی" />
                </div>
              </div>
              <div class="mt-4">
                <label class="text-dark font-weight-bold">روزهای کاری</label>
                <div
                  class="d-flex flex-wrap justify-content-start mt-3 gap-40 bg-light p-3 border-radius-4 day-contents align-items-center h-55">
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
                                class="Log::mt-3 form-row d-flex justify-content-between w-100 pt-4 bg-active-slot border-radius-4"
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
                                  <div class="form-group position-relative">
                                    <button class="btn btn-light btn-sm copy-single-slot-btn" data-toggle="modal"
                                      data-target="#checkboxModal" data-day="{{ $englishDay }}"
                                      data-index="{{ $index }}"
                                      {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                      <img src="{{ asset('dr-assets/icons/copy.svg') }}" alt="کپی">
                                    </button>
                                  </div>
                                  <div class="form-group position-relative">
                                    <button class="btn btn-light btn-sm remove-row-btn"
                                      {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                      <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                                    </button>
                                  </div>
                                </div>
                                <div class="d-flex align-items-center">
                                  <button type="button" class="btn text-black btn-sm btn-outline-primary schedule-btn"
                                    data-toggle="modal" data-target="#scheduleModal" data-day="{{ $englishDay }}"
                                    data-index="{{ $index }}"
                                    {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                    زمانبندی باز شدن نوبت‌ها
                                  </button>
                                </div>
                              </div>
                            @endforeach
                          @else
                            <div
                              class="Log::mt-3 form-row d-flex justify-content-between w-100 pt-4 bg-active-slot border-radius-4"
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
                                <div class="form-group position-relative">
                                  <button class="btn btn-light btn-sm copy-single-slot-btn" data-toggle="modal"
                                    data-target="#checkboxModal" data-day="{{ $englishDay }}"
                                    data-index="{{ $index }}"
                                    {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                    <img src="{{ asset('dr-assets/icons/copy.svg') }}" alt="کپی">
                                  </button>
                                </div>
                                <div class="form-group position-relative">
                                  <button class="btn btn-light btn-sm remove-row-btn"
                                    {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                    <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                                  </button>
                                </div>
                              </div>
                              <div class="d-flex align-items-center">
                                <button type="button" class="btn text-black btn-sm btn-outline-primary schedule-btn"
                                  data-toggle="modal" data-target="#scheduleModal" data-day="{{ $englishDay }}"
                                  data-index="{{ $index }}"
                                  {{ empty($slot['start_time']) || empty($slot['end_time']) || empty($slot['max_appointments']) ? 'disabled' : '' }}>
                                  زمانبندی باز شدن نوبت‌ها
                                </button>
                              </div>
                            </div>
                          @endif
                          <div class="add-new-row mt-3">
                            <button class="add-row-btn btn btn-sm my-btn-primary"
                              wire:click="addSlot('{{ $englishDay }}')">
                              <span>+</span>
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
            <button type="submit"
              class="btn my-btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
              id="save-work-schedule">
              <span class="button_text">ذخیره تغیرات</span>
              <div class="loader"></div>
            </button>
          </div>
          <hr>
          @if (isset($_GET['activation-path']) && $_GET['activation-path'] == true)
            <div class="w-100 mt-3">
              <button class="btn btn-success w-100 h-50" tabindex="0" type="button" id=":rs:"
                data-toggle="modal" data-target="#activation-modal">پایان فعالسازی<span></span></button>
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
  <div class="modal fade" id="scheduleModal" tabindex="-1" data-selected-day="" role="dialog"
    aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered my-modal-lg" role="document">
      <div class="modal-content border-radius-6">
        <div class="modal-header border-radius-6">
          <h6 class="modal-title font-weight-bold" id="scheduleModalLabel">برنامه زمانبندی</h6>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body position-relative">
          <!-- اضافه کردن لودینگ -->
          <div class="loading-overlay d-none" id="scheduleLoading">
            <div class="spinner-border text-primary" role="status">
              <span class="sr-only">در حال بارگذاری...</span>
            </div>
            <p class="mt-2">در حال بارگذاری...</p>
          </div>
          <!-- محتوای اصلی که ابتدا مخفی است -->
          <div class="modal-content-inner" style="display: none;">
            <div class="">
              <div class="">
                <label class="font-weight-bold text-dark">روزهای کاری</label>
                <div class="mt-2 d-flex flex-wrap gap-10 justify-content-start my-768px-styles-day-and-times">
                  <div class="" tabindex="0" role="button"><span class="badge-time-styles-day"
                      data-day="saturday">شنبه</span><span class=""></span></div>
                  <div class="" tabindex="0" role="button"><span class="badge-time-styles-day"
                      data-day="sunday">یکشنبه</span><span class=""></span></div>
                  <div class="" tabindex="0" role="button"><span class="badge-time-styles-day"
                      data-day="monday">دوشنبه</span><span class=""></span></div>
                  <div class="" tabindex="0" role="button"><span class="badge-time-styles-day"
                      data-day="tuesday">سه‌شنبه</span><span class=""></span></div>
                  <div class="" tabindex="0" role="button"><span class="badge-time-styles-day"
                      data-day="wednesday">چهارشنبه</span><span class=""></span></div>
                  <div class="" tabindex="0" role="button"><span class="badge-time-styles-day"
                      data-day="thursday">پنج‌شنبه</span><span class=""></span></div>
                  <div class="" tabindex="0" role="button"><span class="badge-time-styles-day"
                      data-day="friday">جمعه</span><span class=""></span></div>
                </div>
              </div>
            </div>
            <div class="w-100 d-flex mt-4 gap-4 justify-content-center">
              <div class="form-group position-relative timepicker-ui">
                <label class="label-top-input-special-takhasos">شروع</label>
                <input type="text"
                  class="form-control h-50 timepicker-ui-input text-center font-weight-bold font-size-13"
                  id="schedule-start" value="00:00">
              </div>
              <div class="form-group position-relative timepicker-ui">
                <label class="label-top-input-special-takhasos">پایان</label>
                <input type="text"
                  class="form-control h-50 timepicker-ui-input text-center font-weight-bold font-size-13"
                  id="schedule-end" value="23:59">
              </div>
            </div>
            <div class="w-100 d-flex justify-content-end mt-3">
              <button type="submit"
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

  <!-- مودال checkboxModal -->
  <div class="modal fade" id="checkboxModal" tabindex="-1" role="dialog" aria-labelledby="checkboxModalLabel"
    aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-radius-6">
          <h5 class="modal-title" id="checkboxModalLabel">انتخاب روزها برای کپی</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="select-all-copy-modal"
              wire:model.live="selectAllCopyModal">
            <label class="form-check-label" for="select-all-copy-modal">انتخاب همه</label>
          </div>
          @foreach (['saturday' => 'شنبه', 'sunday' => 'یکشنبه', 'monday' => 'دوشنبه', 'tuesday' => 'سه‌شنبه', 'wednesday' => 'چهارشنبه', 'thursday' => 'پنج‌شنبه', 'friday' => 'جمعه'] as $englishDay => $persianDay)
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="{{ $englishDay }}-copy-modal"
                wire:model.live="selectedCopyDays.{{ $englishDay }}">
              <label class="form-check-label" for="{{ $englishDay }}-copy-modal">{{ $persianDay }}</label>
            </div>
          @endforeach
        </div>
        <div class="modal-footer">
          <button type="button" class="btn my-btn-primary h-50 w-100" wire:click="copySlot">ذخیره</button>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('livewire:initialized', () => {
    $(document).ready(function() {
      // غیرفعال کردن رندر مجدد غیرضروری هنگام باز شدن مودال
      Livewire.on('refresh-work-hours', () => {
        initializeTimepicker();
      });

      Livewire.on('refresh-timepicker', () => {
        setTimeout(() => {
          initializeTimepicker();
        }, 100);
      });

      // بستن مودال بعد از ذخیره
      Livewire.on('close-calculator-modal', () => {
        $('#CalculatorModal').modal('hide');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
      });

      // مقداردهی اولیه timepicker
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

      // فراخوانی اولیه timepicker
      initializeTimepicker();

      // مدیریت کلیک روی دکمه حذف با SweetAlert2
      $(document).on('click', '.remove-row-btn', function(e) {
        e.preventDefault();
        const day = $(this).closest('[data-slot-id]').find('.schedule-btn').data('day');
        const index = $(this).closest('[data-slot-id]').find('.schedule-btn').data('index');

        // بررسی اینکه دکمه غیرفعال نیست
        if ($(this).is(':disabled')) {
          return;
        }

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
            @this.call('removeSlot', day, index);
          }
        });
      });

      // مدیریت باز شدن مودال CalculatorModal
      $(document).on('show.bs.modal', '#CalculatorModal', function(e) {
        const $modal = $(this);
        const button = $(e.relatedTarget);
        const day = button.data('day');
        const index = button.data('index');

        @this.set('calculator.day', day);
        @this.set('calculator.index', index);

        const startTime = $(`#morning-start-${day}-${index}`).val();
        const endTime = $(`#morning-end-${day}-${index}`).val();

        if (!startTime || !endTime) {
          console.error('start_time or end_time is empty', {
            startTime,
            endTime
          });
          @this.set('modalMessage', 'لطفاً ابتدا زمان شروع و پایان را وارد کنید');
          @this.set('modalType', 'error');
          @this.set('modalOpen', true);
          @this.dispatch('show-toastr', {
            message: 'لطفاً ابتدا زمان شروع و پایان را وارد کنید',
            type: 'error'
          });
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
          console.error('Invalid time range', {
            startTime,
            endTime
          });
          @this.set('modalMessage', 'زمان پایان باید بعد از زمان شروع باشد');
          @this.set('modalType', 'error');
          @this.set('modalOpen', true);
          @this.dispatch('show-toastr', {
            message: 'زمان پایان باید بعد از زمان شروع باشد',
            type: 'error'
          });
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
      });

      // مدیریت بسته شدن مودال
      $(document).on('hidden.bs.modal', '#CalculatorModal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
        $('#appointment-count').off('input focus');
        $('#time-count').off('input focus');
        $('#count-radio').off('change');
        $('#time-radio').off('change');
      });

      // مدیریت باز شدن مودال checkboxModal
      $(document).on('show.bs.modal', '#checkboxModal', function(e) {
        const button = $(e.relatedTarget);
        const day = button.data('day');
        const index = button.data('index');

        @this.set('copySource.day', day);
        @this.set('copySource.index', index);
      });
    });
  });
</script>

</div>
