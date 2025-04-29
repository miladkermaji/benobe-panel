@php
    use Morilog\Jalali\Jalalian;
    use Carbon\Carbon;
@endphp

<div class="d-flex justify-content-center top-s-wrapper flex-wrap">
  <x-custom-alert 
    id="my-alert" 
    type="success" 
    title="موفقیت!" 
    message="عملیات با موفقیت انجام شد." 
    size="md" 
    :show="true"
/>
    <div class="calendar-and-add-sick-section p-3 w-100">
        <div class="calendar-and-add-sick-section">
            <div class="c-a-wrapper">
                <button class="selectDate_datepicker__xkZeS" wire:click="$dispatch('openXModal', { id: 'mini-calendar-modal' })">
                    <span class="mx-1">{{ Jalalian::fromCarbon(Carbon::parse($selectedDate))->format('Y/m/d') }}</span>
                    <img src="http://127.0.0.1:8000/dr-assets/icons/calendar.svg" alt="تقویم">
                </button>
                <div class="turning_search-wrapper__loGVc">
                    <input type="text" class="my-form-control" placeholder="نام بیمار، شماره موبایل یا کد ملی ..."
                        wire:model.live.debounce.500ms="searchQuery">
                </div>
                <button class="my-btn-primary" wire:click="$dispatch('openXModal', { id: 'add-sick-modal' })">
                    ثبت نوبت دستی
                </button>
            </div>
        </div>
    </div>

    <div wire:ignore class="w-100">
        <x-jalali-calendar-row />
    </div>

    <div class="sicks-content h-100 w-100 position-relative border">
        <div>
            <div class="table-responsive position-relative top-table w-100">
                <table class="table table-hover w-100 text-sm text-center bg-white shadow-sm rounded">
                    <thead class="bg-light">
                        <tr>
                            <th><input class="form-check-input" type="checkbox" id="select-all-row"></th>
                            <th scope="col" class="px-6 py-3 fw-bolder">نام بیمار</th>
                            <th scope="col" class="px-6 py-3 fw-bolder">شماره‌ موبایل</th>
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
                                        <input type="checkbox" class="appointment-checkbox form-check-input" value="{{ $appointment->id }}"
                                            data-status="{{ $appointment->status }}" data-mobile="{{ $appointment->patient->mobile ?? '' }}"
                                            wire:model="cancelIds.{{ $appointment->id }}">
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
                                                wire:click="$dispatch('openXModal', { id: 'end-visit-modal', appointmentId: {{ $appointment->id }} })">
                                                پایان ویزیت
                                            </button>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <x-custom-tooltip title="جابجایی نوبت" placement="top">
                                                <button class="btn btn-light rounded-circle shadow-sm reschedule-btn"
                                                    wire:click="$dispatch('openXModal', { id: 'reschedule-modal', appointmentId: {{ $appointment->id }} })"
                                                    {{ $appointment->status === 'cancelled' || $appointment->status === 'attended' ? 'disabled' : '' }}>
                                                    <img src="{{ asset('dr-assets/icons/rescheule-appointment.svg') }}" alt="جابجایی">
                                                </button>
                                            </x-custom-tooltip>
                                            <x-custom-tooltip title="لغو نوبت" placement="top">
                                                <button class="btn btn-light rounded-circle shadow-sm cancel-btn"
                                                    wire:click="cancelSingleAppointment({{ $appointment->id }})"
                                                    {{ $appointment->status === 'cancelled' || $appointment->status === 'attended' ? 'disabled' : '' }}>
                                                    <img src="{{ asset('dr-assets/icons/cancle-appointment.svg') }}" alt="حذف">
                                                </button>
                                            </x-custom-tooltip>
                                            <x-custom-tooltip title="مسدود کردن کاربر" placement="top">
                                                <button class="btn btn-light rounded-circle shadow-sm block-btn"
                                                    wire:click="$dispatch('openXModal', { id: 'block-user-modal', appointmentId: {{ $appointment->id }} })">
                                                    <img src="{{ asset('dr-assets/icons/block-user.svg') }}" alt="مسدود کردن">
                                                </button>
                                            </x-custom-tooltip>
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
        <div class="d-flex justify-content-start gap-10 nobat-option w-100" wire:ignore>
            <div class="d-flex align-items-center m-2 gap-4">
                <div class="turning_filterWrapper__2cOOi">
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle h-30 fs-13" type="button" id="filterDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            فیلتر
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                            <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', '')">همه نوبت‌ها</a></li>
                            <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', 'scheduled')">در انتظار</a></li>
                            <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', 'cancelled')">لغو شده</a></li>
                            <li><a class="dropdown-item" href="#" wire:click="$set('filterStatus', 'attended')">ویزیت شده</a></li>
                            <li><a class="dropdown-item" href="#" wire:click="$set('dateFilter', 'current_week')">هفته جاری</a></li>
                            <li><a class="dropdown-item" href="#" wire:click="$set('dateFilter', 'current_month')">ماه جاری</a></li>
                            <li><a class="dropdown-item" href="#" wire:click="$set('dateFilter', 'current_year')">سال جاری</a></li>
                        </ul>
                    </div>
                </div>
                <button id="cancel-appointments-btn"
                    class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center shadow-sm" disabled>
                    <img src="{{ asset('dr-assets/icons/cancle-appointment.svg') }}" alt="" srcset="">
                    <span class="d-none d-md-block">لغو نوبت</span>
                </button>
                <button id="move-appointments-btn"
                    class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center shadow-sm"
                    wire:click="$dispatch('openXModal', { id: 'reschedule-modal' })" disabled>
                    <img src="{{ asset('dr-assets/icons/rescheule-appointment.svg') }}" alt="" srcset="">
                    <span class="d-none d-md-block">جابجایی نوبت</span>
                </button>
                <button id="block-users-btn" wire:click="$dispatch('openXModal', { id: 'block-user-modal' })"
                    class="btn btn-light h-30 fs-13 d-flex align-items-center justify-content-center shadow-sm" disabled>
                    <img src="{{ asset('dr-assets/icons/block-user.svg') }}" alt="" srcset="">
                    <span class="d-none d-md-block">مسدود کردن کاربر</span>
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

    <!-- مودال‌ها -->
    <div wire:ignore>
        <x-custom-modal id="mini-calendar-modal" title="انتخاب تاریخ" size="sm" :show="false">
            <x-jalali-calendar />
        </x-custom-modal>
    </div>

    <x-custom-modal id="add-sick-modal" title="ثبت نوبت دستی" size="md" :show="false">
        <form action="" method="post">
            <input type="text" class="my-form-control-light w-100" placeholder="کدملی/کداتباع">
            <div class="mt-2">
                <a class="text-decoration-none text-primary font-bold" href="#"
                    wire:click="$dispatch('openXModal', { id: 'paziresh-modal' })">پذیرش از مسیر ارجاع</a>
            </div>
            <div class="d-flex mt-2 gap-20">
                <button class="btn my-btn-primary w-50 h-50">تجویز نسخه</button>
                <button class="btn btn-outline-info w-50 h-50">ثبت ویزیت</button>
            </div>
        </form>
    </x-custom-modal>

    <x-custom-modal id="paziresh-modal" title="ارجاع" size="md" :show="false">
        <form action="" method="post">
            <input type="text" class="my-form-control-light w-100" placeholder="کدملی/کداتباع بیمار">
            <input type="text" class="my-form-control-light w-100 mt-3" placeholder="کد پیگیری">
            <div class="mt-3">
                <button class="btn my-btn-primary w-100 h-50">ثبت</button>
            </div>
        </form>
    </x-custom-modal>

    <div wire:ignore>
        <x-custom-modal id="reschedule-modal" title="جابجایی نوبت" size="lg" :show="false">
            <x-reschedule-calendar :appointmentId="$rescheduleAppointmentIds ? $rescheduleAppointmentIds : [$rescheduleAppointmentId]" />
        </x-custom-modal>
    </div>

    <div wire:ignore>
        <x-custom-modal id="block-user-modal" title="مسدود کردن کاربر" size="md" :show="false">
            <form wire:submit.prevent="{{ $blockAppointmentId ? 'blockUser' : 'blockMultipleUsers' }}">
                <div class="mb-4 position-relative">
                    <label for="blockedAt" class="label-top-input-special-takhasos">تاریخ شروع مسدودیت</label>
                    <input data-jdp type="text" class="form-control h-50" id="blockedAt" wire:model.live="blockedAt">
                    @error('blockedAt')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-4 position-relative">
                    <label for="unblockedAt" class="label-top-input-special-takhasos">تاریخ پایان مسدودیت (اختیاری)</label>
                    <input data-jdp type="text" class="form-control h-50" id="unblockedAt" wire:model.live="unblockedAt">
                    @error('unblockedAt')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-4 position-relative">
                    <textarea class="form-control" id="blockReason" rows="3" wire:model.live="blockReason"
                        placeholder="دلیل مسدودیت را وارد کنید..."></textarea>
                    @error('blockReason')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="btn my-btn-primary w-100 h-50">مسدود کردن</button>
            </form>
        </x-custom-modal>
    </div>

    <div wire:ignore>
        <x-custom-modal id="end-visit-modal" title="پایان ویزیت" size="md" :show="false">
            <div>
                <textarea class="form-control" rows="5" wire:model="endVisitDescription" placeholder="توضیحات درمان را وارد کنید..."></textarea>
                <button class="btn my-btn-primary w-100 mt-3 shadow-sm end-visit-btn" wire:click="endVisit({{ $endVisitAppointmentId }})">ثبت</button>
            </div>
        </x-custom-modal>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            window.addEventListener('openXModal', event => {
                const modalId = event.detail.id;
                const appointmentId = event.detail.appointmentId || null;
                window.openXModal(modalId);

                if (appointmentId && modalId === 'end-visit-modal') {
                    @this.set('endVisitAppointmentId', appointmentId);
                } else if (appointmentId && modalId === 'reschedule-modal') {
                    @this.set('rescheduleAppointmentId', appointmentId);
                    @this.set('rescheduleAppointmentIds', [appointmentId]);
                } else if (appointmentId && modalId === 'block-user-modal') {
                    @this.set('blockAppointmentId', appointmentId);
                    const appointmentCheckbox = document.querySelector(`.appointment-checkbox[value="${appointmentId}"]`);
                    if (appointmentCheckbox && appointmentCheckbox.dataset.mobile) {
                        @this.set('selectedMobiles', [appointmentCheckbox.dataset.mobile]);
                    }
                }
            });

            Livewire.on('hideModal', () => {
                const openModal = document.querySelector('.x-modal--visible');
                if (openModal) {
                    window.closeXModal(openModal.id);
                }
            });

            Livewire.on('refresh', () => {
                initializeDropdowns();
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

                    @this.set('rescheduleAppointmentIds', selected);
                    window.openXModal('reschedule-modal');
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
                    window.openXModal('block-user-modal');
                });
            } else {
                console.warn('دکمه مسدود کردن کاربران پیدا نشد');
            }

            document.addEventListener('DOMContentLoaded', () => {
                checkCheckboxes();
            });
        });
    </script>
</div>