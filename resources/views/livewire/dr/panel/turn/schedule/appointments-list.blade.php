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
              data-bs-toggle="modal" data-bs-target="#miniCalendarModal">
              <div class="d-flex align-items-center">
                <span class="mx-1">{{ Jalalian::fromCarbon(Carbon::parse($selectedDate))->format('Y/m/d') }}</span>
                <img src="{{ asset('dr-assets/icons/calendar.svg') }}" alt="" srcset="">
              </div>
            </button>
            <div class="modal fade" id="miniCalendarModal" tabindex="-1" aria-labelledby="miniCalendarModalLabel"
              aria-hidden="true" wire:ignore.self>
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-radius-11">
                  <div class="modal-header">
                    <h5 class="modal-title" id="miniCalendarModalLabel">تقویم</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
          <button class="btn my-btn-primary h-50 fs-13" data-bs-toggle="modal"
            data-bs-target="#exampleModalCenterAddSick">ثبت نوبت دستی</button>
          <div class="modal fade" id="exampleModalCenterAddSick" tabindex="-1"
            aria-labelledby="exampleModalCenterAddSickLabel" aria-hidden="true" wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content border-radius-11">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalCenterAddSickLabel">ثبت نوبت دستی</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div>
                    <form action="" method="post">
                      <input type="text" class="my-form-control-light w-100" placeholder="کدملی/کداتباع">
                      <div class="mt-2">
                        <a class="text-decoration-none text-primary font-bold" href="#" data-bs-toggle="modal"
                          data-bs-target="#exampleModalCenterPaziresh">پذیرش از مسیر ارجاع</a>
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
            <div class="modal fade" id="exampleModalCenterPaziresh" tabindex="-1"
              aria-labelledby="exampleModalCenterPazireshLabel" aria-hidden="true" wire:ignore.self>
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-radius-11">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterPazireshLabel">ارجاع</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                      <span
                        class="{{ $paymentStatusInfo['class'] }} fw-bold">{{ $paymentStatusInfo['label'] }}</span>
                    </td>
                    <td>{{ $appointment->insurance ? $appointment->insurance->name : '-' }}</td>
                    <td>
                      @if ($appointment->status !== 'attended' && $appointment->status !== 'cancelled')
                        <button class="btn btn-sm btn-primary shadow-sm end-visit-btn" data-bs-toggle="modal"
                          data-bs-target="#endVisitModalCenter"
                          wire:click="$set('endVisitAppointmentId', {{ $appointment->id }})">پایان ویزیت</button>
                      @else
                        -
                      @endif
                    </td>
                    <td>
                      <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-light rounded-circle shadow-sm reschedule-btn" data-bs-toggle="tooltip"
                          data-bs-placement="top" title="جابجایی نوبت" data-bs-toggle="modal"
                          data-bs-target="#rescheduleModal"
                          wire:click="$set('rescheduleAppointmentId', {{ $appointment->id }})"
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
                          data-bs-placement="top" title="مسدود کردن کاربر" data-bs-toggle="modal"
                          data-bs-target="#blockUserModal"
                          wire:click="$set('blockAppointmentId', {{ $appointment->id }})">
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
              <ul class="dropdown-menu" aria-labelledby="filterDropdown" wire:ignore>
                <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', '')">همه نوبت‌ها</a>
                </li>
                <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', 'scheduled')">در
                    انتظار</a></li>
                <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', 'cancelled')">لغو
                    شده</a></li>
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
            data-bs-toggle="modal" data-bs-target="#rescheduleModal" disabled>
            <img src="{{ asset('dr-assets/icons/rescheule-appointment.svg') }}" alt="" srcset="">
            <span class="d-none d-md-block mx-1">جابجایی نوبت</span>
          </button>
          <button id="block-users-btn"
            class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center shadow-sm"
            data-bs-toggle="modal" data-bs-target="#blockMultipleUsersModal" disabled>
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
    <!-- Modals -->
    <div class="modal fade" id="activation-modal" tabindex="-1" aria-labelledby="activation-modal-label"
      aria-hidden="true" wire:ignore.self>
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-radius-11">
          <div class="modal-header">
            <h5 class="modal-title" id="activation-modal-label">فعالسازی نوبت دهی</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="flex flex-col">
              <span>پزشک گرامی</span>
              <span>با فعال سازی امکان برقراری تماس امن، علاوه بر فراهم آوردن یک ویزیت پیوسته و با کیفیت، زمان انتظار
                پاسخگویی به بیماران را نیز کاهش دهید.</span>
              <span>پاسخ‌دهی به موقع برای برآورده کردن انتظارات بیماران بسیار مهم است.</span>
            </div>
          </div>
          <div class="p-3">
            <a href="" data-bs-toggle="modal" data-bs-target="#contact-modal"
              class="btn my-btn-primary w-100 h-50 d-flex align-items-center text-white justify-content-center">
              فعالسازی تماس امن </a>
            <a href="" class="btn btn-light mt-3 w-100 h-50 d-flex align-items-center justify-content-center"
              data-bs-dismiss="modal"> فعلا نه بعدا فعال میکنم </a>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="contact-modal" tabindex="-1" aria-labelledby="contact-modal-label"
      aria-hidden="true" wire:ignore.self>
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-radius-11">
          <div class="modal-header">
            <h5 class="modal-title" id="contact-modal-label">تماس امن</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body my-modal-body">
            <div class="d-flex flex-column align-items-center w-100">
              <ul class="mx-4 text-sm font-medium list-disc d-flex flex-column">
                <li>در پنل پزشک، در مقابل اسم هر بیمار، دکمه تماس وجود دارد。</li>
                <li>پزشک قادر است در هر زمان با بیمار تماس برقرار کند。</li>
                <li>بیمار در قبض نوبت و در نوبت‌های من، دکمه برقراری تماس را دارد。</li>
                <li>بیمار تنها در ساعت کاری پزشک، قادر به تماس با پزشک است。</li>
                <li>بیمار از زمان نوبت تا ۳ روز بعد از زمان نوبت، دکمه تماس را در اختیار دارد。</li>
                <li>تماس امن همراه با پیام‌رسان است و در صورت نیاز، شما و یا بیمار می‌توانید از هر یک از دو سرویس
                  استفاده کنید。</li>
              </ul>
            </div>
          </div>
          <div class="p-3">
            <a href="#"
              class="btn my-btn-primary w-100 h-50 d-flex align-items-center text-white justify-content-center"
              data-bs-dismiss="modal" onclick="toastr.success('تماس امن با موفقیت فعال شد');">
              شرایط برقراری تماس امن را مطالعه کردم。 </a>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel"
      aria-hidden="true" wire:ignore.self>
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-radius-11">
          <div class="modal-header">
            <h6 class="modal-title" id="rescheduleModalLabel">جابجایی نوبت</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="calendar-header w-100 d-flex justify-content-between align-items-center">
              <div>
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
              <div>
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
    <div class="modal fade" id="endVisitModalCenter" tabindex="-1" aria-labelledby="endVisitModalCenterTitle"
      aria-hidden="true" wire:ignore.self>
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-radius-11">
          <div class="modal-header">
            <h6 class="modal-title fw-bold" id="endVisitModalCenterTitle">توضیحات درمان</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <textarea class="form-control" rows="5" wire:model="endVisitDescription"
              placeholder="توضیحات درمان را وارد کنید..."></textarea>
            <button class="btn my-btn-primary w-100 mt-3 shadow-sm end-visit-btn"
              wire:click="endVisit({{ $endVisitAppointmentId }})">ثبت</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="blockUserModal" tabindex="-1" aria-labelledby="blockUserModalLabel"
      aria-hidden="true" wire:ignore.self>
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-radius-11">
          <div class="modal-header">
            <h5 class="modal-title" id="blockUserModalLabel">مسدود کردن کاربر</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form wire:submit.prevent="blockUser">
              <div class="mb-3">
                <label for="blockedAt" class="form-label">تاریخ شروع مسدودیت</label>
                <input type="text" class="form-control" id="blockedAt" wire:model.live="blockedAt" data-jdp>
                @error('blockedAt')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="unblockedAt" class="form-label">تاریخ پایان مسدودیت (اختیاری)</label>
                <input type="text" class="form-control" id="unblockedAt" wire:model.live="unblockedAt" data-jdp>
                @error('unblockedAt')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="blockReason" class="form-label">دلیل مسدودیت (اختیاری)</label>
                <textarea class="form-control" id="blockReason" rows="3" wire:model.live="blockReason"
                  placeholder="دلیل مسدودیت را وارد کنید..."></textarea>
                @error('blockReason')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <button type="submit" class="btn my-btn-primary w-100">مسدود کردن</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="modal 너 fade" id="blockMultipleUsersModal" tabindex="-1"
      aria-labelledby="blockMultipleUsersModalLabel" aria-hidden="true" wire:ignore.self>
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-radius-11">
          <div class="modal-header">
            <h5 class="modal-title" id="blockMultipleUsersModalLabel">مسدود کردن کاربران</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form wire:submit.prevent="blockMultipleUsers">
              <div class="mb-3">
                <label for="blockedAtMultiple" class="form-label">تاریخ شروع مسدودیت</label>
                <input type="text" class="form-control" id="blockedAtMultiple" wire:model.live="blockedAt"
                  data-jdp>
                @error('blockedAt')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="unblockedAtMultiple" class="form-label">تاریخ پایان مسدودیت (اختیاری)</label>
                <input type="text" class="form-control" id="unblockedAtMultiple" wire:model.live="unblockedAt"
                  data-jdp>
                @error('unblockedAt')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="blockReasonMultiple" class="form-label">دلیل مسدودیت (اختیاری)</label>
                <textarea class="form-control" id="blockReasonMultiple" rows="3" wire:model.live="blockReason"
                  placeholder="دلیل مسدودیت را وارد کنید..."></textarea>
                @error('blockReason')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <button type="submit" class="btn my-btn-primary w-100" id="blockMultipleUsersSubmit">مسدود
                کردن</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <style>
      .table {
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
      }

      .table thead {
        background-color: #f8f9fa;
      }

      .table tbody tr:hover {
        background-color: #f1f5f9;
      }

      .end-visit-btn {
        transition: all 0.3s ease;
      }

      .end-visit-btn:hover {
        background-color: #0052cc;
        transform: translateY(-2px);
      }

      /* استایل‌های تولتیپ */
      .tooltip-inner {
        background-color: #212529;
        color: #fff;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        max-width: 200px;
      }

      .tooltip.bs-tooltip-top .tooltip-arrow::before {
        border-top-color: #212529;
      }

      .tooltip.bs-tooltip-bottom .tooltip-arrow::before {
        border-bottom-color: #212529;
      }

      .tooltip.bs-tooltip-start .tooltip-arrow::before {
        border-left-color: #212529;
      }

      .tooltip.bs-tooltip-end .tooltip-arrow::before {
        border-right-color: #212529;
      }

      .tooltip {
        z-index: 1050 !important;
      }
    </style>
    <script>
      document.addEventListener('livewire:initialized', () => {

        // تعریف متغیرهای DOM
        const selectAllCheckbox = document.getElementById('select-all-row');
        const cancelAppointmentsBtn = document.getElementById('cancel-appointments-btn');
        const moveAppointmentsBtn = document.getElementById('move-appointments-btn');
        const blockUsersBtn = document.getElementById('block-users-btn');
        const blockMultipleUsersSubmit = document.getElementById('blockMultipleUsersSubmit');

  

        // تابع به‌روزرسانی وضعیت دکمه‌ها
      function updateButtonStates() {
  const selectedCheckboxes = document.querySelectorAll('.appointment-checkbox:checked');
  const anySelected = selectedCheckboxes.length > 0;

  if (!cancelAppointmentsBtn || !moveAppointmentsBtn || !blockUsersBtn) {
    console.warn('یکی از دکمه‌ها یافت نشد');
    return;
  }

  // ابتدا همه دکمه‌ها را بر اساس انتخاب شدن یا نشدن تنظیم می‌کنیم
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

    // فقط دکمه‌های لغو و جابجایی را در صورت وجود وضعیت نامعتبر غیرفعال می‌کنیم
    cancelAppointmentsBtn.disabled = hasInvalidStatus;
    moveAppointmentsBtn.disabled = hasInvalidStatus;
    // دکمه بلاک کاربران بدون تغییر باقی می‌ماند (فعال اگر anySelected true باشد)
  }
}

        // تابع مقداردهی اولیه تولتیپ‌ها
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

        // بررسی اولیه تعداد چک‌باکس‌ها
        function checkCheckboxes() {
          const checkboxes = document.querySelectorAll('.appointment-checkbox');
          return checkboxes.length;
        }

        // اجرای اولیه
        setTimeout(() => {
          initializeTooltips();
          checkCheckboxes();
          updateButtonStates();
        }, 100);

        // به‌روزرسانی پس از رندر Livewire
        Livewire.hook('morph.updated', () => {
          setTimeout(() => {
            initializeTooltips();
            checkCheckboxes();
            updateButtonStates();
          }, 100);
        });

        // نمایش توستر موفقیت
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

        // مدیریت رویدادهای SweetAlert و توستر موفقیت
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
          const modal = document.getElementById(event.id);
          if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
            bsModal.hide();
          }
        });

        // مدیریت رویدادهای چک‌باکس‌ها با Event Delegation
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

        // مدیریت دکمه لغو نوبت‌ها
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

        // مدیریت دکمه مسدود کردن گروهی
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
            const blockMultipleUsersModal = new bootstrap.Modal(document.getElementById('blockMultipleUsersModal'));
            blockMultipleUsersModal.show();
          });
        } else {
          console.warn('Block Users Button not found');
        }

        // مدیریت دکمه تأیید مسدود کردن گروهی
        if (blockMultipleUsersSubmit) {
          blockMultipleUsersSubmit.addEventListener('click', function(e) {
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
            Swal.fire({
              title: 'تأیید مسدود کردن',
              text: `آیا مطمئن هستید که می‌خواهید ${selectedMobiles.length} کاربر را مسدود کنید؟`,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'بله، مسدود کن',
              cancelButtonText: 'خیر',
              reverseButtons: true
            }).then((result) => {
              if (result.isConfirmed) {
                Livewire.dispatch('blockMultipleUsers', {
                  mobiles: selectedMobiles
                });
              }
            });
          });
        } else {
          console.warn('Block Multiple Users Submit not found');
        }

        // مدیریت دراپ‌داون فیلتر
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

        // مدیریت کلیک‌های دکمه‌های دارای مودال (برای جلوگیری از اتصال چندگانه)
        document.body.addEventListener('click', (event) => {
          const target = event.target.closest('button');
          if (!target) return;

          if (target.classList.contains('reschedule-btn')) {
            const appointmentId = target.getAttribute('wire:click').match(/\d+/)[0];
            Livewire.dispatch('set', {
              key: 'rescheduleAppointmentId',
              value: parseInt(appointmentId)
            });
            const modal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
            modal.show();
          }

          if (target.classList.contains('cancel-btn')) {
            const appointmentId = target.getAttribute('wire:click').match(/\d+/)[0];
            Livewire.dispatch('confirm-cancel-single', {
              id: parseInt(appointmentId)
            });
          }

          if (target.classList.contains('block-btn')) {
            const appointmentId = target.getAttribute('wire:click').match(/\d+/)[0];
            Livewire.dispatch('set', {
              key: 'blockAppointmentId',
              value: parseInt(appointmentId)
            });
            const modal = new bootstrap.Modal(document.getElementById('blockUserModal'));
            modal.show();
          }
        }, {
          once: true
        }); // جلوگیری از اتصال چندگانه

        // مدیریت تقویم جابجایی
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

        // مدیریت سال و ماه در تقویم جابجایی
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

          // فراخوانی اولیه
          populateYearMonthSelectors();
          const initialYear = moment().jYear();
          const initialMonth = moment().jMonth() + 1;
          generateRescheduleCalendar(initialYear, initialMonth);
        }

        // Initialize Jalali Datepicker
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
