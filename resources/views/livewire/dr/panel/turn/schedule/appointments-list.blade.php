<div>
  @php
    use Morilog\Jalali\Jalalian;
    use Carbon\Carbon;
  @endphp
  <div class="d-flex justify-content-center top-s-wrapper flex-wrap">
    <div class="calendar-and-add-sick-section p-3 w-100">
      <div class="c-a-wrapper">
        <button class="selectDate_datepicker__xkZeS" x-data @click="$dispatch('open-modal',{name:'mini-calendar-modal'})">
          <span
            class="mx-1">{{ Jalalian::fromCarbon(Carbon::parse($selectedDate)->setTimezone('Asia/Tehran'))->format('Y/m/d') }}</span>
          <img src="{{ asset('dr-assets/icons/calendar.svg') }}" alt="تقویم">
        </button>
        <div class="turning_search-wrapper__loGVc">
          <input type="text" class="my-form-control" placeholder="نام بیمار، شماره موبایل یا کد ملی ..."
            wire:model.live.debounce.500ms="searchQuery">
        </div>
        <button class="btn-primary" x-data @click="$dispatch('open-modal', { name: 'add-sick-modal' })">
          ثبت نوبت دستی
        </button>
      </div>
    </div>
    <div wire:ignore class="w-100">
      <x-jalali-calendar-row />
    </div>
    <div class="sicks-content h-100 w-100 position-relative border">
      <div class="d-flex justify-content-start gap-10 nobat-option w-100" wire:ignore>
        <div class="d-flex align-items-center m-2 gap-4 justify-content-between w-100">
          <div class="d-flex">
            <div class="turning_filterWrapper__2cOOi">
              <div class="dropdown">
                <button class="btn btn-light dropdown-toggle h-30 fs-13" type="button" id="filterDropdown"
                  data-bs-toggle="dropdown" aria-expanded="false">
                  فیلتر
                </button>
                <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                  <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', '')">همه نوبت‌ها</a></li>
                  <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', 'scheduled')">در
                      انتظار</a></li>
                  <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', 'cancelled')">لغو شده</a>
                  </li>
                  <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', 'attended')">ویزیت
                      شده</a></li>
                  <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', 'manual')">نوبت‌های
                      دستی</a></li>
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li><a class="dropdown-item" href="#" wire:click="$set('dateFilter', 'current_year')">سال
                      جاری</a></li>
                  <li><a class="dropdown-item" href="#" wire:click="$set('dateFilter', 'current_month')">ماه
                      جاری</a></li>
                  <li><a class="dropdown-item" href="#" wire:click="$set('dateFilter', 'current_week')">هفته
                      جاری</a></li>
                </ul>
              </div>
            </div>
            <div class="d-flex d-none d-md-flex">
              <button class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center shadow-sm"
                wire:click="$set('dateFilter', 'current_year')">
                <img src="{{ asset('dr-assets/icons/calendar.svg') }}" alt="" srcset="">
                <span class="d-none d-md-block">سال جاری</span>
              </button>
              <button class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center shadow-sm"
                wire:click="$set('dateFilter', 'current_month')">
                <img src="{{ asset('dr-assets/icons/calendar.svg') }}" alt="" srcset="">
                <span class="d-none d-md-block">ماه جاری</span>
              </button>
              <button class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center shadow-sm"
                wire:click="$set('dateFilter', 'current_week')">
                <img src="{{ asset('dr-assets/icons/calendar.svg') }}" alt="" srcset="">
                <span class="d-none d-md-block">هفته جاری</span>
              </button>
            </div>
          </div>
          <div class="d-flex">
            <button id="block-users-btn" x-data @click="$dispatch('open-modal', { name: 'block-user-modal' })"
              class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center shadow-sm" disabled>
              <img src="{{ asset('dr-assets/icons/block-user.svg') }}" alt="" srcset="">
              <span class="d-none d-md-block">مسدود کردن کاربر</span>
            </button>
            <button id="move-appointments-btn"
              class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center shadow-sm" disabled>
              <img src="{{ asset('dr-assets/icons/rescheule-appointment.svg') }}" alt="" srcset="">
              <span class="d-none d-md-block">جابجایی نوبت</span>
            </button>
            <button id="cancel-appointments-btn"
              class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center shadow-sm" disabled>
              <img src="{{ asset('dr-assets/icons/cancle-appointment.svg') }}" alt="" srcset="">
              <span class="d-none d-md-block">لغو نوبت</span>
            </button>
          </div>
        </div>
      </div>
      <div class="appointments-container">
        <div wire:loading wire:target="loadAppointments" class="loading-overlay-custom">
          <div class="spinner-custom"></div>
        </div>
        <div class="table-responsive position-relative w-100 d-none d-md-block">
          <table class="table table-hover w-100 text-sm text-center bg-white shadow-sm rounded">
            <thead class="bg-light">
              <tr>
                <th><input class="form-check-input" type="checkbox" id="select-all-row"></th>
                <th scope="col" class="px-6 py-3 fw-bolder">نام بیمار</th>
                <th scope="col" class="px-6 py-3 fw-bolder">شماره‌ موبایل</th>
                <th scope="col" class="px-6 py-3 fw-bolder">کد ملی</th>
                <th scope="col" class="px-6 py-3 fw-bolder">زمان نوبت</th>
                <th scope="col" class="px-6 py-3 fw-bolder">بیعانه</th>
                <th scope="col" class="px-6 py-3 fw-bolder">وضعیت پرداخت</th>
                <th scope="col" class="px-6 py-3 fw-bolder">بیمه</th>
                <th scope="col" class="px-6 py-3 fw-bolder">قیمت نهایی</th>
                <th scope="col" class="px-6 py-3 fw-bolder">پایان ویزیت</th>
                <th scope="col" class="px-6 py-3 fw-bolder">عملیات</th>
              </tr>
            </thead>
            <tbody>
              @if (count($appointments) > 0)
                @foreach ($appointments as $appointment)
                  <tr>
                    <td><input type="checkbox" class="appointment-checkbox form-check-input"
                        value="{{ $appointment->id }}" data-status="{{ $appointment->status }}"
                        data-mobile="{{ $appointment->patient->mobile ?? '' }}"
                        wire:model="cancelIds.{{ $appointment->id }}"></td>
                    <td class="fw-bold">
                      {{ $appointment->patient ? $appointment->patient->first_name . ' ' . $appointment->patient->last_name : '-' }}
                    </td>
                    <td>{{ $appointment->patient ? $appointment->patient->mobile : '-' }}</td>
                    <td>{{ $appointment->patient ? $appointment->patient->national_code : '-' }}</td>
                    <td>{{ Jalalian::fromCarbon(Carbon::parse($appointment->appointment_date))->format('Y/m/d') }}
                      <span class="fw-bold d-block">
                        {{ $appointment->appointment_time->format('H:i') ?? '-' }}
                      </span>
                    </td>
                    <td>
                      @if ($appointment->fee)
                        {{ number_format($appointment->fee) . ' تومان' }}
                      @else
                        ---
                      @endif
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
                        $paymentMethodLabels = [
                            'online' => 'آنلاین',
                            'cash' => 'نقدی',
                            'card_to_card' => 'کارت به کارت',
                            'pos' => 'کارتخوان',
                        ];
                        $paymentMethod = $appointment->payment_method ?? 'online';
                      @endphp
                      <span class="{{ $paymentStatusInfo['class'] }} fw-bold">
                        {{ $paymentStatusInfo['label'] }}
                        @if ($paymentStatus === 'paid')
                          ({{ $paymentMethodLabels[$paymentMethod] ?? '-' }})
                        @endif
                      </span>
                    </td>
                    <td>{{ $appointment->insurance ? $appointment->insurance->name : '-' }}</td>
                    <td>{{ $appointment->final_price ? number_format($appointment->final_price) . ' تومان' : '-' }}
                    </td>
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
                      @if ($status === 'scheduled')
                        <button class="btn btn-sm btn-primary-sm shadow-sm end-visit-btn" x-data
                          @click="$dispatch('open-modal', { name: 'end-visit-modal', appointmentId: {{ $appointment->id }} })">پایان
                          ویزیت</button>
                      @else
                        <span class="{{ $statusInfo['class'] }} fw-bold">{{ $statusInfo['label'] }}</span>
                      @endif
                    </td>
                    <td>
                      <div class="d-flex justify-content-center gap-2">
                        <x-custom-tooltip title="جابجایی نوبت" placement="top">
                          <button class="btn btn-light shadow-sm reschedule-btn" x-data
                            @click="$dispatch('open-modal', { name: 'reschedule-modal', appointmentId: {{ $appointment->id }} })"
                            {{ $appointment->status === 'cancelled' || $appointment->status === 'attended' ? 'disabled' : '' }}>
                            <img src="{{ asset('dr-assets/icons/rescheule-appointment.svg') }}" alt="جابجایی">
                          </button>
                        </x-custom-tooltip>
                        <x-custom-tooltip title="لغو نوبت" placement="top">
                          <button class="btn btn-light shadow-sm cancel-btn"
                            wire:click="cancelSingleAppointment({{ $appointment->id }})"
                            {{ $appointment->status === 'cancelled' || $appointment->status === 'attended' ? 'disabled' : '' }}>
                            <img src="{{ asset('dr-assets/icons/cancle-appointment.svg') }}" alt="حذف">
                          </button>
                        </x-custom-tooltip>
                        <x-custom-tooltip title="مسدود کردن کاربر" placement="top">
                          <button class="btn btn-light shadow-sm block-btn" x-data
                            @click="$dispatch('open-modal', { name: 'block-user-modal', appointmentId: {{ $appointment->id }} })">
                            <img src="{{ asset('dr-assets/icons/block-user.svg') }}" alt="مسدود کردن">
                          </button>
                        </x-custom-tooltip>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td colspan="12" class="text-center">
                    @if ($isSearchingAllDates && $searchQuery)
                      نتیجه‌ای یافت نشد
                    @else
                      نتیجه‌ای یافت نشد
                    @endif
                  </td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
        <!-- نمایش کارت‌ها در موبایل -->
        <div class="appointments-cards d-md-none">
          @if (count($appointments) > 0)
            @foreach ($appointments as $appointment)
              <div class="appointment-card" data-id="{{ $appointment->id }}">
                <div class="card-header">
                  <input type="checkbox" class="appointment-checkbox form-check-input"
                    value="{{ $appointment->id }}" data-status="{{ $appointment->status }}"
                    data-mobile="{{ $appointment->patient->mobile ?? '' }}"
                    wire:model="cancelIds.{{ $appointment->id }}">
                  <span
                    class="fw-bold">{{ $appointment->patient ? $appointment->patient->first_name . ' ' . $appointment->patient->last_name : '-' }}</span>
                  <button class="toggle-details" type="button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <path d="M6 9l6 6 6-6" stroke="#4a5568" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </button>
                </div>
                <div class="card-body">
                  <div class="card-item">
                    <span class="label">شماره موبایل:</span>
                    <span>{{ $appointment->patient ? $appointment->patient->mobile : '-' }}</span>
                  </div>
                  <div class="card-item">
                    <span class="label">زمان نوبت:</span>
                    <span>{{ Jalalian::fromCarbon(Carbon::parse($appointment->appointment_date))->format('Y/m/d') }}
                      {{ '  (' . $appointment->appointment_time->format('H:i') . ')' ?? '-' }}
                    </span>
                  </div>
                  <div class="card-item d-none details">
                    <span class="label">کد ملی:</span>
                    <span>{{ $appointment->patient ? $appointment->patient->national_code : '-' }}</span>
                  </div>
                  <div class="card-item d-none details">
                    <span class="label">بیعانه:</span>
                    <span>
                      @if ($appointment->fee)
                        {{ number_format($appointment->fee) . ' تومان' }}
                      @else
                        ---
                      @endif
                    </span>
                  </div>
                  <div class="card-item d-none details">
                    <span class="label">وضعیت پرداخت:</span>
                    @php
                      $paymentStatusInfo = $paymentStatusLabels[$appointment->payment_status] ?? [
                          'label' => 'نامشخص',
                          'class' => 'text-muted',
                      ];
                      $paymentMethod = $appointment->payment_method ?? 'online';
                    @endphp
                    <span class="{{ $paymentStatusInfo['class'] }} fw-bold">
                      {{ $paymentStatusInfo['label'] }}
                      @if ($appointment->payment_status === 'paid')
                        ({{ $paymentMethodLabels[$paymentMethod] ?? '-' }})
                      @endif
                    </span>
                  </div>
                  <div class="card-item d-none details">
                    <span class="label">بیمه:</span>
                    <span>{{ $appointment->insurance ? $appointment->insurance->name : '-' }}</span>
                  </div>
                  <div class="card-item d-none details">
                    <span class="label">قیمت نهایی:</span>
                    <span>{{ $appointment->final_price ? number_format($appointment->final_price) . ' تومان' : '-' }}</span>
                  </div>
                  <div class="card-item">
                    <span class="label">پایان ویزیت:</span>
                    @php
                      $status = $appointment->status ?? 'scheduled';
                      $statusInfo = $statusLabels[$status] ?? ['label' => 'نامشخص', 'class' => 'text-muted'];
                    @endphp
                    @if ($status === 'scheduled')
                      <button class="btn btn-sm btn-primary-sm shadow-sm end-visit-btn" x-data
                        @click="$dispatch('open-modal', { name: 'end-visit-modal', appointmentId: {{ $appointment->id }} })">پایان
                        ویزیت</button>
                    @else
                      <span class="{{ $statusInfo['class'] }} fw-bold">{{ $statusInfo['label'] }}</span>
                    @endif
                  </div>
                </div>
              </div>
            @endforeach
          @else
            <div class="text-center p-3">
              @if ($isSearchingAllDates && $searchQuery)
                نتیجه‌ای یافت نشد
              @else
                نتیجه‌ای یافت نشد
              @endif
            </div>
          @endif
        </div>
      </div>
    </div>
    <div wire:ignore>
      <x-modal name="mini-calendar-modal" title="انتخاب تاریخ" size="sm">
        <x-slot:body>
          <x-jalali-calendar />
        </x-slot:body>
      </x-modal>
    </div>
    <x-modal name="add-sick-modal" title="ثبت نوبت دستی" size="md">
      <x-slot:body>
        <form wire:submit.prevent="storeWithUser">
          <div class="row g-3">
            <div class="col-12">
              <div class="position-relative">
                <input type="text" class="form-control h-50" wire:model.live.debounce.500ms="searchQuery"
                  placeholder="جستجو با نام، نام خانوادگی، کد ملی یا شماره موبایل...">
                @if ($searchQuery && !$isSearching)
                  <div class="search-results position-absolute w-100 bg-white border rounded shadow-sm mt-1"
                    style="z-index: 1000; max-height: 300px; overflow-y: auto;">
                    @if (count($searchResults) > 0)
                      @foreach ($searchResults as $user)
                        <div class="p-2 hover-bg-light cursor-pointer" wire:click="selectUser({{ $user->id }})">
                          <div class="d-flex justify-content-between">
                            <span>{{ $user->first_name }} {{ $user->last_name }}</span>
                            <span class="text-muted">{{ $user->mobile }}</span>
                          </div>
                          <small class="text-muted">{{ $user->national_code }}</small>
                        </div>
                      @endforeach
                    @else
                      <div class="p-3 text-center">
                        <p class="mb-2">نتیجه‌ای یافت نشد</p>
                        <button type="button" class="btn btn-primary btn-sm"
                          wire:click="$dispatch('open-modal', { name: 'add-new-patient-modal' })">
                          افزودن بیمار جدید
                        </button>
                      </div>
                    @endif
                  </div>
                @endif
              </div>
            </div>
            @if ($selectedUserId)
              <div class="col-md-6 position-relative">
                <label class="label-top-input-special-takhasos">نام</label>
                <input type="text" class="form-control h-50" wire:model="firstName" required>
                @error('firstName')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-md-6 position-relative">
                <label class="label-top-input-special-takhasos">نام خانوادگی</label>
                <input type="text" class="form-control h-50" wire:model="lastName" required>
                @error('lastName')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-md-6 position-relative">
                <label class="label-top-input-special-takhasos">شماره موبایل</label>
                <input type="text" class="form-control h-50" wire:model="mobile" required>
                @error('mobile')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-md-6 position-relative">
                <label class="label-top-input-special-takhasos">کد ملی</label>
                <input type="text" class="form-control h-50" wire:model="nationalCode" required>
                @error('nationalCode')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-md-6 position-relative">
                <label class="label-top-input-special-takhasos">تاریخ نوبت</label>
                <input type="text" class="form-control h-50" data-jdp wire:model="appointmentDate" required>
                @error('appointmentDate')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-md-6 position-relative">
                <label class="label-top-input-special-takhasos">ساعت نوبت</label>
                <input type="text" class="form-control h-50" wire:model="appointmentTime" required readonly>
                @error('appointmentTime')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
            @endif
            <div class="col-12">
              <button type="submit" class="btn btn-primary w-100"
                @if (!$selectedUserId) disabled @endif>ثبت نوبت</button>
            </div>
          </div>
        </form>
      </x-slot:body>
    </x-modal>
    <x-modal name="add-new-patient-modal" title="افزودن بیمار جدید" size="lg">
      <x-slot:body>
        <form wire:submit.prevent="storeNewUser">
          <div class="row g-3">
            <div class="col-md-6 position-relative">
              <label class="label-top-input-special-takhasos">نام</label>
              <input type="text" class="form-control h-50" wire:model="newUser.firstName" required>
              @error('newUser.firstName')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-6 position-relative">
              <label class="label-top-input-special-takhasos">نام خانوادگی</label>
              <input type="text" class="form-control h-50" wire:model="newUser.lastName" required>
              @error('newUser.lastName')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-6 position-relative">
              <label class="label-top-input-special-takhasos">شماره موبایل</label>
              <input type="text" class="form-control h-50" wire:model="newUser.mobile" required>
              @error('newUser.mobile')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-6 position-relative">
              <label class="label-top-input-special-takhasos">کد ملی</label>
              <input type="text" class="form-control h-50" wire:model="newUser.nationalCode" required>
              @error('newUser.nationalCode')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary w-100">ثبت بیمار</button>
            </div>
          </div>
        </form>
      </x-slot:body>
    </x-modal>
    <x-modal name="paziresh-modal" title="ارجاع" size="sm">
      <x-slot:body>
        <form action="" method="post">
          <input type="text" class="my-form-control-light w-100" placeholder="کدملی/کداتباع بیمار">
          <input type="text" class="my-form-control-light w-100 mt-3" placeholder="کد پیگیری">
          <div class="mt-3 w-100">
            <button class="btn my-btn-primary w-100 h-50">ثبت</button>
          </div>
        </form>
      </x-slot:body>
    </x-modal>
    <div wire:ignore>
      <x-modal name="reschedule-modal" title="جابجایی نوبت" size="lg">
        <x-slot:body>
          <x-reschedule-calendar :appointmentId="$rescheduleAppointmentIds ? $rescheduleAppointmentIds : [$rescheduleAppointmentId]" />
        </x-slot:body>
      </x-modal>
    </div>
    <div wire:ignore>
      <x-modal name="block-user-modal" title="مسدود کردن کاربر" size="md">
        <x-slot:body>
          <form wire:submit.prevent="blockMultipleUsers">
            <div class="mb-4 position-relative">
              <label for="blockedAt" class="label-top-input-special-takhasos">تاریخ شروع مسدودیت</label>
              <input data-jdp type="text" class="form-control h-50" id="blockedAt" wire:model.live="blockedAt">
              @error('blockedAt')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="mb-4 position-relative">
              <label for="unblockedAt" class="label-top-input-special-takhasos">تاریخ پایان مسدودیت
                (اختیاری)</label>
              <input data-jdp type="text" class="form-control h-50" id="unblockedAt"
                wire:model.live="unblockedAt">
              @error('unblockedAt')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="mb-4 position-relative">
              <textarea class="form-control h-50" id="blockReason" rows="3" wire:model.live="blockReason"
                placeholder="دلیل مسدودیت را وارد کنید..."></textarea>
              @error('blockReason')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <button type="submit" class="btn my-btn-primary w-100 h-50">مسدود کردن</button>
          </form>
        </x-slot:body>
      </x-modal>
    </div>
    <div>
      <x-modal name="end-visit-modal" title="پایان ویزیت" size="lg">
        <x-slot:body>
          <form wire:submit.prevent="endVisit({{ $endVisitAppointmentId ?? 'null' }})">
            <div class="row g-2">
              <div class="col-12">
                <div class="border rounded p-2 bg-light">
                  <label class="form-label fw-bold mb-1">انتخاب بیمه</label>
                  @if (count($insurances) > 0)
                    @foreach ($insurances as $index => $insurance)
                      <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="selectedInsuranceId"
                          id="insurance_{{ $insurance['id'] }}" wire:model.live="selectedInsuranceId"
                          value="{{ $insurance['id'] }}">
                        <label class="form-check-label"
                          for="insurance_{{ $insurance['id'] }}">{{ $insurance['name'] }}</label>
                      </div>
                    @endforeach
                  @else
                    <p class="text-danger small mb-0">هیچ بیمه‌ای یافت نشد.</p>
                  @endif
                  @error('selectedInsuranceId')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="col-12">
                <div class="border rounded p-2 bg-light services-checkbox-container position-relative">
                  <div class="loading-overlay-custom {{ $isLoadingServices ? 'show-custom' : '' }}">
                    <div class="spinner-custom"></div>
                  </div>
                  <label class="form-label fw-bold mb-1">انتخاب خدمت</label>
                  <div class="checkbox-area" style="max-height: 100px; overflow-y: auto; padding: 0.25rem;">
                    @if (count($services) > 0)
                      @foreach ($services as $service)
                        <div class="form-check mb-1">
                          <input class="form-check-input" type="checkbox" id="service_{{ $service['id'] }}"
                            wire:model.live="selectedServiceIds" value="{{ $service['id'] }}"
                            wire:key="service-{{ $service['id'] }}">
                          <label class="form-check-label" for="service_{{ $service['id'] }}">
                            {{ $service['name'] }} ({{ number_format($service['price']) }} تومان)
                          </label>
                        </div>
                      @endforeach
                    @else
                      <p class="text-danger small mb-0">هیچ خدمتی یافت نشد.</p>
                    @endif
                  </div>
                  @error('selectedServiceIds')
                    <span class="alert alert-danger small">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <!-- ویزیت رایگان و تخفیف در یک ردیف -->
              <div class="col-12">
                <div class="border rounded p-2 bg-light mb-2">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="isFree" wire:model.live="isFree">
                    <label class="form-check-label text-success fw-bold" for="isFree">ویزیت رایگان</label>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <div class="row g-2">
                  <div class="col-md-6">
                    <div class="border rounded p-2 bg-light">
                      <label class="form-label fw-bold mb-1">تخفیف</label>
                      <div class="input-group input-group-sm align-items-center">
                        <input type="number" step="0.01" min="0" max="100" class="form-control"
                          style="height: 40px;" wire:model.live.debounce.500ms="discountPercentage" x-data
                          @click="$dispatch('open-modal', { name: 'discount-modal' })" placeholder="تخفیف (٪)"
                          @if ($isFree) disabled @endif readonly>
                        <span class="input-group-text" style="height: 40px;">٪</span>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="border rounded p-2 bg-light">
                      <label class="form-label fw-bold mb-1">نوع پرداخت</label>
                      <select class="form-select" wire:model.live="paymentMethod"
                        @if ($isFree) disabled @endif>
                        <option value="online">پرداخت آنلاین</option>
                        <option value="cash">پرداخت نقدی</option>
                        <option value="card_to_card">کارت به کارت</option>
                        <option value="pos">کارتخوان</option>
                      </select>
                      @error('paymentMethod')
                        <span class="text-danger small">{{ $message }}</span>
                      @enderror
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <div class="border rounded p-4 bg-white shadow-sm">
                  <h6 class="fw-bold mb-4 text-primary">جزئیات فاکتور</h6>
                  <!-- قیمت پایه -->
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">قیمت پایه:</span>
                    <span>{{ number_format($this->getBasePrice(), 0, '.', ',') }} تومان</span>
                  </div>
                  <!-- نمایش مبلغ تخفیف -->
                  @if ($discountPercentage > 0)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <span class="text-danger">مبلغ تخفیف:</span>
                      <span class="text-danger">-{{ number_format($discountAmount, 0, '.', ',') }} تومان</span>
                    </div>
                  @endif
                  <!-- نمایش بیعانه -->
                  @php
                    $appointment = \App\Models\Appointment::find($endVisitAppointmentId);
                    $fee = $appointment ? $appointment->fee : 0;
                  @endphp
                  @if ($fee > 0)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <span class="text-success">بیعانه پرداخت شده:</span>
                      <span class="text-success">-{{ number_format($fee, 0, '.', ',') }} تومان</span>
                    </div>
                  @endif
                  <!-- قیمت نهایی -->
                  <div class="border-top border-bottom border-success py-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="fw-bold text-success">قیمت نهایی:</span>
                      <span
                        class="fw-bold text-success fs-5">{{ number_format(max(0, $finalPrice - $fee), 0, '.', ',') }}
                        تومان</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <div class="border rounded p-2 bg-light">
                  <label class="form-label fw-bold mb-1">توضیحات درمان</label>
                  <textarea class="form-control h-50" rows="2" wire:model.live="endVisitDescription"
                    placeholder="توضیحات درمان را وارد کنید..."></textarea>
                </div>
              </div>
            </div>
            <button type="submit" class="btn my-btn-primary w-100 mt-2 position-relative">
              <span class="{{ $isSaving ? 'd-none' : '' }}">ثبت</span>
              <div class="spinner-custom-small {{ $isSaving ? 'show-spinner' : '' }} mx-auto"></div>
            </button>
          </form>
        </x-slot:body>
      </x-modal>
    </div>
    <div wire:ignore>
      <x-modal name="discount-modal" title="اعمال تخفیف" size="md">
        <x-slot:body>
          <form wire:submit.prevent="applyDiscount">
            <div class="mb-3 position-relative">
              <label class="label-top-input-special-takhasos">درصد تخفیف</label>
              <div class="input-with-spinner">
                <input type="number" class="form-control h-50"
                  wire:model.live.debounce.500ms="discountInputPercentage" placeholder="درصد تخفیف را وارد کنید"
                  min="0" max="100" step="0.01" @if ($isFree) disabled @endif>
                <div class="spinner-custom-small {{ $isLoadingDiscount ? 'show-spinner' : '' }}"></div>
              </div>
              @error('discountInputPercentage')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="mb-3 position-relative">
              <label class="label-top-input-special-takhasos">مبلغ تخفیف</label>
              <div class="input-with-spinner">
                <input type="number" class="form-control h-50" wire:model.live.debounce.500ms="discountInputAmount"
                  placeholder="مبلغ تخفیف را وارد کنید" min="0" step="1"
                  @if ($isFree) disabled @endif>
                <div class="spinner-custom-small {{ $isLoadingDiscount ? 'show-spinner' : '' }}"></div>
              </div>
              @error('discountInputAmount')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <button type="submit"
              class="btn my-btn-primary w-100 h-50 d-flex justify-content-center align-items-center position-relative"
              @if ($isFree) disabled @endif>
              <span class="{{ $isSaving ? 'd-none' : '' }}">تأیید</span>
              <div class="spinner-custom-small {{ $isSaving ? 'show-spinner' : '' }} mx-auto"></div>
            </button>
          </form>
        </x-slot:body>
      </x-modal>
    </div>
    <div>
      <x-modal name="time-selection-modal" title="انتخاب ساعت نوبت" size="md">
        <x-slot:body>
          <div class="time-selection-container">
            <div class="d-flex flex-wrap gap-2 justify-content-center" id="available-times">
              @if (empty($availableTimes))
                <div class="alert alert-info w-100 text-center">
                  هیچ ساعت خالی برای این تاریخ یافت نشد
                </div>
              @else
                @foreach ($availableTimes as $time)
                  <button type="button" class="btn btn-outline-primary m-1"
                    wire:click="selectTime('{{ $time }}')" wire:loading.attr="disabled">
                    {{ $time }}
                  </button>
                @endforeach
              @endif
            </div>
          </div>
          <div class="w-100 d-flex justify-content-end mt-3">
            <button type="button" class="btn my-btn-primary h-50 col-12" wire:click="selectTime">
              <span class="button_text">انتخاب ساعت</span>
            </button>
          </div>
        </x-slot:body>
      </x-modal>
    </div>
    <!-- Add new modal for reschedule time selection -->
    <div wire:ignore>
      <x-modal name="reschedule-time-modal" title="انتخاب ساعت نوبت جدید" size="md">
        <x-slot:body>
          <div class="time-selection-container">
            <div class="d-flex flex-wrap gap-2 justify-content-center" id="reschedule-available-times">
              @if (empty($availableTimes))
                <div class="alert alert-info w-100 text-center">
                  هیچ ساعت خالی برای این تاریخ یافت نشد
                </div>
              @else
                @foreach ($availableTimes as $time)
                  <button type="button" class="btn btn-outline-primary m-1 reschedule-time-btn"
                    data-time="{{ $time }}">
                    {{ $time }}
                  </button>
                @endforeach
              @endif
            </div>
          </div>
        </x-slot:body>
      </x-modal>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        // استفاده از Event Delegation برای مدیریت کلیک و لمس
        document.body.addEventListener('click', handleToggle);
        document.body.addEventListener('touchstart', handleToggle, {
          passive: false
        });
        // به‌روزرسانی پس از رندر Livewire
        Livewire.on('refresh', () => {
          console.log('Livewire refreshed, re-checking toggle buttons');
        });
      });
      function handleToggle(event) {
        const button = event.target.closest('.appointment-card .toggle-details');
        if (!button) return; // اگر کلیک روی دکمه toggle-details نبود، خارج شو
        event.preventDefault(); // جلوگیری از رفتار پیش‌فرض
        const card = button.closest('.appointment-card');
        if (!card) {
          return;
        }
        const details = card.querySelectorAll('.card-item.details');
        const isExpanded = card.classList.contains('expanded');
        const toggleIcon = button.querySelector('svg');
        if (!toggleIcon) {
          console.warn('Toggle icon (svg) not found in button');
          return;
        }
        if (isExpanded) {
          details.forEach(item => item.classList.add('d-none'));
          card.classList.remove('expanded');
          toggleIcon.style.transform = 'rotate(0deg)';
        } else {
          details.forEach(item => item.classList.remove('d-none'));
          card.classList.add('expanded');
          toggleIcon.style.transform = 'rotate(180deg)';
        }
      }
      window.holidaysData = @json($holidaysData);
      window.appointmentsData = @json($appointmentsData);
      document.addEventListener('livewire:initialized', () => {
        let updateTimeout;
        Livewire.on('calendarDataUpdated', () => {
          clearTimeout(updateTimeout);
          updateTimeout = setTimeout(() => {
            window.holidaysData = @json($holidaysData);
            window.appointmentsData = @json($appointmentsData);
          }, 100);
        });
        Livewire.on('toggle-loading', (event) => {
          const isLoading = event.isLoading !== undefined ? event.isLoading : false;
          setTimeout(() => {
            const loadingOverlay = document.querySelector('.loading-overlay-custom');
            if (loadingOverlay) {
              loadingOverlay.classList.toggle('show-custom', isLoading);
            } else {
              console.error('Loading overlay not found after timeout');
            }
          }, 200); // تاخیر 100 میلی‌ثانیه
        });
        document.addEventListener('DOMContentLoaded', () => {
          const loadingOverlay = document.querySelector('.loading-overlay-custom');
          console.log('Loading overlay exists:', !!loadingOverlay);
        });
        const observer = new MutationObserver((mutations) => {
          mutations.forEach((mutation) => {
            if (mutation.target.classList.contains('loading-overlay-custom')) {
              console.log('Loading overlay class changed:', mutation.target.classList.toString());
            }
          });
        });
        const loadingOverlay = document.querySelector('.loading-overlay-custom');
        if (loadingOverlay) {
          observer.observe(loadingOverlay, {
            attributes: true,
            attributeFilter: ['class']
          });
        } else {
          console.warn('Loading overlay not found for observer');
        }
        Livewire.on('refresh-appointments-list', () => {
          // اطمینان از رفرش کامل کامپوننت
          Livewire.dispatch('loadAppointments');
        });
        window.holidaysData = window.holidaysData || {
          status: false,
          holidays: []
        };
        window.appointmentsData = window.appointmentsData || {
          status: false,
          data: []
        };
        const clinicId = @json($selectedClinicId);
        if (clinicId && clinicId !== 'default') {
          localStorage.setItem('selectedClinicId', clinicId);
        }
        window.addEventListener('open-modal', event => {
          const modalId = event.detail.name;
          const appointmentId = event.detail.appointmentId || null;
          // Alpine خودش این ایونت رو هندل می‌کنه با x-on:open-modal.window
          // حالا Livewire
          if (appointmentId && modalId === 'reschedule-modal') {
            @this.set('rescheduleAppointmentId', appointmentId);
            @this.set('rescheduleAppointmentIds', [appointmentId]);
          } else if (modalId === 'reschedule-modal') {
            const selectedCheckboxes = document.querySelectorAll('.appointment-checkbox:checked');
            const selectedIds = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));
            @this.set('rescheduleAppointmentIds', selectedIds);
            @this.call('loadCalendarData');
          } else if (modalId === 'end-visit-modal' && appointmentId) {
            @this.set('endVisitAppointmentId', appointmentId);
          } else if (appointmentId && modalId === 'block-user-modal') {
            @this.set('blockAppointmentId', appointmentId);
          }
        });
        Livewire.on('refresh', () => {
          initializeDropdowns();
        });
        Livewire.on('no-results-found', (event) => {
          if (event.searchAll) {
            const tbody = document.querySelector('tbody');
            if (tbody) {
              tbody.innerHTML = `
              <tr>
                <td colspan="13" class="text-center">نتیجه‌ای یافت نشد</td>
              </tr>
            `;
            }
            const cardsContainer = document.querySelector('.appointments-cards');
            if (cardsContainer) {
              cardsContainer.innerHTML = `
              <div class="text-center p-3">نتیجه‌ای یافت نشد</div>
            `;
            }
          }
        });
        Livewire.on('show-partial-reschedule-confirm', (event) => {
          const data = event[0] || {};
          const {
            message,
            appointmentIds,
            newDate,
            nextDate,
            availableSlots
          } = data;
          if (!message || !appointmentIds || !newDate || !nextDate || !availableSlots) {
            console.error('Invalid data received in show-partial-reschedule-confirm', data);
            Swal.fire({
              title: 'خطا',
              text: 'داده‌های نامعتبر دریافت شد. لطفاً دوباره تلاش کنید.',
              icon: 'error',
              confirmButtonText: 'باشه',
            });
            return;
          }
          Swal.fire({
            title: 'تأیید جابجایی ناقص',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'بله، منتقل کن',
            cancelButtonText: 'خیر',
            reverseButtons: true,
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatchTo(
                'dr.panel.turn.schedule.appointments-list',
                'confirm-partial-reschedule',
                [appointmentIds, newDate, nextDate, availableSlots]
              );
              window.dispatchEvent(new CustomEvent('close-modal', {
                detail: {
                  name: 'reschedule-modal'
                },
              }));
            }
          });
        });
        Livewire.on('show-first-available-confirm', (event) => {
          const data = event[0] || {};
          const {
            message,
            appointmentIds,
            newDate,
            availableSlots,
            isFullCapacity,
            nextDate
          } = data;
          if (!message || !appointmentIds || !newDate || !availableSlots) {
            console.error('Invalid data in show-first-available-confirm', data);
            Swal.fire({
              title: 'خطا',
              text: 'داده‌های نامعتبر دریافت شد.',
              icon: 'error',
              confirmButtonText: 'باشه',
            });
            return;
          }
          const swalOptions = {
            title: 'تأیید جابجایی به اولین نوبت خالی',
            text: message,
            icon: isFullCapacity ? 'info' : 'warning',
            showCancelButton: true,
            confirmButtonText: isFullCapacity ? 'انتقال نوبت‌ها' : 'انتقال ناقص',
            cancelButtonText: 'لغو',
            reverseButtons: true,
          };
          if (!isFullCapacity) {
            swalOptions.showDenyButton = true;
            swalOptions.denyButtonText = 'انتقال کامل به تاریخ با ظرفیت کافی';
          }
          Swal.fire(swalOptions).then((result) => {
            if (result.isConfirmed) {
              if (isFullCapacity) {
                Livewire.dispatchTo(
                  'dr.panel.turn.schedule.appointments-list',
                  'rescheduleAppointment',
                  [appointmentIds, newDate]
                );
              } else {
                Livewire.dispatchTo(
                  'dr.panel.turn.schedule.appointments-list',
                  'confirm-partial-reschedule',
                  [appointmentIds, newDate, nextDate, availableSlots]
                );
              }
              window.dispatchEvent(new CustomEvent('close-modal', {
                detail: {
                  name: 'reschedule-modal'
                },
              }));
            } else if (result.isDenied && !isFullCapacity) {
              Livewire.dispatchTo(
                'dr.panel.turn.schedule.appointments-list',
                'rescheduleAppointment',
                [appointmentIds, nextDate]
              );
              window.dispatchEvent(new CustomEvent('close-modal', {
                detail: {
                  name: 'reschedule-modal'
                },
              }));
            }
          });
        });
        Livewire.on('services-updated', () => {
          Livewire.dispatch('get-services', {});
        });
        Livewire.on('services-received', (services) => {
          const container = document.querySelector('.services-checkbox-container .checkbox-area');
          if (container) {
            container.innerHTML = '';
            const flatServices = services[0] || [];
            if (flatServices.length === 0) {
              container.innerHTML = '<p class="text-danger">هیچ خدمتی یافت نشد.</p>';
              return;
            }
            flatServices.forEach(service => {
              const div = document.createElement('div');
              div.className = 'form-check';
              div.innerHTML = `
              <input class="form-check-input" type="checkbox"
                     id="service_${service.id}"
                     wire:model.live="selectedServiceIds"
                     value="${service.id}">
              <label class="form-check-label" for="service_${service.id}">
                  ${service.name} (${new Intl.NumberFormat('fa-IR').format(service.price)} تومان)
              </label>
            `;
              container.appendChild(div);
            });
          } else {
            console.warn('Services container not found');
          }
        });
        Livewire.on('close-modal', (event) => {
          const modalId = event?.name || (event && event[0]?.name) || null;
          if (modalId) {
            window.dispatchEvent(new CustomEvent('close-modal', {
              detail: {
                name: modalId
              }
            }));
          } else {
            document.querySelectorAll('.modal.show').forEach(modal => {
              if (modal.name) {
                window.dispatchEvent(new CustomEvent('close-modal', {
                  detail: {
                    name: modalId
                  }
                }));
              }
            });
          }
        });
        Livewire.on('final-price-updated', () => {
          const priceInput = document.querySelector('input[wire\\:model\\.live="finalPrice"]');
          const discountInput = document.querySelector(
            'input[wire\\:click*="$dispatch(\'open-modal\', { name: \'discount-modal\' })"]');
          const isFree = @this.get('isFree');
          const finalPrice = @this.get('finalPrice');
          const discountPercentage = @this.get('discountPercentage');
          if (priceInput) {
            priceInput.value = isFree ? '0 تومان' : `${new Intl.NumberFormat('fa-IR').format(finalPrice)} تومان`;
            priceInput.disabled = isFree;
          }
          if (discountInput) {
            discountInput.value = isFree ? '' : (discountPercentage ?
              `${parseFloat(discountPercentage).toFixed(2)}%` : '');
            discountInput.disabled = isFree;
          }
        });
        Livewire.on('discount-applied', (event) => {
          const percentage = parseFloat(event[0]?.percentage) || parseFloat(@this.get('discountPercentage')) || 0;
          const finalPrice = parseFloat(@this.get('finalPrice'));
          const isFree = @this.get('isFree');
          @this.set('discountPercentage', percentage);
          @this.set('finalPrice', finalPrice);
          const discountInput = document.querySelector('input[wire\\:model\\.live="discountPercentage"]');
          const priceInput = document.querySelector('input[wire\\:model\\.live="finalPrice"]');
          if (discountInput) {
            discountInput.value = isFree ? '' : (percentage ? `${percentage.toFixed(2)}%` : '0.00%');
            discountInput.disabled = isFree;
            discountInput.dispatchEvent(new Event('input'));
          }
          if (priceInput) {
            priceInput.value = isFree ? '0 تومان' : `${new Intl.NumberFormat('fa-IR').format(finalPrice)} تومان`;
            priceInput.disabled = isFree;
          }
          window.dispatchEvent(new CustomEvent('close-modal', {
            detail: {
              name: 'discount-modal'
            }
          }));
        });
        function initializeDropdowns() {
          const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
          dropdownElementList.map(function(dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
          });
        }
        document.addEventListener('DOMContentLoaded', initializeDropdowns);
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
            blockUsersBtn.disabled = false;
          }
        }
        function checkCheckboxes() {
          const checkboxes = document.querySelectorAll('.appointment-checkbox');
          const selectedCheckboxes = document.querySelectorAll('.appointment-checkbox:checked');
          selectAllCheckbox.checked = checkboxes.length > 0 && selectedCheckboxes.length === checkboxes.length;
          updateButtonStates();
        }
        if (selectAllCheckbox) {
          selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.appointment-checkbox');
            checkboxes.forEach(checkbox => {
              const status = checkbox.dataset.status;
              if (status !== 'cancelled' && status !== 'attended') {
                checkbox.checked = selectAllCheckbox.checked;
              }
            });
            updateButtonStates();
          });
        } else {
          console.warn('چک‌باکس انتخاب همه پیدا نشد');
        }
        document.addEventListener('change', function(e) {
          if (e.target.classList.contains('appointment-checkbox')) {
            checkCheckboxes();
          }
        });
        Livewire.on('confirm-cancel-single', (event) => {
          const appointmentId = event.id || (event[0] && event[0].id) || null;
          if (!appointmentId) {
            console.error('شناسه نوبت در confirm-cancel-single پیدا نشد', event);
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
              const ids = [parseInt(appointmentId)];
              @this.set('cancelIds', ids);
              @this.call('triggerCancelAppointments');
            }
          });
        });
        Livewire.on('appointments-cancelled', (event) => {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: event.message || 'نوبت(ها) با موفقیت لغو شد.',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
              toast.addEventListener('mouseenter', Swal.stopTimer);
              toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
          });
        });
        if (cancelAppointmentsBtn) {
          cancelAppointmentsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selected = Array.from(document.querySelectorAll('.appointment-checkbox:checked')).map(cb =>
              parseInt(cb.value));
            if (selected.length === 0) {
              console.warn('هیچ نوبت برای لغو گروهی انتخاب نشده');
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
                @this.set('cancelIds', selected);
                @this.call('triggerCancelAppointments');
              }
            });
          });
        } else {
          console.warn('دکمه لغو نوبت‌ها پیدا نشد');
        }
        if (moveAppointmentsBtn) {
          moveAppointmentsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedCheckboxes = document.querySelectorAll('.appointment-checkbox:checked');
            const selectedIds = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));
            if (selectedIds.length === 0) {
              Swal.fire({
                title: 'خطا',
                text: 'لطفاً حداقل یک نوبت را انتخاب کنید.',
                icon: 'error',
                confirmButtonText: 'باشه'
              });
              return;
            }
            @this.set('rescheduleAppointmentIds', selectedIds);
            window.dispatchEvent(new CustomEvent('open-modal', {
              detail: {
                name: 'reschedule-modal'
              }
            }));
          });
        } else {
          console.warn('دکمه جابجایی نوبت‌ها پیدا نشد');
        }
        if (blockUsersBtn) {
          blockUsersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedCheckboxes = document.querySelectorAll('.appointment-checkbox:checked');
            const mobiles = Array.from(selectedCheckboxes)
              .map(cb => cb.dataset.mobile)
              .filter(mobile => mobile);
            if (mobiles.length === 0) {
              Swal.fire({
                title: 'خطا',
                text: 'لطفاً حداقل یک کاربر را انتخاب کنید.',
                icon: 'error',
                confirmButtonText: 'باشه'
              });
              return;
            }
            @this.set('selectedMobiles', mobiles);
            window.open - modal('block-user-modal');
          });
        } else {
          console.warn('دکمه مسدود کردن کاربران پیدا نشد');
        }
        document.addEventListener('DOMContentLoaded', () => {
          const loadMoreTrigger = document.createElement('div');
          loadMoreTrigger.id = 'load-more-trigger';
          loadMoreTrigger.style.height = '1px';
          document.querySelector('.appointments-container').appendChild(loadMoreTrigger);
          const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && @this.pagination.current_page < @this.pagination.last_page) {
              @this.nextPage();
            }
          }, {
            threshold: 0.1
          });
          observer.observe(loadMoreTrigger);
          // به‌روزرسانی چک‌باکس‌ها
          checkCheckboxes();
        });
        Livewire.on('show-no-results-alert', (event) => {
          Swal.fire({
            title: 'نتیجه‌ای یافت نشد',
            text: 'هیچ نتیجه‌ای برای جستجوی شما یافت نشد. آیا می‌خواهید در همه سوابق و نوبت‌ها جستجو کنید؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'جستجوی همه',
            cancelButtonText: 'لغو',
            reverseButtons: true,
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('confirm-search-all-dates');
            }
          });
        });
        // Add event listener for time input click
        $(document).on('click', 'input[type="text"]', function(e) {
          e.preventDefault();
          const $input = $(this);
          if ($input.attr('readonly')) {
            window.dispatchEvent(new CustomEvent('open-modal', {
              detail: {
                name: 'time-selection-modal'
              }
            }));
            @this.call('openTimeSelectionModal');
          }
        });
        // Handle available times loaded event
        /*
                                                                                                    Livewire.on('available-times-loaded', (event) => {
                                                                                                      console.log('Available times loaded:', event);
                                                                                                      const times = event.times || [];
                                                                                                      const $container = $('#available-times');
                                                                                                      $container.empty();
                                                                                                      if (times.length === 0) {
                                                                                                        $container.html(
                                                                                                          '<div class="alert alert-info text-center w-100">هیچ ساعت خالی برای این تاریخ یافت نشد</div>');
                                                                                                        return;
                                                                                                      }
                                                                                                      times.forEach(time => {
                                                                                                        const $button = $(
                                                                                                          `<button type="button" class="btn btn-sm time-slot-btn btn-outline-primary m-1" data-time="${time}">
        ${time}
      </button>`
                                                                                                        );
                                                                                                        $container.append($button);
                                                                                                      });
                                                                                                      // Handle time selection
                                                                                                      $container.off('click', '.time-slot-btn').on('click', '.time-slot-btn', function() {
                                                                                                        const $btn = $(this);
                                                                                                        const time = $btn.data('time');
                                                                                                        // Remove selection from other buttons
                                                                                                        $('.time-slot-btn').removeClass('btn-primary').addClass('btn-outline-primary');
                                                                                                        // Select this button
                                                                                                        $btn.removeClass('btn-outline-primary').addClass('btn-primary');
                                                                                                        // Update Livewire component
                                                                                                        @this.set('appointmentTime', time);
                                                                                                      });
                                                                                                    });
                                                                                                    */
        // Handle modal close
        Livewire.on('close-modal', (event) => {
          const modalId = event?.name || (event && event[0]?.name) || null;
          if (modalId === 'time-selection-modal') {
            $('#available-times').empty();
            @this.set('isTimeSelectionModalOpen', false);
          }
        });
      });
      // Lazy Loading با IntersectionObserver
    </script>
  </div>
