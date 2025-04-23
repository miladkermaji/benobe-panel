<div>
  @php
    use Morilog\Jalali\Jalalian;
    use Carbon\Carbon;
  @endphp

  <div class="d-flex justify-content-center top-s-wrapper flex-wrap">
    <div class="calendar-and-add-sick-section p-3">
      <div class="d-flex justify-content-between gap-10 align-items-center c-a-wrapper">
        <div>
          <div class="turning_selectDate__MLRSb" wire:ignore>
            <button
              class="selectDate_datepicker__xkZeS cursor-pointer text-center h-50 bg-light-blue d-flex justify-content-center align-items-center"
              data-toggle="modal" data-target="#miniCalendarModal">
              <div class="d-flex align-items-center">
                <span class="mx-1">{{ Jalalian::fromCarbon(Carbon::parse($selectedDate))->format('Y/m/d') }}</span>
                <img src="{{ asset('dr-assets/icons/calendar.svg') }}" alt="" srcset="">
              </div>
            </button>
            <div class="modal fade" id="miniCalendarModal" tabindex="-1" role="dialog"
              aria-labelledby="exampleModalCenterTitle" aria-hidden="true" wire:ignore>
              <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-radius-11">
                  <div class="my-modal-header">
                    <div class="text-end">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span style="font-size: 28px !important; margin: 4px" aria-hidden="true">×</span>
                      </button>
                    </div>
                  </div>
                  <div class="modal-body">
                    <x-jalali-calendar />
                  </div>
                </div>
              </div>
            </div>
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
          <button class="btn my-btn-primary h-50 fs-13" data-toggle="modal" data-target="#exampleModalCenterAddSick">ثبت
            نوبت دستی</button>
          <div class="modal fade" id="exampleModalCenterAddSick" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content border-radius-11">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLongTitle"> ثبت نوبت دستی</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div>
                    <form action="" method="post">
                      <input type="text" class="my-form-control-light w-100" placeholder="کدملی/کداتباع">
                      <div class="mt-2">
                        <a class="text-decoration-none text-primary font-bold" href="#" data-toggle="modal"
                          data-target="#exampleModalCenterPaziresh">پذیرش از مسیر ارجاع</a>
                      </div>
                      <div class="d-flex mt-2 gap-20">
                        <button class="btn my-btn-primary w-50 h-50">تجویز نسخه</button>
                        <button class="btn btn-outline-info w-50 h-50">ثبت ویزیت</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal fade" id="exampleModalCenterPaziresh" tabindex="-1" role="dialog"
              aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-radius-11">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">ارجاع</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <div>
                      <form action="" method="post">
                        <input type="text" class="my-form-control-light w-100" placeholder="کدملی/کداتباع بیمار">
                        <input type="text" class="my-form-control-light w-100 mt-3" placeholder="کد پیگیری">
                        <div class="mt-3">
                          <button class="btn my-btn-primary w-100 h-50">ثبت</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


    <div wire:ignore class="w-100">
      <x-jalali-calendar-row />

    </div>

    <div class="sicks-content h-100 w-100 position-relative border">
      <div>
        <div class="table-responsive position-relative top-table w-100">
          <table class="table w-100 text-sm text-center">
            <thead>
              <tr>
                <th>
                  <input class="form-check-input" type="checkbox" id="select-all-row" disabled>
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
              @foreach ($appointments as $appointment)
                <tr>
                  <td>
                    <input type="checkbox" class="appointment-checkbox form-check-input"
                      value="{{ $appointment->id }}">
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
                    @if ($appointment->status !== 'attended')
                      <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#endVisitModalCenter"
                        wire:click="$set('endVisitAppointmentId', {{ $appointment->id }})">پایان ویزیت</button>
                    @else
                      -
                    @endif
                  </td>
                  <td>
                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#rescheduleModal"
                      wire:click="$set('rescheduleAppointmentId', {{ $appointment->id }})">جابجایی</button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <div class="d-flex justify-content-start gap-10 nobat-option">
        <div class="d-flex align-items-center m-2 gap-4">
          <div class="turning_filterWrapper__2cOOi">
            <div class="dropdown">
              <button
                class="flex items-center justify-center bg-transparent border border-gray-300 rounded-sm hover:bg-gray-100 transition-colors bg-light w-12 h-8 focus:outline-none dropdown-toggle"
                type="button" id="dropdownMenuButton" aria-haspopup="true" aria-expanded="true">
                <input class="w-4 h-4 text-blue-600 border-none focus:ring-0  cursor-pointer form-check-input"
                  type="checkbox" value="" id="select-all">
                <span class="w-2"></span>
              </button>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <ul class="dropdown-list" style="list-style-type: none; padding: 0; margin: 0;">
                  <li>
                    <a href="#" class="dropdown-item" wire:click="$set('filterStatus', '')">همه نوبت‌ها</a>
                  </li>
                  <li>
                    <a href="#" class="dropdown-item" wire:click="$set('filterStatus', 'scheduled')">در
                      انتظار</a>
                  </li>
                  <li>
                    <a href="#" class="dropdown-item" wire:click="$set('filterStatus', 'cancelled')">لغو
                      شده</a>
                  </li>
                  <li>
                    <a href="#" class="dropdown-item" wire:click="$set('filterStatus', 'attended')">ویزیت
                      شده</a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <button id="cancel-appointments-btn"
            class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center"
            wire:click="cancelAppointments([])">
            <img src="{{ asset('dr-assets/icons/cancle-appointment.svg') }}" alt="" srcset="">
            <span class="d-none d-md-block mx-1">لغو نوبت</span>
          </button>
          <button id="move-appointments-btn"
            class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center" data-toggle="modal"
            data-target="#rescheduleModal">
            <img src="{{ asset('dr-assets/icons/rescheule-appointment.svg') }}" alt="" srcset="">
            <span class="d-none d-md-block mx-1">جابجایی نوبت</span>
          </button>
          <button id="block-users-btn"
            class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center" data-toggle="modal"
            data-target="#blockUserModal">
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
              <a class="page-link" href="#" wire:click="previousPage">قبلی</a>
            </li>
          @endif
          @for ($i = 1; $i <= $pagination['last_page']; $i++)
            <li class="page-item {{ $pagination['current_page'] == $i ? 'active' : '' }}">
              <a class="page-link" href="#"
                wire:click="gotoPage({{ $i }})">{{ $i }}</a>
            </li>
          @endfor
          @if ($pagination['current_page'] < $pagination['last_page'])
            <li class="page-item">
              <a class="page-link" href="#" wire:click="nextPage">بعدی</a>
            </li>
          @endif
        </ul>
      </nav>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="activation-modal" tabindex="-1" role="dialog"
      aria-labelledby="activation-modal-label" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-radius-11">
          <div class="modal-header">
            <h5 class="modal-title" id="activation-modal-label">فعالسازی نوبت دهی</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="flex flex-col"><span>پزشک گرامی</span>
              <span>
                با فعال سازی امکان برقراری تماس امن، علاوه بر فراهم آوردن یک ویزیت
                پیوسته و با کیفیت، زمان انتظار پاسخگویی به بیماران را نیز کاهش دهید.
              </span>
              <span>
                پاسخ‌دهی به موقع برای برآورده کردن انتظارات بیماران بسیار مهم است.
              </span>
            </div>
          </div>
          <div class="p-3">
            <a href="" data-toggle="modal" data-target="#contact-modal"
              class="btn my-btn-primary w-100 h-50 d-flex align-items-center text-white justify-content-center">
              فعالسازی تماس امن </a>
            <a href="" class="btn btn-light mt-3 w-100 h-50 d-flex align-items-center justify-content-center"
              onclick="$('#activation-modal').modal('hide'); window.history.pushState({}, '', 'panel');"> فعلا نه
              بعدا فعال میکنم </a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="contact-modal" tabindex="-1" role="dialog" aria-labelledby="contact-modal-label"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-radius-11">
          <div class="modal-header">
            <h5 class="modal-title" id="activation-modal-label">تماس امن</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body my-modal-body">
            <div class="d-flex flex-column align-items-center w-100">
              <ul class="mx-4text-sm font-medium list-disc d-flex flex-column">
                <li>در پنل پزشک، در مقابل اسم هر بیمار، دکمه تماس وجود دارد。</li>
                <li>پزشک قادر است در هر زمان با بیمار تماس برقرار کند。</li>
                <li>بیمار در قبض نوبت و در نوبت‌های من، دکمه برقراری تماس را دارد。</li>
                <li>بیمار تنها در ساعت کاری پزشک، قادر به تماس با پزشک است。</li>
                <li>بیمار از زمان نوبت تا ۳ روز بعد از زمان نوبت، دکمه تماس را در اختیار دارد。</li>
                <li>تماس امن همراه با پیام‌رسان است و در صورت نیاز، شما و یا بیمار می‌توانید از هر یک از دو
                  سرویس استفاده کنید。</li>
              </ul>
            </div>
          </div>
          <div class="p-3">
            <a href="#"
              class="btn my-btn-primary w-100 h-50 d-flex align-items-center text-white justify-content-center"
              onclick="$('#activation-modal').modal('hide'); $('#contact-modal').modal('hide'); window.history.pushState({}, '', 'panel'); toastr.success('تماس امن با موفقیت فعال شد');">
              شرایط برقراری تماس امن را مطالعه کردم。 </a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-radius-11">
          <div class="modal-header">
            <h6 class="modal-title" id="rescheduleModalLabel">جابجایی نوبت</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="calendar-header w-100 d-flex justify-content-between align-items-center">
              <div class="">
                <button id="prev-month-reschedule" class="btn btn-light">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none">
                    <g id="Arrow / Chevron_Right_MD">
                      <path id="Vector" d="M10 8L14 12L10 16" stroke="#000000" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                  </svg>
                </button>
              </div>
              <div class="w-100">
                <select id="year-reschedule" class="form-select w-100 border-0"></select>
              </div>
              <div class="w-100">
                <select id="month-reschedule" class="form-select w-100 border-0"></select>
              </div>
              <div class="">
                <button id="next-month-reschedule" class="btn btn-light">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none">
                    <g id="Arrow / Chevron_Left_MD">
                      <path id="Vector" d="M14 16L10 12L14 8" stroke="#000000" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                  </svg>
                </button>
              </div>
            </div>
            <div class="w-100 d-flex justify-content-end">
              <button id="goToFirstAvailableDashboard" class="btn btn-light w-100 border"
                wire:click="goToFirstAvailableDate">برو به اولین نوبت خالی</button>
            </div>
            <div class="calendar-body calendar-body-g-425 mt-2">
              <div class="calendar-day-name text-center">شنبه</div>
              <div class="calendar-day-name text-center">یک‌شنبه</div>
              <div class="calendar-day-name text-center">دوشنبه</div>
              <div class="calendar-day-name text-center">سه‌شنبه</div>
              <div class="calendar-day-name text-center">چهارشنبه</div>
              <div class="calendar-day-name text-center">پنج‌شنبه</div>
              <div class="calendar-day-name text-center">جمعه</div>
            </div>
            <div class="calendar-body-425 d-none p-2">
              <div class="calendar-day-name text-center">ش</div>
              <div class="calendar-day-name text-center">ی</div>
              <div class="calendar-day-name text-center">د</div>
              <div class="calendar-day-name text-center">س</div>
              <div class="calendar-day-name text-center">چ</div>
              <div class="calendar-day-name text-center">پ</div>
              <div class="calendar-day-name text-center">ج</div>
            </div>
            <div id="calendar-reschedule" class="calendar-body mt-3"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="endVisitModalCenter" tabindex="-1" role="dialog"
      aria-labelledby="endVisitModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-radius-11">
          <div class="modal-header">
            <h6 class="modal-title fw-bold" id="exampleModalCenterTitle">توضیحات درمان</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <textarea class="form-control" rows="5" wire:model="endVisitDescription"
              placeholder="توضیحات درمان را وارد کنید..."></textarea>
            <button class="btn my-btn-primary w-100 mt-3"
              wire:click="endVisit({{ $endVisitAppointmentId }})">ثبت</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="blockUserModal" tabindex="-1" role="dialog" aria-labelledby="blockUserModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-radius-11">
          <div class="modal-header">
            <h5 class="modal-title" id="blockUserModalLabel">مسدود کردن کاربر</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label for="blockMobile" class="form-label">شماره موبایل</label>
                <input type="text" class="form-control" id="blockMobile" wire:model="blockMobile">
                @error('blockMobile')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="blockedAt" class="form-label">تاریخ شروع مسدودیت</label>
                <input type="text" class="form-control" id="blockedAt" wire:model="blockedAt">
                @error('blockedAt')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="unblockedAt" class="form-label">تاریخ پایان مسدودیت (اختیاری)</label>
                <input type="text" class="form-control" id="unblockedAt" wire:model="unblockedAt">
                @error('unblockedAt')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="blockReason" class="form-label">دلیل مسدودیت (اختیاری)</label>
                <textarea class="form-control" id="blockReason" rows="3" wire:model="blockReason"></textarea>
                @error('blockReason')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <button type="button" class="btn my-btn-primary w-100" wire:click="blockUser">مسدود کردن</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('dr-assets/panel/js/jquery-easing/1.4.1/jquery.easing.min.js') }}"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const dropdownButton = document.getElementById('dropdownMenuButton');
      const dropdownMenu = document.querySelector('.dropdown-menu');
      const dropdownItems = document.querySelectorAll('.dropdown-item');

      dropdownButton.addEventListener('click', function() {
        dropdownMenu.classList.toggle('show');
      });

      document.getElementById("select-all").addEventListener('click', function(event) {
        event.stopPropagation();
        const checkboxes = document.querySelectorAll('.appointment-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
      });

      dropdownItems.forEach(item => {
        item.addEventListener('click', function(event) {
          event.stopPropagation();
          dropdownMenu.classList.remove('show');
        });
      });

      document.addEventListener('click', function(event) {
        if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
          dropdownMenu.classList.remove('show');
        }
      });

      // Handle cancel appointments
      document.getElementById('cancel-appointments-btn').addEventListener('click', function() {
        const selected = Array.from(document.querySelectorAll('.appointment-checkbox:checked')).map(cb => cb
          .value);
        if (selected.length > 0) {
          @this.cancelAppointments(selected);
        } else {
          alert('لطفاً حداقل یک نوبت را انتخاب کنید.');
        }
      });

      // Handle reschedule calendar
      const rescheduleCalendarBody = document.getElementById("calendar-reschedule");
      const yearSelect = document.getElementById("year-reschedule");
      const monthSelect = document.getElementById("month-reschedule");

      function generateRescheduleCalendar(year, month) {
        rescheduleCalendarBody.innerHTML = "";
        const firstDayOfMonth = moment(`${year}/${month}/01`, "jYYYY/jMM/jDD").locale("fa");
        const daysInMonth = firstDayOfMonth.jDaysInMonth();
        let firstDayWeekday = firstDayOfMonth.weekday();
        const today = moment().locale("fa");

        for (let i = 0; i < firstDayWeekday; i++) {
          const emptyDay = document.createElement("div");
          emptyDay.classList.add("calendar-day", "empty");
          rescheduleCalendarBody.appendChild(emptyDay);
        }

        for (let day = 1; day <= daysInMonth; day++) {
          const currentDay = firstDayOfMonth.clone().add(day - 1, "days");
          const dayElement = document.createElement("div");
          dayElement.classList.add("calendar-day");
          dayElement.setAttribute("data-date", currentDay.format("jYYYY/jMM/jDD"));

          if (currentDay.day() === 5) {
            dayElement.classList.add("friday");
          }

          if (currentDay.isSame(today, "day")) {
            dayElement.classList.add("today");
          }

          dayElement.innerHTML = `<span>${currentDay.format("jD")}</span>`;

          dayElement.addEventListener("click", function() {
            const selectedDate = this.getAttribute("data-date");
            @this.set('rescheduleNewDate', moment(selectedDate, 'jYYYY/jMM/jDD').format('YYYY-MM-DD'));
            @this.updateAppointmentDate(@this.rescheduleAppointmentId);
          });

          rescheduleCalendarBody.appendChild(dayElement);
        }
      }

      function populateRescheduleSelectBoxes() {
        yearSelect.innerHTML = "";
        const currentYear = moment().jYear();
        for (let year = currentYear - 10; year <= currentYear + 10; year++) {
          const option = document.createElement("option");
          option.value = year;
          option.textContent = year;
          yearSelect.appendChild(option);
        }

        const persianMonths = [
          "فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور",
          "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"
        ];

        monthSelect.innerHTML = "";
        for (let month = 1; month <= 12; month++) {
          const option = document.createElement("option");
          option.value = month;
          option.textContent = persianMonths[month - 1];
          monthSelect.appendChild(option);
        }

        yearSelect.value = currentYear;
        monthSelect.value = moment().jMonth() + 1;

        yearSelect.addEventListener("change", function() {
          generateRescheduleCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
        });

        monthSelect.addEventListener("change", function() {
          generateRescheduleCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
        });
      }

      document.getElementById("prev-month-reschedule").addEventListener("click", function() {
        let currentMonth = parseInt(monthSelect.value);
        let currentYear = parseInt(yearSelect.value);

        if (currentMonth === 1) {
          currentYear -= 1;
          currentMonth = 12;
        } else {
          currentMonth -= 1;
        }

        yearSelect.value = currentYear;
        monthSelect.value = currentMonth;
        generateRescheduleCalendar(currentYear, currentMonth);
      });

      document.getElementById("next-month-reschedule").addEventListener("click", function() {
        let currentMonth = parseInt(monthSelect.value);
        let currentYear = parseInt(yearSelect.value);

        if (currentMonth === 12) {
          currentYear += 1;
          currentMonth = 1;
        } else {
          currentMonth += 1;
        }

        yearSelect.value = currentYear;
        monthSelect.value = currentMonth;
        generateRescheduleCalendar(currentYear, currentMonth);
      });

      populateRescheduleSelectBoxes();
      generateRescheduleCalendar(moment().jYear(), moment().jMonth() + 1);

      // Handle Livewire events
      Livewire.on('alert', ({
        type,
        message
      }) => {
        toastr[type](message);
      });

      Livewire.on('close-modal', ({
        id
      }) => {
        $(`#${id}`).modal('hide');
      });

      Livewire.on('update-reschedule-calendar', ({
        date
      }) => {
        const momentDate = moment(date);
        const jYear = momentDate.jYear();
        const jMonth = momentDate.jMonth() + 1;
        yearSelect.value = jYear;
        monthSelect.value = jMonth;
        generateRescheduleCalendar(jYear, jMonth);
      });
    });
  </script>
</div>
