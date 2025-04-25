<div>
  @php
    use Morilog\Jalali\Jalalian;
    use Carbon\Carbon;
  @endphp
  <div class="d-flex justify-content-center top-s-wrapper flex-wrap">
    <div class="calendar-and-add-sick-section p-3">
      <div class="d-flex justify-content-between gap-10 align-items-center c-a-wrapper">
        <div>
          <div class="turning_selectDate__MLRSb">
            <button
              class="selectDate_datepicker__xkZeS cursor-pointer text-center h-50 bg-light-blue d-flex justify-content-center align-items-center"
              wire:click="$dispatch('showModal', {data: {'alias': 'mini-calendar-modal'}})">
              <div class="d-flex align-items-center">
                <span class="mx-1">{{ Jalalian::fromCarbon(Carbon::parse($selectedDate))->format('Y/m/d') }}</span>
                <img src="{{ asset('dr-assets/icons/calendar.svg') }}" alt="" srcset="">
              </div>
            </button>
          </div>
        </div>
        <div>
          <div class="turning_filterWrapper__2cOOi">
            <div class="turning_search-wrapper__loGVc">
              <input type="text" class="my-form-control" placeholder="نام بیمار، شماره موبایل یا کد ملی ..."
                wire:model.live.debounce.500ms="searchQuery">
            </div>
          </div>
        </div>
        <div class="btn-425-left">
          <button class="btn my-btn-primary h-50 fs-13"
            wire:click="$dispatch('showModal', {data: {'alias': 'add-sick-modal'}})">ثبت نوبت دستی</button>
        </div>
      </div>
    </div>
    <div wire:ignore class="w-100">
      <x-jalali-calendar-row />
    </div>
    <div class="sicks-content h-100 w-100 position-relative border">
      <div>
        <div class="table-responsive position-relative top-table w-100" wire:ignore.self>
          <table class="table table-hover w-100 text-sm text-center bg-white shadow-sm rounded">
            <thead class="bg-light" wire:ignore>
              <tr>
                <th>
                  <input class="form-check-input" type="checkbox" id="select-all-row">
                </th>
                <th scope="col" class="px-6 py-3 fw-bolder">نام بیمار</th>
                <th scope="col" class="px-6 py-3 fw-bolder">شماره‌موبایل</th>
                <th scope="col" class="px-6 py-3 fw-bolder">کد ملی</th>
                <th scope="col" class="px-6 py-3 fw-bolder">تاریخ نوبت</th>
                <th scope="col" class="px-6 py-3 fw-bolder">زمان نوبت</th>
                <th scope="col" class="px-6 py-3 fw-bolder">وضعیت نوبت</th>
                <th scope="col" class="px-6 py-3 fw-bolder">وضعیت پرداخت</th>
                <th scope="col" class="px-6 py-3 fw-bolder">بیمه</th>
                <th scope="col" class="px-6 py-3 fw-bolder">پایان ویزیت</th>
                <th scope="col" class="px-6 py-3 fw-bolder">عملیات</th>
              </tr>
            </thead>
            <tbody>
              @if (count($appointments) > 0)
                @foreach ($appointments as $appointment)
                  <tr>
                    <td>
                      <input type="checkbox" class="appointment-checkbox form-check-input"
                        value="{{ $appointment->id }}" data-status="{{ $appointment->status }}"
                        data-mobile="{{ $appointment->patient->mobile ?? '' }}">
                    </td>
                    <td class="fw-bold">
                      {{ $appointment->patient ? $appointment->patient->first_name . ' ' . $appointment->patient->last_name : '-' }}
                    </td>
                    <td>{{ $appointment->patient ? $appointment->patient->mobile : '-' }}</td>
                    <td>{{ $appointment->patient ? $appointment->patient->national_code : '-' }}</td>
                    <td>{{ Jalalian::fromCarbon(Carbon::parse($appointment->appointment_date))->format('Y/m/d') }}</td>
                    <td>{{ $appointment->appointment_time->format('H:i') ?? '-' }}</td>
                    <td>
                      @php
                        $statusLabels = [
                            'scheduled' => ['label' => 'در انتظار', 'class' => 'text-primary'],
                            'attended' => ['label' => 'ویزیت شده', 'class' => 'text-success'],
                            'cancelled' => ['label' => 'لغو شده', 'class' => 'text-danger'],
                            'missed' => ['label' => 'عدم حضور', 'class' => 'text-warning'],
                            'pending_review' => ['label' => 'در انتظار بررسی', 'class' => 'text-secondary'],
                        ];
                        $status = $appointment->status ?? 'scheduled';
                        $statusInfo = $statusLabels[$status] ?? ['label' => 'نامشخص', 'class' => 'text-muted'];
                      @endphp
                      <span class="{{ $statusInfo['class'] }} fw-bold">{{ $statusInfo['label'] }}</span>
                    </td>
                    <td>
                      @php
                        $paymentStatusLabels = [
                            'paid' => ['label' => 'پرداخت شده', 'class' => 'text-success'],
                            'unpaid' => ['label' => 'پرداخت نشده', 'class' => 'text-danger'],
                            'pending' => ['label' => 'در انتظار پرداخت', 'class' => 'text-primary'],
                        ];
                        $paymentStatus = $appointment->payment_status;
                        $paymentStatusInfo = $paymentStatusLabels[$paymentStatus] ?? [
                            'label' => 'نامشخص',
                            'class' => 'text-muted',
                        ];
                      @endphp
                      <span class="{{ $paymentStatusInfo['class'] }} fw-bold">{{ $paymentStatusInfo['label'] }}</span>
                    </td>
                    <td>{{ $appointment->insurance ? $appointment->insurance->name : '-' }}</td>
                    <td>
                      @if ($appointment->status !== 'attended' && $appointment->status !== 'cancelled')
                        <button class="btn btn-sm btn-primary shadow-sm end-visit-btn"
                          wire:click="$dispatch('showModal', {data: {'alias': 'end-visit-modal', 'params': {'appointmentId': {{ $appointment->id }}}})">پایان
                          ویزیت</button>
                      @else
                        -
                      @endif
                    </td>
                    <td>
                      <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-light rounded-circle shadow-sm reschedule-btn" data-bs-toggle="tooltip"
                          data-bs-placement="top" title="جابجایی نوبت"
                          wire:click="$dispatch('showModal', {data: {'alias': 'reschedule-modal', 'params': {'appointmentId': {{ $appointment->id }}}})"
                          {{ $appointment->status === 'cancelled' || $appointment->status === 'attended' ? 'disabled' : '' }}>
                          <img src="{{ asset('dr-assets/icons/rescheule-appointment.svg') }}" alt="جابجایی">
                        </button>
                        <button class="btn btn-light rounded-circle shadow-sm cancel-btn" data-bs-toggle="tooltip"
                          data-bs-placement="top" title="لغو نوبت"
                          wire:click="cancelSingleAppointment({{ $appointment->id }})"
                          {{ $appointment->status === 'cancelled' || $appointment->status === 'attended' ? 'disabled' : '' }}>
                          <img src="{{ asset('dr-assets/icons/cancle-appointment.svg') }}" alt="حذف">
                        </button>
                        <button class="btn btn-light rounded-circle shadow-sm block-btn" data-bs-toggle="tooltip"
                          data-bs-placement="top" title="مسدود کردن کاربر"
                         wire:click="$dispatch('showModal', {data: {alias: 'block-user-modal', params: {appointmentId: 5}}})">
                          <img src="{{ asset('dr-assets/icons/block-user.svg') }}" alt="مسدود کردن">
                        </button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td colspan="11" class="text-center">نتیجه‌ای یافت نشد</td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
      <div class="d-flex justify-content-start gap-10 nobat-option">
        <div class="d-flex align-items-center m-2 gap-4">
          <div class="turning_filterWrapper__2cOOi">
            <div class="dropdown">
              <button class="btn btn-light dropdown-toggle h-30 fs-13" type="button" id="filterDropdown"
                data-bs-toggle="dropdown" aria-expanded="false">
                فیلتر
              </button>
              <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', '')">همه نوبت‌ها</a></li>
                <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', 'scheduled')">در انتظار</a>
                </li>
                <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', 'cancelled')">لغو شده</a>
                </li>
                <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', 'attended')">ویزیت
                    شده</a></li>
                <li><a class="dropdown-item" href="#" wire:click="$set('dateFilter', 'current_week')">هفته
                    جاری</a></li>
                <li><a class="dropdown-item" href="#" wire:click="$set('dateFilter', 'current_month')">ماه
                    جاری</a></li>
                <li><a class="dropdown-item" href="#" wire:click="$set('dateFilter', 'current_year')">سال
                    جاری</a></li>
              </ul>
            </div>
          </div>
          <button id="cancel-appointments-btn"
            class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center shadow-sm" disabled>
            <img src="{{ asset('dr-assets/icons/cancle-appointment.svg') }}" alt="" srcset="">
            <span class="d-none d-md-block mx-1">لغو نوبت</span>
          </button>
          <button id="move-appointments-btn"
            class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center shadow-sm"
            wire:click="$dispatch('showModal', {data: {'alias': 'reschedule-modal'}})" disabled>
            <img src="{{ asset('dr-assets/icons/rescheule-appointment.svg') }}" alt="" srcset="">
            <span class="d-none d-md-block mx-1">جابجایی نوبت</span>
          </button>
          <button id="block-users-btn"
            class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center shadow-sm"
            wire:cliick="$dispatch('showModal', {data: {'alias': 'block-multiple-users-modal'}})" disabled>
            <img src="{{ asset('dr-assets/icons/block-user.svg') }}" alt="" srcset="">
            <span class="d-none d-md-block mx-1">مسدود کردن کاربر</span>
          </button>
        </div>
      </div>
    </div>
    <div class="pagination-container mt-3 d-flex justify-content-center">
      <nav aria-label="Page navigation">
        <ul class="pagination" id="pagination-links">
          @if ($pagination['current_page'] > 1)
            <li class="page-item">
              <a class="page-link" href="#" wire:click="previousPage" wire:loading.attr="disabled">قبلی</a>
            </li>
          @else
            <li class="page-item disabled">
              <span class="page-link">قبلی</span>
            </li>
          @endif
          @php
            $startPage = max(1, $pagination['current_page'] - 2);
            $endPage = min($pagination['last_page'], $pagination['current_page'] + 2);
          @endphp
          @for ($i = $startPage; $i <= $endPage; $i++)
            <li class="page-item {{ $pagination['current_page'] == $i ? 'active' : '' }}">
              <a class="page-link" href="#" wire:click="gotoPage({{ $i }})"
                wire:loading.attr="disabled">{{ $i }}</a>
            </li>
          @endfor
          @if ($pagination['current_page'] < $pagination['last_page'])
            <li class="page-item">
              <a class="page-link" href="#" wire:click="nextPage" wire:loading.attr="disabled">بعدی</a>
            </li>
          @else
            <li class="page-item disabled">
              <span class="page-link">بعدی</span>
            </li>
          @endif
        </ul>
      </nav>
    </div>

    <script>
      document.addEventListener('livewire:initialized', () => {
        const selectAllCheckbox = document.getElementById('select-all-row');
        const cancelAppointmentsBtn = document.getElementById('cancel-appointments-btn');
        const moveAppointmentsBtn = document.getElementById('move-appointments-btn');
        const blockUsersBtn = document.getElementById('block-users-btn');

        function updateButtonStates() {
          const selectedCheckboxes = document.querySelectorAll('.appointment-checkbox:checked');
          const anySelected = selectedCheckboxes.length > 0;

          if (!cancelAppointmentsBtn || !moveAppointmentsBtn || !blockUsersBtn) {
            console.warn('یکی از دکمه‌ها یافت نشد');
            return;
          }

          cancelAppointmentsBtn.disabled = !anySelected;
          moveAppointmentsBtn.disabled = !anySelected;
          blockUsersBtn.disabled = !anySelected;

          if (anySelected) {
            let hasInvalidStatus = false;
            selectedCheckboxes.forEach(checkbox => {
              const status = checkbox.dataset.status;
              if (status === 'cancelled' || status === 'attended') {
                hasInvalidStatus = true;
              }
            });

            cancelAppointmentsBtn.disabled = hasInvalidStatus;
            moveAppointmentsBtn.disabled = hasInvalidStatus;
          }
        }

        function initializeTooltips() {
          const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
          tooltipTriggerList.forEach(tooltipTriggerEl => {
            const existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
            if (existingTooltip) {
              existingTooltip.dispose();
            }
            new bootstrap.Tooltip(tooltipTriggerEl, {
              trigger: 'hover focus',
              container: 'body',
              boundary: 'window',
              delay: {
                show: 100,
                hide: 200
              }
            });
          });
        }

        function checkCheckboxes() {
          const checkboxes = document.querySelectorAll('.appointment-checkbox');
          return checkboxes.length;
        }

        setTimeout(() => {
          initializeTooltips();
          checkCheckboxes();
          updateButtonStates();
        }, 100);

        Livewire.hook('morph.updated', () => {
          setTimeout(() => {
            initializeTooltips();
            checkCheckboxes();
            updateButtonStates();
          }, 100);
        });

        function showSuccessToast(message) {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
              toast.addEventListener('mouseenter', Swal.stopTimer);
              toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
          });
        }

        Livewire.on('no-results-found', (event) => {
          Swal.fire({
            title: 'جستجوی نوبت',
            text: `پزشک گرامی، برای تاریخ ${event.date} هیچ نوبت یا نتیجه‌ای یافت نشد. آیا می‌خواهید جستجو در سوابق همه نوبت‌ها انجام شود؟`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'بله، جستجو در همه تاریخ‌ها',
            cancelButtonText: 'خیر',
            reverseButtons: true
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('searchAllDates');
            }
          });
        });

        Livewire.on('confirm-cancel-single', (event) => {
          if (!event.id) {
            console.error('Appointment ID is undefined in confirm-cancel-single');
            Swal.fire({
              title: 'خطا',
              text: 'شناسه نوبت نامعتبر است.',
              icon: 'error',
              confirmButtonText: 'باشه'
            });
            return;
          }
          Swal.fire({
            title: 'تأیید لغو نوبت',
            text: 'آیا مطمئن هستید که می‌خواهید این نوبت را لغو کنید؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'بله، لغو کن',
            cancelButtonText: 'خیر',
            reverseButtons: true
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('cancelAppointments', {
                ids: [event.id]
              });
            }
          });
        });

        Livewire.on('appointments-cancelled', (event) => {
          showSuccessToast(event.message || 'نوبت(ها) با موفقیت لغو شد.');
        });

        Livewire.on('close-modal', (event) => {
          Livewire.dispatch('hideModal');
        });

        document.body.addEventListener('change', (event) => {
          if (event.target.id === 'select-all-row') {
            const checkboxes = document.querySelectorAll('.appointment-checkbox');
            checkboxes.forEach(checkbox => {
              checkbox.checked = event.target.checked;
            });
            updateButtonStates();
            initializeTooltips();
          } else if (event.target.classList.contains('appointment-checkbox')) {
            const checkboxes = document.querySelectorAll('.appointment-checkbox');
            if (selectAllCheckbox) {
              selectAllCheckbox.checked = checkboxes.length === document.querySelectorAll(
                '.appointment-checkbox:checked').length;
            }
            updateButtonStates();
            initializeTooltips();
          }
        });

        if (cancelAppointmentsBtn) {
          cancelAppointmentsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selected = Array.from(document.querySelectorAll('.appointment-checkbox:checked')).map(cb =>
              parseInt(cb.value));
            if (selected.length === 0) {
              Swal.fire({
                title: 'خطا',
                text: 'لطفاً حداقل یک نوبت را انتخاب کنید.',
                icon: 'error',
                confirmButtonText: 'باشه'
              });
              return;
            }
            Swal.fire({
              title: 'تأیید لغو نوبت',
              text: `آیا مطمئن هستید که می‌خواهید ${selected.length} نوبت را لغو کنید؟`,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'بله، لغو کن',
              cancelButtonText: 'خیر',
              reverseButtons: true
            }).then((result) => {
              if (result.isConfirmed) {
                Livewire.dispatch('cancelAppointments', {
                  ids: selected
                });
              }
            });
          });
        } else {
          console.warn('Cancel Appointments Button not found');
        }

        if (blockUsersBtn) {
          blockUsersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedMobiles = Array.from(document.querySelectorAll('.appointment-checkbox:checked'))
              .map(cb => cb.dataset.mobile)
              .filter(mobile => mobile);
            if (selectedMobiles.length === 0) {
              Swal.fire({
                title: 'خطا',
                text: 'هیچ کاربری انتخاب نشده است.',
                icon: 'error',
                confirmButtonText: 'باشه'
              });
              return;
            }
            Livewire.dispatch('showModal', {
              data: {
                'alias': 'block-multiple-users-modal'
              }
            });
          });
        } else {
          console.warn('Block Users Button not found');
        }

        const filterDropdownItems = document.querySelectorAll('#filterDropdown + .dropdown-menu .dropdown-item');
        filterDropdownItems.forEach(item => {
          item.addEventListener('click', function(e) {
            e.preventDefault();
            const filterValue = this.getAttribute('wire:click').match(/'([^']+)'/)?.[1] || '';
            Livewire.dispatch('setFilter', {
              filter: filterValue
            });
          });
        });

        function generateRescheduleCalendar(year, month) {
          const rescheduleCalendarBody = document.getElementById("calendar-reschedule");
          if (!rescheduleCalendarBody) {
            console.warn('Reschedule calendar body not found');
            return;
          }
          rescheduleCalendarBody.innerHTML = "";
          const firstDayOfMonth = moment(`${year}/${month}/01`, "jYYYY/jMM/jDD").locale("fa");
          const daysInMonth = firstDayOfMonth.jDaysInMonth();
          let firstDayWeekday = firstDayOfMonth.weekday();
          const today = moment().locale("fa");
          for (let i = 0; i < firstDayWeekday; i++) {
            const emptyDay = document.createElement("div");
            emptyDay.className = "calendar-day empty";
            rescheduleCalendarBody.appendChild(emptyDay);
          }
          const holidays = @json($this->getHolidays());
          const dateStr = `${year}-${month.toString().padStart(2, '0')}-01`;
          Livewire.dispatch('getAppointmentsByDateSpecial', {
            date: dateStr
          });
          Livewire.on('appointmentsFetched', (response) => {
            const appointmentsByDate = response.data || [];
            for (let day = 1; day <= daysInMonth; day++) {
              const jalaliDateStr =
                `${year}/${month.toString().padStart(2, '0')}/${day.toString().padStart(2, '0')}`;
              const gregorianDate = moment(jalaliDateStr, "jYYYY/jMM/jDD").format("YYYY-MM-DD");
              const dayElement = document.createElement("div");
              dayElement.className = "calendar-day text-center";
              const isHoliday = holidays.includes(gregorianDate);
              const hasAppointment = appointmentsByDate.some(appt => appt.appointment_date === gregorianDate);
              const isPast = moment(gregorianDate).isBefore(today, 'day');
              if (isPast) {
                dayElement.classList.add("disabled");
              } else if (isHoliday) {
                dayElement.classList.add("holiday");
              } else if (hasAppointment) {
                dayElement.classList.add("has-appointment");
              } else {
                dayElement.classList.add("available");
                dayElement.style.cursor = "pointer";
                dayElement.addEventListener("click", () => {
                  @this.set('rescheduleNewDate', gregorianDate);
                  document.querySelectorAll(".calendar-day").forEach(el => el.classList.remove("selected"));
                  dayElement.classList.add("selected");
                  Swal.fire({
                    title: 'تأیید جابجایی',
                    text: `آیا می‌خواهید نوبت به تاریخ ${jalaliDateStr} منتقل شود؟`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'بله، جابجا کن',
                    cancelButtonText: 'خیر',
                    reverseButtons: true
                  }).then((result) => {
                    if (result.isConfirmed) {
                      @this.updateAppointmentDate(@this.rescheduleAppointmentId);
                    }
                  });
                });
              }
              dayElement.textContent = day;
              rescheduleCalendarBody.appendChild(dayElement);
            }
          });
          const yearSelect = document.getElementById("year-reschedule");
          const monthSelect = document.getElementById("month-reschedule");
          if (yearSelect && monthSelect) {
            yearSelect.value = year;
            monthSelect.value = month;
          }
        }

        const yearSelect = document.getElementById("year-reschedule");
        const monthSelect = document.getElementById("month-reschedule");

        if (yearSelect && monthSelect) {
          function populateYearMonthSelectors() {
            const currentYear = moment().jYear();
            yearSelect.innerHTML = "";
            for (let y = currentYear - 5; y <= currentYear + 5; y++) {
              const option = document.createElement("option");
              option.value = y;
              option.textContent = y;
              yearSelect.appendChild(option);
            }
            monthSelect.innerHTML = "";
            for (let m = 1; m <= 12; m++) {
              const option = document.createElement("option");
              option.value = m;
              option.textContent = moment().jMonth(m - 1).format("jMMMM");
              monthSelect.appendChild(option);
            }
          }

          yearSelect.addEventListener("change", () => {
            generateRescheduleCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
          });

          monthSelect.addEventListener("change", () => {
            generateRescheduleCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
          });

          document.getElementById("prev-month-reschedule")?.addEventListener("click", () => {
            let newMonth = parseInt(monthSelect.value) - 1;
            let newYear = parseInt(yearSelect.value);
            if (newMonth < 1) {
              newMonth = 12;
              newYear--;
            }
            generateRescheduleCalendar(newYear, newMonth);
          });

          document.getElementById("next-month-reschedule")?.addEventListener("click", () => {
            let newMonth = parseInt(monthSelect.value) + 1;
            let newYear = parseInt(yearSelect.value);
            if (newMonth > 12) {
              newMonth = 1;
              newYear++;
            }
            generateRescheduleCalendar(newYear, newMonth);
          });

          populateYearMonthSelectors();
          const initialYear = moment().jYear();
          const initialMonth = moment().jMonth() + 1;
          generateRescheduleCalendar(initialYear, initialMonth);
        }

        if (typeof jalaliDatepicker !== 'undefined') {
          jalaliDatepicker.startWatch({
            minDate: 'today',
            dateFormat: 'YYYY/MM/DD',
          });
        } else {
          console.warn('Jalali Datepicker not found');
        }
      });
    </script>
  </div>
